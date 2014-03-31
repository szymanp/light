<?php

namespace Light\Data;
use Light\UI\Framework;

class BoundValue
{
	private $target;
	private $binding;
	private $wrapped;
	
	public function __construct($target, $path)
	{
		$this->target = $target;
		$this->wrapped = Helper::wrap($target);
		if ($path instanceof Framework\Binding)
		{
			$this->binding = $path;
		}
		else
		{
			$this->binding = new Framework\Binding($path);
		}
	}
	
	public function get()
	{
		return $this->wrapped->getValue($this->binding->getPath());
	}
	
	public function set($value)
	{
		
	}
}