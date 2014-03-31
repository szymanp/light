<?php

namespace Light\UI\Framework\Input;

/**
 * A name-value pair of data.
 */
class DataUnit
{
	private $name;
	
	private $value;
	
	public function __construct($name, $value)
	{
		$this->name = $name;
		$this->value = $value;
	}
	
	/**
	 * Returns the name of the state property.
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Returns the value of the state property.
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}
}