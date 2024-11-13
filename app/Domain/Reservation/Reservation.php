<?php declare(strict_types = 1);

namespace App\Domain\Reservation;

use App\Domain\Conference\Conference;
use App\Domain\User\User;
use App\Model\Database\Entity\AbstractEntity;
use App\Model\Database\Entity\TCreatedAt;
use App\Model\Database\Entity\TId;
use App\Model\Database\Entity\TUpdatedAt;
use App\Model\Exception\Logic\InvalidArgumentException;
use App\Model\Security\Identity;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Domain\Reservation\ReservationRepository")
 * @ORM\Table(name="`reservation`")
 * @ORM\HasLifecycleCallbacks
 */
class Reservation extends AbstractEntity
{

	use TId;
	use TCreatedAt;
	use TUpdatedAt;

	public const STATE_CREATED = 1;
	public const STATE_PAID = 3;
	public const STATE_CANCELED = 4;

	public const STATES = [
		self::STATE_CREATED => 'Vytvořeno',
		self::STATE_PAID => 'Zaplaceno',
		self::STATE_CANCELED => 'Zrušeno'
	];

	/** @ORM\Column(type="integer", length=10, nullable=FALSE) */
	public int $numOfPeople;

	/** @ORM\Column(type="integer", length=10, nullable=FALSE) */
	public int $state;

	/** @ORM\Column(type="string", length=255, nullable=FALSE, unique=false) */
	public string $firstName;

	/** @ORM\Column(type="string", length=255, nullable=FALSE, unique=false) */
	public string $lastName;

	/** @ORM\Column(type="string", length=255, nullable=FALSE, unique=false) */
	public string $email;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Domain\User\User", inversedBy="reservations")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
	 */
	public ?User $user = null;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Domain\Conference\Conference", inversedBy="reservations")
	 * @ORM\JoinColumn(name="conference_id", referencedColumnName="id", nullable=false)
	 */
	public Conference $conference;



	public function __construct(int $numOfPeople, string $email, string $firstName,
								string $lastName, ?User $user, Conference $conference)
	{
		$this->numOfPeople = $numOfPeople;
		$this->firstName = $firstName;
		$this->lastName = $lastName;
		$this->email = $email;
		$this->user = $user;
		$this->conference = $conference;

		$this->state = self::STATE_CREATED;
	}

	public function getState(): int
	{
		return $this->state;
	}

	public function setState(int $state): void
	{
		if (!array_key_exists($state, self::STATES)) {
			throw new InvalidArgumentException(sprintf('Unsupported state %d', $state));
		}

		$this->state = $state;
	}


}
