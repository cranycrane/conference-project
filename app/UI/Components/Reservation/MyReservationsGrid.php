<?php

namespace App\UI\Components\Reservation;

use App\Domain\Reservation\Reservation;
use App\Model\Services\ReservationService;
use App\UI\Components\BaseGrid;
use Nette\Application\UI\Control;
use Ublaboo\DataGrid\DataGrid;

class MyReservationsGrid extends BaseGrid {

	private ReservationService $reservationService;	

	public function __construct(ReservationService $reservationService) {
		parent::__construct($reservationService);
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
			->setClass('btn btn-primary') // Style the button
			->setText('Zobrazit můj rozvrh'); // Button text

		$this->addTranslation($grid);

		return $grid;
	}

    public function handleViewConference(int $id): void
	{
        $reservation = $this->reservationService->find($id);
        $conferenceId = $reservation->conference->getId();

		$this->presenter->redirect('Conference:detail', ['id' => $conferenceId]);
	}

    public function handleViewSchedule(int $id): void
	{
		
	}

	public function render(): void
	{
		$this->template->setFile(__DIR__ . '/templates/ReservationGrid.latte');
		$this->template->render();
	}
}
