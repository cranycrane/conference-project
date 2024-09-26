<?php declare(strict_types = 1);

namespace App\Domain\Attendance;

use App\Domain\Presentation\Presentation;
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
 * @ORM\Entity(repositoryClass="App\Domain\Attendance\AttendanceRepository")
 * @ORM\Table(name="`attendance`")
 * @ORM\HasLifecycleCallbacks
 */
class Attendance extends AbstractEntity
{

	use TId;
	use TCreatedAt;
	use TUpdatedAt;


	/**
	 * @ORM\ManyToOne(targetEntity="App\Domain\User\User", inversedBy="attendances")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 */
	public User $user;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Domain\Presentation\Presentation", inversedBy="attendances")
	 * @ORM\JoinColumn(name="presentation_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
	 */
	public Presentation $presentation;


	public function __construct(User $user, Presentation $presentation)
	{
		$this->user = $user;
		$this->presentation = $presentation;
	}

}
