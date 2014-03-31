<?php

namespace Light\UI;

class Decorator extends Framework\PropertyObject
{
	private $decoratedComponent;
	
	/**
	 * Sets the decorated component.
	 * @return Decorator
	 */
	public function setOwner(Component $c)
	{
		$this->decoratedComponent = $c;
		return $this;	// fluent API
	}

	public function getOwner()
	{
		return $this->decoratedComponent;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Light\UI\Framework.Element::getResourceFinder()
	 */
	public function getResourceFinder()
	{
		$t = parent::getResourceFinder();
		if (!is_null($t))
		{
			return $t;
		}
		return $this->getOwner()->getResourceFinder();
	}

	public function render()
	{
		$file = $this->getResourceFinder()->getDefaultResourceFile($this);
		
		$owner = $this->getOwner();
		
		include($file);
	}

}
