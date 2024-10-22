<?php

namespace App\Model\Services;

use App\Domain\Room\Room;
use App\Domain\Room\RoomRepository;
use App\Domain\Conference\Conference;
use App\Domain\Conference\ConferenceRepository;
use App\Model\Exception\Logic\InvalidArgumentException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Utils\Validators;

class RoomService implements ICrudService {

	private RoomRepository $RoomRepository;
	private ConferenceRepository $ConferenceRepository;
	private EntityManagerInterface $entityManager;

	public function __construct(
		EntityManagerInterface $entityManager
	) {
		$this->entityManager = $entityManager;
		/** @var RoomRepository $this->RoomRepository */
		$this->RoomRepository = $entityManager->getRepository(Room::class);
		/** @var ConferenceRepository $this->ConferenceRepository */
		$this->ConferenceRepository = $entityManager->getRepository(Conference::class);
		
	}

	/**
	 * @param array<string, scalar> $data
	 */
	public function create(array $data): Room
	{
		$conference = $this->entityManager->getReference(Conference::class, $data['conferenceId']);
		$Room = new Room(
			$data['address'],
			$data['roomNumber'],
			$conference
		);

		// Save Room
		$this->entityManager->persist($Room);
		$this->entityManager->flush();

		return $Room;
	}

	public function update(): void
	{
		$this->entityManager->flush();
	}


	public function saveRoom($values, ?int $conferenceId): Room
	{
		$conference = $this->ConferenceRepository->find($conferenceId);
		if (!$conference) {
			throw new \Exception('Conference not found');
		}

		if (isset($values->id) && $values->id) {
			$room = $this->find($values->id);

			if (!$room) {
				throw new \Exception('Místnost nebyla nalezena.');
			}

			// Zde aktualizujeme hodnoty místnosti
			$room->roomNumber = $values->roomNumber;
			$room->address = $values->address;
			$this->entityManager->flush();
		} else {
			$room = new Room(
				$values->roomNumber,
				$values->address,
				$conference
			);

			$this->entityManager->persist($room);
			$this->entityManager->flush();
		}

		return $room;
	}




	public function delete($id): void
	{
		$user = $this->find($id);
		$this->entityManager->remove($user);
		$this->entityManager->flush();
	}

	public function find($id): Room
	{
		return $this->RoomRepository->find($id);
	}

	public function findByConference(int $conferenceId): ArrayCollection
	{
		return new ArrayCollection($this->RoomRepository->findBy(['conference' => $conferenceId]));
	}

	public function findAll(): ArrayCollection
	{
		return new ArrayCollection($this->RoomRepository->findAll());
	}

	
}
