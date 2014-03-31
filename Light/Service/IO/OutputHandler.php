<?php
namespace Light\Service\IO;

/**
 * Base class for output handlers.
 */
abstract class OutputHandler extends Handler
{
	/**
	 * Sends a reply containing the method return value.
	 * @param mixed	$resp
	 */
	abstract public function sendResponse($resp);
	
	/**
	 * Sends a reply informing about a fault that occurred during execution.
	 * @param Exception	$e
	 */
	abstract public function sendFault(\Exception $e);
}
