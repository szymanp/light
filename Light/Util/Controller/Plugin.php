<?php

namespace Light\Util\Controller;

use Light\Util\URL;

abstract class Plugin
{
	protected $controller;
	protected $name;
	protected $classmap;
	
	public function __construct($name = NULL, Controller $ctrl = NULL)
	{
		if (is_null($ctrl))
		{
			$ctrl = Controller::getInstance();
		}
		if (is_null($name))
		{
			$name = get_class($this);
			$name = substr($name, strrpos($name, "\\") + 1);
		}
		
		$this->name			= $name;
		$this->controller	= $ctrl;
		$this->classmap		= $ctrl->getClassMap();
	}
	
	/**
	 * @return Light\Util\Controller\Controller;
	 */
	public function getController()
	{
		return $this->controller;
	}
	
	/**
	 * @return Light\Util\Controller\ClassMap
	 */
	public function getClassMap()
	{
		return $this->classmap;
	}
	
	/**
	 * @return	string	The name of this plugin.
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Sets the class and package map for this plugin.
	 * @param ClassMap $map
	 * @return Light\Util\Controller\Plugin
	 */
	public function setClassMap(ClassMap $map)
	{
		$this->classmap = $map;
		return $this;
	}
	
	/**
	 * Decodes the specified URI and invokes the corresponding class.
	 * @param Request	$request
	 */
	abstract public function invoke(Request $request);
	
	/**
	 * Returns an URL for invoking the specified class.
	 * @param	string	$class	Class name.
	 * @param	array	$params	List of parameters to pass to the class.
	 * @return	string	An URL for the specified class, if found;
	 *					otherwise, NULL.
	 */
	abstract public function getHref($class, array $params = array());

	/**
	 * Returns an URI fragment containing encoded parameters.
	 * @param	array	$params
	 * @return	string	URI fragment.
	 */	
	abstract public function getHrefParams(array $params);

}