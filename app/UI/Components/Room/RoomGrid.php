<?php

namespace App\UI\Components\Room;

use App\Model\Services\RoomService;
use Nette\Application\UI\Control;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;

class RoomGrid extends Control {

	private RoomService $RoomService;
	public function __construct(RoomService $RoomService) {
		$this->RoomService = $RoomService;
	}

	public function createComponentGrid(): DataGrid {
		$grid = new DataGrid();

		$grid->setDataSource($this->RoomService->findAll());

		$grid->addColumnText('roomNumber', 'Room number')
			->setSortable();

		$grid->addColumnText('address', 'Address')
			->setSortable();

		$grid->addAction('edit', 'Spravovat místnost', 'edit!')
			->setIcon('pencil-alt')
			->setClass('btn btn-sm btn-primary');

		$grid->addAction('delete', 'Smazat', 'delete!')
			->setIcon('trash')
			->setClass('btn btn-sm btn-danger ajax')
			->setConfirmation(
				new StringConfirmation('Opravdu chcete smazat místnost %s?', 'roomNumber')
			);

		return $grid;
	}

	public function handleEdit(int $id): void {
		$this->presenter->flashMessage('Místnost úspěšně upravena', 'success');
		bdump("POMOC");
	}

	public function handleDelete(int $id): void {
		try {
			$this->RoomService->delete($id);
			$this->getPresenter()->flashMessage('Místnost úspěšně smazána', 'success');
		} catch (\Exception $e) {
			$this->getPresenter()->flashMessage('Chyba: ' . $e->getMessage(), 'error');
			$this->getPresenter()->redrawControl('flashMessages');
			return;
		}

		if ($this->presenter->isAjax()) {
			$this->getPresenter()->redrawControl('flashMessages');
			$this->redrawControl('flashes');
			$this['grid']->reload();
			bdump("AHOJ");
		} else {
			bdump("BUUU");
		}
	}

	public function render(): void
	{
		$this->template->setFile(__DIR__ . '/templates/RoomGrid.latte');
		$this->template->render();
	}
}
