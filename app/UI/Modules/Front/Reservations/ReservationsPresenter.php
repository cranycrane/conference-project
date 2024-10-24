<?php

namespace App\UI\Modules\Front\Reservations;

use App\UI\Components\Reservation\MyReservationsGrid;
use App\UI\Components\Reservation\MyReservationsGridFactory;
use App\UI\Components\User\UserGrid;
use App\UI\Components\User\UserGridFactory;
use App\UI\Modules\Front\BaseFrontPresenter;
use Nette\DI\Attributes\Inject;

class ReservationsPresenter extends BaseFrontPresenter {

	#[Inject]
	public MyReservationsGridFactory $gridFactory;

	public function createComponentGrid(): MyReservationsGrid {
		return $this->gridFactory->create();
	}
}
