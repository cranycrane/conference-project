<?php declare(strict_types = 1);

namespace App\Domain\Room;

use App\Domain\Conference\Conference;
use App\Domain\Presentation\Presentation;
use App\Domain\User\User;
use App\Model\Database\Entity\AbstractEntity;
use App\Model\Database\Entity\TCreatedAt;
use App\Model\Database\Entity\TId;
use App\Model\Database\Entity\TUpdatedAt;
use App\Model\Exception\Logic\InvalidArgumentException;
use App\Model\Security\Identity;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Domain\Room\RoomRepository")
 * @ORM\Table(name="`room`")
 * @ORM\HasLifecycleCallbacks
 */
class Room extends AbstractEntity
{

	use TId;
	use TCreatedAt;
	use TUpdatedAt;

	/** @ORM\Column(type="string", length=255, nullable=TRUE) */
	public string $address;

	/** @ORM\Column(type="string", length=255, nullable=FALSE) */
	public string $roomNumber;


	/**
	 * @ORM\OneToMany(targetEntity="App\Domain\Presentation\Presentation", mappedBy="room")
	 */
	public Collection $presentations;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Domain\Conference\Conference", inversedBy="rooms")
	 * @ORM\JoinColumn(name="conference_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 */
	public Conference $conference;


	public function __construct(string $address, string $roomNumber, Conference $conference)
	{
		$this->address = $address;
		$this->roomNumber = $roomNumber;
		$this->conference = $conference;
	}

}
