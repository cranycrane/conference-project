<?php

namespace App\UI\Components\Presentation;

use App\Model\Services\AttendanceService;
use App\Model\Services\PresentationService;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Application\UI\Control;
class ScheduleList extends Control {

	private PresentationService $presentationService;
	private AttendanceService $attendanceService;
	private ArrayCollection $presentations;

	public function __construct(PresentationService $presentationService, AttendanceService $attendanceService, ArrayCollection $presentations) {
		$this->presentationService = $presentationService;
		$this->attendanceService = $attendanceService;

		$groupedPresentations = $this->presentationService->groupPresentationsByDay($presentations);
		$this->presentations = $this->presentationService->sortPresentationsByTime($groupedPresentations);
	}

	public function handleAddSchedule(int $id): void {
		try {
			$this->attendanceService->create([
				'presentationId' => $id,
				'userId' => $this->presenter->getUser()->getId()
			]);
			$this->presenter->flashMessage('Prezentace úspěšne přidána do rozvrhu', 'success');
		} catch (\Throwable $exception) {
			$this->presenter->flashMessage('Nastala chyba při přidávání do rozvrhu', 'error');
		}
		$this->redirect('this');
	}

	public function handleRemoveSchedule(int $id): void {
		try {
			$this->attendanceService->delete($id);
			$this->presenter->flashMessage('Prezentace úspěšne odebrána z rozvrhu', 'success');
		} catch (\Throwable $exception) {
			$this->presenter->flashMessage('Nastala chyba při odebíraní z rozvrhu ' . $exception->getMessage(), 'error');
		}
		$this->redirect('this');
	}

	public function render(): void {
		$this->template->presentationsByDay = $this->presentations;
		$this->template->user = $this->presenter->getUser();
		$this->template->setFile(__DIR__ . '/templates/ScheduleList.latte');
		$this->template->render();
	}
}
