<?php declare(strict_types = 1);

namespace App\UI\Components\Conference;

use Nette\Application\UI\Form;
use Nette\Application\UI\Control;
use App\Model\Services\ConferenceService;
use App\UI\Form\FormFactory;
use DateTime;

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
            ->addRule(Form::MIN, 'Počet účastníků musí být kladné číslo.', 1);

        $form->addText('genre', 'Žánr konference:')
            ->setRequired('Prosím, zadejte žánr konference.');

        $form->addText('place', 'Místo konání:')
            ->setRequired('Prosím, zadejte místo konání.');

        $form->addText('startsAt', 'Začátek konference:')
            ->setRequired('Prosím, zadejte datum začátku.')
            ->addRule($form::PATTERN, 'Datum musí být ve formátu yyyy-mm-dd hh:mm:ss', '\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}');

        $form->addText('endsAt', 'Konec konference:')
            ->setRequired('Prosím, zadejte datum konce.')
            ->addRule($form::PATTERN, 'Datum musí být ve formátu yyyy-mm-dd hh:mm:ss', '\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}');

        $form->addInteger('priceForSeat', 'Cena za místo:')
            ->setRequired('Prosím, zadejte cenu.')
            ->addRule(Form::MIN, 'Cena musí být kladné číslo.', 0);

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
    \Tracy\Debugger::barDump($values, 'Form Values');

    try {
        $this->conferenceService->saveConference($values);
        $this->presenter->flashMessage('Konference byla úspěšně upravena.', 'success');
    } catch (\Exception $e) {
        \Tracy\Debugger::barDump($values, 'Form Values');

        $this->presenter->flashMessage('Nastala chyba při ukládání konference.', 'error');
    }

    if ($this->presenter->isAjax()) {
        // Přidáme payload pro přesměrování
        $this->presenter->payload->redirect = $this->presenter->link('Conference:default');
        $this->presenter->redrawControl(); // Překreslíme modální okno
    } else {
        // Pokud nejde o AJAX, přesměrujeme standardně
        $this->presenter->redirect('Conference:default');
    }
}

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/templates/ConferenceForm.latte');
        $this->template->render();
    }
}