<?php declare(strict_types = 1);

namespace App\Domain\Reservation;

use App\Domain\User\User;
use App\Model\Database\Repository\AbstractRepository;

/**
 * @method Reservation|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Reservation|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Reservation[] findAll()
 * @method Reservation[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Reservation>
 */
class ReservationRepository extends AbstractRepository
{
	public function findReservationsByUser($userId)
	{
		return $this->createQueryBuilder('r')
			->where('r.user = :userId')
			->setParameter('userId', $userId)
			->getQuery()
			->getResult();
	}

}
