<?php

namespace Database\Fixtures;

use App\Domain\Conference\Conference;
use App\Domain\User\User;
use Doctrine\Persistence\ObjectManager;
use DateTime;

class ConferenceFixture extends AbstractFixture
{
	public function getOrder(): int
	{
		return 2; // Zajistěte, že tato fixture bude načtena po UserFixture
	}

	public function load(ObjectManager $manager): void
	{
		foreach ($this->getConferences() as $conferenceData) {
			// Získání uživatele z reference (musíte přidat reference v UserFixture)
			/** @var User $user */
			$user = $this->getReference($conferenceData['userReference']);

			$conference = new Conference(
				$user,
				$conferenceData['title'],
				$conferenceData['genre'],
				$conferenceData['place'],
				$conferenceData['startsAt'],
				$conferenceData['endsAt'],
				$conferenceData['priceForSeat'],
				$conferenceData['capacity'],
				$conferenceData['description']
			);

			$conference->tags = $conferenceData['tags'];
			$conference->setState($conferenceData['state']);

			$referenceName = $conferenceData['title'] === 'Tech Conference 2024' ? 'tech-conference' : 'marketing-summit';
			$this->addReference($referenceName, $conference);

			$manager->persist($conference);
		}

		$manager->flush();
	}

	/**
	 * Vrací data konferencí pro fixture
	 * @return iterable
	 */
	protected function getConferences(): iterable
	{
		yield [
			'userReference' => 'admin-user',
			'title' => 'Tech Conference 2024',
			'genre' => 'Technology',
			'place' => 'Prague',
			'startsAt' => new DateTime('2025-05-01 09:00:00'),
			'endsAt' => new DateTime('2025-05-01 17:00:00'),
			'priceForSeat' => 1000,
			'capacity' => 200,
			'description' => 'A conference about the latest in technology.',
			'tags' => ['Tech', 'Innovation', 'Future'],
			'state' => Conference::STATE_CREATED,
		];

		yield [
			'userReference' => 'user-user',
			'title' => 'Marketing Summit 2024',
			'genre' => 'Marketing',
			'place' => 'Brno',
			'startsAt' => new DateTime('2025-06-15 10:00:00'),
			'endsAt' => new DateTime('2025-06-15 16:00:00'),
			'priceForSeat' => 750,
			'capacity' => 150,
			'description' => 'Insights and trends in marketing for 2024.',
			'tags' => ['Marketing', 'Business', 'Trends'],
			'state' => Conference::STATE_ONGOING,
		];
	}
}
