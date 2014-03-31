<?php

namespace Light\Data;

class ObjectModel implements Model, Object, \Serializable
{
	private $object;
	private $wrapper;
	
	public function __construct($object)
	{
		$this->object = $object;	
	}
	
	public function load()
	{
	}
	
	public function getModelObject()
	{
		return $this->object;
	}
	
	/**
	 * An alias for <c>getModelObject()</c>
	 * @see getModelObject()
	 */
	public function getObject()
	{
		return $this->object;
	}
	
	public function setObject($o)
	{
		$this->object = $o;
		$this->wrapper = NULL;
	}
	
	public function getWrappedObject()
	{
		if (is_null($this->wrapper))
		{
			$this->wrapper = Helper::wrap($this->object);
		}
		return $this->wrapper;
	}
	
	public function serialize()
	{
		return serialize($this->object);
	}
	
	public function unserialize($data)
	{
		$this->object = unserialize($data);
	}
	
	/**
	 * Gets a value for a property.
	 * @param	string	$dotName
	 * @return	mixed
	 */
	public function getValue($dotName)
	{
		return $this->getWrappedObject()->getValue($dotName);
	}
	
	/**
	 * Sets a value for a property.
	 * @param	string	$dotName
	 * @param	mixed	$value
	 */
	public function setValue($dotName,$value)
	{
		$this->getWrappedObject()->setValue($dotName,$value);
	}
	
	
	/**
	 * Checks if the specified property exists.
	 * @param 	string	$dotName
	 * @return	boolean
	 */
	public function hasProperty($dotName)
	{
		return $this->getWrappedObject()->hasProperty($dotName);
	}
	
	/**
	 * Returns a set of primary keys.
	 * @return	string|integer|array
	 */
	public function getIdentifier($asArray=false)
	{
		return $this->getWrappedObject()->getIdentifier($asArray);
	}
	
}