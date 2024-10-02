<?php declare(strict_types = 1);

namespace App\Domain\Conference;

use App\Domain\User\User;
use App\Model\Database\Entity\AbstractEntity;
use App\Model\Database\Entity\TCreatedAt;
use App\Model\Database\Entity\TId;
use App\Model\Database\Entity\TUpdatedAt;
use App\Model\Exception\Logic\InvalidArgumentException;
use App\Model\Security\Identity;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Domain\Conference\ConferenceRepository")
 * @ORM\Table(name="`conference`")
 * @ORM\HasLifecycleCallbacks
 */
class Conference extends AbstractEntity
{

	use TId;
	use TCreatedAt;
	use TUpdatedAt;

	public const STATE_CREATED = 1;
	public const STATE_ONGOING = 2;
	public const STATE_PAID = 3;
	public const STATE_CANCELED = 4;
	public const STATES = [self::STATE_CREATED, self::STATE_ONGOING, self::STATE_PAID, self::STATE_CANCELED];

	/** @ORM\Column(type="string", length=255, nullable=FALSE) */
	public string $title;

	/** @ORM\Column(type="integer", length=10, nullable=FALSE) */
	private int $numOfPeople;

	/** @ORM\Column(type="integer", length=10, nullable=FALSE) */
	private int $state;

	/** @ORM\Column(type="string", length=255, nullable=FALSE) */
	public string $genre;

	/**
	 * @ORM\Column(type="json", nullable=false)
	 */
	public array $tags = [];

	/** @ORM\Column(type="string", length=255, nullable=FALSE) */
	public string $place;

	/** @ORM\Column(type="datetime", nullable=FALSE) */
	protected DateTime $startsAt;

	/** @ORM\Column(type="datetime", nullable=FALSE) */
	protected DateTime $endsAt;

	/** @ORM\Column(type="string", length=255, nullable=TRUE) */
	public string $description;

	/** @ORM\Column(type="integer", length=10, nullable=FALSE) */
	public int $priceForSeat;

	/** @ORM\Column(type="integer", length=10, nullable=FALSE) */
	public int $capacity;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Domain\User\User", inversedBy="conferences")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 */
	private User $user;

	/**
	 * @ORM\OneToMany(targetEntity="App\Domain\Room\Room", mappedBy="room")
	 */
	public Collection $rooms;

	/**
	 * @ORM\OneToMany(targetEntity="App\Domain\Reservation\Reservation", mappedBy="conference")
	 */
	public Collection $reservations;


	public function __construct(
		User $user,
		string $title,
		int $numOfPeople,
		string $genre,
		string $place,
		DateTime $startsAt,
		DateTime $endsAt,
		int $priceForSeat,
		int $capacity,
		string $description = null,
		)
	{
		$this->user = $user;
		$this->title = $title;
		$this->numOfPeople = $numOfPeople;
		$this->genre = $genre;
		$this->place = $place;
		$this->startsAt = $startsAt;
		$this->endsAt = $endsAt;
		$this->priceForSeat = $priceForSeat;
		$this->capacity = $capacity;
		$this->description = $description;

		$this->rooms = new ArrayCollection();
		$this->reservations = new ArrayCollection();


		$this->state = self::STATE_CREATED;
	}

	public function getUser(): User
	{
		return $this->user;
	}

	public function setUser(User $user): void
	{
		$this->user = $user;
	}

	public function cancel(): void
	{
		$this->state = self::STATE_CANCELED;
	}

	public function getState(): int
	{
		return $this->state;
	}

	public function setState(int $state): void
	{
		if (!in_array($state, self::STATES, true)) {
			throw new InvalidArgumentException(sprintf('Unsupported state %s', $state));
		}

		$this->state = $state;
	}

	public function getStartsAt(): DateTime {
        return $this->startsAt;
    }

	public function getEndsAt(): DateTime {
		return $this->endsAt;
	}

}
