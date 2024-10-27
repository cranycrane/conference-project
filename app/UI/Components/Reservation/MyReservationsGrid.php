<?php

namespace App\UI\Components\Reservation;

use App\Domain\Reservation\Reservation;
use App\Model\Services\PresentationService;
use App\Model\Services\ReservationService;
use App\UI\Components\BaseGrid;
use App\UI\Components\Presentation\ScheduleList;
use App\UI\Components\Presentation\ScheduleListFactory;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Control;
use Nette\DI\Attributes\Inject;
use Ublaboo\DataGrid\DataGrid;

class MyReservationsGrid extends BaseGrid {

	private ReservationService $reservationService;
	private PresentationService $presentationService;
	private ScheduleListFactory $scheduleListFactory;
	#[Persistent]
	public ?int $currentConferenceId = null;

	public function __construct(ReservationService $reservationService, PresentationService $presentationService, ScheduleListFactory $scheduleListFactory) {
		parent::__construct($reservationService);
		$this->presentationService = $presentationService;
		$this->scheduleListFactory = $scheduleListFactory;
		$this->reservationService = $reservationService;
	}

	public function createComponentGrid(): DataGrid {
		$grid = new DataGrid();

        $grid->setDataSource($this->reservationService->findByUser($this->presenter->getUser()->getId()));

		$grid->addColumnText('conference', 'Konference')
			->setSortable()
			->setRenderer(function (Reservation $reservation) {
				return $reservation->conference->title;
			});

            $grid->addColumnDateTime('startsAt', 'Začátek', 'getStartsAt')
            ->setFormat('j.n.Y H:i')
            ->setSortable()
            ->setRenderer(function (Reservation $reservation) {
                $startsAt = $reservation->conference->getStartsAt();
                return $startsAt instanceof \DateTime ? $startsAt->format('j.n.Y H:i') : null;
            });

        $grid->addColumnDateTime('endsAt', 'Konec', 'getEndsAt')
            ->setFormat('j.n.Y H:i')
            ->setSortable()
            ->setRenderer(function (Reservation $reservation) {
                $endsAt = $reservation->conference->getEndsAt();
                return $endsAt instanceof \DateTime ? $endsAt->format('j.n.Y H:i') : null;
            });

		$grid->addColumnText('state', 'Stav')
			->setSortable()
			->setRenderer(function ($reservation) {
				return $reservation::STATES[$reservation->state];
			});

		$grid->addColumnText('numOfPeople', 'Počet lidí')
			->setSortable();

        $grid->addAction('viewConference', 'Zobrazit konferenci')
			->setClass('btn btn-primary') // Style the button
			->setText('Zobrazit konferenci'); // Button text

        $grid->addAction('viewSchedule', 'Zobrazit můj rozvrh')
			->addAttributes(['data-bs-toggle' => 'modal', 'data-bs-target' => '#dialog-schedule'])
			->setClass('btn btn-primary ajax') // Style the button
			->setText('Zobrazit můj rozvrh'); // Button text

		$this->addTranslation($grid);

		return $grid;
	}

	public function createComponentUserScheduleList(): ?ScheduleList {
		$user = $this->presenter->getUser();

		if (!$user->isLoggedIn()) {
			return null;
		}

		$userId = $user->getId();
		$presentations = $this->presentationService->findUserSchedule($userId, $this->currentConferenceId);

		if (!$presentations->isEmpty()) {
			return $this->scheduleListFactory->create($presentations);
		}

		return $this->scheduleListFactory->create();
	}

    public function handleViewConference(int $id): void
	{
        $reservation = $this->reservationService->find($id);
        $conferenceId = $reservation->conference->getId();

		$this->presenter->redirect('Conference:detail', ['id' => $conferenceId]);
	}

    public function handleViewSchedule(int $id): void
	{
		$reservation = $this->reservationService->find($id);
		$this->currentConferenceId = $reservation->conference->getId();

		if ($this->presenter->isAjax()) {
			$this->redrawControl('myScheduleSnippet');
		}
	}

	public function render(): void
	{
		$this->template->currentConferenceId = $this->currentConferenceId;
		$this->template->setFile(__DIR__ . '/templates/MyReservationsGrid.latte');
		$this->template->render();
	}
}
