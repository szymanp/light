<?php

namespace Light\UI\Component;
use Light\UI;
use Light\UI\Framework\ValueProperty;

/**
 * A component that renders a single content element.
 * 
 * @property mixed	Content		Content to be rendered
 *
 */
abstract class ContentControl extends Renderable {

	protected function construct()
	{
		parent::construct();
		ValueProperty::create($this, "Content");
	}
	
	protected function init()
	{
		if ($this->Content instanceof UI\Component && !$this->hasElement($this->Content))
		{
			$this->add($this->Content);
		}
		parent::init();
	}
}
