<?php

namespace Light\UI\Util;

class PageHistory implements \IteratorAggregate
{
	private $stack;
	
	public function __construct()
	{
		$this->stack = array();
	}
	
	public function store($pageClass)
	{
		if (($key = array_search($pageClass, $this->stack, true)) !== false)
		{
			$this->stack = array_slice($this->stack, 0, $key);
		}
		
		array_push($this->stack,$pageClass);
	}
	
	public function getIterator()
	{
		return new \ArrayIterator($this->stack);
	}
}