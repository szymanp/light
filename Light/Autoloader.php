<?php

namespace Light;

spl_autoload_register( array( "Light\Autoloader", "autoload" ) );

class Autoloader
{
	/**
	 * @var Autoloader_Path[]
	 */
	private static $paths = array();
	
	/**
	 * If true, debug output will be printed upon load failure.
	 * @var boolean
	 */
	public static $debug = false;
	
	/**
	 * Autoloads the given class.
	 * @param string	$class	Class name.
	 */
	public static function autoload($class)
	{
		$path = self::find($class);

		if (!is_null($path))
		{
			include_once($path);
		}
		else
		{
			@include_once($class);
		}
	}
	
	/**
	 * Finds the given resource.
	 * @param	string	$name	E.g. <kbd>Light\Util\Locale\String</kbd>
	 * @param	string	$ext	E.g. <kbd>php</kbd>
	 * @return	string	Full path to the file, if found; otherwise, NULL.
	 */
	public static function find($name, $ext = "php", $separator="_")
	{
		$file = strtr($name,$separator . "\\",DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR) . "." . $ext;
		
		if ($file[0] == DIRECTORY_SEPARATOR)
		{
			$file = substr($file, 1);
		}
		
		$debugLog = "";
		
		foreach(self::$paths as $pathSpec)
		{
			$path = $pathSpec->buildPath($file);
			
			if (self::$debug)
			{
				$debugLog .= $pathSpec . " => " . $path . "\n";
			}
			
			if (!is_null($path) && file_exists($path))
			{
				return $path;
			}
		}
		
		if (self::$debug)
		{
			print "\nAutoloading resource '$name' - finding file $file:\n" . $debugLog . "\n";
		}
		
		return NULL;
	}

	/**
	 * Register a new root path for resources.
	 *
	 * @param string	$path	Path to root of resources.
	 * @param string	$prefix	A prefix for resources under this path.
	 */	
	public static function addPath($path, $prefix = null)
	{
		$pathSpec = new Autoloader_Path(realpath($path), $prefix);
		self::$paths[] = $pathSpec;
	}
	
	public static function prependPath($path, $prefix = null)
	{
		$pathSpec = new Autoloader_Path(realpath($path), $prefix);
		array_unshift(self::$paths,$pathSpec);
	}

	/**
	 * Appends the given path to the PHP include path.
	 * @param string	$path	Path to be appended to the PHP include path.
	 */
	public static function addIncludePath($path)
	{
		set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	}
	
	private static function lcfirst($s)
	{
		return strtolower($s[0]) . substr($s,1);
	}
}

class Autoloader_Path
{
	/**
	 * @var string
	 */
	public $path;
	
	/**
	 * Note: The prefix must always end with a DIRECTORY_SEPARATOR.
	 * @var string
	 */
	public $prefix;
	
	public function __construct($path, $prefix = null)
	{
		$this->path	= $path;
		
		if (is_string($prefix))
		{
			$this->prefix = $prefix . DIRECTORY_SEPARATOR;
		}
	}
	
	public function buildPath($filename)
	{
		$filename = $this->stripPrefix($filename);
		if (is_null($filename))
		{
			return null;
		}
	
		return $this->path . DIRECTORY_SEPARATOR . $filename;
	}
	
	private function stripPrefix($filename)
	{
		if (!is_null($this->prefix))
		{
			$length = strlen($this->prefix);
			if (substr($filename, 0, $length ) == $this->prefix)
			{
				return substr($filename, $length);
			}
			
			return null;
		}
		else
		{
			return $filename;
		}
	}
	
	public function __toString()
	{
		return $this->path . ($this->prefix ? " [" . $this->prefix . "]" : "");
	}
}