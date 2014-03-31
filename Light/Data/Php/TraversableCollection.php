<?php

namespace Light\Data\Php;
use Light\Data;

abstract class TraversableCollection implements Data\Collection {

	protected	$array;

	public function __construct(&$array) {
		if (!is_array($array)) {
			if (!(($array instanceof IteratorAggregate) &&
				  ($array instanceof ArrayAccess))) {
				throw new Exception("Wrong argument");
			}
		}
		$this->array	= &$array;
	}
	
	public function getIterator() {
		if (is_array( $this->array )) {
			return new \ArrayIterator( $this->array );
		} else {
			return $this->array->getIterator();
		}
	}
	
	/* ArrayAccess */
	
	public function offsetSet($k,$v) {
		if (is_array( $this->array )) {
			if (is_null($k))
			{
				$this->array[] = $v;
			}
			else
			{
				$this->array[$k] = $v;
			}
		} else {
			$this->array->offsetSet($k,$v);
		}
	}
	
	public function offsetUnset($k) {
		if (is_array( $this->array )) {
			unset( $this->array[$k] );
		} else {
			$this->array->offsetUnset($k);
		}
	}
	
	public function offsetExists($k) {
		if (is_array( $this->array )) {
			return isset( $this->array[$k] );
		} else {
			return $this->array->offsetExists($k);
		}
	}
	
	public function offsetGet($k) {
		if (is_array( $this->array )) {
			return $this->array[$k];
		} else {
			return $this->array->offsetGet($k);
		}
	}
	
	/* Countable */
	
	public function count() {
		if (is_array( $this->array )) {
			return count( $this->array );
		} else if ($this->array instanceof Countable) {
			return $this->array->count();
		} else {
			return -1;
		}
	}

	/**
	 * Checks if the Collection is capable of providing count of its elements.
	 * @return 	boolean
	 */
	public function isCountable() {
		return is_array( $this->array ) || ($this->array instanceof Countable);
	}
	
	/**
	 * Checks if the Collection is capable of limiting the number of elements natively.
	 * @return	boolean
	 */
	public function hasNativeLimiting() {
		return false;
	}
	
	/**
	 * Returns a new Collection that uses the given Criteria.
	 * @return	Data_Collection
	 */
	public function criteria(Data\Criteria $c) {
		throw new Exception("Unimplemented");
		$coll = clone $this;
		if ($coll->criteria == null) {
			$coll->criteria = $c;
		} else {
			$coll->criteria->append($c);
		}
		return $coll;
	}
}