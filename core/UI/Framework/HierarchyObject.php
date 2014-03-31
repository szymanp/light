<?php

namespace Light\UI\Framework;
use Light\UI\Util\ResourceFinder;
use Light\UI\Framework\Input\DataUnit;
use Light\UI\Framework\Input\GetPostRequestDecoder;

abstract class HierarchyObject extends PropertyObject
{
	/**
	 * Name of this element in the hierarchy.
	 * @var string
	 */
	private $name;
	
	/**
	 * @var Light\UI\Framework\HierarchyObject
	 */
	private $container;
	
	/**
	 * @var Light\UI\Framework\EventHandler
	 */
	private $eventHandler;
	
	/**
	 * Constructs a new HierarchyObject.
	 * @param string	$name	A local name for this object.
	 */
	public function __construct($name)
	{
		$this->name = $name;
		$this->eventHandler = new EventHandler($this);
		
		parent::__construct();
	}

	/**
	 * Sets a container for this object.
	 * @param HierarchyObject	$c
	 */	
	public function attachTo(HierarchyObject $c)
	{
		$this->container = $c;
	}
	
	/**
	 * Returns the unqualified name of this element.
	 * @return string
	 */
	public function getLocalName() 
	{
		return $this->name;
	}
	
	/**
	 * Returns the fully-qualified name of this element.
	 *
	 * The returned name will be prefixed with the names of all parent objects.
	 *
	 * @return string	E.g. "ToDoForm[SubmitButton]"
	 */
	public function getName()
	{
		if (is_null( $this->container ))
		{
			return $this->name;
		}
		
		$pname = $this->container->getName();
		if (empty( $pname )) {
			return $this->name;
		}
		return $pname . "[" . $this->getLocalName() . "]";
	}
	
	/**
	 * Returns the fully-qualified name of this element in array form.
	 * @param boolean	$stripRoot	Strip the name of the root container.
	 * @return string[]
	 */
	public function getNameArray($stripRoot = true)
	{
		$result = array($this->getLocalName());
		for($o=$this;$o=$o->container;!is_null($o))
		{
			$name = $o->getLocalName();
			if (empty($name)) continue;
			array_unshift($result, $name);
		}
		if ($stripRoot)
		{
			array_shift($result);
		}
		return $result;
	}
	
	/**
	 * Returns a string to be used for an action field in the URL.
	 * @return string
	 */
	public function createAction($name)
	{
		$names = implode(".", $this->getNameArray());
		return GetPostRequestDecoder::ACTION_TARGET . "[" . (empty($names) ? "" : $names . ".") . $name . "]";
	}
	
	/**
	 * Returns a ResourceFinder for this object.
	 *
	 * If this object does not have its own ResourceFinder, an ancestor's ResourceFinder
	 * will be returned. If no ResourceFinder is defined in the hierarchy up to the root,
	 * then a new ResourceFinder will be constructed and set for this object.
	 *
     * @see Light\UI\Framework\Element::getResourceFinder()
	 * @return Light\UI\Util\ResourceFinder
	 */
	public function getResourceFinder()
	{
		$t = parent::getResourceFinder();
		
		if (!is_null($t))
		{
			return $t;
		}
		else if ($this->hasContainer())
		{
			return $this->getContainer()->getResourceFinder();
		}
		else
		{
			$this->setResourceFinder($t = new ResourceFinder($this));
			return $t;
		}
	}
	
	/**
	 * Returns the event handler for this element.
	 * @return Light\UI\Framework\EventHandler
	 */
	public function getEventHandler()
	{
		return $this->eventHandler;
	}

	/**
	 * Returns the parent of this Component.
	 * @return Light\UI\Framework\HierarchyObject
	 */
	public function getContainer()
	{
		return $this->container;
	}
	
	/**
	 * Returns true if this object is part of another object.
	 * @return boolean
	 */
	public function hasContainer()
	{
		return !is_null($this->container);
	}
	
	/**
	 * Finds the first parent container of the specified class.
	 * @param string $class
	 * @return Light\UI\Framework\HierarchyObject
	 */
	public function findContainer($class)
	{
		$c = $this->container;
		while (!is_null($c))
		{
			if ($c instanceof $class)
			{
				return $c;
			}
			$c = $c->getContainer();
		}
		return NULL;
	}

	// RequestHandler interface implementation (partial)
	
	public function invokeRequestHandlerAction(DataUnit $actionData)
	{
		if ($this->getLifecycleStage() < LifecycleObject::STATE_LOADED)
		{
			throw new Exception("Component is not LOADED");
		}
		
		$event = new Event($actionData->getName(), $actionData->getValue());
		$this->getEventHandler()->raise($event);
	}
}
