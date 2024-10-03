<?php

namespace App\Model\Services;

use App\Domain\Presentation\Presentation;
use App\Domain\Presentation\PresentationRepository;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Model\Exception\Logic\DuplicateEmailException;
use App\Model\Exception\Logic\InvalidArgumentException;
use App\Model\Security\Passwords;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Utils\Validators;

class PresentationService implements ICrudService {

	private PresentationRepository $presentationRepository;
	private EntityManagerInterface $entityManager;

	public function __construct(
		EntityManagerInterface $entityManager
	) {
		$this->entityManager = $entityManager;
		/** @var PresentationRepository $this->presentationRepository */
		$this->presentationRepository = $entityManager->getRepository(Presentation::class);
	}

	/**
	 * @param array<string, scalar> $data
	 */
	public function create(array $data): Presentation
	{
		$presentation = new Presentation(
			$data['user'],
			$data['title'],
			$data['description'] ?? null,
			$data['tags'] ?? null,
			$data['photo'] ?? null
		);

		// Save presentation
		$this->entityManager->persist($presentation);
		$this->entityManager->flush();

		return $presentation;
	}

	public function findByBySpeaker(int $speakerId): ArrayCollection {
		return new ArrayCollection($this->presentationRepository->findPresentationsBySpeaker($speakerId));
	}

  public function findByConference(int $conferenceId): ArrayCollection
  {
    return new ArrayCollection($this->presentationRepository->findBy(['conference' => $conferenceId]));
  }

  public function findUpcomingPresentationsWithMostAttendances(): ArrayCollection {
    return new ArrayCollection($this->presentationRepository->findUpcomingPresentationsWithMostAttendances());
  }

	public function delete($id): void
	{
		$user = $this->find($id);
		$this->entityManager->remove($user);
		$this->entityManager->flush();
	}

	public function find($id): Presentation
	{
		return $this->presentationRepository->find($id);
	}

	public function findAll(): ArrayCollection
	{
		return new ArrayCollection($this->presentationRepository->findAll());
	}
}
