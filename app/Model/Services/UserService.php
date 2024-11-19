<?php

namespace App\Model\Services;

use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Model\Exception\Logic\DuplicateEmailException;
use App\Model\Exception\Logic\InvalidArgumentException;
use App\Model\Security\Passwords;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Utils\Validators;

class UserService implements ICrudService
{
	const PasswordMinLength = 8;
	private UserRepository $userRepository;
	private EntityManagerInterface $entityManager;

	public function __construct(
		EntityManagerInterface $entityManager
	) {
		$this->entityManager = $entityManager;
		/** @var UserRepository $this->userRepository */
		$this->userRepository = $entityManager->getRepository(User::class);
	}

	/**
	 * @param array<string, scalar> $data
	 */
	public function create(array $data): User
	{
		if (!Validators::isEmail($data['email'])) {
			throw new InvalidArgumentException;
		}

		// Create User
		$user = new User(
			(string) $data['email'],
			Passwords::create()->hash(strval($data['password'] ?? md5(microtime()))),
			$data['firstName'],
			$data['lastName']
		);

		// Set role
		if (isset($data['role'])) {
			$user->setRole((string) $data['role']);
		}

		// Save user
		try {
			$this->entityManager->persist($user);
			$this->entityManager->flush();
			$user->activate();
		} catch(UniqueConstraintViolationException) {
			throw new DuplicateEmailException();
		}

		return $user;
	}

	public function changePassword($userId, $newPassword): void {
		$user = $this->find($userId);
		$passwordHash = Passwords::create()->hash(strval($newPassword ?? md5(microtime())));
		$user->changePasswordHash($passwordHash);

		$this->entityManager->flush();
	}

	public function changeEmail($userId, $newEmail): void {
		$user = $this->find($userId);
		$user->email = $newEmail;

		$this->entityManager->flush();
	}

	public function update(User $user): void
	{
		$this->entityManager->persist($user);
		$this->entityManager->flush();
	}

	public function delete($id): void
	{
		$user = $this->find($id);
		$this->entityManager->remove($user);
		$this->entityManager->flush();
	}

	public function find($id): ?User
	{
		return $this->userRepository->find($id);
	}

	public function findAll(): ArrayCollection
	{
		return new ArrayCollection($this->userRepository->findAll());
	}

}
