<?php declare(strict_types = 1);

namespace App\UI\Modules\Base;

use App\Model\Latte\TemplateProperty;
use App\Model\Security\SecurityUser;
use App\UI\Components\Conference\ConferenceForm;
use App\UI\Components\Conference\ConferenceFormFactory;
use App\UI\Control\TFlashMessage;
use App\UI\Control\TModuleUtils;
use Contributte\Application\UI\Presenter\StructuredTemplates;
use Nette\Application\UI\Presenter;
use Nette\DI\Attributes\Inject;

/**
 * @property-read TemplateProperty $template
 * @property-read SecurityUser $user
 */
abstract class BasePresenter extends Presenter
{

	use StructuredTemplates;
	use TFlashMessage;
	use TModuleUtils;


	public function beforeRender(): void {
		$this->template->user = $this->getUser();
	}



}
