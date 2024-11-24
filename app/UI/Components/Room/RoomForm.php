<?php declare(strict_types = 1);

namespace App\UI\Components\Room;

use Nette\Application\UI\Form;
use Nette\Application\UI\Control;
use App\Domain\Room\Room;
use App\Model\Services\RoomService;
use App\UI\Form\FormFactory;
use Doctrine\Common\Collections\ArrayCollection;

class RoomForm extends Control
{
    private FormFactory $formFactory;
    private RoomService $roomService;
    private ?Room $room;
    private int $conferenceId;

    public function __construct(FormFactory $formFactory, RoomService $roomService, int $conferenceId, Room $room = null)
    {
        $this->formFactory = $formFactory;
        $this->roomService = $roomService;
        $this->room = $room;
		$this->conferenceId = $conferenceId;
    }

    public function createComponentForm(): Form
    {
        $form = $this->formFactory->forFrontend();

        $form->addHidden('id')
            ->setDefaultValue($this->room ? $this->room->getId() : null);

        $form->addText('roomNumber', 'Room Number:')
            ->setRequired('Please enter the room number.')
            ->addRule(Form::MAX_LENGTH, 'Číslo místnosti může mít maximálně 255 znaků.', 255);

        $form->addText('address', 'Address:')
            ->setRequired('Please enter the address.')
            ->addRule(Form::MAX_LENGTH, 'Adresa místnosti může mít maximálně 255 znaků.', 255);

        if ($this->room) {
            $form->setDefaults($this->room);
        }

        $form->addSubmit('submit', 'Uložit');

		$form->onSuccess[] = [$this, 'formSucceeded'];

		return $form;
    }

    public function formSucceeded(Form $form, array $values): void {
		try {
			if($this->room) {
				$this->room->roomNumber = $values['roomNumber'];
				$this->room->address = $values['address'];

				$this->roomService->update();

			} else {
				$values['conferenceId'] = $this->conferenceId;

				$this->roomService->create($values);
			}

			$this->presenter->flashMessage('Místnost byla úspěšně uložena.', 'success');

		} catch (\Exception $e) {
			$this->presenter->flashMessage('Nastala neznámá chyba. Na opravě pracujeme.' . $e->getMessage(), 'error');
		}
        $this->redirect('this');
	}


    public function render(): void
    {
        $this->template->setFile(__DIR__ . '/templates/RoomForm.latte');
        $this->template->render();
    }
}
