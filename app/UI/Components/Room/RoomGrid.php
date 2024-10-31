<?php

namespace App\UI\Components\Room;

use App\Model\Services\RoomService;
use Nette\Application\Attributes\Persistent;
use Nette\Forms\Container;
use Nette\Application\UI\Control;
use Nette\Utils\ArrayHash;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use App\UI\Components\Room\RoomForm;
use App\UI\Components\Room\RoomFormFactory;
use App\UI\Modules\Front\BaseFrontPresenter;
use Nette\DI\Attributes\Inject;

class RoomGrid extends Control {

	private RoomService $roomService;
	private RoomFormFactory $roomFormFactory;
	private int $conferenceId;
	#[Persistent]
	public ?int $currentRoomId = null;

	public function __construct(RoomService $roomService, RoomFormFactory $roomFormFactory, int $conferenceId) {
		$this->roomService = $roomService;
		$this->conferenceId = $conferenceId;
		$this->roomFormFactory = $roomFormFactory;
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

		$grid->addAction('edit', 'Upravit', 'edit!')
			->setClass('btn btn-primary')
			->setText('Upravit')
			->setDataAttribute('bs-toggle', 'modal')  // Trigger the modal
			->setDataAttribute('bs-target', '#dialog-editRoom');  // Set unique modal ID for each room;

		$grid->addAction('delete', 'Smazat', 'delete!')
			->setIcon('trash')
			->setClass('btn btn-danger')
			->setConfirmation(
				new StringConfirmation('Opravdu chcete smazat místnost %s?', 'roomNumber')
			);


		/**
		 * Big inline editing
		 */
		$grid->addInlineEdit()
			->onControlAdd[] = function(Container $container): void {
			$container->addText('roomNumber', '');
			$container->addText('address', '');
		};

		$grid->getInlineEdit()->onSetDefaults[] = function(Container $container, $item): void {
			$container->setDefaults([
				'roomNumber' => $item->roomNumber,
				'address' => $item->address,
			]);
		};

		$grid->getInlineEdit()->onSubmit[] = function($id, ArrayHash $values): void {
			/**
			 * Save new values
			 */
			$room = $this->roomService->find($id);
			$room->roomNumber = $values['roomNumber'];
			$room->address = $values['address'];
			$this->roomService->update();
		};

		return $grid;
	}

	public function handleEdit(int $id): void
    {
		$this->currentRoomId = $id;  // Store the room ID for editing
		if ($this->presenter->isAjax()) {
			$this->redrawControl('roomEditSnippet');
		}
    }

	public function handleDelete(int $id): void
	{
		$this->roomService->delete($id);
		$this->presenter->flashMessage('Místnost byla úspěšně smazána.', 'success');
		$this->presenter->redrawControl('grid');
		$this->redirect('this');
	}

	public function createComponentRoomEditForm(): RoomForm {
		if($this->currentRoomId != null){
			$room=$this->roomService->find($this->currentRoomId);
			return $this->roomFormFactory->create($this->conferenceId, $room);
		}
		{
			return $this->roomFormFactory->create($this->conferenceId);
		}
	}

	public function render(): void
	{
		$this->template->currentRoomId = $this->currentRoomId;
		$this->template->setFile(__DIR__ . '/templates/RoomGrid.latte');
		$this->template->render();
	}
}
