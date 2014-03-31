<?php
namespace Light\Service\IO;

/**
 * Base class for input handlers.
 */
abstract class InputHandler extends Handler
{
	/**
	 * Returns the name of the method that should be called.
	 * @return string
	 */
	abstract public function getMethodName();
	
	/**
	 * Returns an associative array of decoded parameters for the method call.
	 * @return array
	 */
	abstract public function getMethodParameters();
	
	/**
	 * Returns an associative array of decoded parameters for the service invocation.
	 * @return array
	 */
	abstract public function getServiceParameters();
}
