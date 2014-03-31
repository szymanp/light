<?php
namespace Light\UI\Framework;

abstract class Property
{
	/**
	 * Name of property.
	 * @var string
	 */
	private $name;
	
	/**
	 * Type of property, e.g. string.
	 * @var string
	 */
	private $type;
	
	/**
	 * Can this property be set/read from the web.
	 * @var boolean
	 */
	private $external = false;
	
	/**
	 * Alternative name used for the property in URLs.
	 * @var string
	 */
	private $alias;

	/**
	 * Constructs a new Property and registers it with the given PropertyObject.
	 * @param PropertyObject	$owner
	 * @param string			$name
	 * @param string			$type
	 * @param string			$alias
	 * @return Light\UI\Framework\Property
	 */
	public static function create(PropertyObject $owner, $name, $type = null, $alias = null)
	{
		$prop = new static($name, $type, $alias);
		$owner->registerProperty($prop);
		return $prop;
	}
	
	/**
	 * Constructs a new Property.
	 * @param string	$name
	 * @param string	$type
	 * @param string	$alias
	 */
	public function __construct($name, $type = null, $alias = null)
	{
		$this->name 	= $name;
		$this->type 	= $type;
		$this->alias	= $alias;
	}
	
	/**
	 * Returns the name of this property.
	 * @return string
	 */	
	public function getName()
	{
	    return $this->name;
	}
	
	/**
	 * Returns the URL name this property.
	 * @return string
	 */
	public function getUrlName()
	{
		return is_null($this->alias) ? $this->name : $this->alias;
	}
	
	/**
	 * Returns the type of this property.
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * Sets whether this property can be read/written from the web.
	 * @param boolean	$v
	 * @return Light\UI\Framework\Property
	 */
	public function external($v = true)
	{
		$this->external = $v;
		return $this;
	}
	
	/**
	 * Returns true if this property can be read or set from the web.
	 * @return boolean
	 */
	public function isExternal()
	{
		return $this->external;
	}
	
	/**
	 * Sets the value of this property.
	 * @param PropertyObject	$obj	Object holding the property.
	 * @param mixed				$value	Value to be set.
	 */
	abstract public function setValue(PropertyObject $obj, $value);
	
	/**
	 * Reads the value of this property.
	 * @param PropertyObject	$obj	Object holding the property.
	 * @return mixed
	 */
	abstract public function getValue(PropertyObject $obj);
}