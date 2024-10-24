<?php

namespace App\UI\Form;

use Contributte\FormsBootstrap\BootstrapRenderer;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;

class CustomBootstrapRenderer extends BootstrapRenderer
{
	public function renderLabel(BaseControl $control): Html
	{
		$labelHtml = parent::renderLabel($control);
		$labelHtml->setAttribute('id', $control->getHtmlId());

		if ($control->isRequired()) {
			$labelHtml->addHtml(' <span class="text-danger">*</span>');
		}

		return $labelHtml;
	}

	protected function renderDescription(BaseControl $control): ?Html {
		$description = parent::renderDescription($control);
		if($description) {
			$description->setAttribute('id', $control->getHtmlId());
		}

		return $description;
	}

}
