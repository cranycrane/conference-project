<?php declare(strict_types = 1);

namespace App\UI\Modules\Admin\Room;

use App\Domain\Conference\Conference;
use App\Domain\Room\Room;
use App\Model\Services\RoomService;
use App\Model\Services\ConferenceService;
use App\UI\Components\Room\RoomGrid;
use App\UI\Components\Room\RoomGridFactory;
use App\UI\Components\Room\RoomForm;
use App\UI\Components\Room\RoomFormFactory;
use App\UI\Modules\Admin\BaseAdminPresenter;
use Nette\DI\Attributes\Inject;
use Nette\Bridges\ApplicationLatte\Template;

final class RoomPresenter extends BaseAdminPresenter
{
    #[Inject]
    public RoomService $roomService;

    #[Inject]
    public ConferenceService $conferenceService;

    #[Inject]
    public RoomFormFactory $roomFormFactory;

    #[Inject]
    public RoomGridFactory $roomGridFactory;

    private ?Room $currentRoom = null;

    private int $conferenceId;

    public function actionDefault(?int $conferenceId = null): void
    {
        $this->conferenceId = $conferenceId ?? 0; // Default to 0 or some valid ID
    }

    public function createComponentRoomGrid(): RoomGrid {
        return $this->roomGridFactory->create($this->conferenceId);
    }

    public function createComponentRoomForm(): RoomForm {
        return $this->roomFormFactory->create($this->conferenceId);
    }


    public function renderDefault(int $conferenceId = null): void
    {
        if ($conferenceId !== null) {
            $rooms = $this->roomService->findByConference($conferenceId);
            $conference = $this->conferenceService->find($conferenceId);

            if (!$conference) {
                $this->error('Conference not found');
            }
            $this->template->conferenceId = $conferenceId;
            $this->template->conferenceName = $conference->title;
        } else {
            $rooms = $this->roomService->findAll();
            $this->template->conferenceName = null;
        }

        $this->template->rooms = $rooms;
    }





}
