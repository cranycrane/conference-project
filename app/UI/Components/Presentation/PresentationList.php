<?php

namespace App\UI\Components\Presentation;

use App\Domain\Presentation\Presentation;
use App\Model\Services\PresentationService;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Control;
use Nette\DI\Attributes\Inject;

class PresentationList extends Control {

	private PresentationFormFactory $presentationFormFactory;

	private PresentationService $presentationService;

	private ArrayCollection $presentations;
  	#[Persistent]
	public ?int $currentPresentationId = null;

	public function __construct(PresentationService $presentationService, ArrayCollection $presentations, PresentationFormFactory $presentationFormFactory) {
		$this->presentationService = $presentationService;
		$this->presentations = $presentations;
    	$this->presentationFormFactory = $presentationFormFactory;
	}

  	public function handleEdit(int $id): void {
      $this->currentPresentationId = $id;

      if ($this->presenter->isAjax()) {
        $this->redrawControl('presentationEditSnippet');
      }
    }

	public function createComponentPresentationEditForm(): PresentationForm {
		$id = $this->currentPresentationId;

		$presentation = $this->presentations->filter(function ($presentation) use ($id) {
		  return $presentation->getId() === $id;
		})->first();

		if($presentation) {
			return $this->presentationFormFactory->create($presentation->conference->getId(), $presentation);
		} else {
			return $this->presentationFormFactory->create($this->presentations->first()->getId());
		}
	}

	public function render(): void
	{
    	$this->template->currentPresentationId = $this->currentPresentationId;
		$this->template->presentations = $this->presentations;
		$this->template->currentDateTime = new \DateTime();
		$this->template->user = $this->presenter->getUser();
		$this->template->setFile(__DIR__ . '/templates/PresentationList.latte');
		$this->template->render();
	}

}
