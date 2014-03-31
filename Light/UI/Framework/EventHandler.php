<?php

namespace Light\UI\Framework;

class EventHandler
{
	/** @var Light\UI\Framework\HierarchyObject */
	private $owner;
	
	private $bind = array();

	public function __construct(HierarchyObject $owner)
	{
		$this->owner = $owner;
	}
	
	/**
	 * Attaches a handler to an event.
	 * @param string	$eventName
	 * @param callback	$handler
	 */
	public function bind($eventName, $handler)
	{
		$this->bind[$eventName][] = $handler;
	}
	
	/**
	 * Accepts
	 */
	public function raise(Event $event)
	{
		if ($event->isCaught)
		{
			throw new \Exception("The event has already been caught");
		}
		
		$event->setTarget($this->owner);
		$this->accept($event);
	}
	
	protected function accept(Event $event)
	{
		$eventName = $event->getName();
		if (isset($this->bind[$eventName]))
		{
			foreach($this->bind[$eventName] as $callback)
			{
				call_user_func($callback, $event, $this->owner);
				
				if ($event->isCaught())
				{
					return;
				}
			}
		}
		
		if (!is_null($parent = $this->owner->getContainer()))
		{
			$parent->getEventHandler()->accept($event);
		}
	}
}