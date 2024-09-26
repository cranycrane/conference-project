<?php declare(strict_types = 1);

namespace App\Domain\User;

use App\Domain\Reservation\Reservation;
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
 * @ORM\Entity(repositoryClass="App\Domain\User\UserRepository")
 * @ORM\Table(name="`user`")
 * @ORM\HasLifecycleCallbacks
 */
class User extends AbstractEntity
{

	use TId;
	use TCreatedAt;
	use TUpdatedAt;

	public const ROLE_ADMIN = 'admin';
	public const ROLE_USER = 'user';

	public const STATE_FRESH = 1;
	public const STATE_ACTIVATED = 2;
	public const STATE_BLOCKED = 3;

	public const STATES = [self::STATE_FRESH, self::STATE_BLOCKED, self::STATE_ACTIVATED];

	/** @ORM\Column(type="string", length=255, nullable=TRUE, unique=false) */
	public string $firstName;

	/** @ORM\Column(type="string", length=255, nullable=TRUE, unique=false) */
	public string $lastName;

	/** @ORM\Column(type="string", length=255, nullable=FALSE, unique=TRUE) */
	public string $email;

	/** @ORM\Column(type="integer", length=10, nullable=FALSE) */
	private int $state;

	/** @ORM\Column(type="string", length=255, nullable=FALSE) */
	private string $password;

	/** @ORM\Column(type="string", length=255, nullable=FALSE) */
	private string $role;

	/**
	 * @var DateTime|NULL
	 * @ORM\Column(type="datetime", nullable=TRUE)
	 */
	private ?DateTime $lastLoggedAt = null;

	/**
	 * @ORM\OneToMany(targetEntity="App\Domain\Reservation\Reservation", mappedBy="user")
	 */
	public Collection $reservations;

	public function __construct(string $email, string $passwordHash)
	{
		$this->email = $email;
		$this->password = $passwordHash;
		$this->reservations = new ArrayCollection();

		$this->role = self::ROLE_USER;
		$this->state = self::STATE_FRESH;
	}

	public function addReservation(Reservation $reservation): void
	{
		if (!$this->reservations->contains($reservation)) {
			$this->reservations[] = $reservation;
			$reservation->setUser($this);
		}
	}

	public function changeLoggedAt(): void
	{
		$this->lastLoggedAt = new DateTime();
	}

	public function getEmail(): string
	{
		return $this->email;
	}

	public function getLastLoggedAt(): ?DateTime
	{
		return $this->lastLoggedAt;
	}

	public function getRole(): string
	{
		return $this->role;
	}

	public function setRole(string $role): void
	{
		$this->role = $role;
	}

	public function getPasswordHash(): string
	{
		return $this->password;
	}

	public function changePasswordHash(string $password): void
	{
		$this->password = $password;
	}

	public function block(): void
	{
		$this->state = self::STATE_BLOCKED;
	}

	public function activate(): void
	{
		$this->state = self::STATE_ACTIVATED;
	}

	public function isActivated(): bool
	{
		return $this->state === self::STATE_ACTIVATED;
	}

	public function getFullname(): string
	{
		return $this->firstName . ' ' . $this->lastName;
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

	public function toIdentity(): Identity
	{
		return new Identity($this->getId(), [$this->role], [
			'email' => $this->email,
			'state' => $this->state,
		]);
	}

}
