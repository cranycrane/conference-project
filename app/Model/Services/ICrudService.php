<?php

namespace App\Model\Services;

use App\Model\Database\Entity\AbstractEntity;
use Doctrine\Common\Collections\ArrayCollection;

interface ICrudService {

	public function create(array $data): AbstractEntity;
	public function delete(int $id): void;
	public function find(int $id): ?AbstractEntity;
	public function findAll(): ArrayCollection;
}
