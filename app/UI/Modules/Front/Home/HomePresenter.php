<?php declare(strict_types = 1);

namespace App\UI\Modules\Front\Home;

use App\Model\Services\PresentationService;
use App\UI\Components\Presentation\PresentationList;
use App\UI\Components\Presentation\PresentationListFactory;
use App\UI\Modules\Front\BaseFrontPresenter;
use Nette\DI\Attributes\Inject;

final class HomePresenter extends BaseFrontPresenter
{

	#[Inject]
	public PresentationListFactory $presentationListFactory;

	#[Inject]
	public PresentationService $presentationService;

	public function createComponentPopularPresentations(): PresentationList {
		return $this->presentationListFactory->create($this->presentationService->findUpcomingPresentationsWithMostAttendances());
	}

}
