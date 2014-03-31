<?php

namespace Light\UI\Util;

use Light\UI\Framework;

/**
 * A template 
 */
class ResourceFinder {

	/**
	 * @var	Framework\Element
	 */
	protected $owner;
	
	/**
	 * Directories containing resource files.
	 * @var array<string>
	 */
	protected $resourceDirs;
	
	public function __construct(Framework\Element $owner) {
		$this->owner	= $owner;
	}
	
	public function getDefaultResourceFile(Framework\Element $element, $suffix = ".phtml")
	{
		$name = get_class($element);
		$p = strrpos($name,"\\");
		if ($p === false)
		{
			$p = strrpos($name,"_");
		}
		if ($p !== false) {
			$name = substr($name,$p+1);
		}		
		
		return $this->getResourceFile($element, $name . $suffix);
	}
	
	public function getResourceFile(Framework\Element $element, $filename)
	{
		$dir = $this->findResourceDir($element, $filename);
		return $dir . DIRECTORY_SEPARATOR . $filename;
	}
	
	// properties
	
	/**
	 * Specifies a directory where resources can be found.
	 * The directories added using this method will be searched in order
	 * so that last added will be tried first.
	 * @param string	$dir
	 */
	public function addResourceDir($dir) {
		if (!is_array($this->resourceDirs)) $this->resourceDirs = array();
		array_unshift($this->resourceDirs, $dir);
	}
	
	public function getResourceDirs() {
		return $this->resourceDirs;
	}
	
	protected function findResourceDir(Framework\Element $element, $filename)
	{
		if (!empty( $this->resourceDirs ))
		{
			foreach($this->resourceDirs as $rd)
			{
				if (file($rd . DIRECTORY_SEPARATOR . $filename))
				{
					return $rd;
				}
			}
		}
		
		return $this->getDefaultResourceDir($element);
	}
	
	protected function getDefaultResourceDir(Framework\Element $element) {
		$refl = new \ReflectionObject($element);
		return dirname($refl->getFileName());
	}

}
