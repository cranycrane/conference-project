<?php

namespace App\UI\Components\Sign;

use App\Domain\User\User;
use App\Model\Exception\Logic\DuplicateEmailException;
use App\Model\Services\UserService;
use App\UI\Form\FormFactory;
use Nette\Application\UI\Control;
use Nette\Forms\Form;

class SignUpForm extends Control {

	private UserService $userService;
	private FormFactory $formFactory;
	private ?User $user;

	public function __construct(
		UserService $userService,
		FormFactory $formFactory,
		User        $user = null
	) {
		$this->userService = $userService;
		$this->formFactory = $formFactory;
		$this->user = $user;
	}

	protected function createComponentForm(): Form
	{
		$form = $this->formFactory->forFrontend();

		$form->addEmail('email', 'E-mail:')
			->setOption('required', true)
			->addRule($form::Email, 'Prosím zadejte platný e-mail')
			->setRequired('Zadejte váš e-mail.');

		$form->addText('firstName', 'Křestní jméno:')
			->addRule($form::Filled, 'Prosím zadejte křesní jméno')
			->setRequired('Prosím, zadejte křestní jméno.');

		$form->addText('lastName', 'Příjmení:')
			->setRequired('Prosím, zadejte příjmení.');

		if(!$this->user) {

		$form->addPassword('password', 'Heslo:')
			->setOption('description', sprintf('alespoň %d znaků', $this->userService::PasswordMinLength))
			->setRequired('Vytvořte si své heslo.')
			->addRule($form::MinLength, 'Heslo musí mít minimálně 8 znaků', $this->userService::PasswordMinLength);

		$form->addPassword('passwordVerify', 'Heslo znovu:')
			->setRequired('Zopakujte své heslo:')
			->addRule($form::Equal, 'Hesla se neshodují.', $form['password']);
		}


		$form->addSubmit('send', $this->user ? 'Aktualizovat údaje'  : 'Registrovat se');

		if($this->user) {
			$form->setDefaults($this->user);
		}

		// Handle form submission
		$form->onSuccess[] = [$this, 'signUpFormSucceeded'];

		return $form;
	}

	public function signUpFormSucceeded(Form $form, array $values): void {
		try {
			if($this->user) {
				$this->user->email = $values['email'];
				$this->user->firstName = $values['firstName'];
				$this->user->lastName = $values['lastName'];

				$this->userService->update($this->user);

				$this->presenter->flashMessage('Údaje úspěšně aktualizovány', 'success');
			} else {
				// Attempt to register a new user
				$this->userService->create($values);
				$this->presenter->getUser()->login($values['email'], $values['password']);
				$this->presenter->flashMessage('Registrace byla úspěšná.', 'success');
				$this->presenter->redirect(':Front:Home:default');
			}
		} catch (DuplicateEmailException) {
			// Handle the case where the email is already taken
			$form['email']->addError('E-mail se již používá.');
		}
	}

	public function render(): void {
		$this->template->setFile(__DIR__.'/templates/SignUpForm.latte');
		$this->template->render();
	}

}
