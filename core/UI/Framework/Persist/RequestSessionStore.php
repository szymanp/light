<?php

namespace Light\UI\Framework\Persist;

interface RequestSessionStore extends RequestStore
{
	/**
	 * Appends URI parameters to an array.
	 * @param array		$args	A key-value pair set of arguments.
	 */
	public function appendArguments(array &$args);	
}