<?php

namespace Light\UI\Decorator;
use Light\UI\Decorator as Decorator;

class Label extends Decorator 
{
	public function render()
	{
		if ($this->getOwner()->hasProperty("Label"))
		{
			parent::render();
		}
	}
}
