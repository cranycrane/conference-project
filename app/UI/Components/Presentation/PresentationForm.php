<?php

namespace App\UI\Components\Presentation;

use App\Domain\Presentation\Presentation;
use App\Model\Services\PresentationService;
use App\UI\Form\FormFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Application\UI\Control;
use Nette\Forms\Form;

class PresentationForm extends Control {

	private PresentationService $presentationService;
	private FormFactory $formFactory;
	private ?Presentation $presentation;
	private int $conferenceId;
	public function __construct(PresentationService $presentationService, FormFactory $formFactory, int $conferenceId, Presentation $presentation = null) {
		$this->presentationService = $presentationService;
		$this->presentation = $presentation;
		$this->formFactory = $formFactory;
		$this->conferenceId = $conferenceId;
	}

	public function createComponentForm(): Form {
		$form = $this->formFactory->forFrontend();

		$form->addText('title', 'Název prezentace')
			->setRequired();

		$form->addTextArea('description', 'Popis prezentace')
			->setRequired();

		$form->addText('speakerName', 'Jméno řečníka:')
			->setDisabled()
			->setDefaultValue($this->presentation->speaker->getFullname());

		$form->addDateTime('startsAt', 'Kdy začne:')
			->setDisabled();

		$form->addDateTime('endsAt', 'Kdy skončí:')
			->setDisabled();

		$form->addText('roomNumber', 'Místnost:')
			->setDisabled()
			->setDefaultValue($this->presentation->room ? $this->presentation->room->roomNumber : 'Není přiřazena');

		$form->addUpload('photoImage', 'Fotka/Poster:')
			->addRule($form::Image, 'Soubor musí být JPEG, PNG, GIF, WebP nebo AVIF')
			->addRule($form::MaxFileSize, 'Maximální velikost je 5 MB', 2 * 1024 * 1024);

		// todo vymyslet jak s fotkama

		if ($this->presentation) {
			$form->setDefaults($this->presentation);

			$form->addText('state', 'Stav:')
				->setDisabled()
				->setDefaultValue(Presentation::STATES[$this->presentation->state]);
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
				$this->presentation->tags = $values['tags'] ?? null;

				if ($values['photoImage']->isOk()) {
					$this->presentation->setPhotoUpload($values['photoImage']);
				}

				$this->presentationService->update();

			} else {
				$values['userId'] = $this->presenter->getUser()->getId();
				$values['conferenceId'] = $this->conferenceId;

				$this->presentationService->create($values);
			}

			$this->presenter->flashMessage('Prezentace úspěšně uložena.', 'success');

		} catch (\Exception $e) {
			$this->presenter->flashMessage('Nastala neznámá chyba. Na opravě pracujeme.' . $e->getMessage(), 'error');
		}
	}

	public function render(): void
	{
		$this->template->setFile(__DIR__ . '/templates/PresentationForm.latte');
		$this->template->render();
	}

}
