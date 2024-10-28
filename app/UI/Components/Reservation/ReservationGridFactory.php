<?php

namespace App\UI\Components\Reservation;

interface ReservationGridFactory {

	public function create(?int $conferenceId = null): ReservationGrid;

}
