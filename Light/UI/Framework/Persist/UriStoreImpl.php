<?php

namespace Light\UI\Framework\Persist;
use Light\UI;
use Light\Exception;

class UriStoreImpl implements UriStore
{
	/** @var \SplObjectStorage */
	private $knownProperties;
	
	/** @var \SplObjectStorage */
	private $savedProperties;
	
	public function __construct()
	{
		$this->knownProperties = new \SplObjectStorage();
		$this->savedProperties = new \SplObjectStorage();
	}
	
	public function open()
	{
	}
	
	public function close()
	{
	}
	
	public function save(UI\Component $c, Property $property, $value)
	{
		if (!is_scalar($value) && !is_null($value))
		{
			throw new Exception\InvalidParameterType('$value', $value, "scalar");
		}
		$this->savedProperties[$property] = $value;
		$this->knownProperties[$property] = $c;
	}
	
	public function load(UI\Component $c, Property $property, &$value)
	{
		if (!is_null($name = $property->getAlias()))
		{
			if (isset($_REQUEST[$name]))
			{
				$value = $_REQUEST[$name];
				return true;
			}
		}
		
		$name = $c->getId() . "." . $property->getName();
		if (isset($_REQUEST[$name]))
		{
			$value = $_REQUEST[$name];
			return true;
		}
		
		$name = $property->getName();
		if (isset($_REQUEST[$name]))
		{
			$value = $_REQUEST[$name];
			return true;
		}
		
		return false;
	}

	public function appendArguments($class, array &$args)
	{
		foreach($this->savedProperties as $property)
		{
			$value = $this->savedProperties->getInfo();
			
			if (!$property->isWithinRange($class))
			{
				continue;
			}
			
			$name = $property->getAlias();
			if (is_null($name))
			{
				$name = $property->getName();
				if (isset($args[$name]))
				{
					$name = $this->knownProperties[$property]->getId() . "." . $name;
				}
			}
			
			if (isset($args[$name]))
			{
				throw new \Exception("Property with name/alias <$name> is already present in arguments.");
			}
			
			$args[$name] = (string) $value;
		}
	}	
}