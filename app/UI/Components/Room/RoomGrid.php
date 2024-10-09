<?php

namespace App\UI\Components\Room;

use App\Model\Services\RoomService;
use Nette\Forms\Container;
use Nette\Application\UI\Control;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;

class RoomGrid extends Control {

	private RoomService $roomService;
	private ?int $conferenceId = null;

	public function __construct(RoomService $roomService) {
		$this->roomService = $roomService;
	}

	public function setConferenceId(?int $conferenceId): void {
        $this->conferenceId = $conferenceId; // Setter to pass the conferenceId
    }

	public function createComponentGrid(): DataGrid {
		$grid = new DataGrid();


		if ($this->conferenceId !== null) {
            $grid->setDataSource($this->roomService->findByConference($this->conferenceId));
        } else {
            $grid->setDataSource($this->roomService->findAll());
        }

		$grid->addColumnText('roomNumber', 'Room number')
        	->setSortable(); // Make sure the alias matches your query

		$grid->addColumnText('address', 'Address')
			->setSortable();

		$grid->addAction('edit', 'Upravit')
			->setClass('btn btn-primary')
			->setText('Upravit');

		$grid->addAction('delete', 'Smazat', 'delete!')
			->setIcon('trash')
			->setClass('btn btn-danger')
			->setConfirmation(
				new StringConfirmation('Opravdu chcete smazat místnost %s?', 'roomNumber')
			);

		return $grid;
	}

	public function handleEdit(int $id): void
    {
        // Use the stored conferenceId instead of passing it
        $this->presenter->redirect('Room:edit', ['id' => $id, 'conferenceId' => $this->conferenceId]);
    }

	public function handleDelete(int $id): void
	{
		$this->roomService->delete($id);
		$this->presenter->flashMessage('Místnost byla úspěšně smazána.', 'success');
		$this->presenter->redrawControl('grid');
	}

	public function render(): void
	{
		$this->template->setFile(__DIR__ . '/templates/RoomGrid.latte');
		$this->template->render();
	}
}
