<?php declare(strict_types = 1);

namespace App\UI\Components\Conference;

use Nette\Application\UI\Form;
use Nette\Application\UI\Control;
use App\Model\Services\ConferenceService;
use App\Model\Exception\Logic\NoCapacityException;
use App\UI\Form\FormFactory;
use DateTime;
use Tracy\Debugger;
use Tracy\ILogger;

class ConferenceForm extends Control
{
    private FormFactory $formFactory;
    private ConferenceService $conferenceService;
    private ?int $conferenceId = null;

    public function __construct(FormFactory $formFactory, ConferenceService $conferenceService)
    {
        $this->formFactory = $formFactory;
        $this->conferenceService = $conferenceService;
    }

    public function setId(int $conferenceId){
        $this->conferenceId = $conferenceId;
    }

    public function createComponentForm(): Form
    {

        // Využití vaší továrny na formuláře
        $form = $this->formFactory->forBackend();
        $form->setAjax(false);
        $form->addHidden('id');

        $form->addText('title', 'Název konference:')
            ->setRequired('Prosím, zadejte název konference.')
            ->addRule(Form::MAX_LENGTH, 'Název konference může mít maximálně 255 znaků.', 255);

        $form->addText('genre', 'Žánr konference:')
            ->setRequired('Prosím, zadejte žánr konference.')
            ->addRule(Form::MAX_LENGTH, 'Žánr konference může mít maximálně 255 znaků.', 255);

        $form->addText('place', 'Místo konání:')
            ->setRequired('Prosím, zadejte místo konání.');

		$isConferenceRunning = $this->conferenceId &&
			(new DateTime() > $this->conferenceService->find($this->conferenceId)->getStartsAt());

        $form->addDateTime('startsAt', 'Začátek konference:')
			->setRequired('Prosím, zadejte datum začátku.')
            ->addRule(function ($control) {
                $startDate = $control->value;
                if (!$startDate instanceof \DateTimeInterface) {
                    $startDate = new DateTime($startDate);
                }
                return $startDate >= new DateTime('today');
            }, 'Datum začátku nemůže být v minulosti.');

        $form->addDateTime('endsAt', 'Konec konference:')
			->setOmitted(false)
            ->setRequired('Prosím, zadejte datum konce.')
            ->addRule(function ($control) use ($form) {
                $startDate = $form['startsAt']->value;
                if (!$startDate instanceof \DateTimeInterface) {
                    $startDate = new DateTime($startDate);
                }
                $endDate = $control->value;
                if (!$endDate instanceof \DateTimeInterface) {
                    $endDate = new DateTime($endDate);
                }
                return $endDate > $startDate;
            }, 'Datum konce musí být pozdější než datum začátku.');

		if ($isConferenceRunning) {
			$form['startsAt']->setHtmlAttribute('readonly', 'readonly');
			$form['endsAt']->setHtmlAttribute('readonly', 'readonly');
		}

        $form->addInteger('priceForSeat', 'Cena za místo:')
            ->setRequired('Prosím, zadejte cenu.')
            ->addRule(Form::Min, 'Cena musí být kladné číslo.', 0);

        $numAttendees = $this->conferenceId ? $this->conferenceService->find($this->conferenceId)->getNumOfAttendees() : 0;

        $form->addInteger('capacity', 'Kapacita:')
            ->setRequired('Prosím, zadejte kapacitu.')
            //->addRule(Form::MIN, "Kapacita musí být větší než počet již přihlášených účastníků, tzn. {$numAttendees}.", $numAttendees)
            ->addRule(Form::MIN, 'Kapacita musí být kladné číslo.', 1);

        $form->addTextArea('description', 'Popis:')
            ->setNullable()
            ->addRule(Form::MAX_LENGTH, 'Popis konference může mít maximálně 255 znaků.', 255);

		$form->addUpload('photoImage', 'Fotka/Poster:')
			->setOption('description', sprintf('maximálně 5 MB, JPEG, PNG, GIF, WebP nebo AVIF'))
			->addRule($form::Image, 'Soubor musí být JPEG, PNG, GIF, WebP nebo AVIF')
			->addRule($form::MaxFileSize, 'Maximální velikost je 5 MB', 2 * 1024 * 1024);


        $form->addSubmit('send', 'Uložit konferenci');

        $form->onSuccess[] = [$this, 'formSucceeded'];
        $form->onError[] = [$this, 'formError'];

        return $form;
    }

    public function formError(Form $form): void
    {
        foreach ($form->getControls() as $control) {
            if ($control->error !== null) {
                $this->presenter->flashMessage($control->error, 'error');
            }
        }
    }

    public function formSucceeded(Form $form, $values): void
{
    try {
        $this->conferenceService->saveConference($values);
        $this->presenter->flashMessage('Konference byla úspěšně upravena.', 'success');
    }
    catch (NoCapacityException) {
        $this->presenter->flashMessage('Kapacita musí být větší než počet již přihlášených účastníků', 'error');
    }
    catch (\Exception $e) {
        Debugger::log('Error while saving conference form:' . $e->getMessage(), ILogger::ERROR);
        $errorMessage = $e->getMessage();
        $this->presenter->flashMessage($errorMessage, 'error');
    }
	$this->redirect('this');
}

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/templates/ConferenceForm.latte');
        $this->template->render();
    }
}
