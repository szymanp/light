<?php

namespace Light\Data;

abstract class AbstractCollection implements CompositeCollection
{
	private $capability	= 0;
	
	protected function setCapability($capability)
	{
		$this->capability = $capability;
	}
	
	public function hasCapability($capability)
	{
		return ($this->capability & $capability) != 0;
	}
}
