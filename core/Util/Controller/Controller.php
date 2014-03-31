<?php

namespace Light\Util\Controller;

use Light\Util\URL;
use Light\Exception;
use Light\Autoloader;

class Controller
{
	private static $instance;
	
	/** @var Light\Util\Controller\ClassMap */
	protected $classmap;
	protected $plugins	= array();
	private $defaultPlugin;
	private $controllerUrl;
	
	private $invokedClass;
	private $invokedClassParams;
	
	public static function setInstance(Controller $c)
	{
		self::$instance = $c;
	}
	
	/**
	 * @return Light\Util\Controller\Controller
	 */
	public static function getInstance()
	{
		return self::$instance;
	}
	
	/**
	 * 
	 * @param string $url	URL of the controller.
	 */
	public function __construct($url = "")
	{
		if (self::$instance == NULL)
		{
			self::$instance = $this;
		}
		
		$this->controllerUrl = $url;
		$this->classmap		 = new ClassMap();
	}
	
	/**
	 * @param string	$class
	 * @param string	$uri
	 * @return Light\Util\Controller\Controller
	 */
	public function publishClass($class, $uri = NULL)
	{
		$this->classmap->publishClass($class, $uri);
		return $this;
	}
	
	/**
	 * @param string	$package
	 * @param string	$uri
	 * @return Light\Util\Controller\Controller
	 */
	public function publishPackage($package, $uri = NULL)
	{
		$this->classmap->publishPackage($package, $uri);
		return $this;
	}
	
	/**
	 * @return	Controller	For fluent API.
	 */
	public function setPlugin($uriPrefix, Plugin $plugin)
	{
		if (is_null($this->defaultPlugin))
		{
			$this->defaultPlugin = $plugin;
		}
		if (empty($uriPrefix))
		{
			$uriPrefix = "/";
		}
		
		$this->plugins[$uriPrefix] = $plugin;
		return $this;
	}
	
	public function run()
	{
		$path = @ $_SERVER['PATH_INFO'];
		if ($path === "" || is_null($path) || $path === false)
		{
			$path = "/";
		}
		
		// determine the plugin to use
		$found = false;
		krsort($this->plugins);
		
		foreach($this->plugins as $prefix => $plugin)
		{
			if (substr($path, 0, strlen($prefix)) == $prefix)
			{
				$found = true;
				break;
			}
		}
		
		if (!$found)
		{
			throw new Exception\Exception("No handler found for <%1>", $path);
		}
		
		// invoke the plugin
		$path = substr($path, strlen($prefix));
		$path = ($path === false)?"":$path;
		$request = new Request($path);
		$plugin->invoke($request);
	}
	
	/**
	 * Returns the class and package map.
	 * @return Light\Util\Controller\ClassMap
	 */
	public function getClassMap()
	{
		return $this->classmap;
	}
	
	/**
	 * Returns an URL for the specified class.
	 * @param	string	$class	Class name.
	 * @param	array	$params	List of parameters to pass to the class.
	 * @param	string	$plugin	Plugin name.
	 * @return	string	An URL for the specified class, if it was found;
	 *					otherwise, NULL.
	 */
	public function getHref($class, array $params = array(), $plugin = NULL)
	{
		$p = $this->findPlugin($plugin);
		$uri = $p->getHref($class, $params);
		
		if (is_null($uri))
		{
			return NULL;
		}
		
		$pluginBaseUri = array_search($p, $this->plugins, true);

		$url = URL::joinPaths(array($this->controllerUrl, $pluginBaseUri, $uri));
		return $url;
	}
	
	public function getHrefParams(array $params, $plugin = NULL)
	{
		return $this->findPlugin($plugin)->getHrefParams($params);
	}	
	
	/**
	 * Returns the class that is currently being executed.
	 * @return string
	 */
	public function getInvokedClass()
	{
		return $this->invokedClass;
	}
	
	/**
	 * Notifies the controller about a change in the currently invoked class.
	 * @param string 	$class
	 * @param array 	$params
	 */
	public function notifyInvokedClassChange($class, array $params)
	{
		$this->invokedClass = $class;
		$this->invokedClassParams = $params;
	}
	
	/**
	 * Finds a class that corresponds to the given URI.
	 * @param	string	$path	URI
	 * @return	string	Class name, if found; otherwise, NULL.
	 */
	public function findClass($path)
	{
		return $this->classmap->findClass($path);
	}
	
	/**
	 * Finds a class that <b>might</b> correspond to the given URI.
	 * @param	string	$path	URI. If a class is found, then the parameter
	 *							will contain the remaining URI fragment.
	 * @return	string	Class name, if found; otherwise, NULL.
	 */
	public function findClassEx(& $path)
	{
		return $this->classmap->findClassEx($path);
	}
	
	/**
	 * Finds an URI that corresponds to the given class.
	 * @param	string	$class	Classname.
	 * @return	string	URI, if class is published; otherwise, NULL.
	 */
	public function findURI($class)
	{
		return $this->classmap->findURI($class);
	}

	/**
	 * Finds a plugin with the given name.
	 * @param	string	$name	Plugin name. If NULL, then default plugin will be returned.
	 * @return	Light\Util\Controller\Plugin
	 */
	public function findPlugin($name = NULL)
	{
		if (is_null($name))
		{
			return $this->defaultPlugin;
		}
		else
		{
			foreach($this->plugins as $p)
			{
				if ($p->getName() == $name)
				{
					return $p;
				}
			}
			throw new Exception\Exception("Plugin %1 was not registered", $name);
		}
	}
}