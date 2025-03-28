<?php

namespace App\Model\Services;

use App\Domain\Conference\Conference;
use App\Domain\Conference\ConferenceRepository;
use App\Domain\Presentation\Presentation;
use App\Domain\Presentation\PresentationRepository;
use App\Domain\Reservation\Reservation;
use App\Domain\Reservation\ReservationRepository;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Model\Exception\Logic\DuplicateEmailException;
use App\Model\Exception\Logic\InvalidArgumentException;
use App\Model\Exception\Logic\NoCapacityException;
use App\Model\Security\Passwords;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Utils\Validators;

class ReservationService implements ICrudService {

	private ReservationRepository $reservationRepository;
	private ConferenceRepository $conferenceRepository;
	private EntityManagerInterface $entityManager;

	public function __construct(
		EntityManagerInterface $entityManager
	) {
		$this->entityManager = $entityManager;
		/** @var ReservationRepository $this ->presentationRepository */
		$this->reservationRepository = $entityManager->getRepository(Reservation::class);
		/** @var ConferenceRepository $this ->conferenceRepository */
		$this->conferenceRepository = $entityManager->getRepository(Conference::class);
	}

	/**
	 * @param array<string, scalar> $data
	 */
	public function create(array $data): Reservation {
		$user = $data['userId'] ? $this->entityManager->getReference(User::class, $data['userId']) : null;
		$conference = $this->entityManager->getReference(Conference::class, $data['conferenceId']);

		$remainingCapacity = $conference->capacity - $conference->getNumOfAttendees();
		if ($remainingCapacity < $data['numOfPeople']) {
			throw new NoCapacityException();
		}

		$reservation = new Reservation(
			$data['numOfPeople'],
			$data['email'],
			$data['firstName'],
			$data['lastName'],
			$user,
			$conference
		);

		if($conference->priceForSeat === 0) {
			$reservation->setState(Reservation::STATE_PAID);
		}


		// Save reservation
		$this->entityManager->persist($reservation);
		$this->entityManager->flush();

		return $reservation;
	}

	public function update(): void {
		$this->entityManager->flush();
	}

	public function delete($id): void {
		$reservation = $this->find($id);
		$this->entityManager->remove($reservation);
		$this->entityManager->flush();
	}

	public function find($id): ?Reservation {
		return $this->reservationRepository->find($id);
	}

	public function findByUser(int $userId): ArrayCollection
	{
		return new ArrayCollection($this->reservationRepository->findBy(['user' => $userId]));
	}

	public function findByConference(int $conferenceId): ArrayCollection
	{
		return new ArrayCollection($this->reservationRepository->findBy(['conference' => $conferenceId]));
	}

	public function findAll(): ArrayCollection {
		return new ArrayCollection($this->reservationRepository->findAll());
	}

	public function getRemainingCapacity(int $conferenceId): int {
		$conference = $this->conferenceRepository->find($conferenceId);
		if (!$conference) {
			throw new \Exception('Konference neexistuje.');
		}

		$capacity = $conference->capacity;
		$reserved = $conference->getNumOfAttendees();

		return $capacity - $reserved;
	}
}
