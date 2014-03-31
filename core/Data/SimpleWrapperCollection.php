<?php

namespace Light\Data;
use Light\Exception\NotImplementedException;

class SimpleWrapperCollection implements CompositeCollection
{
	private $capability	= 0;
	protected $collection;

	public function __construct(Collection $c)
	{
		$this->collection = $c;
		
		if ($c instanceof \Countable)
		{
			$this->capability += CompositeCollection::CAP_COUNT;
		}
		if ($c instanceof FilteringCollection)
		{
			$this->capability += CompositeCollection::CAP_FILTER;
		}
		if ($c instanceof ReadCollection)
		{
			$this->capability += CompositeCollection::CAP_READ;
		}
		if ($c instanceof ReadWriteCollection)
		{
			$this->capability += CompositeCollection::CAP_WRITE;
		}
	}
	
	public function hasCapability($capability)
	{
		return ($this->capability & $capability) != 0;
	}
	
	// Model
	
	public function load()
	{
		$this->collection->load();
	}
	
	public function getModelObject()
	{
		return $this->collection->getModelObject();
	}
	
	// IteratorAggregate
	
	public function getIterator()
	{
		return $this->collection->getIterator();
	}
	
	// Countable
	
	/**
	 * @return	integer
	 */
	public function count()
	{
		if ($this->collection instanceof \Countable)
		{
			return $this->collection->count();
		}
		throw new NotImplementedException();
	}
	
	// FilteringCollection
	
	/**
	 * @return	WrapperCollection
	 */
	public function criteria(Criteria $c)
	{
		if ($this->collection instanceof FilteringCollection)
		{
			return new WrapperCollection($this->collection->criteria($c));
		}
		throw new NotImplementedException();
	}
	
	// ReadCollection
	
	public function offsetGet($key)
	{
		if ($this->collection instanceof ReadCollection)
		{
			return $this->collection->offsetGet($key);
		}
		throw new NotImplementedException();
	}

	public function offsetExists($key)
	{
		if ($this->collection instanceof ReadCollection)
		{
			return $this->collection->offsetExists($key);
		}
		throw new NotImplementedException();
	}
	
	// ReadWriteCollection
	
	public function offsetSet($key, $value)
	{
		if ($this->collection instanceof ReadCollection)
		{
			$this->collection->offsetSet($key, $value);
		}
		throw new NotImplementedException();
	}

	public function offsetUnset($key)
	{
		if ($this->collection instanceof ReadCollection)
		{
			$this->collection->offsetUnset($key);
		}
		throw new NotImplementedException();
	}

}
