<?php

namespace App\UI\Components\Reservation;

use App\Domain\Reservation\Reservation;
use App\Model\Services\ReservationService;
use App\UI\Components\BaseGrid;
use Nette\Application\UI\Control;
use Ublaboo\DataGrid\DataGrid;

class ReservationGrid extends BaseGrid {

	private ReservationService $reservationService;	

	public function __construct(ReservationService $reservationService) {
		parent::__construct($reservationService);
		$this->reservationService = $reservationService;
	}

	public function createComponentGrid(): DataGrid {
		$grid = new DataGrid();
		
		$grid->setDataSource($this->reservationService->findAll());

		$grid->addColumnText('email', 'Uživatel')
			->setSortable();

		$grid->addColumnText('conference', 'Konference')
			->setSortable()
			->setRenderer(function (Reservation $reservation) {
				return $reservation->conference->title;
			});

		$grid->addColumnText('state', 'Stav')
			->setSortable()
			->setRenderer(function ($reservation) {
				return $reservation::STATES[$reservation->state];
			});

		$grid->addColumnText('numOfPeople', 'Počet lidí')
			->setSortable();

		$this->addDeleteAction($grid);
		$this->addTranslation($grid);

		return $grid;
	}

	public function render(): void
	{
		$this->template->setFile(__DIR__ . '/templates/ReservationGrid.latte');
		$this->template->render();
	}
}
