<?php

namespace Light\UI\Component;
use Light\UI\Util\TagBuilder;

class Button extends ContentControl
{
	protected $type	= "submit";
	
	public function getType()
	{
		return $this->type;
	}
	
	public function render()
	{
		$button = new TagBuilder("BUTTON", $this);
		$button
			->attribute("name", $this->createAction("click"))
			->property("Id", 	"id")
			->property("Type", 	"type")
			->property("Label",	"title")
			->attribute("value", 1);

		$button->printOpeningTag();	
		$this->propertyRender("Content");
		$button->printClosingTag();
	}
}