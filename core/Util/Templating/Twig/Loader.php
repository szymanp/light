<?php

namespace Light\Util\Templating\Twig;

use Light\Autoloader;
use Light\Exception\Exception;
use \Twig_LoaderInterface;

class Loader implements Twig_LoaderInterface
{
	const EXTENSION = ".twig.html";
	
	public function getSource($name)
	{
		$file = $this->findTemplate($name);
		
		return file_get_contents($file);
	}
	
	public function getCacheKey($name)
	{
		return $name;
	}
	
	public function isFresh($name, $time)
	{
		return filemtime($this->findTemplate($name)) < $time;
	}
	
	private function findTemplate($name)
	{
		if ($name[0] == DIRECTORY_SEPARATOR || substr($name, 1, 2) == ":" . DIRECTORY_SEPARATOR)
		{
			$file = $name;
		}
		else
		{
			if (substr($name, -strlen(self::EXTENSION)) == self::EXTENSION)
			{
				$name = substr($name, 0, -strlen(self::EXTENSION));
			}
			$file = Autoloader::find($name, substr(self::EXTENSION,1));
		}
		
		if (is_null($file))
		{
			throw new Exception("No template file with name '%1' found", $name);
		}
		else if (!file_exists($file))
		{
			throw new Exception("Template file '%1' for '%2' not found", $file, $name);
		}
		
		return $file;
	}
}