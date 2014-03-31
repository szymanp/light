<?php

namespace Light\UI\View;

/**
 * An exception thrown to redirect to another component or URL.
 *
 */
class RedirectException extends \Exception
{
	private $target;
	
	public function __construct($target)
	{
		parent::__construct("");
		$this->target = $target;	
	}
	
	public function getTarget()
	{
		return $this->target;
	}
}