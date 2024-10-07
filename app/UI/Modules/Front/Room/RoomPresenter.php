<?php declare(strict_types = 1);

namespace App\UI\Modules\Front\Room;

use App\Domain\Room\Room;
use App\Model\Services\RoomService;
use App\UI\Components\Room\RoomGrid;
use App\UI\Components\Room\RoomGridFactory;
use App\UI\Components\Room\RoomForm;
use App\UI\Components\Room\RoomFormFactory;
use App\UI\Modules\Front\BaseFrontPresenter;
use Nette\DI\Attributes\Inject;

final class RoomPresenter extends BaseFrontPresenter
{
    #[Inject]
    public RoomService $roomService;

    #[Inject]
    public RoomFormFactory $roomFormFactory;

    #[Inject]
    public RoomGridFactory $roomGridFactory;

    public function createComponentRoomGrid(): RoomGrid {
        return $this->roomGridFactory->create();
    }

    public function createComponentRoomForm(): RoomForm {
        return $this->roomFormFactory->create();
    }
   
    public function renderEdit(int $id): void
    {
        $room = $this->roomService->find($id);
        if (!$room) {
            $this->error('Room not found');
        }

        $template = $this->getTemplate();
        $template->room = $room;

        $form = $this['roomForm']->getComponent('form');
        $form->setDefaults([
            'id' => $room->getId(),
            'roomNumber' => $room->roomNumber,
            'address' => $room->address,
            'conference' => $room->conference,
        ]);
    }

    public function renderCreate(): void
    {
        $template = $this->getTemplate();
        $template->room = null;
    }

   
}