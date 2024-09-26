<?php

namespace App\UI\Components\Conference;

use Nette\Application\UI\Control;
use Ublaboo\DataGrid\DataGrid;

class ConferenceGrid extends Control {

	public function __construct() {

	}

	public function createComponentGrid(): DataGrid {
		$grid = new DataGrid();




		return $grid;
	}
}
