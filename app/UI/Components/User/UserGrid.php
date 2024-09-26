<?php

namespace App\UI\Components\User;

use App\Model\Services\UserService;
use Nette\Application\UI\Control;
use Ublaboo\DataGrid\Column\Action\Confirmation\StringConfirmation;
use Ublaboo\DataGrid\DataGrid;

class UserGrid extends Control {

	private UserService $userService;
	public function __construct(UserService $userService) {
		$this->userService = $userService;
	}

	public function createComponentGrid(): DataGrid {
		$grid = new DataGrid();

		$grid->setDataSource($this->userService->findAll());

		$grid->addColumnText('id', 'ID')
			->setSortable();

		$grid->addColumnText('email', 'E-mail')
			->setSortable();

		$grid->addAction('edit', 'Spravovat uživatele', 'edit!')
			->setIcon('pencil-alt')
			->setClass('btn btn-sm btn-primary');

		$grid->addAction('delete', 'Smazat', 'delete!')
			->setIcon('trash')
			->setClass('btn btn-sm btn-danger ajax')
			->setConfirmation(
				new StringConfirmation('Opravdu chcete smazat uživatele %s?', 'email')
			);

		return $grid;
	}

	public function handleEdit(int $id): void {
		$this->presenter->flashMessage('Uživatel úspěšně smazán', 'success');
		bdump("POMC");
	}

	public function handleDelete(int $id): void {
		try {
			$this->userService->delete($id);
			$this->getPresenter()->flashMessage('Uživatel úspěšně smazán', 'success');
		} catch (\Exception $e) {
			$this->getPresenter()->flashMessage('Chyba: ' . $e->getMessage(), 'error');
			$this->getPresenter()->redrawControl('flashMessages');
			return;
		}

		if ($this->presenter->isAjax()) {
			$this->getPresenter()->redrawControl('flashMessages');
			$this->redrawControl('flashes');
			$this['grid']->reload();
			bdump("AHOJ");
		} else {
			bdump("BUUU");
		}
	}

	public function render(): void
	{
		$this->template->setFile(__DIR__ . '/templates/UserGrid.latte');
		$this->template->render();
	}
}
