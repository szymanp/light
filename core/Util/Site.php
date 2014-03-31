<?php

namespace Light\Util;
use Exception;

class Site {

	/**
	 * Active site.
	 * @var	Util_Site
	 */
	private static $site;
	
	/**
	 * @return Light\Util\Site
	 */
	public static function getInstance() {
		if (is_null( self::$site )) {
			self::$site = new self();
		}
		return self::$site;
	}

	/**
	 * A list of published directories.
	 * @var array
	 */
	private $published	= array();

	/**
	 * @param	string	$path	Path to a published resource.
	 * @param	string	$url	Host-relative URL where the resource is available.
	 * @return Light\Util\Site	For fluent API.
	 */
	public function addPublished($path,$url) {
		$path = realpath($path);
		$this->published[$path] = $url;
		return $this;	// fluent API
	}
	
	/**
	 * Returns an URL for a given file resource.
	 * @param	string	$path	File path to a published resource on disk.
	 * @return	string	A host-relative URL for the given filepath.
	 */
	public function getUrlFor($path) {
	
		$plen = strlen($path);
	
		foreach($this->published as $ppath => $url) {
			$pplen = strlen($ppath);
			if ($pplen > $plen) {
				continue;
			}
			
			if (substr($path,0,$pplen) != $ppath) {
				continue;
			}
			
			$rel 	= strtr(substr($path,$pplen),DIRECTORY_SEPARATOR,"/");
			if ($rel[0] == "/") {
				$rel = substr($rel,1);
			}
			$final	= $url . $rel;
			return $final;
		}
		
		return NULL;
	}
	
	/**
	 * Returns an URL for a given file resource, or throws an exception in case of failure.
	 * 
	 * @param	string	$path	File path to a published resource on disk.
	 * @throws Exception
	 * @return	string	A host-relative URL for the given filepath.
	 */
	public function getUrlForOrThrow($path)
	{
		$uri = $this->getUrlFor($path);
		
		if (!is_null($uri))
		{
			return $uri;
		}
		
		$published = "";
		foreach($this->published as $ppath => $url) $published .= "$ppath at <$url>\n";
		
		throw new Exception("Resource <$path> is not published. Published URLs include: " . $published);
	}

}