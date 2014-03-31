<?php

namespace Light\UI\View;
use Light\Util\Controller\Controller;

use Light\UI\Container;
use Light\UI\Component;

/**
 * A view that initializes a single component.
 *
 */
class ComponentView extends AbstractView
{
	private $component;
	private $root;
	
	private $_path;
	
	/**
	 * Sets the root component.
	 * @param Container $root
	 */
	public function setRoot(Container $root)
	{
		$this->root = $root;
	}
	
	/**
	 * Sets the root component class.
	 * @param string	$clazz
	 */
	public function setRootClass($clazz)
	{
		$this->root = new $clazz;
	}
	
	/**
	 * Sets the component that is to be executed.
	 * @param Component $c
	 */
	public function setTargetComponent(Component $c)
	{
		$this->component = $c;
	}
	
	/**
	 * Sets the path to the component that is to be executed.
	 * @param array $path
	 */
	public function setTargetPath(array $path)
	{
		$this->_path = $path;
	}
	
	/**
	 * @return Light\UI\Component
	 */
	public function getRoot()
	{
		return $this->root;
	}

	/**
	 * @return Light\UI\Component
	 */
	public function getTargetComponent()
	{
		return $this->component;
	}
	
	/**
	 * Initializes the view.
	 */
	public function initialize()
	{
		$this->root->attachToView($this);
		
		$this->root->init();
		
		// find the target component from the path, if it is not specified directly
		if (is_null($this->component))
		{
			$instance = $this->root;
		
			foreach($this->_path as $name)
			{
				$instance = $instance->getElement($name);
			}
			
			$this->component = $instance;
		}
		
		// process events

		if (empty( $this->name )) {
			$post	= $_POST;
			$get	= $_GET;
		} else {
			$post	= @$_POST[$this->name];
			$get	= @$_GET[$this->name];
		}
		if (is_array($post)) {
			$this->processContainerEvents($post,$this->root);
		}
		if (is_array($get)) {
			$this->processContainerEvents($get,$this->root);
		}
		
		// notify the controller about the executed class
		
		Controller::getInstance()->notifyInvokedClassChange(get_class($this->root),array());
	}
	
	public function run(Container $c)
	{
		throw new Exception("This view cannot be executed directly");
	}
}