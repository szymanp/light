<?php
namespace Light\Service\IO;
use Light\Service\Exception;

class UrlInputHandler extends InputHandler
{
	/**
	 * Name of the urlencode variable in which the method name is stored.
	 * @param string
	 */
	protected $urlencMethod = "m";

	/**
	 * Returns the name of the method that should be called.
	 * @return string
	 */
	public function getMethodName()
	{
		if (!isset( $_REQUEST[$this->urlencMethod] ))
		{
			throw new Exception\InvalidMethodException(NULL);
		}
			
		return $_REQUEST[$this->urlencMethod];	  
	}
	
	/**
	 * Returns an associative array of decoded parameters for the method call.
	 * @return array
	 */
	public function getMethodParameters()
	{
		$params = array();

		foreach( $_REQUEST as $name => $value )
		{
			if ($name === $this->urlencMethod) continue;
			
			$params[$name] = $value;
		}

		return $params;
	}
	
	public function getServiceParameters()
	{
		// TODO
		return array();
	}

}
