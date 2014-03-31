<?php

namespace Light\UI\Component\Table;
use Light\UI;

/**
 * A column is a type of a DataTemplate.
 * 
 * @author Piotrek
 *
 */
interface Column {

	public function attach(UI\Container $container);

	/**
	 * Returns a new component instance for this column.
	 * @return Light\UI\Component 
	 */
	public function getInstance($value, $key);
	
	public function getValue($value);
	
	/** @return string */
	public function getLabel();
	
	/** @return string */
	public function getHREF($value, $key);
	
	/** @return UI\Util\TagAttributes */
	public function getAttributes();
}