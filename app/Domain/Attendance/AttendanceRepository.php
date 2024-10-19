<?php declare(strict_types = 1);

namespace App\Domain\Attendance;

use App\Domain\Reservation\Reservation;
use App\Domain\User\User;
use App\Model\Database\Repository\AbstractRepository;

/**
 * @method Attendance|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Attendance|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Attendance[] findAll()
 * @method Attendance[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Attendance>
 */
class AttendanceRepository extends AbstractRepository
{
	public function findAttendancesByUser($userId)
	{
		return $this->createQueryBuilder('r')
			->where('r.user = :userId')
			->setParameter('userId', $userId)
			->getQuery()
			->getResult();
	}

	public function findUserSchedule(int $userId, int $conferenceId): array {
		return $this->createQueryBuilder('a')
			->innerJoin('a.presentation', 'p')
			->innerJoin('p.conference', 'c') // Předpokládáme, že prezentace je spojená s konferencí
			->where('a.user = :userId')
			->andWhere('c.id = :conferenceId')
			->setParameter('userId', $userId)
			->setParameter('conferenceId', $conferenceId)
			->getQuery()
			->getResult();
	}

}
