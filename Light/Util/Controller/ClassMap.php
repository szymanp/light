<?php

namespace Light\Util\Controller;

/**
 * A mapping between URLs and classes or packages.
 *
 */
class ClassMap
{
	protected $classes	= array();
	protected $packages = array();
	
	/**
	 * Makes the specified class available at the given URI.
	 * @param string	$class
	 * @param string	$uri
	 * @return Light\Util\Controller\ClassMap
	 */
	public function publishClass($class, $uri = NULL)
	{
		if ($uri === NULL)
		{
			$uri = strtr($class, "\\", "/");
		}
		$this->classes[$class] = $uri;
		return $this;
	}

	/**
	 * Makes the specified package available at the given URI. 
	 * @param string	$package
	 * @param string	$uri
 	 * @return Light\Util\Controller\ClassMap
	 */
	public function publishPackage($package, $uri = NULL)
	{
		if ($uri === NULL)
		{
			$uri = strtr($package, "\\", "/");
		}
		$this->packages[$package] = $uri;
		return $this;
	}
	
	/**
	 * Finds a class that corresponds to the given URI.
	 * @param	string	$path	URI
	 * @return	string	Class name, if found; otherwise, NULL.
	 */
	public function findClass($path)
	{
		foreach($this->classes as $class => $uri)
		{
			if ($path === $uri)
			{
				return $class;
			}
		}
		
		if (empty($path))
		{
			return NULL;
		}
		
		foreach($this->packages as $package => $uri)
		{
			$l = strlen($uri);
			if (substr($path, 0, $l) === $uri)
			{
				return $package . "\\" . strtr(substr($path, $l), "/", "\\");
			}
		}
		
		return NULL;
	}
	
	/**
	 * Finds a class that <b>might</b> correspond to the given URI.
	 * @param	string	$path	URI. If a class is found, then the parameter
	 *							will contain the remaining URI fragment.
	 * @return	string	Class name, if found; otherwise, NULL.
	 */
	public function findClassEx(& $path)
	{
		foreach($this->classes as $class => $uri)
		{
			$testuri = $uri;
			$l = strlen($testuri);
			if ($testuri[$l] != "/")
			{
				$testuri .= "/";
				$l++;
			}
		
			if (substr($path, 0, $l) === $testuri)
			{
				$path = substr($path, $l);
				return $class;
			}
		}
		
		if (empty($path))
		{
			return NULL;
		}
		
		foreach($this->packages as $package => $uri)
		{
			$l = strlen($uri);
			$matched = substr($path, 0, $l);
			if ($matched !== $uri)
			{
				continue;
			}
			
			$class = $package;
			
			$remaining = explode("/", substr($path, $l));
			foreach($remaining as $element)
			{
				$matched	.= "/" . $element;
				$class		.= "\\" . $element;
				if (!is_null(Autoloader::find($class, "php")))
				{
					$path = substr($path, strlen($matched));
					return $class;
				}
			}
		}
		
		return NULL;
	}
	
	/**
	 * Finds an URI that corresponds to the given class.
	 * @param	string	$class	Classname.
	 * @return	string	URI, if class is published; otherwise, NULL.
	 */
	public function findURI($class)
	{
		$uri = NULL;
		
		if ($class[0] != "\\") $class = "\\" . $class;
		
		if (isset( $this->classes[$class] ))
		{
			$uri = $this->classes[$class];
		}
		else
		{
			foreach($this->packages as $package => $puri)
			{
				$l = strlen($package);
				if (substr($class, 0, $l) === $package)
				{
					$uri = $puri . "/" . strtr(substr($class, $l+1), "\\", "/");
					break;
				}
			}
		}
		
		return $uri;
	}
	
}
