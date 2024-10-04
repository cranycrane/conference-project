<?php

namespace App\UI\Components\Presentation;

use App\Model\Services\PresentationService;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Application\UI\Control;

class PresentationList extends Control {

	private PresentationService $presentationService;

	private ArrayCollection $presentations;
	public function __construct(PresentationService $presentationService, ArrayCollection $presentations) {
		$this->presentationService = $presentationService;
		$this->presentations = $presentations;
	}


	// todo vymyslet edit v listu - problem s predavanim conferenceId
//	public function createComponentPresentationEditForm(PresentationFormFactory $formFactory): PresentationForm {
//		return $formFactory->create();
//	}

	public function render(): void
	{
		$this->template->presentations = $this->presentations;
		$this->template->currentDateTime = new \DateTime();
		$this->template->user = $this->presenter->getUser();
		$this->template->setFile(__DIR__ . '/templates/PresentationList.latte');
		$this->template->render();
	}

}
