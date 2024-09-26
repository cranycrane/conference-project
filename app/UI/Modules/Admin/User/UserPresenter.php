<?php

namespace App\UI\Modules\Admin\User;

use App\UI\Components\User\UserGrid;
use App\UI\Components\User\UserGridFactory;
use App\UI\Modules\Admin\BaseAdminPresenter;
use Nette\DI\Attributes\Inject;

class UserPresenter extends BaseAdminPresenter {

	#[Inject]
	public UserGridFactory $userGridFactory;


	public function createComponentUserGrid(): UserGrid {
		return $this->userGridFactory->create();
	}
}
