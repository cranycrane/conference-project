<?php

namespace App\UI\Components\Reservation;

use App\Domain\Reservation\Reservation;
use App\Model\Services\ReservationService;
use App\UI\Components\BaseGrid;
use Nette\Application\UI\Control;
use Ublaboo\DataGrid\DataGrid;

class ReservationGrid extends BaseGrid {

	private ReservationService $reservationService;
	private ?int $conferenceId = null;

	public function __construct(ReservationService $reservationService, ?int $conferenceId = null) {
		parent::__construct($reservationService);
		$this->reservationService = $reservationService;
		$this->conferenceId = $conferenceId;
	}

	public function setConferenceId(?int $conferenceId): void {
        $this->conferenceId = $conferenceId;
    }

	public function createComponentGrid(): DataGrid {
		$grid = new DataGrid();


		if ($this->conferenceId !== null) {
            $grid->setDataSource($this->reservationService->findByConference($this->conferenceId));
        } else {
            $grid->setDataSource($this->reservationService->findAll());
        }

		$grid->addColumnText('email', 'Uživatel');


		if ($this->conferenceId == null){
			$grid->addColumnText('conference', 'Konference')
				->setRenderer(function (Reservation $reservation) {
					return $reservation->conference->title;
				});
		}

		$grid->addColumnStatus('state', 'Stav')
			->setOptions(Reservation::STATES) //
			->onChange[] = [$this, 'statusChange'];

		$grid->addColumnText('numOfPeople', 'Počet lidí');

		$this->addDeleteAction($grid);
		$this->addTranslation($grid);

		return $grid;
	}

	public function statusChange($id, $newStatus): void
	{
		// Update the reservation state in the service
		$reservation = $this->reservationService->find($id);
		if ($reservation) {
			$reservation->setState($newStatus);
			//$reservation->state = $newStatus;
			$this->reservationService->update();


			$this->presenter->flashMessage('Stav rezervace byl úspěšně změněn.', 'success');
		} else {
			$this->presenter->flashMessage('Rezervace nebyla nalezena.', 'danger');
		}

		// Redraw the item to update the status in the grid

		if ($this->presenter->isAjax()) {
			$this['grid']->redrawItem($id);
		}
	}


	public function render(): void
	{
		$this->template->setFile(__DIR__ . '/templates/ReservationGrid.latte');
		$this->template->render();
	}
}
