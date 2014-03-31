<?php

namespace Light\Data;

use Light\Exception\NotImplementedException;
use Light\Exception\InvalidParameterType;

/**
 * An array for storing objects and retrieving them quickly by their properties.
 * The properties used for accessing objects must be immutable - i.e. they must not change
 * once the objects is added to the collection.
 */
class ObjectArray implements \IteratorAggregate, \Countable
{
	/**
	 * Objects with key being the sequential index. 
	 * @var array
	 */
	private $objects = array();
	
	/**
	 * A list of properties to index on.
	 * @var array
	 */
	private $properties;
	
	private $listByProperty = array();
	private $listByKey = array();
	
	/**
	 * @param array $properties		A list of property names to index.
	 * 								A property name can be a variable name or a method name (without the "get" prefix).
	 */
	public function __construct(array $properties)
	{
		$this->properties = $properties;
	}
	
	/**
	 * @param array $array
	 * @param array $properties
	 * @return Light\Data\ObjectArray
	 */
	public static function fromArray(array $array, array $properties)
	{
		$coll = new self($properties);
		
		foreach($array as $key => $value)
		{
			$coll->add($value, is_string($key)?$key:null);
		}
		
		return $coll;
	}
	
	/**
	 * Adds an object to this collection.
	 * @param object	$object		The object to add
	 * @param string	$key		A key to associate with the object
	 * @throws InvalidParameterType
	 * @return Light\Data\ObjectArray
	 */
	public function add($object, $key = null)
	{
		if (!is_object($object))
		{
			throw new InvalidParameterType('$object', $object, 'object');
		}
		
		foreach($this->properties as $index => $property)
		{
			if (method_exists($object, $name = "get" . $property))
			{
				$value = $object->$name();
			}
			else
			{
				$value = $object->$property;
			}
			
			$this->listByProperty[$index][$value] = $object;
		}
		
		if (!is_null($key))
		{
			$this->listByKey[$key] = $object;
		}
		
		$this->objects[] = $object;
		
		return $this;
	}
	
	/**
	 * Removes an object from this collection.
	 * @param object	$object
	 * @throws NotImplementedException
	 * @return Light\Data\ObjectArray
	 */
	public function remove($object)
	{
		// @todo
		throw new NotImplementedException();
	}
	
	/**
	 * Returns an object at the given index.
	 * @param integer	$index
	 * @return object
	 */
	public function getAtIndex($index)
	{
		return @$this->objects[$index];
	}
	
	/**
	 * Returns an object with the given key. 
	 * @param string $key
	 * @return object
	 */
	public function getAtKey($key)
	{
		return @$this->listByKey[$key];
	}
	
	/**
	 * Returns an object having the given property value.
	 * @param string	$property
	 * @param mixed		$value
	 * @return object
	 */
	public function get($property, $value)
	{
		$index = array_search($property, $this->properties);
		if ($index === false) return null;
		return @$this->listByProperty[$index][$value];
	}
	
	/**
	 * @return integer
	 */
	public function count()
	{
		return count($this->objects);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see IteratorAggregate::getIterator()
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->objects);
	}
	
}