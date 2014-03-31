<?php

namespace Light\UI\Component\Table;
use Light\UI;
use Light\Data;

class ActionColumn implements Column {

	protected $container;
	protected $label;
	protected $elements;
	protected $name;
	protected $attributes;
	
	public function __construct($name, $label = "") {
		$this->name			= $name;
		$this->label		= $label;
		$this->attributes	= new UI\Util\TagAttributes();
	}
	
	public function add($class, $name, UI\Style $style)
	{
		$this->elements[] = array($class,$name,$style);
	}
	
	public function addButton($name, $label, $callback)
	{
		$style = new UI\Style();
		$style->Content = $label;
		$style->addEventHandler("click", $callback);
		$this->add("Light\UI\Component\Button", $name, $style);
	}
	
	public function attach(UI\Container $table)
	{
		$this->container = $table;
	}
	
	public function getLabel()
	{
		return $this->label;
	}
	
	public function getInstance($object, $key)
	{
		$name = $this->name;
		if (!is_null($key))
		{
			$name .= (string) $key;
		}
		
		if ($this->container->hasElement($name))
		{
			return $this->container->getElement($name);
		}

		$class = "Light\UI\Component\Panel";
		
		$instance = new $class($name);
		$instance->setDataContext(Data\Helper::wrap($object));
		
		foreach($this->elements as $eldef)
		{
			$elclass = $eldef[0];
			$elem = new $elclass($eldef[1]);
			$instance->add($elem);
			$eldef[2]->applyTo($elem);
		}
		
		$this->container->lateAdd($instance);
		
		return $instance;		
	}
	
	public function getValue($value) {
		return NULL;
	}
	
	public function getHREF($value, $key)
	{
		return NULL;
	}	
	
	public function getAttributes()
	{
		return $this->attributes;
	}	

	public function setAttribute($name,$value)
	{
		$this->attributes->set($name,$value);
		return $this;
	}	
	
}