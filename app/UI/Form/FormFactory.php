<?php declare(strict_types = 1);

namespace App\UI\Form;

use App\Bootstrap;
use Contributte\Forms\Rendering\Bootstrap4InlineRenderer;
use Contributte\FormsBootstrap\BootstrapForm;
use Contributte\FormsBootstrap\Enums\BootstrapVersion;
use Contributte\FormsBootstrap\Enums\RenderMode;
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
		$form = new BootstrapForm();
		$form->setRenderer(new CustomBootstrapRenderer());
		$form->setAjax(false);
		if ($this->user->isLoggedIn()) {
			$form->addProtection();
		}
		return $form;
	}

}
