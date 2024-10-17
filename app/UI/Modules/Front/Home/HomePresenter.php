<?php declare(strict_types = 1);

namespace App\UI\Modules\Front\Home;

use App\Model\Services\ConferenceService;
use App\Model\Services\PresentationService;
use App\UI\Components\Conference\ConferenceList;
use App\UI\Components\Conference\ConferenceListFactory;
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

	#[Inject]
	public ConferenceListFactory $conferenceListFactory;

	#[Inject]
	public ConferenceService $conferenceService;


	public function createComponentPopularPresentations(): PresentationList {
		return $this->presentationListFactory->create($this->presentationService->findUpcomingPresentationsWithMostAttendances());
	}

	public function createComponentSoonConferences(): ConferenceList {
		bdump($this->conferenceService->find5UpcomingConferences()->count());
		return $this->conferenceListFactory->create($this->conferenceService->find5UpcomingConferences());
	}

}
