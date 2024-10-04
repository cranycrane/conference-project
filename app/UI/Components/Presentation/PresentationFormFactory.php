<?php

namespace App\UI\Components\Presentation;

use App\Domain\Presentation\Presentation;

interface PresentationFormFactory {

	public function create(int $conferenceId, Presentation $presentation = null): PresentationForm;

}
