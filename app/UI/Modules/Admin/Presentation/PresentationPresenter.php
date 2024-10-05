<?php

namespace App\UI\Modules\Admin\Presentation;

use App\UI\Components\Presentation\PresentationGrid;
use App\UI\Components\Presentation\PresentationGridFactory;
use App\UI\Components\User\UserGrid;
use App\UI\Components\User\UserGridFactory;
use App\UI\Modules\Admin\BaseAdminPresenter;
use Nette\DI\Attributes\Inject;

class PresentationPresenter extends BaseAdminPresenter {

	#[Inject]
	public PresentationGridFactory $gridFactory;

	public function createComponentGrid(): PresentationGrid {
		return $this->gridFactory->create();
	}
}
