<?php

namespace App\UI\Modules\Admin\Reservation;

use App\UI\Components\Presentation\PresentationGrid;
use App\UI\Components\Presentation\PresentationGridFactory;
use App\UI\Components\Reservation\ReservationGrid;
use App\UI\Components\Reservation\ReservationGridFactory;
use App\Model\Services\ConferenceService;
use App\UI\Components\User\UserGrid;
use App\UI\Components\User\UserGridFactory;
use App\UI\Modules\Admin\BaseAdminPresenter;
use Nette\DI\Attributes\Inject;

class ReservationPresenter extends BaseAdminPresenter {

	#[Inject]
	public ReservationGridFactory $gridFactory;

	#[Inject]
	public ConferenceService $conferenceService;

	private ?int $conferenceId = null;

    public function actionDefault(?int $conferenceId = null): void
    {
        $this->conferenceId = $conferenceId;

		if ($conferenceId) {
			$conference = $this->conferenceService->find($conferenceId); 
			if ($conference) {
				$this->template->conferenceName = $conference->title;
			}
		}
    }

	public function createComponentGrid(): ReservationGrid {
        $grid = $this->gridFactory->create();
        
        // Nastavíme conferenceId do gridu
        $grid->setConferenceId($this->conferenceId);
        
        return $grid;
    }
}
