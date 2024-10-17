<?php

namespace App\UI\Components\Presentation;

use App\Model\Services\PresentationService;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Application\UI\Control;
class ScheduleList extends Control {

	private PresentationService $presentationService;
	private ArrayCollection $presentations;

	public function __construct(PresentationService $presentationService, ArrayCollection $presentations) {
		$this->presentationService = $presentationService;

		$groupedPresentations = $this->presentationService->groupPresentationsByDay($presentations);
		$this->presentations = $this->presentationService->sortPresentationsByTime($groupedPresentations);
	}

	public function render(): void {
		$this->template->presentationsByDay = $this->presentations;
		$this->template->user = $this->presenter->getUser();
		$this->template->setFile(__DIR__ . '/templates/ScheduleList.latte');
		$this->template->render();
	}
}
