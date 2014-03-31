<?php

namespace Light\UI\Framework;
use Light\Data;
use \Exception;

class BindingObject extends HierarchyObject {

	private $datacontext;
	private $databindings = array();
	protected $validationProblems = array();
	
	public function setDataContext(Data\Entity $dc) {
		$this->datacontext = $dc;
		return $this;	// fluent API
	}
	
	/**
	 * @return	Data_Entity
	 */
	public function getDataContext() {
		if (!is_null( $this->datacontext )) {
			return $this->datacontext;
		}
		if ($this->hasContainer()) {
			return $this->getContainer()->getDataContext();
		}
		return NULL;
	}

	public function databind($property,$path) {
		if ($path instanceof Binding)
		{
			$binding = $path;
		}
		else
		{
			$binding = new Binding($path);
		}
		$this->databindings[$property] = $binding;
		return $this;	// fluent API
	}
	
	public function setProperty($name,$value) {
		if (isset( $this->databindings[$name] )) {
			$context = $this->getDataContext();
			
			if (is_null($context)) {
				throw new Exception("Datacontext is not set");
			}
			
			if ($context instanceof Data\Object)
			{
				$binding = $this->databindings[$name];
				if ($binding->getIgnoreWrites())
				{
					// ignore this write attempt
					return $this;
				}
				
				if (!is_null( $converter = $binding->getConverter() ))
				{
					$value = $converter->convertTo($value);
				}
				
				try
				{
					$context->setValue($binding->getPath(),$value);
				}
				catch (Data\ValidationException $e)
				{
					
				}
				return $this;
			}
			
			throw new Exception("Cannot bind to given context");
		}
		return parent::setProperty($name,$value);
	}
	
	public function getProperty($name) {
		if (isset( $this->databindings[$name] )) {
			$context = $this->getDataContext();
			
			if (is_null($context)) {
				throw new Exception("Datacontext is not set");
			}
			
			if ($context instanceof Data\Object)
			{
				$binding = $this->databindings[$name];
				$value = $context->getValue($binding->getPath());
				if (!is_null( $converter = $binding->getConverter() ))
				{
					$value = $converter->convertFrom($value);
				}
				
				return $value;
			}
			
			throw new Exception("Cannot bind to given context");
		}
		return parent::getProperty($name);
	}

}

