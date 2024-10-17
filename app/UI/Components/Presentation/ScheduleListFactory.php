<?php

namespace App\UI\Components\Presentation;

use Doctrine\Common\Collections\ArrayCollection;

interface ScheduleListFactory {

	public function create(ArrayCollection $presentations): ScheduleList;

}
