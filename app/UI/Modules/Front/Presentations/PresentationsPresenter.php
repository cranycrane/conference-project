<?php declare(strict_types = 1);

namespace App\UI\Modules\Front\Presentations;

use App\Domain\Conference\Conference;
use App\Domain\Presentation\Presentation;
use App\Model\Services\PresentationService;
use App\UI\Components\Presentation\PresentationForm;
use App\UI\Components\Presentation\PresentationFormFactory;
use App\UI\Components\Presentation\PresentationList;
use App\UI\Components\Presentation\PresentationListFactory;
use App\UI\Modules\Front\BaseFrontPresenter;
use Nette\DI\Attributes\Inject;

final class PresentationsPresenter extends BaseFrontPresenter
{
	#[Inject]
	public PresentationListFactory $presentationListFactory;

	#[Inject]
	public PresentationService $presentationService;

  #[Inject]
  public PresentationFormFactory $presentationFormFactory;

  private ?Presentation $currentPresentation = null;

  private int $conferenceId;

	public function createComponentMyPresentations(): PresentationList {
		return $this->presentationListFactory->create($this->presentationService->findByBySpeaker($this->user->getId()));
	}

  protected function createComponentPresentationEditForm(): PresentationForm {
    return $this->presentationFormFactory->create($this->conferenceId, $this->currentPresentation);
  }

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
}
