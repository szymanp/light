<?php
namespace Light\UI\Framework;

/**
 * A property that is backed by a instance variable.
 *
 */
class PropertyProperty extends Property
{
	private $variable;
	
	/**
	 * Sets the name of the backing variable.
	 * @param string	$name
	 * @return Light\UI\Framework\PropertyProperty
	 */
	public function variable($name)
	{
		$this->variable = $name;
		return $this;
	}
	
	public function setValue(PropertyObject $obj, $value)
	{
		$var = !empty($this->variable) ? $this->variable : $this->getName();
	
		$obj->__execset($var, $value);
	}
	
	public function getValue(PropertyObject $obj)
	{
		$var = !empty($this->variable) ? $this->variable : $this->getName();

		return $obj->__execget($var);
	}
}
