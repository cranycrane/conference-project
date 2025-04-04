<?php declare(strict_types = 1);

namespace Database\Fixtures;

use App\Domain\User\User;
use App\Model\Security\Passwords;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends AbstractFixture
{

	public function getOrder(): int
	{
		return 1;
	}

	public function load(ObjectManager $manager): void
	{
		foreach ($this->getUsers() as $user) {
			$entity = new User(
				$user['email'],
				$this->container->getByType(Passwords::class)->hash('admin'),
				$user['firstName'],
				$user['lastName']
			);
			$entity->activate();
			$entity->setRole($user['role']);

			$manager->persist($entity);

			$this->addReference($user['role'] === User::ROLE_ADMIN ? 'admin-user' : 'user-user', $entity);
		}
		$manager->flush();
	}


	/**
	 * @return mixed[]
	 */
	protected function getUsers(): iterable
	{
		yield [
			'email' => 'admin@admin.cz',
			'role' => User::ROLE_ADMIN,
			'firstName' => 'Jakub',
			'lastName' => 'Jeřábek',
		];

        yield [
            'email' => 'user@user.cz',
            'role' => User::ROLE_USER,
        	'firstName' => 'Vojtěch',
			'lastName' => 'Teichmann',
		];
	}

}
