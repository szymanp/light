<?php

namespace Light\Util;

/**
 * An interface for classes providing storage of additional (possibly calculated) fields.
 */
interface SupplementaryFields
{
	/**
	 * Returns the value of the supplementary field.
	 * @param string	$fieldName
	 * @return mixed	Returns the value of the supplementary field, if it exists;
	 * 					otherwise, NULL.
	 */
	public function getSupplementaryValue($fieldName);
	
	/**
	 * Checks if the supplementary field is defined on this object.
	 * @param string	$fieldName
	 * @return boolean
	 */
	public function hasSupplementaryValue($fieldName);
	
	/**
	 * Sets a value for the supplementary field.
	 * @param string	$fieldName
	 * @param mixed		$value		Either an explicit value or a Closure object for calculating this value.
	 * @return \Light\Util\SupplementaryFields	For fluent API.
	 */
	public function setSupplementaryValue($fieldName, $value);
}