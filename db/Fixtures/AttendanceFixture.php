<?php

namespace Database\Fixtures;

use App\Domain\Attendance\Attendance;
use App\Domain\Presentation\Presentation;
use App\Domain\User\User;
use Doctrine\Persistence\ObjectManager;

class AttendanceFixture extends AbstractFixture
{
	public function getOrder(): int
	{
		return 6;
	}

	public function load(ObjectManager $manager): void
	{
		foreach ($this->getAttendances() as $attendanceData) {
			// Získání referencí uživatele a prezentace
			/** @var User $user */
			$user = $this->getReference($attendanceData['userReference']);

			/** @var Presentation $presentation */
			$presentation = $this->getReference($attendanceData['presentationReference']);

			$attendance = new Attendance($user, $presentation);

			$manager->persist($attendance);
		}

		$manager->flush();
	}

	protected function getAttendances(): iterable
	{
		yield [
			'userReference' => 'admin-user',
			'presentationReference' => 'presentation-ai',
		];

		yield [
			'userReference' => 'user-user',
			'presentationReference' => 'presentation-marketing',
		];
	}
}
