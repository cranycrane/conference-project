<?php declare(strict_types = 1);

namespace App\UI\Form;

use Contributte\FormsBootstrap\BootstrapForm;
use Nette\Forms\Form;
use Nette\Security\User;

final class FormFactory
{
	public function __construct(
		private User $user,
	) {
	}

	public function forFrontend(): Form
	{
		return $this->create();
	}

	public function forBackend(): Form
	{
		return $this->create();
	}

	private function create(): Form
	{
		$form = new BootstrapForm;
		if ($this->user->isLoggedIn()) {
			$form->addProtection();
		}
		return $form;
	}

}
