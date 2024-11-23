<?php declare(strict_types = 1);

namespace App\UI\Components\Question;

use App\Domain\Question\Question;
use Nette\Application\UI\Control;
use App\Domain\Reservation\Reservation;
use App\Model\Services\QuestionService;
use App\Model\Services\UserService;
use App\UI\Form\FormFactory;
use Doctrine\Common\Collections\ArrayCollection;
use App\Model\Exception\Logic\DuplicateEmailException;
use App\Domain\User\User;
use Nette\Forms\Form;
use Tracy\Debugger;

class QuestionForm extends Control
{
    private FormFactory $formFactory;
    private QuestionService $questionService;
    private int $presentationId;
    private ?int $userId = null;
	private ?Question $question;

    public function __construct(FormFactory $formFactory, QuestionService $questionService,
								int $presentationId, ?int $userId, ?Question $question = null)
    {
        $this->formFactory = $formFactory;
        $this->questionService = $questionService;
		$this->presentationId = $presentationId;
        $this->userId = $userId;
		$this->question = $question;
    }

    public function createComponentForm(): Form
    {
        $form = $this->formFactory->forFrontend();

        $form->addHidden('id');

        $question = $form->addTextArea('question', 'Otázka:')
            ->setRequired('Prosím, zadejte otázku')
            ->addRule(Form::MAX_LENGTH, 'Otázka může mít maximálně 255 znaků.', 255);


		if($this->question) {
			$form->setDefaults($this->question);
		}

        $form->addSubmit('submit', 'Odeslat otázku');

        $form->onSuccess[] = [$this, 'formSucceeded'];

        return $form;
    }

    public function formSucceeded(Form $form, array $values): void {
        try{
			if($this->question) {
				$this->question->question = $values['question'];

				$this->questionService->update();
			} else {
				$values['userId'] = $this->userId;
				$values['presentationId'] = $this->presentationId;

				$this->questionService->create($values);
			}

			$this->presenter->flashMessage('Otázka úspěšně uložena.', 'success');

		} catch (\Exception) {
			$this->presenter->flashMessage('Nastala neznámá chyba při ukládání otázky', 'error');
		}

		$this->redirect('this');
	}

    public function render(): void
    {
        $this->template->userId = $this->userId;
        $this->template->setFile(__DIR__ . '/templates/QuestionForm.latte');
        $this->template->render();
    }
}
