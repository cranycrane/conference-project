<?php

namespace App\UI\Components\Conference;

use App\Domain\Presentation\Presentation;
use App\Model\Services\ConferenceService;
use App\Model\Services\PresentationService;
use App\UI\Components\Presentation\PresentationForm;
use App\UI\Components\Presentation\PresentationFormFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Control;
use Nette\DI\Attributes\Inject;

class ConferenceList extends Control {

	private ConferenceFormFactory $conferenceFormFactory;

	private ConferenceService $conferenceService;

	private ArrayCollection $conferences;
  #[Persistent]
  public ?int $currentConferenceId = null;

	public function __construct(ConferenceService $conferenceService, ArrayCollection $conferences, ConferenceFormFactory $conferenceFormFactory) {
		$this->conferenceService = $conferenceService;
		$this->conferences = $conferences;
    	$this->conferenceFormFactory = $conferenceFormFactory;
	}

  	public function handleEdit(int $id): void {
      $this->currentConferenceId = $id;

      if ($this->presenter->isAjax()) {
        $this->redrawControl('conferenceEditSnippet');
      }
    }

	public function createComponentConferenceEditForm(): ConferenceForm {
		$id = $this->currentConferenceId;

		$conference = $this->conferences->filter(function ($presentation) use ($id) {
		  return $presentation->getId() === $id;
		})->first();

		$form = $this->conferenceFormFactory->create();
		$formComp = $form['form'];

		$formComp->setDefaults([
			'id' => $conference->getId(),
			'title' => $conference->title,
			'genre' => $conference->genre,
			'place' => $conference->place,
			'startsAt' => $conference->getStartsAt()->format('Y-m-d H:i:s'),
			'endsAt' => $conference->getEndsAt()->format('Y-m-d H:i:s'),
			'priceForSeat' => $conference->priceForSeat,
			'capacity' => $conference->capacity,
			'description' => $conference->description,
		]);


		return $form;
	}

	public function render(): void
	{
    	$this->template->currentConferenceId = $this->currentConferenceId;
		$this->template->conferences = $this->conferences;
		$this->template->currentDateTime = new \DateTime();
		$this->template->user = $this->presenter->getUser();
		$this->template->setFile(__DIR__ . '/templates/ConferenceList.latte');
		$this->template->render();
	}

}
