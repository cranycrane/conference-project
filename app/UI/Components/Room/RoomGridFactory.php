<?php

namespace App\UI\Components\Room;

use App\UI\Components\Room\RoomGrid;

interface RoomGridFactory {

	public function create(int $conferenceId): RoomGrid;

}