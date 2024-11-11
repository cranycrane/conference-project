<?php

namespace App\Model\Services;

use App\Domain\Conference\Conference;
use App\Domain\Conference\ConferenceRepository;
use App\Domain\User\UserRepository;
use App\Domain\User\User;
use App\Model\Utils\DateTime;
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
    if (isset($values->id) && $values->id) {
        // Pokud je ID přítomné, upravujeme existující konferenci
        $conference = $this->find($values->id);

        if (!$conference) {
            throw new \Exception('Konference nebyla nalezena.');
        }

        // Zde aktualizujeme hodnoty konference
        $conference->title = $values->title;
        $conference->getNumOfPeople();
        $conference->genre = $values->genre;
        $conference->place = $values->place;
        $conference->setStartsAt(DateTime::createFromImmutable($values->startsAt));
        $conference->setEndsAt(DateTime::createFromImmutable($values->endsAt));
        $conference->priceForSeat = (int) $values->priceForSeat;
        $conference->capacity = (int) $values->capacity;
        $conference->description = $values->description ?? null;

        $this->entityManager->flush();
    } else {
        // Pokud není ID, vytváříme novou konferenci
        $conference = new Conference(
            $this->userRepository->find($this->netteUser->getId()),
            $values->title,
            (int) $values->numOfPeople,
            $values->genre,
            $values->place,
			DateTime::createFromImmutable($values->startsAt),
			DateTime::createFromImmutable($values->endsAt),
            (int) $values->priceForSeat,
            (int) $values->capacity,
            $values->description ?? null
        );

        $this->entityManager->persist($conference);
        $this->entityManager->flush();
    }

    return $conference;
}


     /**
     * Aktualizace celé konference na základě hodnot z inline editace.
     * @param int $id
     * @param array $values
     */
    public function updateConference(int $id, array $values): void
    {
        $conference = $this->find($id);
        if (!$conference) {
            throw new InvalidArgumentException("Konference nenalezena.");
        }

        $conference->title = $values['title'];
        $conference->place = $values['place'];
        $conference->setStartsAt(new \DateTime($values['startsAt']));
        $conference->setEndsAt(new \DateTime($values['endsAt']));
        $conference->priceForSeat = (int)$values['priceForSeat'];
        $conference->capacity = (int)$values['capacity'];

        $this->entityManager->flush();
    }

    /**
     * Aktualizace konkrétního pole v konferenci.
     * @param int $id
     * @param string $field
     * @param mixed $value
     */
    public function updateField(int $id, string $field, $value): void
    {
        $conference = $this->find($id);
        if (!$conference) {
            throw new InvalidArgumentException("Konference nenalezena.");
        }

        // Použití setter metody dle názvu pole
        switch ($field) {
            case 'title':
                $conference->title = $value;
                break;
            case 'place':
                $conference->place = $value;
                break;
            case 'startsAt':
                $conference->setStartsAt(new \DateTime($value));
                break;
            case 'endsAt':
                $conference->setEndsAt(new \DateTime($value));
                break;
            case 'priceForSeat':
                $conference->priceForSeat = (int)$value;
                break;
            case 'capacity':
                $conference->capacity = (int)$value;
                break;
            default:
                throw new InvalidArgumentException("Field '{$field}' does not exist.");
        }

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
    
        foreach ($conference->reservations as $reservation) {
            $this->entityManager->remove($reservation);
        }
    
        foreach ($conference->presentations as $presentation) {
            $this->entityManager->remove($presentation);
        }

        $this->entityManager->remove($conference);
        $this->entityManager->flush();
    }

	public function find5UpcomingConferences(): ArrayCollection {
		return new ArrayCollection($this->conferenceRepository->find5UpcomingConferences());
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


    public function findByUser(?int $userId): ArrayCollection
    {
    if ($userId === null) {
        return new ArrayCollection();
    }

    return new ArrayCollection($this->conferenceRepository->findBy(['user' => $userId]));
    }


}
