<?php

namespace Light\UI\Util;
use Light\UI\Framework;
use Light\UI\Component;

/**
 * A helper for printing HTML tags for Components.
 */
class TagBuilder
{
	private $owner;
	private $tagName;
	private $hasContent		= true;
	private $static_attrs	= array();
	private $property_attrs	= array();
	
	public function __construct($tagName, Component $owner = NULL)
	{
		$this->owner	= $owner;
		$this->tagName	= $tagName;
	}
	
	public function __set($name, $value)
	{
		$this->static_attrs[$name] = $value;
	}
	
	/**
	 * Sets a tag attribute.
	 * @return Light\UI\Util\TagBuilder
	 */
	public function attribute($name, $value)
	{
		$this->__set($name, $value);
		return $this;
	}
	
	/**
	 * Adds a component property as a tag attribute.
	 * The property is rendered as the tag attribute only if it is not NULL.
	 * @param string	$name
	 * @param string	$alias	Name to use instead of property's name
	 * @return Light\UI\Util\TagBuilder
	 */
	public function property($name, $alias = "")
	{
		$this->property_attrs[$name] = $alias;
		return $this;
	}
	
	public function getTag()
	{
		if ($this->hasContent)
		{
			// todo
		}
		return $this->getOpeningTag() . $this->getClosingTag();
	}
	
	public function getOpeningTag($close = false)
	{
		$str = "<" . strtolower($this->tagName);
		
		foreach($this->static_attrs as $name=>$value)
		{
			$str .= " " . $name . "=\"" . htmlspecialchars($value, ENT_NOQUOTES) . "\"";
		}
		if (!is_null($this->owner))
		{
			foreach($this->property_attrs as $name => $alias)
			{
				$p = $this->owner->propertyQuoted($name);
				if (!empty($p))
				{
					if (empty($alias)) $alias = $name;
					$str .= " " . $alias . "=\"" . $p . "\"";
				}
			}
		
			$str .= $this->owner->getAttributeStr();
		}
		
		if ($close) $str .= " /";
		
		$str .= ">";
		
		return $str;
	}
	
	public function getClosingTag()
	{
		return "</" . strtolower($this->tagName) . ">";
	}
	
	public function printOpeningTag($close = false)
	{
		print $this->getOpeningTag($close);
	}

	public function printClosingTag()
	{
		print $this->getClosingTag();
	}

}