<?php

namespace App\Model\Services;


use App\Model\Database\Entity\AbstractEntity;
use App\Model\Database\Repository\AbstractRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class EntityCrudHelper {

	protected EntityManagerInterface $entityManager;

	public function __construct(EntityManagerInterface $entityManager) {
		$this->entityManager = $entityManager;
	}

	public function create($entity): void
	{
		$this->entityManager->persist($entity);
		$this->entityManager->flush();
	}

	public function update($entity): void
	{
		$this->entityManager->flush();
	}

	public function delete($entity): void
	{
		$this->entityManager->remove($entity);
		$this->entityManager->flush();
	}

	public function find($repository, $id)
	{
		return $repository->find($id);
	}

	public function findAll($repository)
	{
		return $repository->findAll();
	}
}
