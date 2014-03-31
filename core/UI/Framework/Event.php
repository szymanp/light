<?php

namespace Light\UI\Framework;

class Event
{
	/** @var string */
	private $name;
	
	/**
	 * The framework element that raised this event.
	 * @var Light\UI\Framework\HierarchyObject
	 */
	private $target;
	
	/**
	 * Optional arguments to this event.
	 * @var mixed
	 */
	private $args;
	
	/**
	 * @var boolean
	 */
	private $caught = false;
	
	public function __construct($name, $args = null)
	{
		$this->name = $name;
		$this->args = $args;
	}
	
	/**
	 * Returns the name of the event.
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * Returns the arguments passed with the event.
	 * @return mixed
	 */
	public function getArgs()
	{
		return $this->args;
	}
	
	/**
	 * Sets the framework element that raised this event.
	 * @param HierarchyObject	$target
	 */
	public function setTarget(HierarchyObject $target)
	{
		$this->target = $target;
	}
	
	/**
	 * Returns the framework element that raised this event.
	 * @return Light\UI\Framework\HierarchyObject
	 */
	public function getTarget()
	{
		return $this->target;
	}
	
	/**
	 * Marks this event as caught.
	 */
	public function catchEvent()
	{
		if ($this->caught)
		{
			throw new \Exception("Event has already been caught");
		}
		$this->caught = true;
	}
	
	/**
	 * Returns true if this event has been caught.
	 * @return boolean
	 */
	public function isCaught()
	{
		return $this->caught;
	}
}