<?php

namespace App\Model\Services;

use App\Domain\Attendance\Attendance;
use App\Domain\Conference\Conference;
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
    $user = $this->entityManager->getReference(User::class, $data['userId']);
    $conference = $this->entityManager->getReference(Conference::class, $data['conferenceId']);

		$presentation = new Presentation(
			$user,
      $conference,
			$data['title'],
			$data['description'] ?? null,
			$data['tags'] ?? null,
			$data['photo'] ?? null
		);

		if($data['photoImage']->isOk()) {
			$presentation->setPhotoUpload($data['photoImage']);
		}

		// Save presentation
		$this->entityManager->persist($presentation);
		$this->entityManager->flush();

		return $presentation;
	}


	public function findUserSchedule(int $userId, int $conferenceId): ArrayCollection {
		$attendances = $this->entityManager->getRepository(Attendance::class)->findUserSchedule($userId, $conferenceId);
		$presentations = array_map(fn($attendance) => $attendance->presentation, $attendances);
		return new ArrayCollection($presentations);
	}

	public function groupPresentationsByDay(ArrayCollection $presentations): ArrayCollection {
		$grouped = [];

		foreach ($presentations as $presentation) {
			$day = $presentation->startsAt->format('Y-m-d');
			if (!isset($grouped[$day])) {
				$grouped[$day] = [];
			}
			$grouped[$day][] = $presentation;
		}

		ksort($grouped);

		return new ArrayCollection($grouped);
	}

	public function sortPresentationsByTime(ArrayCollection $groupedPresentations): ArrayCollection {
		foreach ($groupedPresentations as $day => &$presentations) {
			usort($presentations, function($a, $b) {
				return $a->startsAt <=> $b->startsAt;
			});
		}

		return $groupedPresentations;
	}

  public function update(): void
  {
    $this->entityManager->flush();
  }

	public function findByBySpeaker(int $speakerId): ArrayCollection {
		return new ArrayCollection($this->presentationRepository->findPresentationsBySpeaker($speakerId));
	}

	public function findByConferenceApproved(int $conferenceId): ArrayCollection
	{
		return new ArrayCollection(
			$this->presentationRepository->findBy([
				'conference' => $conferenceId,
				'state' => 2
			])
		);
	}

	public function findByConferenceNotApproved(int $conferenceId): ArrayCollection
	{
		return new ArrayCollection(
			$this->presentationRepository->findBy([
				'conference' => $conferenceId,
				'state' => [1, 3]
			])
		);
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

	public function find($id): ?Presentation
	{
		return $this->presentationRepository->find($id);
	}

	public function findAll(): ArrayCollection
	{
		return new ArrayCollection($this->presentationRepository->findAll());
	}

	public function findByUser(?int $userId): ArrayCollection
	{
		if ($userId === null) {
			// Vrátíme prázdnou kolekci, pokud je uživatel null
			return new ArrayCollection();
		}

		return new ArrayCollection($this->presentationRepository->findBy(criteria: ['speaker' => $userId]));
	}

	public function findByConference(int $conferenceId): ArrayCollection
	{
		return new ArrayCollection($this->presentationRepository->findBy(['conference' => $conferenceId]));
	}
}
