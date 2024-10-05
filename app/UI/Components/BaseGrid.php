<?php

namespace App\UI\Components;

use App\Model\Services\ICrudService;
use Nette\Application\UI\Control;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;
use Ublaboo\DataGrid\Localization\SimpleTranslator;

class BaseGrid extends Control {

	/** @var ICrudService */
	protected ICrudService $crudService;

	public function __construct(ICrudService $crudService) {
		$this->crudService = $crudService;
	}

	public function handleDelete(int $id): void {
		try {
			$this->crudService->delete($id);
			$this->presenter->flashMessage('Záznam úspěšně smazán.', 'success');
		} catch (\Exception $e) {
			$this->presenter->flashMessage('Chyba při mazání záznamu: ' . $e->getMessage(), 'error');
		}

		$this->presenter->redirect('this');
	}

	protected function addDeleteAction(DataGrid $grid): void {
		$grid->addAction('delete', 'Smazat', 'delete!')
			->setClass('btn btn-danger')
			->setIcon('trash')
			->setConfirmation(
				new StringConfirmation('Opravdu chcete smazat tento záznam?')
			);
	}

	protected function addTranslation(DataGrid $grid): void {
		$translator = new SimpleTranslator([
			'ublaboo_datagrid.no_item_found_reset' => 'Žádné položky nenalezeny. Filtr můžete vynulovat',
			'ublaboo_datagrid.no_item_found' => 'Žádné položky nenalezeny.',
			'ublaboo_datagrid.here' => 'zde',
			'ublaboo_datagrid.items' => 'Položky',
			'ublaboo_datagrid.all' => 'všechny',
			'ublaboo_datagrid.from' => 'z',
			'ublaboo_datagrid.reset_filter' => 'Resetovat filtr',
			'ublaboo_datagrid.group_actions' => 'Hromadné akce',
			'ublaboo_datagrid.show_all_columns' => 'Zobrazit všechny sloupce',
			'ublaboo_datagrid.hide_column' => 'Skrýt sloupec',
			'ublaboo_datagrid.action' => 'Akce',
			'ublaboo_datagrid.previous' => 'Předchozí',
			'ublaboo_datagrid.next' => 'Další',
			'ublaboo_datagrid.choose' => 'Vyberte',
			'ublaboo_datagrid.execute' => 'Provést',
		]);

		$grid->setTranslator($translator);
	}
}
