<?php declare(strict_types = 1);

namespace App\UI\Components\Conference;

use Nette\Application\UI\Form;
use Nette\Application\UI\Control;
use App\Model\Services\ConferenceService;
use App\UI\Form\FormFactory;
use DateTime;
use Tracy\Debugger;
use Tracy\ILogger;

class ConferenceForm extends Control
{
    private FormFactory $formFactory;
    private ConferenceService $conferenceService;

    public function __construct(FormFactory $formFactory, ConferenceService $conferenceService)
    {
        $this->formFactory = $formFactory;
        $this->conferenceService = $conferenceService;
    }

    public function createComponentForm(): Form
    {
        // Využití vaší továrny na formuláře
        $form = $this->formFactory->forBackend(); // nebo forBackend(), pokud je formulář určen pro administrátory
        $form->setAjax(false);
        $form->addHidden('id');


        $form->addText('title', 'Název konference:')
            ->setRequired('Prosím, zadejte název konference.');

        $form->addInteger('numOfPeople', 'Počet účastníků:')
            ->setRequired('Prosím, zadejte počet účastníků.')
            ->addRule(Form::Min, 'Počet účastníků musí být kladné číslo.', 1);

        $form->addText('genre', 'Žánr konference:')
            ->setRequired('Prosím, zadejte žánr konference.');

        $form->addText('place', 'Místo konání:')
            ->setRequired('Prosím, zadejte místo konání.');

        $form->addDate('startsAt', 'Začátek konference:')
			->setRequired('Prosím, zadejte datum začátku.');

        $form->addDate('endsAt', 'Konec konference:')
            ->setRequired('Prosím, zadejte datum konce.');

        $form->addInteger('priceForSeat', 'Cena za místo:')
            ->setRequired('Prosím, zadejte cenu.')
            ->addRule(Form::Min, 'Cena musí být kladné číslo.', 0);

        $form->addInteger('capacity', 'Kapacita:')
            ->setRequired('Prosím, zadejte kapacitu.')
            ->addRule(Form::MIN, 'Kapacita musí být kladné číslo.', 1);

        $form->addTextArea('description', 'Popis:')
            ->setNullable();

        $form->addSubmit('send', 'Uložit konferenci');

        $form->addSubmit('back', 'Zpět')
            ->setHtmlAttribute('class', 'btn btn-secondary') // Šedé tlačítko
            ->setValidationScope([]) // Bez validace
            ->onClick[] = function() {
                $this->presenter->redirect('Conference:default');
        };

        $form->onSuccess[] = [$this, 'formSucceeded'];

        return $form;
    }

    public function formSucceeded(Form $form, $values): void
{
    try {
        $this->conferenceService->saveConference($values);
        $this->presenter->flashMessage('Konference byla úspěšně upravena.', 'success');
    } catch (\Exception $e) {
        Debugger::log('Error while saving conference form:' . $e->getMessage(), ILogger::ERROR);
        $this->presenter->flashMessage('Nastala chyba při ukládání konference.', 'error');
    }
}

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/templates/ConferenceForm.latte');
        $this->template->render();
    }
}
