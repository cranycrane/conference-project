<?php

namespace App\UI\Modules\Admin\Conference;

use App\UI\Components\Conference\ConferenceGrid;
use App\UI\Components\Conference\ConferenceGridFactory;
use App\UI\Components\Conference\ConferenceForm;
use App\UI\Components\Conference\ConferenceFormFactory;
use App\UI\Modules\Admin\BaseAdminPresenter;
use Nette\DI\Attributes\Inject;

class ConferencePresenter extends BaseAdminPresenter {

    #[Inject]
    public ConferenceGridFactory $conferenceGridFactory;

    #[Inject]
    public ConferenceFormFactory $conferenceFormFactory;


    public function createComponentConferenceGrid(): ConferenceGrid {
        return $this->conferenceGridFactory->create();
    }

    // Přidáme metodu pro vytvoření komponenty ConferenceForm
    public function createComponentConferenceForm(): ConferenceForm {
        return $this->conferenceFormFactory->create();
    }

}
