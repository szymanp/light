<?php

namespace Light\Data;

interface FilteringCollection extends Collection
{
	/**
	 * Returns a new Collection that uses the given Criteria.
	 * @param	Criteria	$criteria
	 * @return	FilteringCollection
	 */
	public function criteria(Criteria $criteria);
}
