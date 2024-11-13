<?php declare(strict_types = 1);

namespace App\Domain\Question;

use App\Model\Database\Repository\AbstractRepository;

/**
 * @method Question|NULL find($id, ?int $lockMode = NULL, ?int $lockVersion = NULL)
 * @method Question|NULL findOneBy(array $criteria, array $orderBy = NULL)
 * @method Question[] findAll()
 * @method Question[] findBy(array $criteria, array $orderBy = NULL, ?int $limit = NULL, ?int $offset = NULL)
 * @extends AbstractRepository<Question>
 */
class QuestionRepository extends AbstractRepository
{

}
