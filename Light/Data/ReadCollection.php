<?php

namespace Light\Data;

interface ReadCollection extends Collection, \ArrayAccess
{
	/**
	 * Returns a single object from this collection.
	 * If the collection has not been loaded, just this single object is fetched.
	 * @param	mixed	$key
	 * @return	object	The object, if it exists; otherwise, NULL.
	 */
//	(Already defined as part of ArrayAccess)
//	public function offsetGet($key);
	
	/**
	 * Checks if the object identified by the key is part of this collection.
	 * @param	mixed	$key
	 * @return	boolean
	 */
//	(Already defined as part of ArrayAccess)
//	public function offsetExists($key);

//	NOTE: The other 2 ArrayAccess methods should, when this interface is implemented,
//	throw NotImplementedException()
	
}
