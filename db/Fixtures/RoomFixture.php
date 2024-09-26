<?php

namespace Database\Fixtures;

use App\Domain\Conference\Conference;
use App\Domain\Room\Room;
use Doctrine\Persistence\ObjectManager;

class RoomFixture extends AbstractFixture
{
	public function getOrder(): int
	{
		return 4; // Zajistěte, že tato fixture bude načtena po ConferenceFixture
	}

	public function load(ObjectManager $manager): void
	{
		foreach ($this->getRooms() as $roomData) {
			// Získání reference konference
			/** @var Conference $conference */
			$conference = $this->getReference($roomData['conferenceReference']);

			$room = new Room(
				$roomData['address'],
				$roomData['roomNumber'],
				$conference
			);

			$referenceName = $roomData['roomNumber'] === '101A' ? 'tech-room-101' : 'marketing-room-202';
			$this->addReference($referenceName, $room);

			$manager->persist($room);
		}

		$manager->flush();
	}

	/**
	 * Vrací data místností pro fixture
	 * @return iterable
	 */
	protected function getRooms(): iterable
	{
		yield [
			'conferenceReference' => 'tech-conference',
			'address' => '123 Tech Street',
			'roomNumber' => '101A',
		];

		yield [
			'conferenceReference' => 'marketing-summit',
			'address' => '456 Business Blvd',
			'roomNumber' => '202B',
		];
	}
}
