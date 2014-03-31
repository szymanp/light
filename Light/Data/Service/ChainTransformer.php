<?php

namespace Light\Data\Service;

use Light\Service\Util\DataObjectDecoder;
use Light\Exception\InvalidParameterType;
use Light\Exception\Exception;

/**
 * A class for transforming objects using a set of rules.
 */
class ChainTransformer
{
	private $properties	= array();
	private $transforms = array();
	private $each = NULL;
	private $required = false;

	/**
	 * @return Light\Data\Service\ChainTransformer
	 */
	public function property($name)
	{
		if (!isset($this->properties[$name]))
		{
			$this->properties[$name] = new self;
		}
		return $this->properties[$name];
	}
	
	/**
	 * Marks this ChainTransformer as required.
	 * @return Light\Data\Service\ChainTransformer
	 */
	public function required()
	{
		$this->required = true;
		return $this;
	}
	
	/**
	 * Decodes an object using the DataObjectDecoder.
	 * @param string	$class
	 * @return Light\Data\Service\ChainTransformer	For fluent API.
	 */
	public function decode($class)
	{
		$this->transforms[] = 
		function($v) use ($class)
		{
			$dd = new DataObjectDecoder();
			return $dd->decode(new \ReflectionClass($class), $v);
		};
		return $this;
	}

	/**
	 * Calls a method on the current object.
	 * The current object is NOT replaced with the return value of the method.
	 * @param mixed	$methodOrClosure	Method of the object, or a closure.
	 * @return Light\Data\Service\ChainTransformer	For fluent API.
	 */
	public function call($methodOrClosure)
	{
		$this->transforms[] =
		function($v) use ($methodOrClosure)
		{
			if (is_string($methodOrClosure))
			{
				$v->$method();
			}
			else
			{
				$methodOrClosure($v);
			}
			return $v;
		};
		return $this;
	}
	
	/**
	 * Calls a method on the current object and replaces the object with the return value.
	 * @param mixed	$methodOrClosure	Method of the object, or a closure.
	 * @return Light\Data\Service\ChainTransformer	For fluent API.
	 */
	public function transform($methodOrClosure)
	{
		$this->transforms[] =
		function($v) use ($methodOrClosure)
		{
			if (is_string($methodOrClosure))
			{
				return $v->$methodOrClosure();
			}
			else
			{
				return $methodOrClosure($v);
			}
		};
		return $this;
	}
	
	/**
	 * Returns a new ChainTransformer that will be executed for each element in a collection.
	 * @return Light\Data\Service\ChainTransformer
	 */
	public function each()
	{
		if (is_null($this->each))
		{
			$this->each = new self;
		}
		return $this->each;
	}
	
	/**
	 * Executes the transformation and returns the result.
	 * @param mixed	$object
	 * @return mixed
	 */
	public function execute($object)
	{
		$result = $this->createCopy($object);
		
		if (!empty($this->properties))
		{
			$result = $this->transformProperties($result);
		}
		
		foreach($this->transforms as $transform)
		{
			$result = $transform($result);
		}
		
		if (!is_null($this->each))
		{
			foreach($result as $key => $value)
			{
				if (is_array($result))
				{
					$result[$key] = $this->each->execute($value);
				}
				else
				{
					$result->$key = $this->each->execute($value);
				}
			}
		}
		
		return $result;
	}
	
	private function createCopy($object)
	{
		if (is_object($object))
		{
			return clone $object;
		}
		else
		{
			return $object;
		}
	}
	
	private function transformProperties($object)
	{
		// if $object is an array, try to convert it to a stdClass object
		if (is_array($object))
		{
			$new_object = new \stdClass;
			foreach($object as $key => $value)
			{
				if (is_numeric($key))
				{
					throw new InvalidParameterType('$object', $object, 'object or array w/o numeric keys');
				}
				$new_object->$key = $value;
			}
			$object = $new_object;
		}
		
		// assert that $object is an object
		if (!is_object($object))
		{
			throw new InvalidParameterType('$object', $object, 'object|array');
		}
		
		foreach($this->properties as $name => $prop)
		{
			if (!isset($object->$name))
			{
				if ($prop->required)
				{
					throw new Exception("Required property %1 is not set", $name);
				}
				continue;
			}
			
			$value = $object->$name;
			$value = $prop->execute($value);
			
			$object->$name = $value;
		}
		
		return $object;
	}
}
