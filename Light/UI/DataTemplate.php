<?php

namespace Light\UI;
use Light\UI\Component\Renderable;
use Light\Data;

/**
 * Data template 
 */
class DataTemplate extends \UI_Component_Renderable {

	private $key;
	private $dataobject;
	private $wrappedObject;
	
	public function setDataObject($object, $key = NULL)
	{
		$this->dataobject = $object;
		$this->key = $key;
		$this->setDataContext($this->getWrappedObject());
	}
	
	public function getDataObject()
	{
		return $this->dataobject;
	}
	
	public function getKey()
	{
		return $this->key;
	}
	
	public function getWrappedObject()
	{
		if (is_null($this->wrappedObject))
		{
			$this->wrappedObject = Data\Helper::wrap($this->dataobject);
		}
		return $this->wrappedObject;
	}
	
	public function __get($name)
	{
		if ($this->getWrappedObject()->hasProperty($name))
		{
			$v = $this->getWrappedObject()->getValue($name);
			return $v;
		}
		
		return parent::__get($name);
	}
}
