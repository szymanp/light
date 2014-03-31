<?php

namespace Light\UI;
use Light\Exception\Exception;

use Light\UI\Framework\LifecycleObject;

use Light\UI\Framework\Persist;
use Light\UI\Framework\Input\RequestHandler;

/**
 * A component that can contain other components.
 *
 */
class Container extends Component
{
	protected $elements = array();
	protected $persistElements = true;
	
	public function add(Component $c) {
		
		if ($this->getLifecycleStage() >= Component::STATE_INITED)
		{
			throw new \Exception("Cannot add components when the Container is already in INITED state. Use lateAdd() instead. Occured at: <" . $c->getName() . ">");
		}
		
		$this->addComponent($c);
		return $this;
	}
	
	public function lateAdd(Component $c) {
		$this->addComponent($c);
		$c->gotoStage($this->getCurrentStage());
		return $this;
	}
	
	private function addComponent(Component $c)
	{
		$ln = $c->getLocalName();
		if (isset($this->elements[$ln])) {
			$this->elements[$ln]->finish();
		}
		
		$this->elements[$ln] = $c;
		$c->attachTo($this);
	}
	
	public function getElement($name) {
		if (!$this->hasElement($name)) {
			throw new \Exception("Component $name not found");
		}
		return $this->elements[(string)$name];
	}
	
	/**
	 * Checks if the specified element is part of this container.
	 * @param mixed $element	Element name or the component object itself.
	 * @return boolean
	 */
	public function hasElement($element)
	{
		if (is_object($element))
		{
			return in_array($element, $this->elements, true);
		}
		else
		{
			return isset($this->elements[(string)$element]);
		}
	}
	
	// content components
	
	public function setProperty($p,$v)
	{
		if ($v instanceof Component) {
			$this->lateAdd($v);
		}
		return parent::setProperty($p,$v);
	}
	
	// events
	
	/**
	 * Override to initialize static elements in this container.
	 */
	protected function init()
	{
		parent::init();
			
		foreach($this->elements as $el) {
			$el->init();
		}
	}

	/**
	 * Override to initialize dynamic elements in this container.
	 */
	protected function load()
	{
		parent::load();
		foreach($this->elements as $el) $el->load();
	}

	public function finish() {
		parent::finish();
		foreach($this->elements as $el) $el->finish();
	}
	
	// getter
	
	public function __get($name)
	{
		if ($this->isPropertyDefined($name))
		{
			return $this->getProperty($name);
		}
		else if ($this->hasElement($name))
		{
			return $this->getElement($name);
		}
		else
		{
			return parent::__get($name);
		}
	}
	
	public function __isset($name) {
		return parent::__isset($name) || isset($this->elements[$name]);
	}
	
	// persistence
	
	public function persistComponent(Persist\StoreManager $storemgr, $clientOnly) {
		parent::persistComponent($storemgr, $clientOnly);
		if ($this->persistElements) {
			foreach($this->elements as $elem) {
				$elem->persistComponent($storemgr, $clientOnly);
			}
		}
	}
	
//	public function restoreComponent(Persist\StoreManager $storemgr) {
//		parent::restoreComponent($storemgr);
//		if ($this->persistElements) {
//			foreach($this->elements as $elem) {
//				$elem->restoreComponent($storemgr);
//			}
//		}
//	}

	// RequestHandler interface implementation (partial)
	
	public function getRequestHandler($name, $index = null)
	{
		if ($this->getLifecycleStage() < LifecycleObject::STATE_INITED)
		{
			throw new Exception("Component is not initialized");
		}
		
		// @todo Support for $index
		if ($this->hasElement($name))
		{
			return $this->getElement($name);
		}
	}

}
