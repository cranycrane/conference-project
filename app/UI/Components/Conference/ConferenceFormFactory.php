<?php

namespace App\UI\Components\Conference;

interface ConferenceFormFactory
{
  public function create(): ConferenceForm;
}