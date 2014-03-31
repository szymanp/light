<?php

namespace Light\UI\Framework\Persist;

interface UriStore extends RequestStore, ClientStore
{
	/**
	 * Appends FORM parameters to an array.
	 * @param string	$class	Recipient class for the serialized state information.
	 * 							Used for resolving state range.
	 * @param array		$args	A key-value pair set of arguments.
	 */
	public function appendArguments($class, array &$args);
}