<?php

namespace App\UI\Modules\Front\Sign;

use App\Model\Exception\Runtime\AuthenticationException;
use App\Model\Services\UserService;
use App\UI\Form\FormFactory;
use App\UI\Modules\Base\BasePresenter;
use App\UI\Modules\Front\BaseFrontPresenter;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\DI\Attributes\Inject;

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

	/**
	 * Logs out the currently authenticated user.
	 */
	public function actionOut(): void
	{
		$this->getUser()->logout();
	}

}
