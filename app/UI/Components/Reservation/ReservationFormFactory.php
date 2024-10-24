<?php

namespace App\UI\Components\Reservation;

use App\Domain\Reservation\Reservation;

interface ReservationFormFactory {

	public function create(int $conferenceId, int $userId = null): ReservationForm;

}
