<?php declare(strict_types = 1);

namespace App\UI\Components\Reservation;

use Nette\Application\UI\Form;
use Nette\Application\UI\Control;
use App\Domain\Reservation\Reservation;
use App\Model\Services\ReservationService;
use App\Model\Services\UserService;
use App\UI\Form\FormFactory;
use Doctrine\Common\Collections\ArrayCollection;
use App\Model\Exception\Logic\DuplicateEmailException;
use App\Domain\User\User;







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

        $emailField = $form->addEmail('email', 'E-mail:')
			->setOption('required', true)
			->addRule($form::Email, 'Prosím zadejte platný e-mail')
			->setRequired('Zadejte váš e-mail.');

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
        else{
            $registrationCheckbox = $form->addCheckbox('registration', 'Chci se zároveň registrovat');

            $passwordField = $form->addPassword('password', 'Heslo:')
                ->setHtmlId('passwordField')
                ->setRequired(false)
                ->setOption('description', sprintf('alespoň %d znaků', $this->userService::PasswordMinLength));
                
            $passwordField2 = $form->addPassword('passwordVerify', 'Heslo znovu:')
                ->setHtmlId('passwordField2')
                ->setRequired(false);
                
            // Add condition to require the password field if the registration checkbox is checked
            $passwordField->addConditionOn($registrationCheckbox, $form::EQUAL, true)
                ->setRequired('Vytvořte si své heslo.')
                ->addRule($form::MinLength, 'Heslo musí mít minimálně 8 znaků', $this->userService::PasswordMinLength);

            $passwordField2->addConditionOn($registrationCheckbox, $form::EQUAL, true)
                ->setRequired('Zopakujte své heslo:')
                ->addRule($form::Equal, 'Hesla se neshodují.', $form['password']);

            $registrationCheckbox
                ->addCondition($form::EQUAL, true)
                ->toggle('#passwordField2') 
                ->toggle('#passwordField');       
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
                $values['password'] = null;
            }
			$values['userId'] = $this->userId;
			$values['conferenceId'] = $this->conferenceId;

            if($values['password']){
                $newUser = $this->userService->create($values);
                $values['userId'] = $newUser->getId();
            }

			$this->reservationService->create($values);

			$this->presenter->flashMessage('Rezervace úspěšně uložena.', 'success');

		} catch (\Exception $e) {
			$this->presenter->flashMessage('Nastala neznámá chyba. Na opravě pracujeme.' . $e->getMessage(), 'error');
            
		}
        $this->redirect('this');
	}

    public function render(): void
    {
        $this->template->userId = $this->userId;
        $this->template->setFile(__DIR__ . '/templates/ReservationForm.latte');
        $this->template->render();
    }
}
