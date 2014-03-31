<?php

namespace Light\UI\Framework;
use Light\Data;
use Light\UI\Framework\Input\DataUnit;
use Light\Exception\Exception;

/**
 * A class for storing object properties.
 *
 */
abstract class PropertyObject extends Element
{
	/**
	 * @var Property[]	A mapping between a name and a Property object.
	 */
	private $properties = array();
	
	/**
	 * @var Property[]	A mapping between a URL name and a Proprty object.
	 */
	private $urlnames = array();

	/**
	 * Registers a new property for this PropertyObject.
	 * @param Property	$prop
	 *
	 * @internal This method needs to be public because it is called by PropertyProperty.
	 *
	 * @return Light\UI\Framework\PropertyObject
	 */
	public function registerProperty(Property $prop)
	{
		$this->properties[$prop->getName()]		= $prop;
		$this->urlnames[$prop->getUrlName()] 	= $prop;
		return $this;
	}
	
	/**
	 * Assigns a value to a property.
	 * @param string	$name
	 * @param mixed		$value
	 * @return Light\UI\Framework\PropertyObject
	 */
	public function setProperty($name,$value)
	{
		$prop = @ $this->properties[$name];
		if ($prop)
		{
			$prop->setValue($this, $value);
		}
		else if (method_exists($this, $m = "set" . $name))
		{
			$this->$m($value);
		}
		else
		{
			throw new Exception("Cannot write to undefined property '%1'", $name);
		}
		return $this;
	}

	/**
	 * Returns a property value for the specified name.
	 * @param string	$name
	 * @return mixed
	 */
	public function getProperty($name)
	{
		$prop = @ $this->properties[$name];
		if ($prop)
		{
			return $prop->getValue($this);
		}
		elseif (method_exists($this,$m = "get" . $name))
		{
			return $this->$m();
		}
		else
		{
			throw new Exception("Cannot read undefined property '%1'", $name);
		}
	}
	
	/**
	 * Checks if the given property is defined and has a non-NULL value assigned.
	 * Note that the value check does not work for method properties.
	 * @return boolean
	 */
	public function hasProperty($name)
	{
		return method_exists($this, "get" . $name)
			   || $this->isPropertyDefined($name)
			   	  && $this->getProperty($name) !== null;
	}
	
	/**
	 * Returns true if the named property is defined.
	 * @return boolean
	 */
	public function isPropertyDefined($name)
	{
		return isset($this->properties[$name]);
	}
	
	/**
	 * Returns a property definition object.
	 * @param string	$name
	 * @return Light\UI\Framework\Property	A Property object, or NULL if it doesn't exist.
	 */
	protected function getPropertyDefinition($name)
	{
		return @ $this->properties[$name];
	}

	/**
	 * Returns a property definition object by its URL name.
	 * @param string	$name	URL name of the object
	 * @return Light\UI\Framework\Property	A Property object, or NULL if it doesn't exist.
	 */
	protected function getPropertyDefinitionByUrlName($name)
	{
		return @ $this->urlnames[$name];
	}

	/**
	 * Returns a list of properties defined for this object.
	 * @param string	$type	Type name to filter on.
	 * @return Light\UI\Framework\Property[]
	 */	
	protected function getProperties($type = null)
	{
		if (is_null($type))
		{
			return $this->properties;
		}
		else
		{
			$list = array();
			foreach($this->properties as $name => $prop)
			{
				if ($prop instanceof $type)
				{
					$list[$name] = $prop;
				}
			}
			return $list;
		}
	}
	
	// attributes
	
	/**
	 * @return Light\UI\Framework\PropertyObject
	 */
	public function setAttribute($name,$value) {
		$this->attributes[$name] = $value;
		return $this;
	}
	
	public function getAttribute($name) {
		return @ $this->attributes[$name];
	}
	
	public function getAttributes() {
		return $this->attributes;
	}
	
	public function hasAttribute($name) {
		return isset($this->attributes[$name]);
	}
	
	public function hasAttributes() {
		return !empty($this->attributes);
	}
	
	public function getAttributeStr()
	{
		$attributes = $this->getProperties("Light\UI\Framework\AttributeProperty");
		
		$str = "";
		foreach($attributes as $n => $v) {
			$str .= " " . $this->sanitizeHtml($n) . "=\"" . $this->sanitizeHtml($v) . "\"";
		}
		return $str;
	}

	// property read/write
	
	public function __get($name)
	{
		return $this->getProperty($name);
	}
	
	public function __isset($name)
	{
		return $this->hasProperty($name);
	}
	
	public function __set($name,$value)
	{
		$this->setProperty($name,$value);
	}
	
	public function __execute($method, array $args)
	{
		return call_user_func_array(array($this, $method), $args);
	}
	
	public function __execset($var, $value)
	{
		$this->$var = $value;
	}
	
	public function __execget($var)
	{
		return $this->$var;
	}
	
	// misc
	
	protected function sanitizeHtml($str)
	{
		return htmlspecialchars($str,ENT_NOQUOTES);
	}
	
	// RequestHandler interface implementation (partial)
	
	public function setRequestHandlerState(DataUnit $dataUnit)
	{
		$prop = $this->getPropertyDefinitionByUrlName($dataUnit->getName());
		if (is_null($prop))
		{
			// this property is not known to the object
			return false;
		}
		if (!$prop->isExternal())
		{
			throw new Exception("Property '%1' cannot be set from the web", $dataUnit->getName());
		}
		
		$this->setProperty($prop->getName(), $dataUnit->getValue());
		return true;
	}
}