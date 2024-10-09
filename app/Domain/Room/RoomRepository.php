<?php declare(strict_types = 1);

namespace App\Domain\Room;

use App\Domain\Room\Room;
use App\Domain\User\User;
use App\Model\Database\Repository\AbstractRepository;

/**
 * @method Room|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Room|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Room[] findAll()
 * @method Room[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Room>
 */
class RoomRepository extends AbstractRepository
{
    
}
