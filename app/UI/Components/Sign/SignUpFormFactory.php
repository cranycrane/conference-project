<?php

namespace App\UI\Components\Sign;

use App\Domain\User\User;

interface SignUpFormFactory {

	public function create(User $user = null): SignUpForm;
}
