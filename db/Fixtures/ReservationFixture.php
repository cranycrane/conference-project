<?php

namespace Database\Fixtures;

use App\Domain\Reservation\Reservation;
use App\Domain\User\User;
use App\Domain\Conference\Conference;
use Database\Fixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

class ReservationFixture extends AbstractFixture
{
	public function getOrder(): int
	{
		return 3; // Zajistěte, že tato fixture bude načtena po UserFixture a ConferenceFixture
	}

	public function load(ObjectManager $manager): void
	{
		foreach ($this->getReservations() as $reservationData) {
			// Získání uživatele a konference z referencí
			/** @var User $user */
			$user = $this->getReference($reservationData['userReference']);

			/** @var Conference $conference */
			$conference = $this->getReference($reservationData['conferenceReference']);

			$reservation = new Reservation(
				$reservationData['numOfPeople'],
				$reservationData['email'],
				$reservationData['firstName'],
				$reservationData['lastName'],
				$user,
				$conference
			);

			// Nastavení dalších atributů, pokud je potřeba
			$reservation->setState($reservationData['state']);

			$manager->persist($reservation);
		}

		$manager->flush();
	}

	/**
	 * Vrací data rezervací pro fixture
	 * @return iterable
	 */
	protected function getReservations(): iterable
	{
		yield [
			'userReference' => 'admin-user',
			'conferenceReference' => 'tech-conference',
			'numOfPeople' => 2,
			'email' => 'admin@admin.cz',
			'firstName' => 'Jakub',
			'lastName' => 'Jeřábek',
			'state' => Reservation::STATE_CONFIRMED,
		];

		yield [
			'userReference' => 'user-user',
			'conferenceReference' => 'marketing-summit',
			'numOfPeople' => 1,
			'email' => 'user@user.cz',
        	'firstName' => 'Vojtěch',
			'lastName' => 'Teichmann',
			'state' => Reservation::STATE_PAID,
		];
	}
}
