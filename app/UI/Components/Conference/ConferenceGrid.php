<?php

namespace App\UI\Components\Conference;

use App\Model\Services\ConferenceService;
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

		$grid->addColumnText('title', 'Název')
			->setSortable();
		$grid->addColumnText('place', 'Místo')
			->setSortable();
		$grid->addColumnDateTime('startsAt', 'Začátek', 'getStartsAt')
			->setFormat('j.n.Y H:i'); // Případně upravte formát dle potřeby
		$grid->addColumnDateTime('endsAt', 'Konec', 'getEndsAt')
			->setFormat('j.n.Y H:i');
		$grid->addColumnNumber('priceForSeat', 'Cena za sedadlo')
			->setFormat(0, ',', ' ');
		$grid->addColumnNumber('capacity', 'Kapacita')
			->setFormat(0, ',', ' ');

	//	$grid->addAction('edit', 'Editovat', 'Edit!')
	//		->setIcon('pencil')
	//		->setClass('btn btn-primary');
	//	$grid->addAction('delete', 'Smazat', 'Delete!')
	//		->setIcon('trash')
	//		->setClass('btn btn-danger')
	//		->setConfirmation(new StringConfirmation('Opravdu chcete smazat konferenci %s?', 'title'));

		return $grid;
	}

	public function render(): void
	{
		$this->template->setFile(__DIR__ . '/templates/ConferenceGrid.latte');
		$this->template->render();
	}
}
