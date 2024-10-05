<?php

namespace App\UI\Modules\Front\Sign;

use App\Model\Exception\Logic\DuplicateEmailException;
use App\Model\Exception\Runtime\AuthenticationException;
use App\Model\Services\UserService;
use App\UI\Form\FormFactory;
use App\UI\Modules\Base\BasePresenter;
use App\UI\Modules\Front\BaseFrontPresenter;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Presenter;
use Nette\DI\Attributes\Inject;
use Nette\Forms\Form;

class SignPresenter extends BaseFrontPresenter {
	/**
	 * Stores the previous page hash to redirect back after successful login.
	 */
	#[Persistent]
	public string $backlink = '';
	#[Inject]
	public UserService $userService;

	#[Inject]
	public FormFactory $formFactory;

	protected function createComponentSignInForm(): Form
	{
		$form = $this->formFactory->forFrontend();
		$form->addText('email', 'E-mail:')
			->setRequired('Zadejte e-mail');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Zadejte heslo.');

		$form->addSubmit('send');

		// Handle form submission
		$form->onSuccess[] = function (Form $form, \stdClass $data): void {
			try {
				// Attempt to login user
				$this->getUser()->login($data->email, $data->password);
				$this->getUser()->setExpiration('2 hours');
				$this->restoreRequest($this->backlink);
				if ($this->getUser()->isInRole('admin')) {
					$this->redirect(':Admin:Home:');
				}
				else {
					$this->redirect(':Front:Home:');
				}
			} catch (AuthenticationException) {
				$form->addError('Jméno nebo heslo je chybně zadáno');
				$this->flashMessage('Jméno nebo heslo chybně zadáno', 'error');
			}
		};

		return $form;
	}

	protected function createComponentSignUpForm(): Form
	{
		$form = $this->formFactory->forFrontend();

		$form->addEmail('email', 'E-mail:')
			->addRule($form::Email, 'Prosím zadejte platný e-mail')
			->setRequired('Zadejte váš e-mail.');

		$form->addText('firstName', 'Křestní jméno:')
			->setRequired('Prosím, zadejte křestní jméno.');

		$form->addText('lastName', 'Příjmení:')
			->setRequired('Prosím, zadejte příjmení.');

//		$form->addInteger('yearBorn', 'Rok narození:')
//			->addRule($form::Min, 'Zadejte platný rok narození', 1940)
//			->addRule($form::Max, 'Zadejte platný rok narození', (int) date('Y'))
//			->setRequired('Prosím, zadejte rok narození.');

		$form->addPassword('password', 'Heslo:')
			->setOption('description', sprintf('alespoň %d znaků', $this->userService::PasswordMinLength))
			->setRequired('Vytvořte si své heslo.')
			->addRule($form::MinLength, 'Heslo musí mít minimálně 8 znaků', $this->userService::PasswordMinLength);

		$form->addPassword('passwordVerify', 'Heslo znovu:')
			->setRequired('Zopakujte své heslo:')
			->addRule($form::Equal, 'Hesla se neshodují.', $form['password']);


		$form->addSubmit('send', 'Registrovat se');

		// Handle form submission
		$form->onSuccess[] = function (Form $form, array $data): void {
			try {
				// Attempt to register a new user
				$this->userService->create($data);
				$this->getUser()->login($data['email'], $data['password']);

				$this->presenter->flashMessage('Registrace byla úspěšná.', 'success');
				$this->redirect(':Front:Home:default');
			} catch (DuplicateEmailException) {
				// Handle the case where the email is already taken
				$form['email']->addError('E-mail se již používá.');
			}
		};

		return $form;
	}

	/**
	 * Logs out the currently authenticated user.
	 */
	public function actionOut(): void
	{
		$this->getUser()->logout();
	}

}
