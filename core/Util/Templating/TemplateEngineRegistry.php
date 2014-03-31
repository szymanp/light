<?php

namespace Light\Util\Templating;

use Light\Exception\Exception;

class TemplateEngineRegistry
{
	private static $instance;
	
	/**
	 * Returns an instance of the TemplateEngineRegistry.
	 * @return \Light\Util\Templating\TemplateEngineRegistry
	 */
	public static function getInstance()
	{
		if (!self::$instance)
		{
			self::$instance = new self;
		}
		return self::$instance;
	}
	
	private $mappings	= array(
		"Twig"		=> "Light\Util\Templating\Twig",
		"StrRepl"	=> "Light\Util\Templating\StrRepl",
		"Mustache"	=> "Light\Util\Templating\Mustache"
		);
	
	private $customizers = array();
	
	private function __construct()
	{
	}
	
	/**
	 * Returns a new instance of the specified template engine. 
	 * @param string	$name	Template engine name.
	 * @throws Exception
	 * @return \Light\Util\Templating\TemplateEngine
	 */
	public function get($name)
	{
		if (!isset($this->mappings[$name]))
		{
			throw new Exception("Template engine %1 not found in registry", $name);
		}
		
		$class = $this->mappings[$name];
		return new $class();
	}
	
	/**
	 * Sets a classname for the specified template engine.
	 * @param string	$name
	 * @param string 	$value	Class name
	 * @return \Light\Util\Templating\TemplateEngineRegistry
	 */
	public function set($name, $value)
	{
		$this->mappings[$name] = $value;
		return $this;
	}
	
	/**
	 * Adds a callback that will be run whenever a new TemplateEngine is ready to be displayed.
	 * @param string	$name		Template engine name
	 * @param string	$type		Template engine-specific customizer type
	 * @param \Closure	$callback
	 */
	public function addCustomizer($name, $type, \Closure $callback)
	{
		$class = $this->mappings[$name];
		$this->customizers[$class][$type][] = $callback;
		return $this;
	}
	
	public function runCustomizers(TemplateEngine $engine, $type, $argument = NULL)
	{
		$class = get_class($engine);
	
		if (!isset($this->customizers[$class][$type]))
		{
			return;
		}
		
		foreach($this->customizers[$class][$type] as $cust)
		{
			$cust($engine, $argument);
		}
	}
}