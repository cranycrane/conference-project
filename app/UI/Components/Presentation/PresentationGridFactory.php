<?php

namespace App\UI\Components\Presentation;

use App\UI\Components\Presentation\PresentationGrid;

interface PresentationGridFactory {

	public function create(): PresentationGrid;

}
