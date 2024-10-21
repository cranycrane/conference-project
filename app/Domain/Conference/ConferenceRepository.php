<?php

namespace App\Domain\Conference;

use Doctrine\ORM\EntityRepository;

class ConferenceRepository extends EntityRepository
{

	/**
	 *
	 * @return Conference[]
	 */
	public function find5UpcomingConferences(): array
	{
		return $this->createQueryBuilder('c')
			->where('c.endsAt >= :now')
			->setParameter('now', new \DateTime())
			->orderBy('c.endsAt', 'ASC')
			->setMaxResults(5)
			->getQuery()
			->getResult();
	}

    public function findActiveConferences()
    {
        return $this->createQueryBuilder('c')
            ->where('c.active = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }
}
