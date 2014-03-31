<?php
namespace Light\UI\Framework;

/**
 * A property that stores a value.
 *
 */
class ValueProperty extends Property
{
	private $value;
	
	public function set($value)
	{
		$this->value = $value;
		return $this;
	}
	
	public function get()
	{
		return $this->value;
	}
	
	public function setValue(PropertyObject $obj, $value)
	{
		$this->value = $value;
	}
	
	public function getValue(PropertyObject $obj)
	{
		return $this->value;
	}
}
