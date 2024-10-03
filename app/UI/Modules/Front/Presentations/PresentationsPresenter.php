<?php declare(strict_types = 1);

namespace App\UI\Modules\Front\Presentations;

use App\Model\Services\PresentationService;
use App\UI\Components\Presentation\PresentationList;
use App\UI\Components\Presentation\PresentationListFactory;
use App\UI\Modules\Front\BaseFrontPresenter;
use Nette\DI\Attributes\Inject;

final class PresentationsPresenter extends BaseFrontPresenter
{
	#[Inject]
	public PresentationListFactory $presentationListFactory;

	#[Inject]
	public PresentationService $presentationService;

	public function createComponentMyPresentations(): PresentationList {
		return $this->presentationListFactory->create($this->presentationService->findByBySpeaker($this->user->getId()));
	}
}
