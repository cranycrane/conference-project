<?php

namespace App\Model\Services;

use App\Domain\Conference\Conference;
use App\Domain\Presentation\Presentation;
use App\Domain\Presentation\PresentationRepository;
use App\Domain\Question\Question;
use App\Domain\Question\QuestionRepository;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Model\Exception\Logic\DuplicateEmailException;
use App\Model\Exception\Logic\InvalidArgumentException;
use App\Model\Security\Passwords;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Utils\Validators;

class QuestionService implements ICrudService {

	private QuestionRepository $questionRepository;
	private EntityManagerInterface $entityManager;

	public function __construct(
		EntityManagerInterface $entityManager
	) {
		$this->entityManager = $entityManager;
		/** @var QuestionRepository $this ->presentationRepository */
		$this->questionRepository = $entityManager->getRepository(Question::class);
	}

	/**
	 * @param array<string, scalar> $data
	 */
	public function create(array $data): Question {
		$user = $this->entityManager->getReference(User::class, $data['userId']);
		$presentation = $this->entityManager->getReference(Presentation::class, $data['conferenceId']);

		$question = new Question(
			$data['question'],
			$user,
			$presentation
		);

		$this->entityManager->persist($question);
		$this->entityManager->flush();

		return $question;
	}

	public function update(): void {
		$this->entityManager->flush();
	}

	public function delete($id): void {
		$question = $this->find($id);
		$this->entityManager->remove($question);
		$this->entityManager->flush();
	}

	public function find($id): ?Question {
		return $this->questionRepository->find($id);
	}

	public function findByUser(int $userId): ArrayCollection
	{
		return new ArrayCollection($this->questionRepository->findBy(['user' => $userId]));
	}

	public function findByPresentation(int $presentationId): ArrayCollection
	{
		return new ArrayCollection($this->questionRepository->findBy(['presentation' => $presentationId]));
	}

	public function findAll(): ArrayCollection {
		return new ArrayCollection($this->questionRepository->findAll());
	}
}
