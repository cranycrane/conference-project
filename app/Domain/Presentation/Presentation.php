<?php declare(strict_types = 1);

namespace App\Domain\Presentation;

use App\Domain\Attendance\Attendance;
use App\Domain\Conference\Conference;
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
use Nette\Http\FileUpload;

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

	public const STATES = [
		self::STATE_CREATED => 'Čeká na schválení',
		self::STATE_BLOCKED => 'Blokováno',
		self::STATE_APPROVED => 'Schváleno'
	];
	/** @ORM\Column(type="string", length=255, nullable=FALSE) */
	public string $title;

	/** @ORM\Column(type="integer", length=10, nullable=FALSE) */
	public int $state;

	/** @ORM\Column(type="string", length=255, nullable=TRUE) */
	public string $description;

	/**
	 * @ORM\Column(type="json", nullable=TRUE)
	 */
	public ?array $tags = [];

	/** @ORM\Column(type="string", length=255, nullable=TRUE) */
	public ?string $photo;

	/** @ORM\Column(type="datetime", nullable=TRUE) */
	public ?DateTime $startsAt;

	/** @ORM\Column(type="datetime", nullable=TRUE) */
	public ?DateTime $endsAt;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Domain\User\User", inversedBy="presentations")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 */
	public User $speaker;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Domain\Room\Room", inversedBy="presentations")
	 * @ORM\JoinColumn(name="room_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
	 */
	public ?Room $room;

	/**
	 * @ORM\OneToMany(targetEntity="App\Domain\Attendance\Attendance", mappedBy="presentation")
	 */
	public Collection $attendances;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Domain\Conference\Conference", inversedBy="presentations")
	 * @ORM\JoinColumn(name="conference_id", referencedColumnName="id", nullable=false)
	 */
	public Conference $conference;



	public function __construct(User $speaker, Conference $conference, string $title, string $description = null, array $tags = null, string $photo = null)
	{
		$this->speaker = $speaker;
		$this->conference = $conference;
		$this->title = $title;
		$this->description = $description;
		$this->tags = $tags;
		$this->photo = $photo;
		$this->attendances = new ArrayCollection();


		$this->state = self::STATE_CREATED;
	}

	public function getUserAttendance(int $userId): ?Attendance
	{
		foreach ($this->attendances as $attendance) {
			if ($attendance->user->getId() === $userId) {
				return $attendance;
			}
		}
		return null;
	}

	public function setPhotoUpload(FileUpload $file): void {
		$fileName = 'uploads/presentations/' . $file->getSanitizedName();
		$file->move($fileName);
		$this->photo = $fileName;
	}

	public function setState(int $state): void
	{
		if (!array_key_exists($state, self::STATES)) {
			throw new InvalidArgumentException(sprintf('Unsupported state %d', $state));
		}

		$this->state = $state;
	}

	public function getStateLabel(): string
	{
		return self::STATES[$this->state] ?? 'Neznámý';
	}


	public function getState(): int
	{
		return $this->state;
	}
}
