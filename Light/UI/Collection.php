<?php

namespace Light\UI;

use Light\Exception;
use Light\Data\Coords;
use Light\Data;
use Light\UI\Framework\Listener;

class Collection implements \Countable, \IteratorAggregate, Listener\StageChange
{
	/** @var Light\UI\Component */
	private $owner;
	private $baseClassName;
	private $className;
	private $generator;
	private $source;
	private $transformers = array();
	private $namePattern;
	/** @var Light\UI\Style */
	private $style;
	
	private $elementsByIndex	= array();
	private $elementsByName		= array();
	private $nextIndex			= 0;
	
	private $stageTrigger		= Component::STATE_LOADED;

	public function __construct(Component $owner, $baseClassName = "", $namePattern = "")
	{
		$this->owner			= $owner;
		$this->baseClassName 	= $this->className = $baseClassName;
		$this->namePattern		= $namePattern;
		$this->style			= new Style();
	}
	
	// life cycle management

	public function setStageTrigger($trigger)
	{
		$this->stageTrigger = $trigger;
		return $this;
	}
	
	public function setLazyLoad($v = true)
	{
		if ($v)
		{
			$this->stageTrigger = Component::STATE_RENDERED;
		}
		else
		{
			$this->stageTrigger = Component::STATE_LOADED;
		}
	}
	
	public function onStageChanged($stage)
	{
		if ($stage < $this->stageTrigger)
		{
			return;
		}
		
		// make sure that all elements are constructed
		foreach($this->getIterator() as $c) {}
	}
	
	// element source
	
	/**
	 * Sets the underlying source of data for this Collection.
	 * For each item in the source, a component will be created and appended 
	 * to the Collection.
	 * @param	mixed	$source
	 * @return	Collection	For fluent API.
	 */
	public function setSource($source)
	{
		$this->source = $source;
		return $this;
	}
	
	public function __set($name, $value)
	{
		switch($name)
		{
		case "Source":	$this->setSource($value); break;
		default:		throw new \Exception("Invalid property: $name");
		}
	}

	/**
	 * Creates a new Component for the specified index.
	 * The Component is automatically added to the Collection.
	 * @param integer	$index
	 * @param mixed		$dataobject
	 * @return	Component
	 */
	protected function generateElement($index, $dataobject = NULL)
	{
		// generate the name
		$name = $this->namePattern . $index;
	
		// construct a new instance
		if (!is_null($this->generator))
		{
			$c = call_user_func_array(
					$this->generator, 
					array(
						$this->owner,
						$index,
						$dataobject));
		}
		else
		{
			$clazz = $this->className;
			$c = new $clazz($name);
		}
		
		// attach to owner
		$this->attachToOwner($c);
		
		// set datacontext
		if (!is_null($dataobject))
		{
			$c->setDataContext(Data\Helper::wrap($dataobject));
		}
		
		// apply style
		$this->style->applyTo($c);
		
		// transformers
		$this->invokeTransformers($c, $index, $dataobject);

		// add to collection
		$this->elementsByIndex[$index]	= $c;
		$this->elementsByName[$name]	= $c;
		
		return $c;
	}
	
	private function invokeTransformers(Component $c, $index, $dataobject)
	{
		foreach($this->transformers as $transformer)
		{
			call_user_func_array(
				$transformer,
				array(
					$this->owner,
					$c,
					$index,
					$dataobject));
		}
	}
	
	protected function attachToOwner(Component $c)
	{
		$c->attachTo($this->owner);
	}
	
	// Collection access

	/**
	 * Add a Component to the collection.
	 * @param Component	$c
	 * @return Collection	For fluent API.
	 */	
	public function add(Component $c, $dataobject = NULL)
	{
		if (!is_a($c, $this->className))
		{
			throw new Exception\InvalidParameterType('$c',$c,$this->className);
		}
		
		if (!is_null($this->source))
		{
			throw new \Exception("A Source has been already set");
		}
		
		$index = $this->nextIndex++;
		$this->elementsByIndex[$index]		 = $c;
		$this->elementsByName[$c->getName()] = $c;

		$this->attachToOwner($c);
		
		$this->invokeTransformers($c, $index, $dataobject);
		
		return $this;
	}
	
	/**
	 * Returns an element matching the specified index, if found.
	 * @param integer	$index
	 * @return Component	The component at the given index, if it exists; otherwise, NULL.
	 */
	public function get($index)
	{
		if (!is_integer($index))
		{
			throw new Exception\InvalidParameterType('$index',$index,"integer");
		}
		if (isset( $this->elementsByIndex[$index] ))
		{
			return $this->elementsByIndex[$index];
		}
		if (is_null($this->source))
		{
			throw new \Exception("'source' cannot be NULL");
		}
		Data\ModelManager::prepare($this->source);
		if (isset( $this->source[$index] ))
		{
			return $this->generateElement($index, $this->source[$index]);
		}
		return NULL;
	}
	
	/**
	 * Creates a new component and appends it to this collection.
	 * @param mixed		$dataobject	An optional dataobject that will be passed to the new component.
	 * @return Component
	 */
	public function getNew($dataobject = NULL)
	{
		$index = $this->nextIndex++;
		return $this->generateElement($index, $dataobject);
	}
	
	public function getIterator()
	{
		return new Collection_Iterator($this);
	}
	
	public function removeAt($index)
	{
		if (isset($this->elementsByIndex[$index]))
		{
			$c = $this->elementsByIndex[$index];
			unset($this->elementsByIndex[$index]);
			
			$key = array_search($c, $this->elementsByName, true);
			unset($this->elementsByName[$key]);
		}
	}
	
	public function remove(Component $c)
	{
		if (($key = array_search($c, $this->elementsByName, true)) !== false)
		{
			unset($this->elementsByName[$key]);

			$key = array_search($c, $this->elementsByIndex, true);
			unset($this->elementsByIndex[$key]);
		} 
	}
	
	// Generators and transformers
	
	/**
	 * Sets a generator for this Collection.
	 * @param Collection\Generator|Closure|callback	$generator
	 * @return Collection	For fluent API.
	 */
	public function generator($generator)
	{
		if (!($generator instanceof Closure)
			&& !($generator instanceof Collection\Transformer)
			&& !is_callable($generator))
		{
			throw new Exception\InvalidParameterType('$generator',$generator,"callable");
		}
		
		$this->generator = $generator;
		
		return $this;
	}
	
	/**
	 * Adds a transformer to this Collection.
	 * @param Collection\Transformer|Closure|callback	$generator
	 * @return Collection	For fluent API.
	 */
	public function transformer($transformer)
	{
		if (!($transformer instanceof Closure)
			&& !($transformer instanceof Collection\Transformer)
			&& !is_callable($transformer))
		{
			throw new Exception\InvalidParameterType('$transformer',$transformer,"callable");
		}
		
		$this->transformers[] = $transformer;
		
		return $this;
	}
	
	/**
	 * Sets the class that will be instantiated as Collection elements.
	 * @param string	$className
	 * @return Collection	For fluent API.
	 */
	public function setClass($className)
	{
		if (!empty($this->baseClassName)
			&& !is_subclass_of($className, $this->baseClassName))
		{
			throw new Exception\InvalidParameterValue('$className',$className,"Must be a subclass of " . $this->baseClassName);
		}
		
		$this->className = $className;
		
		return $this;
	}
	
	// Naming and numbering
	
	public function getNamePattern()
	{
		return $this->namePattern;
	}
	
	/**
	 * Tests if the given element name matches this provider's pattern.
	 * @return boolean
	 */
	public function isNamePatternMatch($name)
	{
		return (substr($name, 0, strlen($this->namePattern)) == $namePattern);
	}
	
	/**
	 * Extracts the coordinates from the element name.
	 * @return Coords	The element coordinates, if the pattern matches;
	 *					otherwise, NULL.
	 */
	public function extractCoordsFromName($name)
	{
		// @TODO
		$l = strlen($this->namePattern);
		if (substr($name, 0, $l) == $namePattern)
		{
			$clazz = $this->coordsClassName;
			$coords = new $clazz;
			return $coords->unserialize(substr($name, $l + 1 ));
		}
		return NULL;
	}
	
	// Interfaces
	
	public function count()
	{
		// @TODO
//		return count($this->elementsByIndex) + count($this->source);
	}
	
	public function offsetExists($index)
	{
		return isset($this->elementsByIndex[$index]);
	}
	
}

class Collection_Iterator implements \Iterator
{
	private $coll;
	private $index = 0;
	private $count;
	
	public function __construct(Collection $c)
	{
		$this->coll = $c;
		$this->count = $c->count();
	}
	
	public function current()
	{
		return $this->coll->get($this->index);
	}
	
	public function key()
	{
		return $this->index;
	}
	
	public function next()
	{
		$this->index++;
	}
	
	public function rewind()
	{
		$this->index = 0;
	}
	
	public function valid()
	{
		return $this->coll->get($this->index) != NULL;
	}
}