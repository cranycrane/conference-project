<?php

namespace App\UI\Components\Presentation;

use App\Domain\Presentation\Presentation;
use App\Model\Services\PresentationService;
use App\Model\Utils\Html;
use App\UI\Components\BaseGrid;
use Doctrine\Common\Collections\ArrayCollection;
use Nette\Application\Attributes\Persistent;
use Nette\Application\UI\Control;
use Nette\DI\Attributes\Inject;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;

class PresentationGrid extends BaseGrid {

	private PresentationFormFactory $formFactory;
	private PresentationService $presentationService;
	#[Persistent]
	public ?int $currentPresentationId = null;
	public function __construct(PresentationService $presentationService, PresentationFormFactory $formFactory) {
		parent::__construct($presentationService);
		$this->presentationService = $presentationService;
		$this->formFactory = $formFactory;
	}

	public function createComponentGrid(): DataGrid {
		$grid = new DataGrid();
		$grid->setDataSource($this->presentationService->findAll());

		$grid->addColumnText('title', 'Název')
			->setSortable();

		$grid->addColumnStatus('state', 'Stav')
			->setOptions(Presentation::STATES)
			->onChange[] = [$this, 'statusChange'];

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

		$grid->addAction('edit', 'Upravit', 'edit!')
			->setClass('btn btn-primary ajax')
			->setDataAttribute('bs-toggle', 'modal')
			->setDataAttribute('bs-target', '#dialog-presentation');


		$this->addDeleteAction($grid);

		$this->addTranslation($grid);

		return $grid;
	}

	public function statusChange($id, $newStatus): void
	{
		$presentation = $this->presentationService->find($id);
		if ($presentation) {
			$presentation->setState($newStatus);
			$this->presentationService->update();

			$this->presenter->flashMessage('Stav prezentace byl úspěšně změněn.', 'success');
		} else {
			$this->presenter->flashMessage('Prezentace nebyla nalezena.', 'danger');
		}

		// Redraw the item to update the status in the grid

		if ($this->presenter->isAjax()) {
			$this['grid']->redrawItem($id);
		}
	}

	public function createComponentPresentationEditForm(): PresentationForm {
		$presentation = $this->presentationService->find($this->currentPresentationId);
		return $this->formFactory->create($presentation->conference->getId(), $presentation);
	}

	public function handleEdit($id): void {
		$this->currentPresentationId = $id;
		$this->redrawControl('presentationEditSnippet');
	}

	public function render(): void
	{
		$this->template->currentPresentationId = $this->currentPresentationId;
		$this->template->setFile(__DIR__ . '/templates/PresentationGrid.latte');
		$this->template->render();
	}
}
