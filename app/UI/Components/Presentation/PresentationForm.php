<?php

namespace App\UI\Components\Presentation;

use App\Domain\Presentation\Presentation;
use App\Model\Services\PresentationService;
use App\UI\Form\FormFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Application\UI\Control;
use Nette\Forms\Form;
use Tracy\Debugger;
use Tracy\ILogger;

class PresentationForm extends Control {

	private PresentationService $presentationService;
	private FormFactory $formFactory;
	private ?Presentation $presentation;
	private int $conferenceId;

	public function __construct(
		PresentationService $presentationService,
		FormFactory         $formFactory,
		int                 $conferenceId,
		Presentation        $presentation = null
	) {
		$this->presentationService = $presentationService;
		$this->presentation = $presentation;
		$this->formFactory = $formFactory;
		$this->conferenceId = $conferenceId;
	}

	public function createComponentForm(): Form {
		$form = $this->formFactory->forFrontend();

		$isOrganizerOrAdmin = $this->presenter->getUser()->isInRole('admin') ||
			$this->presenter->getUser()->isInRole('organizer');

		$form->addHidden('id')
			->setDefaultValue($this->presentation ? $this->presentation->getId() : null);

		$form->addText('title', 'Název prezentace')
			->setRequired();

		$form->addTextArea('description', 'Popis prezentace')
			->setRequired();

		$form->addText('speakerName', 'Jméno řečníka:')
			->setDisabled(true)
			->setDefaultValue($this->presentation?->speaker->getFullname()
				?? $this->presenter->getUser()->getIdentity()->getFullname());

		$form->addDateTime('startsAt', 'Kdy začne:')
			->setDisabled(!$isOrganizerOrAdmin);

		$form->addDateTime('endsAt', 'Kdy skončí:')
			->setDisabled(!$isOrganizerOrAdmin);

		$roomOptions = [];
		if ($this->presentation) {
			foreach ($this->presentation->conference->rooms as $room) {
				$roomOptions[$room->getId()] = $room->roomNumber; // Room ID as key, Room number as value
			}
		}

		$form->addSelect('roomNumber', 'Místnost:', $roomOptions)
			->setDisabled(!$isOrganizerOrAdmin)
			->setHtmlAttribute('class', 'form-select')
			->setPrompt('Není přiřazena')
			->setDefaultValue($this->presentation?->room ? $this->presentation->room->getId() : null); // Use room ID, not room number


		$form->addUpload('photoImage', 'Fotka/Poster:')
			->setOption('description', sprintf('maximálně 5 MB, JPEG, PNG, GIF, WebP nebo AVIF'))
			->addRule($form::Image, 'Soubor musí být JPEG, PNG, GIF, WebP nebo AVIF')
			->addRule($form::MaxFileSize, 'Maximální velikost je 5 MB', 2 * 1024 * 1024);

		if($this->presentation) {
			$form->addSelect('state', 'Stav:', Presentation::STATES)
				->setDisabled(!$isOrganizerOrAdmin)
				->setHtmlAttribute('class', 'form-select')
				->setDefaultValue($this->presentation->state);

			$form->setDefaults($this->presentation);
		}

		$form->addSubmit('submit', 'Uložit');

		$form->onSuccess[] = [$this, 'formSucceeded'];

		return $form;
	}

	public function formSucceeded(Form $form, array $values): void {
		try {
			if($this->presentation) {
				$this->presentation->title = $values['title'];
				$this->presentation->description = $values['description'];
				if(isset($values['tags'])) {
					$this->presentation->tags = $values['tags'];
				}
				$this->presentation->state = $values['state'];

				$selectedRoom = $this->presentation->conference->rooms->filter(function ($room) use ($values) {
					return $room->getId() === (int) $values['roomNumber'];
				})->first();

				$this->presentation->room = $selectedRoom ?: null;

				if($values['photoImage']->isOk()) {
					$this->presentation->setPhotoUpload($values['photoImage']);
				}

				$this->presentationService->update($this->presentation, $values);

			} else {
				$values['userId'] = $this->presenter->getUser()->getId();
				$values['conferenceId'] = $this->conferenceId;

				$this->presentationService->create($values);
			}

			$this->presenter->flashMessage('Prezentace úspěšně uložena.', 'success');

		} catch(\Exception $e) {
			Debugger::log("ERROR Saving presentation: " . $e->getMessage(), ILogger::EXCEPTION);
			$this->presenter->flashMessage($e->getMessage(), 'error');
		}

		$this->presenter->redirect('this');
	}

	public function render(): void {
		$this->template->setFile(__DIR__.'/templates/PresentationForm.latte');
		$this->template->render();
	}

}
