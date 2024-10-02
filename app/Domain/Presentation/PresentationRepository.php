<?php declare(strict_types = 1);

namespace App\Domain\Presentation;

use App\Domain\Reservation\Reservation;
use App\Domain\User\User;
use App\Model\Database\Repository\AbstractRepository;

/**
 * @method Presentation|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Presentation|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Presentation[] findAll()
 * @method Presentation[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Presentation>
 */
class PresentationRepository extends AbstractRepository
{

}
