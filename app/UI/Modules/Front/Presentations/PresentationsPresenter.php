<?php declare(strict_types=1);

namespace App\UI\Modules\Front\Presentations;

use App\Domain\Conference\Conference;
use App\Domain\Presentation\Presentation;
use App\Model\Services\PresentationService;
use App\UI\Components\Presentation\PresentationForm;
use App\UI\Components\Presentation\PresentationFormFactory;
use App\UI\Components\Presentation\PresentationList;
use App\UI\Components\Presentation\PresentationListFactory;
use App\UI\Components\Question\QuestionForm;
use App\UI\Components\Question\QuestionFormFactory;
use App\UI\Components\Question\QuestionGrid;
use App\UI\Components\Question\QuestionGridFactory;
use App\UI\Modules\Front\BaseFrontPresenter;
use Nette\DI\Attributes\Inject;

final class PresentationsPresenter extends BaseFrontPresenter {

	#[Inject]
	public PresentationListFactory $presentationListFactory;

	#[Inject]
	public PresentationService $presentationService;

	#[Inject]
	public PresentationFormFactory $presentationFormFactory;

	#[Inject]
	public QuestionGridFactory $questionGridFactory;

	#[Inject]
	public QuestionFormFactory $questionFormFactory;

	private ?Presentation $currentPresentation = null;

	private int $conferenceId;

	public function actionDetail($id): void {
		$this->currentPresentation = $this->presentationService->find($id);
		if(!$this->currentPresentation) {
			$this->flashMessage('Nelze přistoupit na neexistující prezentaci');
			$this->redirect(':Front:Home:default');
		}

		$this->conferenceId = $this->currentPresentation->conference->getId();
	}

	public function renderDetail($id): void {
		$this->template->presentation = $this->presentationService->find($id);
	}

	public function createComponentQuestionGrid(): QuestionGrid {
		return $this->questionGridFactory->create($this->currentPresentation->getId());
	}

	public function createComponentQuestionForm(): QuestionForm {
		return $this->questionFormFactory->create($this->currentPresentation->getId(), $this->getUser()->getId());
	}

	protected function createComponentPresentationEditForm(): PresentationForm {
		return $this->presentationFormFactory->create($this->conferenceId, $this->currentPresentation);
	}
}
