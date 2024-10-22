<?php declare(strict_types = 1);

namespace App\UI\Modules\Front\Conference;

use App\Domain\Conference\Conference;
use App\Domain\Presentation\Presentation;
use App\Model\Services\ConferenceService;
use App\Model\Services\PresentationService;
use App\UI\Components\Conference\ConferenceForm;
use App\UI\Components\Conference\ConferenceFormFactory;
use App\UI\Components\Conference\ConferenceList;
use App\UI\Components\Conference\ConferenceListFactory;
use App\UI\Components\Presentation\PresentationForm;
use App\UI\Components\Presentation\PresentationFormFactory;
use App\UI\Components\Presentation\PresentationList;
use App\UI\Components\Presentation\PresentationListFactory;
use App\UI\Components\Room\RoomGrid;
use App\UI\Components\Room\RoomGridFactory;
use App\UI\Components\Room\RoomForm;
use App\UI\Components\Room\RoomFormFactory;
use App\UI\Modules\Front\BaseFrontPresenter;
use Nette\DI\Attributes\Inject;

final class ConferencePresenter extends BaseFrontPresenter
{
	#[Inject]
	public PresentationListFactory $presentationListFactory;

	#[Inject]
	public ConferenceListFactory $conferenceListFactory;

	#[Inject]
	public PresentationService $presentationService;

	#[Inject]
	public PresentationFormFactory $presentationFormFactory;

	#[Inject]
	public ConferenceService $conferenceService;

	#[Inject]
	public ConferenceFormFactory $conferenceFormFactory;

	#[Inject]
	public RoomGridFactory $roomGridFactory;

	#[Inject]
	public RoomFormFactory $roomFormFactory;

	private int $conferenceId;

	public function createComponentPresentationsList(): PresentationList {
		return $this->presentationListFactory->create($this->presentationService->findByConferenceApproved($this->conferenceId));
	}

	public function createComponentPresentationsNotApprovedList(): PresentationList {
		return $this->presentationListFactory->create($this->presentationService->findByConferenceNotApproved($this->conferenceId));
	}

	public function createComponentConferenceList(): ConferenceList {
		return $this->conferenceListFactory->create($this->conferenceService->findAll());
	}

	public function createComponentPresentationForm(): PresentationForm {
		return $this->presentationFormFactory->create($this->conferenceId);
	}

	public function createComponentRoomGrid(): RoomGrid {
		return $this->roomGridFactory->create($this->conferenceId);
	}

	public function createComponentRoomForm(): RoomForm {
		return $this->roomFormFactory->create($this->conferenceId);
	}

	protected function createComponentConferenceEditForm(): ConferenceForm {
		$form = $this->conferenceFormFactory->create();

		$conference = $this->conferenceService->find($this->conferenceId);

		$formComp = $form['form'];

		$formComp->setDefaults([
			'id' => $conference->getId(),
			'title' => $conference->title,
			'numOfPeople' => $conference->getNumOfPeople(),
			'genre' => $conference->genre,
			'place' => $conference->place,
			'startsAt' => $conference->getStartsAt()->format('Y-m-d H:i:s'),
			'endsAt' => $conference->getEndsAt()->format('Y-m-d H:i:s'),
			'priceForSeat' => $conference->priceForSeat,
			'capacity' => $conference->capacity,
			'description' => $conference->description,
		]);

		return $form;
	}

	public function actionDetail(string $id): void {
		$this->conferenceId = (int)$id;
	}

	public function renderDetail(string $id): void {
		$this->template->conference = $this->conferenceService->find((int)$id);
		$this->template->conferenceId = $this->conferenceId;
	}
}
