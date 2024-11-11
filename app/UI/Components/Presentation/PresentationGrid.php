<?php

namespace App\UI\Components\Presentation;

use App\Domain\Presentation\Presentation;
use App\Model\Services\PresentationService;
use App\Model\Utils\Html;
use App\UI\Components\BaseGrid;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Application\UI\Control;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;

class PresentationGrid extends BaseGrid {

	private PresentationService $presentationService;

	private ?int $conferenceId = null;

	public function __construct(PresentationService $presentationService, ?int $conferenceId = null) {
		parent::__construct($presentationService);
		$this->presentationService = $presentationService;
		$this->conferenceId = $conferenceId;
	}

	public function setConferenceId(?int $conferenceId): void {
        $this->conferenceId = $conferenceId;
    }

	public function createComponentGrid(): DataGrid {
		$grid = new DataGrid();

		if ($this->conferenceId !== null) {
            $grid->setDataSource($this->presentationService->findByConference($this->conferenceId));
        } else {
            $grid->setDataSource($this->presentationService->findAll());
        }

		$grid->addColumnText('title', 'Název')
			->setSortable();

		$grid->addColumnText('state', 'Stav')
			->setSortable()
			->setRenderer(function ($item) {
				return $item->getStateLabel();
			});

		$grid->addColumnText('description', 'Popis')
			->setRenderer(function ($item) {
				return mb_strimwidth($item->description, 0, 100, '...');
			});

		$grid->addColumnText('tags', 'Tagy')
			->setRenderer(function (Presentation $presentation) {
				return is_array($presentation->tags) ? implode(', ', $presentation->tags) : '';
			});

		$grid->addColumnText('photo', 'Foto');

		$grid->addColumnDateTime('startsAt', 'Začátek')
			->setFormat('j.n.Y H:i')
			->setSortable();

		$grid->addColumnDateTime('endsAt', 'Konec')
			->setFormat('j.n.Y H:i')
			->setSortable();

		$grid->addColumnText('speaker.email', 'Řečník')
			->setSortable();

		$grid->addColumnText('formFilled', 'Přidělena místnost')
			->setRenderer(function($presentation) {
				if ($presentation->room) {
					return $presentation->room->roomNumber;
				} else {
					return Html::el('i')->class('fa fa-times text-danger')->aria('hidden', 'true');
				}
			})
			->setAlign('center');

		$grid->addColumnText('attendanceCount', 'Počet účastníků')
			->setRenderer(function($presentation) {
				return $presentation->attendances->count();
			})
			->setSortable();

		$this->addDeleteAction($grid);

		$this->addTranslation($grid);

		return $grid;
	}

	public function render(): void
	{
		$this->template->setFile(__DIR__ . '/templates/PresentationGrid.latte');
		$this->template->render();
	}
}
