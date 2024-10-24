<?php

namespace App\UI\Components\Room;

use App\Domain\Room\Room;

interface RoomFormFactory
{
  public function create(int $conferenceId, Room $room = null): RoomForm;
}