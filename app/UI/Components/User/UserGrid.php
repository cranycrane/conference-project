<?php

namespace App\UI\Components\User;

use App\Domain\User\User;
use App\Model\Services\UserService;
use Nette\Application\UI\Control;
use Nette\Forms\Container;
use Nette\Utils\ArrayHash;
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

		$grid->addColumnText('id', 'ID');

		$grid->addColumnText('email', 'E-mail');

		$grid->addColumnText('firstName', 'Křestní jméno');

		$grid->addColumnText('lastName', 'Příjmení');

		$grid->addColumnStatus('state', 'Stav')
			->setOptions(User::STATES) //
			->onChange[] = [$this, 'statusChange'];

		$grid->addInlineEdit()
			->onControlAdd[] = function(Container $container): void {
			$container->addText('email', '');
			$container->addText('firstName', '');
			$container->addText('lastName', '');
		};

		$grid->getInlineEdit()->onSetDefaults[] = function(Container $container, $item): void {
			$container->setDefaults([
				'email' => $item->email,
				'firstName' => $item->firstName,
				'lastName' => $item->lastName,
			]);
		};

		$grid->getInlineEdit()->onSubmit[] = function($id, ArrayHash $values): void {
			/**
			 * Save new values
			 */
			$user = $this->userService->find($id);
			$user->email = $values['email'];
			$user->firstName = $values['firstName'];
			$user->lastName = $values['lastName'];
			$this->userService->update($user);
		};

		$grid->addAction('delete', 'Smazat', 'delete!')
			->setIcon('trash')
			->setClass('btn btn-sm btn-danger ajax')
			->setConfirmation(
				new StringConfirmation('Opravdu chcete smazat uživatele %s?', 'email')
			);

		return $grid;
	}

	public function statusChange($id, $newStatus): void
	{
		// Update the reservation state in the service
		$user = $this->userService->find($id);
		if ($user) {
			$user->setState($newStatus);
			$this->userService->update($user);

			$this->presenter->flashMessage('Stav uživatele byl úspěšně změněn.', 'success');
		} else {
			$this->presenter->flashMessage('Uživatel nebyl nalezen.', 'danger');
		}

		if ($this->presenter->isAjax()) {
			$this['grid']->redrawItem($id);
		}
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
