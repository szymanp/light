<?php

namespace Light\UI\Framework\Persist;

class Property
{
	private $name;
	private $scope = Store::REQUEST;
	private $alias;
	private $using;
	private $range;
	
	public function __construct($name, $defaultRange = NULL)
	{
		$this->name = $name;
		$this->range = $defaultRange;
	}
	
	public function scope($scope)
	{
		$this->scope = $scope;
		return $this;
	}
	
	public function alias($alias)
	{
		$this->alias = $alias;
		return $this;
	}
	
	public function using($using)
	{
		$this->using = $using;
		return $this;
	}
	
	public function range($range)
	{
		if ($this->scope == Store::SESSION)
		{
			throw new \Exception("Range can be set only for properties stored in a REQUEST store.");
		}
		$this->range = $range;
		return $this;
	}

	public function getName()
	{
	    return $this->name;
	}

	public function getScope()
	{
	    return $this->scope;
	}

	public function getAlias()
	{
	    return $this->alias;
	}

	public function getUsing()
	{
	    return $this->using;
	}
	
	public function getRange()
	{
		return $this->range;
	}
	
	public function isWithinRange($target)
	{
		if ($target[0] != "\\") $target = "\\" . $target;
		$l = strlen($this->range);
		return substr($target, 0, $l) === $this->range;
	}
}