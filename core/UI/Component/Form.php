<?php

namespace Light\UI\Component;
use Light\UI\Framework\ValueProperty;

use Light\UI\Util\Href;

use Light\Util;
use Light\UI\Util\TagBuilder;
use Light\Service\Service;

/**
 * @property string		Method
 */
class Form extends ContentControl
{
	protected function construct()
	{
		parent::construct();
		ValueProperty::create($this, "Method", "string")->set("POST");
	}

	public function render()
	{
		if (!$this->getVisible())
		{
			return;
		}
		$hasForm = !is_null($this->getForm());
		
		if (!$hasForm)
		{
			$url = Href::toSelf();
			
			$hrefstr = $url->evaluate();
			$args = $hrefstr->getUrlArguments();
			
			$formTag = new TagBuilder("FORM", $this);
			$formTag->property("Name",				"name");
			$formTag->property("Label",				"title");
			$formTag->property("Method",			"method");
			$formTag->property("OnSubmitResult",	"onSubmitResult");
			$formTag->attribute("action",			$hrefstr->getBaseUrl());
		}
		else
		{
			$formTag = new TagBuilder("DIV", $this);
		}

		$formTag->printOpeningTag();
		
		foreach($args as $name => $value)
		{
			$input = new TagBuilder("INPUT");
			$input
				->attribute("type", "hidden")
				->attribute("name", $name)
				->attribute("value", $value);
			$input->printOpeningTag(true);
		}
		
		print $this->propertyRender("Content");
		
		$formTag->printClosingTag();
	}
}
