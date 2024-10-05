<?php

namespace App\UI\Components\Conference;

use App\Model\Services\ConferenceService;
use Nette\Forms\Container;
use Nette\Application\UI\Control;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;


class ConferenceGrid extends Control {

	private $conferenceService;

	public function __construct(ConferenceService $conferenceService) {
		$this->conferenceService = $conferenceService;
	}

	public function createComponentGrid(): DataGrid {
		$grid = new DataGrid();
		$grid->setDataSource($this->conferenceService->findAll());
	
		// Přidání sloupců
		$grid->addColumnText('title', 'Název')
			->setSortable();
		$grid->addColumnText('place', 'Místo')
			->setSortable();
		$grid->addColumnDateTime('startsAt', 'Začátek', 'getStartsAt')
			->setFormat('j.n.Y H:i');
		$grid->addColumnDateTime('endsAt', 'Konec', 'getEndsAt')
			->setFormat('j.n.Y H:i');
		$grid->addColumnNumber('priceForSeat', 'Cena za sedadlo')
			->setFormat(0, ',', ' ');
		$grid->addColumnNumber('capacity', 'Kapacita')
			->setFormat(0, ',', ' ');
	

		// Akce pro mazání
		$grid->addAction('delete', 'Smazat', 'delete!')
			->setIcon('trash')
			->setClass('btn btn-danger')
			->setConfirmation(new StringConfirmation('Opravdu chcete smazat konferenci %s?', 'title'));

		$grid->addAction('edit', 'Upravit')
			->setClass('btn btn-primary') // Nastavení třídy pro modré tlačítko
			->setText('Upravit');
	
	
		return $grid;
	}

	public function handleDelete(int $id): void
	{
		$this->conferenceService->delete($id);
		$this->presenter->flashMessage('Konference byla úspěšně smazána.', 'success');
		$this->presenter->redrawControl('conferenceGrid');
	}

	public function handleEdit(int $id): void
{
    // Zpracujte úpravu zde - přesměrování na editaci nebo inline úprava
    $this->presenter->redirect('Conference:edit', $id); // Předpoklad, že máte edit akci v ConferencePresenter
}

	public function render(): void
	{
		$this->template->setFile(__DIR__ . '/templates/ConferenceGrid.latte');
		$this->template->render();
	}
}
