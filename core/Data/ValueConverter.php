<?php

namespace Light\Data;

/**
 * A <c>ValueConverter</c> converts data from one format into another, and back.
 */
interface ValueConverter
{
	/**
	 * Converts a value from the native format to the intermediate one.
	 */
	public function convertFrom($value);
	
	/**
	 * Converts a value from the intermediate format to the native one.
	 */
	public function convertTo($value);
}
