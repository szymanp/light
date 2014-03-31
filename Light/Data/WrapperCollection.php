<?php

namespace Light\Data;
use Light\Exception\NotImplementedException;
use Light\Exception\InvalidParameterType;

/**
 * A collection that can adapt a <c>Traversable</c> or a PHP array.
 */
class WrapperCollection implements CompositeCollection
{
	private $func_load;
	private $func_getIterator;
	private $func_count;
	private $func_get;
	private $func_exists;
	private $func_set;
	private $func_unset;
	private $func_add;
	private $func_remove;
	private $func_filter;

	private $removedCapabilities = 0;

	/** Context passed on to pluggable methods */
	protected $context;
	
	/** @var array|Traversable */
	protected $collection;
	
	/** @var array */
	private $collectionCopy;

	/**
	 * @param	mixed	$coll	Collection
	 */
	public function __construct($coll = NULL)
	{
		if (!is_null($coll))
		{
			$this->setCollection($coll);
		}
	}

	/**
	 * @param	Traversable	$coll
	 * @return	WrapperCollection	For fluent API.
	 */
	public function setCollection($coll)
	{
		if (!is_array($coll) && !($coll instanceof \Traversable))
		{
			throw new InvalidParameterType('$coll',$coll,"Traversable");
		}

		$this->collection = $coll;
		return $this;
	}

	public function hasCapability($capability)
	{
		if (($this->removedCapabilities & $capability) != 0)
		{
			return false;
		}
	
		switch($capability)
		{
		case CompositeCollection::CAP_COUNT:
			$result = (!empty($this->func_count))
				|| ($this->collection instanceof CompositeCollection
					? $this->collection->hasCapability(self::CAP_COUNT)
					: $this->collection instanceof \Countable)
				|| is_array($this->collection);
			break;
				
		case CompositeCollection::CAP_READ:
			$result = (!empty($this->func_get) && !empty($this->func_exists))
				|| ($this->collection instanceof CompositeCollection
					? $this->collection->hasCapability(self::CAP_READ)
					: ($this->collection instanceof \ArrayAccess
					   || $this->collection instanceof ReadCollection))
				|| is_array($this->collection);
			break;

		case CompositeCollection::CAP_WRITE:
			$result = (!empty($this->func_set) && !empty($this->func_unset) && !empty($this->func_get))
				|| ($this->collection instanceof CompositeCollection
					? $this->collection->hasCapability(self::CAP_WRITE)
					: ($this->collection instanceof \ArrayAccess
					   || $this->collection instanceof ReadWriteCollection))
				|| is_array($this->collection);
			break;

		case CompositeCollection::CAP_FILTER:
			$result = (!empty($this->func_filter))
				|| ($this->collection instanceof CompositeCollection
					? $this->collection->hasCapability(self::CAP_FILTER)
					: $this->collection instanceof FilteringCollection);
			break;

		default:
			throw new \Exception("Unknown capability $capability");
		}
		return $result;
	}

	/**
	 * Indicate that the collection does not have this capability.
	 * @param	integer	$capability
	 * @return	WrapperCollection	For fluent API.
	 */	
	public function removeCapability($capability)
	{
		$this->removedCapabilities |= $capability;
		return $this;
	}

	/**
	 * Sets the context that will be passed to the pluggable methods.
	 * @param	mixed	$context	Context that will be passed to the pluggable methods
	 * @return	WrapperCollection	For fluent API.
	 */
	public function setContext($context = NULL)
	{
		$this->context = $context;
		return $this;
	}
	
	// pluggable methods
	
	public function setLoader($callback)
	{
		$this->func_load = $callback;
		return $this;
	}
	
	public function setGetIterator($callback)
	{
		$this->func_getIterator = $callback;
		return $this;
	}
	
	public function setSetter($callback)
	{
		$this->func_set = $callback;
		return $this;
	}

	public function setUnsetter($callback)
	{
		$this->func_unset = $callback;
		return $this;
	}

	public function setAdder($callback)
	{
		$this->func_add = $callback;
		return $this;
	}

	public function setRemover($callback)
	{
		$this->func_remove = $callback;
		return $this;
	}
	
	// Model
	
	public function load()
	{
		if (!empty($this->func_load))
		{
			call_user_func_array($this->func_load, array($this, $this->context));
		}
	}
	
	public function getModelObject()
	{
		return $this->getIterator();
	}
	
	// IteratorAggregate
	
	public function getIterator()
	{
		if (!empty($this->func_getIterator))
		{
			return call_user_func_array($this->func_getIterator, array($this, $this->context));
		}
		if (is_array($this->collection))
		{
			return new \ArrayIterator($this->collection);
		}
		if ($this->collection instanceof \IteratorAggregate)
		{
			return $this->collection->getIterator();
		}
		if ($this->collection instanceof \Iterator)
		{
			return $this->collection;
		}
		throw new NotImplementedException();
	}
	
	// Countable
	
	/**
	 * @return	integer
	 */
	public function count()
	{
		if (!empty($this->func_count))
		{
			return call_user_func_array($this->func_count, array($this, $this->context));
		}
		if ($this->collection instanceof CompositeCollection)
		{
			if ($this->collection->hasCapability(CompositeCollection::CAP_COUNT))
			{
				return $this->collection->count();
			}
			throw new NotImplementedException();
		}
		if ($this->collection instanceof \Countable)
		{
			return $this->collection->count();
		}
		if (is_array($this->collection))
		{
			return count($this->collection);
		}
		throw new NotImplementedException();
	}
	
	// FilteringCollection
	
	/**
	 * @return	WrapperCollection
	 */
	public function criteria(Criteria $c)
	{
		throw new NotImplementedException();
	}
	
	// ReadCollection
	
	public function offsetGet($key)
	{
		if (!empty($this->func_get))
		{
			return call_user_func_array($this->func_get, array($this, $this->context, $key));
		}
		if ($this->collection instanceof CompositeCollection)
		{
			if ($this->collection->hasCapability(CompositeCollection::CAP_READ))
			{
				return $this->collection->offsetGet($key);
			}
		}
		elseif ($this->collection instanceof \ArrayAccess
			|| is_array($this->collection))
		{
			return $this->collection[$key];
		}
		if ($this->collection instanceof \Traversable)
		{
			$this->loadCollectionCopy();
			return $this->collectionCopy[$key];
		}
		throw new NotImplementedException();
	}

	public function offsetExists($key)
	{
		if (!empty($this->func_exists))
		{
			return call_user_func_array($this->func_exists, array($this, $this->context, $key));
		}
		if ($this->collection instanceof CompositeCollection)
		{
			if ($this->collection->hasCapability(CompositeCollection::CAP_READ))
			{
				return $this->collection->offsetGet($key);
			}
		}
		elseif ($this->collection instanceof \ArrayAccess
			|| is_array($this->collection))
		{
			return isset($this->collection[$key]);
		}
		if ($this->collection instanceof \Traversable)
		{
			$this->loadCollectionCopy();
			return isset($this->collectionCopy[$key]);
		}
		throw new NotImplementedException();
	}
	
	// ReadWriteCollection
	
	public function offsetSet($key, $value)
	{
		if (!empty($this->func_set))
		{
			call_user_func_array($this->func_set, array($this, $this->context, $key, $value));
			return;
		}
		if (!empty($this->func_add) && is_null($key))
		{
			call_user_func_array($this->func_add, array($this, $this->context, $key, $value));
			return;
		}
		if ($this->collection instanceof CompositeCollection)
		{
			if ($this->collection->hasCapability(CompositeCollection::CAP_WRITE))
			{
				$this->collection->offsetSet($key, $value);
				return;
			}
		}
		elseif ($this->collection instanceof \ArrayAccess
			|| is_array($this->collection))
		{
			$this->collection[$key] = $value;
			return;
		}
		throw new NotImplementedException();
	}

	public function offsetUnset($key)
	{
		if (!empty($this->func_unset))
		{
			call_user_func_array($this->func_unset, array($this, $this->context, $key));
			return;
		}
		if ($this->collection instanceof CompositeCollection)
		{
			if ($this->collection->hasCapability(CompositeCollection::CAP_WRITE))
			{
				$this->collection->offsetUnset($key);
				return;
			}
		}
		elseif ($this->collection instanceof \ArrayAccess
			|| is_array($this->collection))
		{
			unset($this->collection[$key]);
			return;
		}
		throw new NotImplementedException();
	}
	
	public function add($value)
	{
		if (!empty($this->func_add))
		{
			return call_user_func_array($this->func_add, array($this, $this->context, $value));
		}
		if ($this->collection instanceof CompositeCollection)
		{
			if ($this->collection->hasCapability(CompositeCollection::CAP_WRITE))
			{
				return $this->collection->add($value);
			}
		}
		elseif ($this->collection instanceof \ArrayAccess
			|| is_array($this->collection))
		{
			$this->collection[] = $value;
			return true;
		}
		throw new NotImplementedException();
	}
	
	public function remove($value)
	{
		if (!empty($this->func_remove))
		{
			return call_user_func_array($this->func_remove, array($this, $this->context, $value));
		}
		if ($this->collection instanceof CompositeCollection)
		{
			if ($this->collection->hasCapability(CompositeCollection::CAP_WRITE))
			{
				return $this->collection->remove($value);
			}
		}
		elseif ($this->collection instanceof \ArrayAccess)
		{
			foreach($this->collection as $k => $v)
			{
				if ($v === $value)
				{
					unset($this->collection[$k]);
					return true;
				}
			}
			return false;
		}
		elseif (is_array($this->collection))
		{
			$k = array_search($value, $this->collection, true);
			if ($k === false)
			{
				return false;
			}
			unset($this->collection[$k]);
		}
		throw new NotImplementedException();
	}
	
	// private methods
	
	private function loadCollectionCopy()
	{
		if (!is_null($this->collectionCopy))
		{
			return;
		}
		
		$this->collectionCopy = iterator_to_array($this->collection, true);
	}

}
