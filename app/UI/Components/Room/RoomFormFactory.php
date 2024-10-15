<?php

namespace App\UI\Components\Room;

interface RoomFormFactory
{
  public function create(bool $admin): RoomForm;
}