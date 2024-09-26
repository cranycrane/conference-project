<?php declare(strict_types = 1);

namespace App\Domain\Presentation;

use App\Domain\Room\Room;
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
 * @ORM\Entity(repositoryClass="App\Domain\Presentation\PresentationRepository")
 * @ORM\Table(name="`presentation`")
 * @ORM\HasLifecycleCallbacks
 */
class Presentation extends AbstractEntity
{

	use TId;
	use TCreatedAt;
	use TUpdatedAt;

	public const STATE_CREATED = 1;
	public const STATE_APPROVED = 2;
	public const STATE_BLOCKED = 3;

	public const STATES = [self::STATE_CREATED, self::STATE_BLOCKED, self::STATE_APPROVED];

	/** @ORM\Column(type="string", length=255, nullable=FALSE) */
	private string $title;

	/** @ORM\Column(type="integer", length=10, nullable=FALSE) */
	private int $state;

	/** @ORM\Column(type="string", length=255, nullable=FALSE) */
	private string $description;

	/**
	 * @ORM\Column(type="json", nullable=false)
	 */
	private array $tags = [];

	/** @ORM\Column(type="string", length=255, nullable=TRUE) */
	private ?string $photo;

	/** @ORM\Column(type="datetime", nullable=FALSE) */
	protected DateTime $startsAt;

	/** @ORM\Column(type="datetime", nullable=FALSE) */
	protected DateTime $endsAt;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Domain\User\User", inversedBy="presentations")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
	 */
	public User $speaker;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Domain\Room\Room", inversedBy="presentations")
	 * @ORM\JoinColumn(name="room_id", referencedColumnName="id", nullable=false)
	 */
	public Room $room;

	/**
	 * @ORM\OneToMany(targetEntity="App\Domain\Attendance\Attendance", mappedBy="presentation")
	 */
	public Collection $attendances;



	public function __construct(string $title, string $description, array $tags, string $photo = null)
	{
		$this->title = $title;
		$this->description = $description;
		$this->photo = $photo;
		$this->attendances = new ArrayCollection();


		$this->state = self::STATE_CREATED;
	}

	public function setState(int $state): void
	{
		if (!in_array($state, self::STATES, true)) {
			throw new InvalidArgumentException(sprintf('Unsupported state %s', $state));
		}

		$this->state = $state;
	}


	public function getState(): int
	{
		return $this->state;
	}
}
