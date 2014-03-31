<?php
namespace Light\UI\Framework;

/**
 * A property that is backed by a setter and/or getter method.
 *
 */
class MethodProperty extends Property
{
	private $setter;
	private $getter;
	
	public function setter($setter)
	{
		$this->setter = $setter;
		return $this;
	}
	
	public function getter($getter)
	{
		$this->getter = $getter;
		return $this;
	}
	
	public function setValue(PropertyObject $obj, $value)
	{
		if (is_null($this->setter))
		{
			$setter = "set" . $this->getName();
			$obj->__execute($setter, array($value));
		}
		else if (is_string($this->setter))
		{
			$setter = $this->setter;
			$obj->__execute($setter, array($value));
		}
		else
		{
			call_user_func($this->setter, $value);
		}
	}
	
	public function getValue(PropertyObject $obj)
	{
		if (is_null($this->getter))
		{
			$getter = "get" . $this->getName();
			return $obj->__execute($getter, array());
		}
		else if (is_string($this->getter))
		{
			$getter = $this->getter;
			return $obj->__execute($getter, array());
		}
		else
		{
			return call_user_func($this->getter);
		}
	}
}