<?php declare(strict_types = 1);

namespace App\UI\Components\Room;

use Nette\Application\UI\Form;
use Nette\Application\UI\Control;
use App\Model\Services\RoomService;
use App\UI\Form\FormFactory;

class RoomForm extends Control
{
    private FormFactory $formFactory;
    private RoomService $RoomService;
    private ?int $conferenceId = null;

    public function __construct(FormFactory $formFactory, RoomService $RoomService)
    {
        $this->formFactory = $formFactory;
        $this->RoomService = $RoomService;
    }

    public function setConferenceId(?int $conferenceId): void {
        $this->conferenceId = $conferenceId; // Setter to pass the conferenceId
    }

    public function createComponentForm(): Form
    {
        // Využití vaší továrny na formuláře
        $form = $this->formFactory->forBackend(); // nebo forBackend(), pokud je formulář určen pro administrátory
        $form->setAjax(false);
        $form->addHidden('id');
      
        $form->addText('roomNumber', 'Room Number:')
            ->setRequired('Please enter the room number.');
        $form->addText('address', 'Address:')
            ->setRequired('Please enter the address.');


        $form->addSubmit('send', 'Uložit místnost');

        $form->addSubmit('back', 'Zpět')
            ->setHtmlAttribute('class', 'btn btn-secondary') // Šedé tlačítko
            ->setValidationScope([]) // Bez validace
            ->onClick[] = function() {
                // Use $this->conferenceId inside the callback
                $this->presenter->redirect('Room:default', ['conferenceId' => $this->conferenceId]);
        };

        $form->onSuccess[] = [$this, 'formSucceeded'];

        return $form;
    }

    public function formSucceeded(Form $form, $values): void
    {
        \Tracy\Debugger::barDump($values, 'Form Values');

        try {
            $this->RoomService->saveRoom($values, $this->conferenceId);
            $this->presenter->flashMessage('Místnost byla úspěšně upravena.', 'success');
        } catch (\Exception $e) {
            \Tracy\Debugger::barDump($values, 'Form Values');

            $this->presenter->flashMessage('Nastala chyba při ukládání místnosti.', 'error');
        }
        \Tracy\Debugger::barDump($this->conferenceId, 'conferenceId before redirect');
        if ($this->presenter->isAjax()) {
            // Přidáme payload pro přesměrování
            $this->presenter->payload->redirect = $this->presenter->link('Room:default', ['conferenceId' => $this->conferenceId]);
            $this->presenter->redrawControl(); // Překreslíme modální okno
        } else {
            // Pokud nejde o AJAX, přesměrujeme standardně
            $this->presenter->redirect('Room:default', ['conferenceId' => $this->conferenceId]);
        }
    }

    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/templates/RoomForm.latte');
        $this->template->render();
    }
}