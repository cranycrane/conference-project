<?php declare(strict_types = 1);

namespace App\UI\Components\Reservation;

use Nette\Application\UI\Form;
use Nette\Application\UI\Control;
use App\Model\Services\ReservationService;
use App\UI\Form\FormFactory;
use DateTime;

class ReservationForm extends Control
{
    private FormFactory $formFactory;
    private ReservationService $ReservationService;
    private ?string $email;
    private ?string $firstName;
    private ?string $lastName;

    public function __construct(FormFactory $formFactory, ReservationService $ReservationService, 
                                string $email = null, string $firstName = null, string $lastName = null)
    {
        $this->formFactory = $formFactory;
        $this->ReservationService = $ReservationService;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
    }

    public function createComponentForm(): Form
    {
        // Využití vaší továrny na formuláře
        $form = $this->formFactory->forFrontend(); // nebo forBackend(), pokud je formulář určen pro administrátory
        $form->setAjax(false);
        $form->addHidden('id');

        $emailField = $form->addText('email', 'Email:')
            ->setRequired('Prosím, zadejte email.');

        $firstNameField = $form->addText('firstName', 'Jméno:')
            ->setRequired('Prosím, zadejte křestní jméno.');

        $lastNameField = $form->addText('lastName', 'Příjmení:')
            ->setRequired('Prosím, zadejte příjmení.');

        if ($this->email && $this->firstName && $this->lastName) {
            // Set email value and disable the field if an email is passed
            $emailField->setDefaultValue($this->email)
                ->setDisabled();
            $emailField->setDefaultValue($this->email)
                ->setDisabled();
            $emailField->setDefaultValue($this->email)
                ->setDisabled();
        }

        $form->addInteger('numOfPeople', 'Počet lidí:')
            ->setRequired('Prosím, zadejte počet lidí.')
            ->setDefaultValue(1)
            ->addRule(Form::MIN, 'Počet lidí musí být kladný.', 1);

        $form->addSubmit('send', 'Uložit konferenci');

        $form->onSuccess[] = [$this, 'formSucceeded'];

        return $form;
    }

    public function formSucceeded(Form $form, $values): void
{
    \Tracy\Debugger::barDump($values, 'Form Values');

    try {
        $this->ReservationService->saveReservation($values);
        $this->presenter->flashMessage('Rezervace byla úspěšně uložena.', 'success');
    } catch (\Exception $e) {
        \Tracy\Debugger::barDump($values, 'Form Values');

        $this->presenter->flashMessage('Nastala chyba při ukládání rezervace.', 'error');
    }

//    if ($this->presenter->isAjax()) {
//        // Přidáme payload pro přesměrování
//        $this->presenter->payload->redirect = $this->presenter->link('Reservation:default');
//        $this->presenter->redrawControl(); // Překreslíme modální okno
//    } else {
//        // Pokud nejde o AJAX, přesměrujeme standardně
//        $this->presenter->redirect('Reservation:default');
//    }
}

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/templates/ReservationForm.latte');
        $this->template->render();
    }
}
