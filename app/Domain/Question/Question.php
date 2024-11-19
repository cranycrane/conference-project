<?php declare(strict_types = 1);

namespace App\Domain\Question;

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
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Domain\Question\QuestionRepository")
 * @ORM\Table(name="`question`")
 * @ORM\HasLifecycleCallbacks
 */
class Question extends AbstractEntity
{

	use TId;
	use TCreatedAt;
	use TUpdatedAt;

	/** @ORM\Column(type="string", length=255, nullable=FALSE, unique=false) */
	public string $question;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Domain\Presentation\Presentation", inversedBy="questions")
	 * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
	 */
	public Presentation $presentation;

	/**
	 * @ORM\ManyToOne(targetEntity="App\Domain\User\User", inversedBy="questions")
	 * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
	 */
	public User $user;


	public function __construct(string $question, User $user, Presentation $presentation)
	{
		$this->question = $question;
		$this->user = $user;
		$this->presentation = $presentation;
	}

}
