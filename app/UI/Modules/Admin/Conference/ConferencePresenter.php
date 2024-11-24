<?php

namespace App\UI\Modules\Admin\Conference;

use App\UI\Components\Conference\ConferenceGrid;
use App\UI\Components\Conference\ConferenceGridFactory;
use App\UI\Components\Conference\ConferenceForm;
use App\UI\Components\Conference\ConferenceFormFactory;
use App\UI\Modules\Admin\BaseAdminPresenter;
use Nette\DI\Attributes\Inject;
use App\Model\Services\ConferenceService;
use Nette\Bridges\ApplicationLatte\Template;



/**
 * @property \Nette\Bridges\ApplicationLatte\Template $template
 */
class ConferencePresenter extends BaseAdminPresenter {

    #[Inject]
    public ConferenceGridFactory $conferenceGridFactory;

    #[Inject]
    public ConferenceFormFactory $conferenceFormFactory;

    #[Inject]
    public ConferenceService $conferenceService;

    public $conferenceId = null;


    public function createComponentConferenceGrid(): ConferenceGrid {
        return $this->conferenceGridFactory->create();
    }

    public function createComponentConferenceForm(): ConferenceForm {
        $conferenceForm = $this->conferenceFormFactory->create();

        if (isset($this->conferenceId)) {
            $conferenceForm->setId($this->conferenceId);
        }

        return $conferenceForm;
    }

    public function renderEdit(int $id): void
    {
    $conference = $this->conferenceService->find($id);
    if (!$conference) {
        $this->error('Konference nenalezena');
    }

    $this->conferenceId = $id;

    $template = $this->getTemplate();
    $template->conference = $conference;

    $form = $this['conferenceForm']->getComponent('form');
    $form->setDefaults([
        'id' => $conference->getId(),
        'title' => $conference->title,
        'genre' => $conference->genre,
        'place' => $conference->place,
        'startsAt' => $conference->getStartsAt()->format('Y-m-d H:i:s'),
        'endsAt' => $conference->getEndsAt()->format('Y-m-d H:i:s'),
        'priceForSeat' => $conference->priceForSeat,
        'capacity' => $conference->capacity,
        'description' => $conference->description,
    ]);

    }
    public function renderCreate(): void
    {
        $template = $this->getTemplate();
        $template->conference = null;
    }
}
