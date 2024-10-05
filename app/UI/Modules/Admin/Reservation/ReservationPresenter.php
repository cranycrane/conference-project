<?php

namespace App\UI\Modules\Admin\Reservation;

use App\UI\Components\Presentation\PresentationGrid;
use App\UI\Components\Presentation\PresentationGridFactory;
use App\UI\Components\Reservation\ReservationGrid;
use App\UI\Components\Reservation\ReservationGridFactory;
use App\UI\Components\User\UserGrid;
use App\UI\Components\User\UserGridFactory;
use App\UI\Modules\Admin\BaseAdminPresenter;
use Nette\DI\Attributes\Inject;

class ReservationPresenter extends BaseAdminPresenter {

	#[Inject]
	public ReservationGridFactory $gridFactory;

	public function createComponentGrid(): ReservationGrid {
		return $this->gridFactory->create();
	}
}
