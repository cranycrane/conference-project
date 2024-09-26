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
	public const STATE_CONFIRMED = 2;
	public const STATE_PAID = 3;
	public const STATE_CANCELED = 4;

	public const STATES = [self::STATE_CREATED, self::STATE_CONFIRMED, self::STATE_PAID, self::STATE_CANCELED];

	/** @ORM\Column(type="integer", length=10, nullable=FALSE) */
	private int $numOfPeople;

	/** @ORM\Column(type="integer", length=10, nullable=FALSE) */
	private int $state;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Domain\User\User", inversedBy="reservations")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 */
	private User $user;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Domain\Conference\Conference", inversedBy="reservations")
	 * @ORM\JoinColumn(name="conference_id", referencedColumnName="id", nullable=false)
	 */
	private Conference $conference;



	public function __construct(int $numOfPeople, User $user, Conference $conference)
	{
		$this->numOfPeople = $numOfPeople;
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
		if (!in_array($state, self::STATES, true)) {
			throw new InvalidArgumentException(sprintf('Unsupported state %s', $state));
		}

		$this->state = $state;
	}


}
