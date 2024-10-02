<?php

namespace App\Model\Services;

use App\Domain\Conference\Conference;
use App\Domain\Conference\ConferenceRepository;
use App\Domain\User\UserRepository;
use App\Domain\User\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use App\Model\Exception\Logic\InvalidArgumentException;
use Nette\Security\User as NetteUser;

class ConferenceService
{
    private ConferenceRepository $conferenceRepository;
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private NetteUser $netteUser;

    public function __construct(EntityManagerInterface $entityManager, NetteUser $netteUser)
    {
        $this->entityManager = $entityManager;
        $this->conferenceRepository = $entityManager->getRepository(Conference::class);
        /** @var UserRepository $this->userRepository */
        $this->userRepository = $entityManager->getRepository(User::class);
        $this->netteUser = $netteUser;
    }

    /**
     * Creates a new conference.
     * @param array $data
     * @return Conference
     */
    public function create(array $data): Conference
    {
        if (!isset($data['userId'])) {
            throw new InvalidArgumentException("User ID is required.");
        }

        $user = $this->userRepository->find($data['userId']);
        if (!$user) {
            throw new InvalidArgumentException("User not found.");
        }

        $conference = new Conference(
            $user,
            $data['title'],
            (int) $data['numOfPeople'],
            $data['genre'],
            $data['place'],
            new \DateTime($data['startsAt']),
            new \DateTime($data['endsAt']),
            (int) $data['priceForSeat'],
            (int) $data['capacity'],
            $data['description'] ?? null
        );

        $this->entityManager->persist($conference);
        $this->entityManager->flush();

        return $conference;
    }

    /**
     * Saves a conference based on form input.
     * @param object $values
     * @return Conference
     */
    public function saveConference($values): Conference
    {
        $user = $this->userRepository->find($this->netteUser->getId());

        if (!$user) {
            throw new InvalidArgumentException("User not found.");
        }

        $conference = new Conference(
            $user,
            $values->title,
            (int) $values->numOfPeople,
            $values->genre,
            $values->place,
            new \DateTime($values->startsAt),
            new \DateTime($values->endsAt),
            (int) $values->priceForSeat,
            (int) $values->capacity,
            $values->description ?? null
        );

        $this->entityManager->persist($conference);
        $this->entityManager->flush();

        return $conference;
    }

    /**
     * Updates the specified conference.
     * @param int $conferenceId
     * @param array $data
     */
    public function update(int $conferenceId, array $data): void
    {
        $conference = $this->find($conferenceId);

        if (!$conference) {
            throw new InvalidArgumentException("Conference not found.");
        }

        // Update properties as necessary
        // Example: $conference->setTitle($data['title']);
        // Call setters as needed and check for existence of keys in $data

        $this->entityManager->flush();
    }

    /**
     * Deletes a conference by ID.
     * @param int $id
     */
    public function delete(int $id): void
    {
        $conference = $this->find($id);
        if (!$conference) {
            throw new InvalidArgumentException("Conference not found.");
        }

        $this->entityManager->remove($conference);
        $this->entityManager->flush();
    }

    /**
     * Finds a conference by ID.
     * @param int $id
     * @return Conference
     */
    public function find(int $id): Conference
    {
        return $this->conferenceRepository->find($id);
    }

    /**
     * Returns all conferences.
     * @return ArrayCollection
     */
    public function findAll(): ArrayCollection
    {
        return new ArrayCollection($this->conferenceRepository->findAll());
    }
}
