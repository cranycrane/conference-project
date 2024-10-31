<?php

namespace App\UI\Components\Question;

use App\Domain\Question\Question;
use App\Domain\Reservation\Reservation;
use App\UI\Components\Reservation\ReservationForm;

interface QuestionFormFactory {

	public function create(int $presentationId, int $userId, ?Question $question = null): QuestionForm;

}
