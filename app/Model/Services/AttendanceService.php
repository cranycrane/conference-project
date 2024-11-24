<?php

namespace App\Model\Services;

use App\Domain\Conference\Conference;
use App\Domain\Presentation\Presentation;
use App\Domain\Presentation\PresentationRepository;
use App\Domain\Attendance\Attendance;
use App\Domain\Attendance\AttendanceRepository;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Model\Exception\Logic\DuplicateEmailException;
use App\Model\Exception\Logic\InvalidArgumentException;
use App\Model\Security\Passwords;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Utils\Validators;

class AttendanceService implements ICrudService {

	private AttendanceRepository $attendanceRepository;
	private EntityManagerInterface $entityManager;

	public function __construct(
		EntityManagerInterface $entityManager
	) {
		$this->entityManager = $entityManager;
		/** @var AttendanceRepository $this ->presentationRepository */
		$this->attendanceRepository = $entityManager->getRepository(Attendance::class);
	}

	/**
	 * @param array<string, scalar> $data
	 */
	public function create(array $data): Attendance {
		$user = $this->entityManager->getReference(User::class, $data['userId']);
		$presentation = $this->entityManager->getReference(Presentation::class, $data['presentationId']);

		$attendance = new Attendance(
			$user,
			$presentation
		);

		// Save attendance
		$this->entityManager->persist($attendance);
		$this->entityManager->flush();

		return $attendance;
	}

	public function update(): void {
		$this->entityManager->flush();
	}

	public function delete($id): void {
		$user = $this->find($id);
		$this->entityManager->remove($user);
		$this->entityManager->flush();
	}

	public function find($id): ?Attendance {
		return $this->attendanceRepository->find($id);
	}

	public function findAll(): ArrayCollection {
		return new ArrayCollection($this->attendanceRepository->findAll());
	}
}
