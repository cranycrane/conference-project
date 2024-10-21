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
use App\UI\Components\Presentation\ScheduleList;
use App\UI\Components\Presentation\ScheduleListFactory;
use App\UI\Components\Reservation\ReservationForm;
use App\UI\Components\Reservation\ReservationFormFactory;
use App\UI\Components\Room\RoomGrid;
use App\UI\Components\Room\RoomGridFactory;
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
	public ReservationFormFactory $reservationFormFactory;

	#[Inject]
	public ConferenceService $conferenceService;

	#[Inject]
	public ConferenceFormFactory $conferenceFormFactory;

	#[Inject]
	public ScheduleListFactory $scheduleListFactory;

	#[Inject]
	public RoomGridFactory $roomGridFactory;

	private int $conferenceId;

	public function createComponentPresentationsList(): PresentationList {
		return $this->presentationListFactory->create($this->presentationService->findByConferenceApproved($this->conferenceId));
	}

	public function createComponentScheduleList(): ScheduleList {
		return $this->scheduleListFactory->create($this->presentationService->findByConferenceApproved($this->conferenceId));
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

	public function createComponentReservationForm(): ReservationForm {
		$user = $this->getUser();
		$userId = $user->isLoggedIn() ? $user->getId() : null;

		return $this->reservationFormFactory->create($this->conferenceId, $userId);
	}

	public function createComponentRoomGrid(): RoomGrid {
		$grid = $this->roomGridFactory->create();

        $conferenceId = $this->conferenceId;

        if ($conferenceId !== null) {
            $conferenceId = (int) $conferenceId; // Cast conferenceId to an integer
        }

        $grid->setConferenceId($conferenceId); // Pass the conferenceId to RoomGrid

        return $grid;
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

	public function createComponentUserScheduleList(): ?ScheduleList {
		$user = $this->getUser();

		if (!$user->isLoggedIn()) {
			return null;
		}

		$userId = $user->getId();
		$presentations = $this->presentationService->findUserSchedule($userId, $this->conferenceId);

			bdump("NULL");
		if (!$presentations->isEmpty()) {
			return $this->scheduleListFactory->create($presentations);
		}


		return null;
	}

	public function actionDetail(string $id): void {
		$this->conferenceId = (int)$id;
	}

	public function renderDetail(string $id): void {
		$this->template->userScheduleList = $this->createComponentUserScheduleList();
		$this->template->conference = $this->conferenceService->find((int)$id);
	}

	public function renderMy(): void {
		$userId = $this->getUser()->getId();  // Získáme ID přihlášeného uživatele
		$this->template->conferences = $this->presentationService->findByUser($userId);  // Najdeme prezentace uživatele
	}

}
