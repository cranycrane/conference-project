<?php declare(strict_types = 1);

namespace App\UI\Modules\Front;

use App\UI\Components\Conference\ConferenceForm;
use App\UI\Components\Conference\ConferenceFormFactory;
use App\UI\Modules\Base\UnsecuredPresenter;
use Nette\DI\Attributes\Inject;

abstract class BaseFrontPresenter extends UnsecuredPresenter
{

	#[Inject]
	public ConferenceFormFactory $conferenceFormFactory;

	public function createComponentConferenceForm(): ConferenceForm {
		return $this->conferenceFormFactory->create();
	}

}
