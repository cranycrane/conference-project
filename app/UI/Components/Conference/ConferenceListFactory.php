<?php

namespace App\UI\Components\Conference;

use App\UI\Components\Presentation\PresentationList;
use Doctrine\Common\Collections\ArrayCollection;

interface ConferenceListFactory {

	public function create(ArrayCollection $conferences): ConferenceList;

}
