<?php

namespace App\Model\Services;

use App\Domain\Room\Room;
use App\Domain\Room\RoomRepository;
use App\Model\Exception\Logic\InvalidArgumentException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Nette\Utils\Validators;

class RoomService implements ICrudService {

	private RoomRepository $RoomRepository;
	private EntityManagerInterface $entityManager;

	public function __construct(
		EntityManagerInterface $entityManager
	) {
		$this->entityManager = $entityManager;
		/** @var RoomRepository $this->RoomRepository */
		$this->RoomRepository = $entityManager->getRepository(Room::class);
	}

	/**
	 * @param array<string, scalar> $data
	 */
	public function create(array $data): Room
	{
		$Room = new Room(
			$data['address'],
			$data['roomNumber'],
			$data['conference'] 
		);

		// Save Room
		$this->entityManager->persist($Room);
		$this->entityManager->flush();

		return $Room;
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

	public function findAll(): ArrayCollection
	{
		return new ArrayCollection($this->RoomRepository->findAll());
	}
}
