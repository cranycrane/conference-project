<?php

namespace App\UI\Components\Presentation;

use Doctrine\Common\Collections\ArrayCollection;

interface PresentationListFactory {

	public function create(ArrayCollection $presentations): PresentationList;

}
