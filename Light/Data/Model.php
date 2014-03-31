<?php

namespace Light\Data;

interface Model
{
	/**
	 * Loads the model object into memory.
	 */
	public function load();
	
	/**
	 * Returns the model object.
	 * @return mixed
	 */
	public function getModelObject();
}
