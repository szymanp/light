<?php

namespace Light\Data;

// what about commits?

interface ReadWriteCollection extends ReadCollection
{
	/**
	 * Adds an object to this collection at the specified offset.
	 * If the <c>$key</c> is null, then the value will be added at an arbitrary place in the collection.
	 * @param	mixed	$key
	 * @param	mixed	$value
	 */
//	public function offsetSet($key, $value);
	
	/**
	 * Removes an element from the collection.
	 * @param	mixed	$key
	 */
//	public function offsetUnset($key);
	
	/**
	 * Adds an element to this collection at an arbitrary position. 
	 * @param 	mixed	$value
	 * @return	boolean	<c>True</c> if the element was added successfully; otherwise, <c>false</c>.
	 */
	public function add($value);
	
	/**
	 * Removes the first matching element from this collection. 
	 * @param 	mixed	$value
	 * @return	boolean	<c>True</c> if the element was removed successfully; otherwise, <c>false</c>.
	 */
	public function remove($value);
}
