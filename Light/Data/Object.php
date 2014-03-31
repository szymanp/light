<?php

namespace Light\Data;

interface Object extends Entity {

	/**
	 * Gets a value for a property.
	 * @param	string	$dotName
	 * @return	mixed
	 */
	public function getValue($dotName);
	
	/**
	 * Sets a value for a property.
	 * @param	string	$dotName
	 * @param	mixed	$value
	 */
	public function setValue($dotName,$value);
	
	/**
	 * Checks if the specified property exists.
	 * @param 	string	$dotName
	 * @return	boolean
	 */
	public function hasProperty($dotName);
	
	/**
	 * Returns a set of primary keys.
	 * @return	string|integer|array
	 */
	public function getIdentifier($asArray=false);
	
}
