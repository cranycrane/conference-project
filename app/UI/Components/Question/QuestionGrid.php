<?php

namespace App\UI\Components\Question;

use App\Domain\Question\Question;
use App\Model\Services\QuestionService;
use App\UI\Components\BaseGrid;
use Nette\Application\UI\Control;
use Nette\Forms\Container;
use Nette\Utils\ArrayHash;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;

class QuestionGrid extends BaseGrid {

	private QuestionService $questionService;
	private ?int $presentationId = null;

	public function __construct(QuestionService $questionService, ?int $presentationId = null) {
		parent::__construct($questionService);
		$this->questionService = $questionService;
		$this->presentationId = $presentationId;
	}

	public function createComponentGrid(): DataGrid {
		$grid = new DataGrid();

		if ($this->presentationId !== null) {
            $grid->setDataSource($this->questionService->findByPresentation($this->presentationId));
        } else {
			$grid->addColumnText('presentation.title', 'Prezentace');
			$grid->addColumnText('presentation.conference.title', 'Konference');
            $grid->setDataSource($this->questionService->findAll());
        }

		$grid->addColumnText('user.email', 'Uživatel');

		$grid->addColumnText('question', 'Otázka');

		$grid->addAction('delete', 'Smazat')
			->setRenderCondition(function ($item) {
				$currentUser = $this->presenter->getUser();
				$authorId = $item->user->getId();

				return $currentUser->isInRole('admin') || $currentUser->getId() === $authorId;
			})
			->setClass('btn btn-danger btn-sm')
			->setConfirmation(new StringConfirmation('Opravdu chcete tuto otázku smazat?'));

		/**
		 * Big inline editing
		 */
		$grid->addInlineEdit()
			->onControlAdd[] = function(Container $container): void {
			$container->addText('question', '');
		};

		$grid->getInlineEdit()->onSetDefaults[] = function(Container $container, $item): void {
			$container->setDefaults([
				'question' => $item->question,
			]);
		};

		$grid->getInlineEdit()->onSubmit[] = function($id, ArrayHash $values): void {
			/**
			 * Save new values
			 */
			$question = $this->questionService->find($id);
			$question->question = $values['question'];
			$this->questionService->update();
		};


		$this->addTranslation($grid);

		return $grid;
	}

	public function render(): void
	{
		$this->template->setFile(__DIR__ . '/templates/QuestionGrid.latte');
		$this->template->render();
	}
}
