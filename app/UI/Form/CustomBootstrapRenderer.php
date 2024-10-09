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

		if ($control->isRequired()) {
			$labelHtml->addHtml(' <span class="text-danger">*</span>');
		}

		return $labelHtml;
	}
}
