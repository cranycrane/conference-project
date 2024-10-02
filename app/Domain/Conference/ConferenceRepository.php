<?php

namespace App\Domain\Conference;

use Doctrine\ORM\EntityRepository;

class ConferenceRepository extends EntityRepository
{
    // Zde můžete přidat vlastní metody specifické pro konference
    public function findActiveConferences()
    {
        return $this->createQueryBuilder('c')
            ->where('c.active = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }
}
