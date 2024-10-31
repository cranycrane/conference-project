<?php

namespace App\UI\Components\Question;

use App\UI\Components\Question\QuestionGrid;

interface QuestionGridFactory {

	public function create(?int $presentationId = null): QuestionGrid;

}
