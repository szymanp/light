<?php

namespace Light\UI;

/**
 * Style holds property values and method calls to be applied to another object.
 *
 */
class Style
{
	protected $values = array();
	protected $methodCalls = array();
	
	public function __call($name, $arguments)
	{
		$this->methodCalls[] = array($name, $arguments);
	}
	
	public function __set($name,$value)
	{
		$this->values[$name] = $value;
	}
	
	public function applyTo($object)
	{
		foreach($this->values as $key => $value)
		{
			$object->$key = $value;
		}
		
		foreach($this->methodCalls as $call)
		{
			call_user_func_array(array($object,$call[0]),$call[1]);
		}
	}
	
}