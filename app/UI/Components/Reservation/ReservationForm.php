<?php declare(strict_types = 1);

namespace App\UI\Components\Reservation;

use Nette\Application\UI\Form;
use Nette\Application\UI\Control;
use App\Domain\Reservation\Reservation;
use App\Model\Services\ReservationService;
use App\Model\Services\UserService;
use App\UI\Form\FormFactory;
use Doctrine\Common\Collections\ArrayCollection;






class ReservationForm extends Control
{
    private FormFactory $formFactory;
    private ReservationService $reservationService;
    private UserService $userService;
    private int $conferenceId;
    private ?int $userId = null;
    private ?string $email = null;
    private ?string $firstName = null;
    private ?string $lastName = null;

    public function __construct(FormFactory $formFactory, ReservationService $reservationService,
                                UserService $userService, int $conferenceId, int $userId = null)
    {
        $this->formFactory = $formFactory;
        $this->reservationService = $reservationService;
        $this->userService = $userService;
        $this->conferenceId = $conferenceId;
        $this->userId = $userId;
        if ($this->userId) {
            $user = $this->userService->find($this->userId);
            if ($user){
                $this->email = $user->email;
                $this->firstName = $user->firstName;
                $this->lastName = $user->lastName;
            }
        }

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
            $emailField
                ->setDisabled()
                ->setDefaultValue($this->email);
            $firstNameField
                ->setDisabled()
                ->setDefaultValue($this->firstName);

            $lastNameField
                ->setDisabled()
                ->setDefaultValue($this->lastName);
        }

        $form->addInteger('numOfPeople', 'Počet lidí:')
            ->setRequired('Prosím, zadejte počet lidí.')
            ->setDefaultValue(1)
            ->addRule(Form::MIN, 'Počet lidí musí být kladný.', 1);

        $form->addSubmit('submit', 'Potvrdit rezervaci');

        $form->onSuccess[] = [$this, 'formSucceeded'];

        return $form;
    }

    public function formSucceeded(Form $form, array $values): void {
        try{
            if ($this->email && $this->firstName && $this->lastName){
                $values['email'] = $this->email;
			    $values['firstName'] = $this->firstName;
                $values['lastName'] = $this->lastName;
            }
			$values['userId'] = $this->userId;
			$values['conferenceId'] = $this->conferenceId;
			$this->reservationService->create($values);

			$this->presenter->flashMessage('Rezervace úspěšně uložena.', 'success');

		} catch (\Exception $e) {
			$this->presenter->flashMessage('Nastala neznámá chyba. Na opravě pracujeme.' . $e->getMessage(), 'error');
		}
        $this->redirect('this');
	}

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/templates/ReservationForm.latte');
        $this->template->render();
    }
}
