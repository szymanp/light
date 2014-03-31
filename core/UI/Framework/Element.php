<?php

namespace Light\UI\Framework;

use Light\UI\Util\ResourceFinder;

abstract class Element {

	/** @var Light\UI\Util\ResourceFinder */
	private $resourceFinder;
	
	public function __construct()
	{
		$this->construct();
	}
	
	/**
	 * Constructor logic to be implemented in base classes.
	 */
	protected function construct()
	{
	}
	
	/**
	 * Sets a ResourceFinder for this element. 
	 * @param ResourceFinder $t
	 */
	public function setResourceFinder(ResourceFinder $t)
	{
		$this->resourceFinder	= $t;
	}

	/**
	 * Returns a ResourceFinder to be used with this Framework Element.
	 * @return Light\UI\Util\ResourceFinder
	 */	
	public function getResourceFinder()
	{
		return $this->resourceFinder;
	}
	
}