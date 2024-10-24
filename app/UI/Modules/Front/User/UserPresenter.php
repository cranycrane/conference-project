<?php declare(strict_types=1);

namespace App\UI\Modules\Front\User;

use App\Model\Services\ConferenceService;
use App\Model\Services\PresentationService;
use App\Model\Services\UserService;
use App\UI\Accessory\RequireLoggedUser;
use App\UI\Components\Conference\ConferenceList;
use App\UI\Components\Conference\ConferenceListFactory;
use App\UI\Components\Presentation\PresentationList;
use App\UI\Components\Presentation\PresentationListFactory;
use App\UI\Components\Sign\SignUpForm;
use App\UI\Components\Sign\SignUpFormFactory;
use App\UI\Form\FormFactory;
use App\UI\Modules\Front\BaseFrontPresenter;
use Nette\DI\Attributes\Inject;
use Nette\Forms\Form;

final class UserPresenter extends BaseFrontPresenter {

	use RequireLoggedUser;

	#[Inject]
	public UserService $userService;

	#[Inject]
	public SignUpFormFactory $signUpFormFactory;

	#[Inject]
	public PresentationListFactory $presentationListFactory;

	#[Inject]
	public PresentationService $presentationService;

	#[Inject]
	public ConferenceListFactory $conferenceListFactory;

	#[Inject]
	public ConferenceService $conferenceService;

	#[Inject]
	public FormFactory $formFactory;

	public function createComponentUpdateProfileForm(): SignUpForm {
		return $this->signUpFormFactory->create($this->userService->find($this->getUser()->getId()));
	}

	public function createComponentMyPresentations(): PresentationList {
		return $this->presentationListFactory->create($this->presentationService->findByBySpeaker($this->user->getId()));
	}

	public function createComponentMyConferences(): ConferenceList {
        $userId = $this->getUser()->getId();
        $upcomingConferences = $this->conferenceService->findByUser($userId);
        return $this->conferenceListFactory->create($upcomingConferences);
    }

	public function createComponentChangePasswordForm(): Form {
		$form = $this->formFactory->forFrontend();

		$form->addPassword('password', 'Heslo:')
			->setOption('description', sprintf('alespoň %d znaků', $this->userService::PasswordMinLength))
			->setRequired('Vytvořte si své heslo.')
			->addRule($form::MinLength, 'Heslo musí mít minimálně 8 znaků', $this->userService::PasswordMinLength);

		$form->addPassword('passwordVerify', 'Heslo znovu:')
			->setRequired('Zopakujte své heslo:')
			->addRule($form::Equal, 'Hesla se neshodují.', $form['password']);

		$form->addSubmit('send');

		// Handle form submission
		$form->onSuccess[] = function (Form $form, \stdClass $data): void {
			try {
				$this->userService->changePassword($this->getUser()->id, $data->password);
				$this->presenter->flashMessage('Heslo úspěšně změněno.', 'success');
			} catch (\Exception) {
				$this->presenter->flashMessage('Chyba při vytváření hesla.', 'error');
			}
		};

		return $form;
	}

}
