<?php

namespace Light\UI\Component;
use Light\UI\Framework\PropertyProperty;

use Light\UI;

/**
 * A component that renders a single content element inside a DIV.
 * 
 * @property string	$ElementType	Type of element to render the content inside. Defaults to DIV.
 */
class ContentBox extends ContentControl
{
	protected $elementType = "DIV";
	
	protected function construct()
	{
		parent::construct();
		PropertyProperty::create($this, "ElementType")
		->variable("elementType");
	}
	
	protected function render()
	{
		if (!$this->isVisible()) {
			return;
		}
		
		$tag = new UI\Util\TagBuilder($this->elementType, $this);
		$tag->property("Id", "id");
		$tag->printOpeningTag();
		
		print $this->propertyRender("Content");
		
		$tag->printClosingTag();
	}
}
