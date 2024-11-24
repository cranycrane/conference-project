<?php declare(strict_types = 1);

namespace App\UI\Components\Reservation;

use Nette\Application\UI\Form;
use Nette\Application\UI\Control;
use App\Domain\Reservation\Reservation;
use App\Model\Services\ReservationService;
use App\Model\Services\ConferenceService;
use App\Model\Services\UserService;
use App\UI\Form\FormFactory;
use Doctrine\Common\Collections\ArrayCollection;
use App\Model\Exception\Logic\DuplicateEmailException;
use App\Model\Exception\Logic\NoCapacityException;
use App\Domain\User\User;
use Tracy\Debugger;

class ReservationForm extends Control
{
    private FormFactory $formFactory;
    private ReservationService $reservationService;
    private UserService $userService;
    private ConferenceService $conferenceService;
    private int $conferenceId;
    private ?int $userId = null;
    private ?string $email = null;
    private ?string $firstName = null;
    private ?string $lastName = null;

    public function __construct(FormFactory $formFactory, ReservationService $reservationService,
                                UserService $userService, ConferenceService $conferenceService, int $conferenceId,  int $userId = null)
    {
        $this->formFactory = $formFactory;
        $this->reservationService = $reservationService;
        $this->userService = $userService;
        $this->conferenceId = $conferenceId;
        $this->conferenceService = $conferenceService;
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
        $form = $this->formFactory->forFrontend();
        $form->setAjax(false);
        $form->addHidden('id');

        $emailField = $form->addEmail('email', 'E-mail:')
			->setOption('required', true)
			->addRule($form::Email, 'Prosím zadejte platný e-mail')
            ->addRule(Form::MAX_LENGTH, 'E-mail může mít maximálně 255 znaků.', 255)
			->setRequired('Zadejte váš e-mail.');

        $firstNameField = $form->addText('firstName', 'Jméno:')
            ->setRequired('Prosím, zadejte křestní jméno.')
            ->addRule(Form::MAX_LENGTH, 'Jméno může mít maximálně 255 znaků.', 255);

        $lastNameField = $form->addText('lastName', 'Příjmení:')
            ->setRequired('Prosím, zadejte příjmení.')
            ->addRule(Form::MAX_LENGTH, 'Přijímení může mít maximálně 255 znaků.', 255);

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
                ->addCondition($form::Equal, true)
                ->toggle('#passwordField2')
                ->toggle('#passwordField');
        }

        $form->addInteger('numOfPeople', 'Počet lidí (max. 20):')
            ->setRequired('Prosím, zadejte počet lidí.')
            ->setDefaultValue(1)
            ->addRule(Form::MIN, 'Počet lidí musí být kladný.', 1)
            ->addRule(Form::MAX, "Konference má zbývající kapacitu jen {$this->reservationService->getRemainingCapacity($this->conferenceId)} osob", $this->reservationService->getRemainingCapacity($this->conferenceId))
            ->addRule(Form::MAX, 'Najednout můžete rezervovat maximálně 20 vstupenek', 20)
            ->setHtmlId('numOfPeople');

        $conference = $this->conferenceService->find($this->conferenceId);
        if (!$conference) {
            throw new \Exception('Konference nebyla nalezena.');
        }
        $pricePerSeat = $conference->priceForSeat;


        $form->addHidden('pricePerSeat', (string)$pricePerSeat);




        $form->addSubmit('submit', 'Potvrdit rezervaci');


        $form->onSuccess[] = [$this, 'formSucceeded'];
		$form->onError[] = [$this, 'formError'];

        return $form;
    }

	public function formError(Form $form): void
	{
		foreach ($form->getControls() as $control) {
			if ($control->error !== null) {
				$this->presenter->flashMessage('Chyba při provádění rezervace: ' . $control->error, 'error');
			}
		}
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

		}
        catch (DuplicateEmailException) {
            $this->presenter->flashMessage('Uživatel s tímto e-mailem jíž existuje', 'error');
		}
        catch (NoCapacityException) {
            $this->presenter->flashMessage('Konference nemá dostatečnou kapacitu', 'error');
        }
        $this->redirect('this');
	}

    public function render(): void
    {
        $this->template->userId = $this->userId;
        $conference = $this->conferenceService->find($this->conferenceId);
        if (!$conference) {
            throw new \Exception('Konference nebyla nalezena.');
        }

        $this->template->pricePerSeat = $conference->priceForSeat;
        $this->template->setFile(__DIR__ . '/templates/ReservationForm.latte');
        $this->template->render();
    }
}

