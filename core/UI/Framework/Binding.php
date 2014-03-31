<?php

namespace Light\UI\Framework;
use Light\Data;

class Binding
{
	private $path;
	private $converter;
	private $ignoreWrites = false;
	
	public function __construct($path)
	{
		$this->path = $path;
	}
	
	public function getPath()
	{
	    return $this->path;
	}

	public function getConverter()
	{
	    return $this->converter;
	}

	public function setConverter(Data\ValueConverter $converter)
	{
	    $this->converter = $converter;
	    return $this;
	}
	
	/**
	 * Sets whether the binding should be read-only.
	 * @param boolean	$ignoreWrites
	 * @return Binding
	 */
	public function setIgnoreWrites($ignoreWrites)
	{
	    $this->ignoreWrites = $ignoreWrites;
	    return $this;
	}

	public function getIgnoreWrites()
	{
	    return $this->ignoreWrites;
	}
}