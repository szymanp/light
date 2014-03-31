<?php

namespace Light\Data;

interface CompositeCollection extends ReadWriteCollection, FilteringCollection, \Countable
{
	const CAP_COUNT		= 1;
	const CAP_FILTER	= 2;
	const CAP_READ		= 4;
	const CAP_WRITE		= 8;	

	/**
	 * Checks whether this collection has the specified capability.
	 * @param	integer	$capability	One of CAP_* constants.
	 * @return	boolean
	 */
	public function hasCapability($capability);
}
