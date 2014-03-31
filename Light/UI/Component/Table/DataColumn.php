<?php

namespace Light\UI\Component\Table;
use Light\UI;
use Light\Data;

class DataColumn implements Column {

	protected $container;
	protected $path;
	protected $label;
	protected $href;
	protected $attributes;
	
	public function __construct($path,$label) {
		$this->path			= $path;
		$this->label		= $label;
		$this->attributes	= new UI\Util\TagAttributes();
	}
	
	public function setHREF(UI\Util\Href $href)
	{
		$this->href = $href;
		return $this;
	}
	
	public function attach(UI\Container $table)
	{
		$this->container = $table;
	}
	
	public function getLabel()
	{
		return $this->label;
	}
	
	public function getInstance($value, $key)
	{
		return NULL;
	}
	
	public function getValue($value) {
		return Data\Helper::get($value,$this->path);
	}
	
	public function getHREF($value, $key)
	{
		if ($this->href != null)
		{
			return $this->href->evaluate($value);
		}
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