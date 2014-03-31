<?php

namespace Light\UI;

use Light\Data\Helper;

class DataTemplateProvider
{
	private $class;
	private $container;
	private $namePattern;
	
	public function __construct(Container $container, $name = "", $templateClass = NULL)
	{
		$this->container = $container;
		$this->class = $templateClass;
		$this->namePattern = $name;
	}
	
	public function setTemplate($class)
	{
		$this->class = $class;
	}
	
	public function getNamePattern()
	{
		return $this->namePattern;
	}
	
	/**
	 * Tests if the given element name matches this provider's pattern.
	 * @return boolean
	 */
	public function isNamePatternMatch($name)
	{
		return (substr($name, 0, strlen($this->namePattern)) == $namePattern);
	}
	
	/**
	 * Extracts an item key from the element name.
	 * @return string	The item key, if the pattern matches;
	 *					otherwise, NULL.
	 */
	public function extractKeyFromName($name)
	{
		$l = strlen($this->namePattern);
		if (substr($name, 0, $l) == $namePattern)
		{
			return substr($name, $l + 1 );
		}
		return NULL;
	}
	
	public function getTemplateInstance($object, $key = NULL, $forceAdd = false)
	{
		if (empty($this->class))
		{
			throw new \Exception("No class specified for template.");
		}
		
		$name = "";
		if (!empty($this->namePattern))
		{
			$name = $this->namePattern;
		}
		if (!is_null($key))
		{
			$name .= (string) $key;
		}
		
		if (!$forceAdd && $this->container->hasElement($name))
		{
			return $this->container->getElement($name);
		}
		
		$class = $this->class;
		
		$instance = new $class($name);
		if ($instance instanceof DataTemplate)
		{
			$instance->setDataObject($object, $key);
		}
		else
		{
			$instance->setDataContext(Helper::wrap($object));
		}
		
		$this->container->lateAdd($instance);
		
		return $instance;
	}
}