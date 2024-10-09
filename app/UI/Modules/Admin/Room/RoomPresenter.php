<?php declare(strict_types = 1);

namespace App\UI\Modules\Admin\Room;

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

    

    public function createComponentRoomGrid(): RoomGrid {
        $grid = $this->roomGridFactory->create();
    
        $conferenceId = $this->getParameter('conferenceId'); // Get conferenceId from request
    
        if ($conferenceId !== null) {
            $conferenceId = (int) $conferenceId; // Cast conferenceId to an integer
        }
    
        $grid->setConferenceId($conferenceId); // Pass the conferenceId to RoomGrid
    
        return $grid;
    }

    public function createComponentRoomForm(): RoomForm {
        $form = $this->roomFormFactory->create();

        $conferenceId = $this->getParameter('conferenceId'); // Get conferenceId from request
    
        if ($conferenceId !== null) {
            $conferenceId = (int) $conferenceId; // Cast conferenceId to an integer
        }
    
        $form->setConferenceId($conferenceId); // Pass the conferenceId to RoomGrid
    
        return $form;
    }
   
    public function renderEdit(int $id, int $conferenceId = null): void
    {
        $room = $this->roomService->find($id);
        if (!$room) {
            $this->error('Room not found');
        }

        $template = $this->getTemplate();
        $template->room = $room;
        $template->conferenceId = $conferenceId;

        $form = $this['roomForm']->getComponent('form');
        $form->setDefaults([
            'id' => $room->getId(),
            'roomNumber' => $room->roomNumber,
            'address' => $room->address,
            'conference' => $room->conference,
        ]);
    }

    public function renderDefault(int $conferenceId = null): void
    {
        if ($conferenceId !== null) {
            $rooms = $this->roomService->findByConference($conferenceId);
            $conference = $this->conferenceService->find($conferenceId); // Fetch the conference by its ID

            if (!$conference) {
                $this->error('Conference not found');
            }
            $this->template->conferenceId = $conferenceId;
            $this->template->conferenceName = $conference->title; // Pass the conference name to the template
        } else {
            $rooms = $this->roomService->findAll();
            $this->template->conferenceName = null; // No specific conference
        }

        $this->template->rooms = $rooms;
    }


    public function renderCreate(int $conferenceId = null): void
    {
        $template = $this->getTemplate();
        $template->room = null; // No room data since we're creating a new one
        
        // Fetch the conference entity based on the conferenceId
        $conference = $this->conferenceService->find($conferenceId);
        
        if (!$conference) {
            $this->error('Conference not found');
        }
        
        $template->conference = $conference;
        
        // Set default values for the form, including the Conference object
        $form = $this['roomForm']->getComponent('form');
        $form->setDefaults([
            'conference' => $conference,  // Automatically set the Conference object for the new room
        ]);
    }

   
}