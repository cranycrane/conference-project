<?php

namespace App\UI\Components\Question;

use App\Domain\Question\Question;
use App\Model\Services\QuestionService;
use App\UI\Components\BaseGrid;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Control;
use Nette\Forms\Container;
use Nette\Utils\ArrayHash;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;

class QuestionGrid extends BaseGrid {

	private QuestionService $questionService;
	private ?int $presentationId = null;
	private  QuestionFormFactory $questionFormFactory;

	#[Persistent]
	public ?int $currentQuestionId = null;

	public function __construct(QuestionService $questionService, QuestionFormFactory $questionFormFactory, ?int $presentationId = null) {
		parent::__construct($questionService);
		$this->questionService = $questionService;
		$this->presentationId = $presentationId;
		$this->questionFormFactory = $questionFormFactory;
	}

	public function createComponentGrid(): DataGrid {
		$grid = new DataGrid();
		$grid->setRememberState(false);

		$user = $this->presenter->getUser();
		$isOrganizerOrAdmin = $user->isLoggedIn() && ($user->isInRole('admin') ||
				$user->getId() === $this->presentationId);

		if ($this->presentationId !== null) {
			$grid->setDataSource($this->questionService->findByPresentation($this->presentationId));
		} else {
			$grid->addColumnText('presentation.title', 'Prezentace');
			$grid->addColumnText('presentation.conference.title', 'Konference');
			$grid->setDataSource($this->questionService->findAll());
		}

//		$grid->addColumnText('user.email', 'Uživatel');

		$grid->addColumnText('question', 'Otázka');

		if($isOrganizerOrAdmin) {
			$grid->addAction('edit', 'Upravit', 'edit!')
				->setRenderCondition(function ($item) {
					$currentUser = $this->presenter->getUser();
					$authorId = $item->user->getId();

					return $currentUser->isInRole('admin') || $currentUser->getId() === $authorId;
				})
				->setClass('btn btn-primary btn-sm ajax')
				->setDataAttribute('bs-toggle', 'modal')
				->setDataAttribute('bs-target', '#dialog-question');

			$grid->addAction('delete', 'Smazat')
				->setRenderCondition(function ($item) {
					$currentUser = $this->presenter->getUser();
					$authorId = $item->user->getId();

					return $currentUser->isInRole('admin') || $currentUser->getId() === $authorId;
				})
				->setClass('btn btn-danger btn-sm')
				->setConfirmation(new StringConfirmation('Opravdu chcete tuto otázku smazat?'));
		}

		$this->addTranslation($grid);

		return $grid;
	}

	public function handleEdit($id): void {
		$this->currentQuestionId = $id;
		$this->redrawControl('questionEditSnippet');
	}

	public function createComponentQuestionEditForm(): QuestionForm {
		$question = $this->questionService->find($this->currentQuestionId);
		return $this->questionFormFactory->create($this->presentationId, $this->presenter->getUser()->getId(), $question);
	}

	public function render(): void
	{
		$this->template->currentQuestionId = $this->currentQuestionId;
		$this->template->setFile(__DIR__ . '/templates/QuestionGrid.latte');
		$this->template->render();
	}
}
