<?php

namespace Light\UI\Framework\Persist;
use Light\UI;

interface Store
{
	const SESSION	= 1;
	const REQUEST	= 2;
	
	const URI		= 4;
	const FORM		= 8;
	const SERVER	= 16;
	
	/**
	 * Prepares the store for restoring data.
	 * This method is called once before any calls to load() or close().
	 */
	public function open();
	
	/**
	 * Notifies the store that no more data will be added.
	 * This method is called once after all calls to load() or save().
	 */
	public function close();
	
	public function save(UI\Component $c, Property $property, $value);
	
	/**
	 * 
	 * @param UI\Component 	$c
	 * @param Property 		$property
	 * @param mixed 		$value
	 * @return boolean	TRUE if a value exists, FALSE otherwise.
	 */
	public function load(UI\Component $c, Property $property, &$value);
}
