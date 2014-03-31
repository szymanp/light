<?php

namespace Light;

spl_autoload_register( array( "Light\Autoloader", "autoload" ) );

class Autoloader {
	
	private static $paths = array();
		
	public static function autoload($class) {
		
		$file = strtr($class,"_\\",DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR) . ".php";
		
		$finalpath = $file;
		$found = false;

		foreach( self::$paths as $path ) {
			$fullpath = $path . DIRECTORY_SEPARATOR . $file;
			if (file_exists($fullpath)) {
				$finalpath = $fullpath;
				$found = true;
				break;
			}
		}
		
		if ($found)
		{
			include_once( $finalpath );
		}
		else
		{
			@include_once( $finalpath );
		}
	}
	
	/**
	 * @param	string	$name	E.g. <kbd>Util_Locale_String</kbd>
	 * @param	string	$ext	E.g. <kbd>php</kbd>
	 * @return	string	Full path to the file.
	 */
	public static function find($name,$ext,$separator="_")
	{
		$file = strtr($name,$separator . "\\",DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR) . "." . $ext;
		
		if ($file[0] == DIRECTORY_SEPARATOR)
		{
			$file = substr($file, 1);
		}
		
		$finalpath = $file;

		foreach( self::$paths as $path )
		{
			$fullpath = $path . DIRECTORY_SEPARATOR . $file;
			if (file_exists($fullpath)) {
				return $fullpath;
			}
		}
		
		return NULL;
	}
	
	public static function addPath($path) {
		$path = realpath($path);
		if (in_array($path,self::$paths))
			return;
			
		self::$paths[] = $path;
	}
	
	public static function prependPath($path) {
		$path = realpath($path);
		if (in_array($path,self::$paths))
			return;
			
		array_unshift(self::$paths,$path);
	}

	public static function addIncludePath($path) {
		set_include_path(get_include_path() . PATH_SEPARATOR . $path);
	}
	
	private static function lcfirst($s) {
		return strtolower($s[0]) . substr($s,1);
	}
	
}


?>