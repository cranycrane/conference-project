<?php

namespace Database\Fixtures;

use App\Domain\Conference\Conference;
use App\Domain\Presentation\Presentation;
use App\Domain\Room\Room;
use App\Domain\User\User;
use DateTime;
use Doctrine\Persistence\ObjectManager;

class PresentationFixture extends AbstractFixture
{
	public function getOrder(): int
	{
		return 5;
	}

	public function load(ObjectManager $manager): void
	{
		foreach ($this->getPresentations() as $presentationData) {
			// Získání referencí uživatele, místnosti a konference
			/** @var User $speaker */
			$speaker = $this->getReference($presentationData['userReference']);

			/** @var Room $room */
			$room = $this->getReference($presentationData['roomReference']);

			/** @var Conference $conference */
			$conference = $this->getReference($presentationData['conferenceReference']);

			$presentation = new Presentation(
				$speaker,
				$conference,
				$presentationData['title'],
				$presentationData['description'],
				$presentationData['tags'],
				$presentationData['photo']
			);

			$presentation->startsAt = $presentationData['startsAt'];
			$presentation->endsAt = $presentationData['endsAt'];
			$presentation->room = $room;
			$presentation->setState($presentationData['state']);

			$referenceName = $presentationData['title'] === 'Future of AI' ? 'presentation-ai' : 'presentation-marketing';
			$this->addReference($referenceName, $presentation);

			$manager->persist($presentation);
		}

		$manager->flush();
	}


	/**
	 * Vrací data pro prezentace
	 * @return iterable
	 */
	protected function getPresentations(): iterable
	{
		yield [
			'userReference' => 'admin-user',
			'roomReference' => 'tech-room-101',
			'conferenceReference' => 'tech-conference',
			'title' => 'Future of AI',
			'description' => 'A deep dive into AI advancements.',
			'tags' => ['AI', 'Machine Learning', 'Innovation'],
			'photo' => null,
			'state' => Presentation::STATE_APPROVED,
			'startsAt' => new DateTime('2025-05-01 10:00:00'),
			'endsAt' => new DateTime('2025-05-01 11:30:00'),
		];

		yield [
			'userReference' => 'user-user',
			'roomReference' => 'marketing-room-202',
			'conferenceReference' => 'marketing-summit',
			'title' => 'Digital Marketing Trends',
			'description' => 'Exploring the future of digital marketing.',
			'tags' => ['Marketing', 'Digital', 'Trends'],
			'photo' => null,
			'state' => Presentation::STATE_APPROVED,
			'startsAt' => new DateTime('2025-06-15 11:00:00'),
			'endsAt' => new DateTime('2025-06-15 12:30:00'),
		];
	}
}
