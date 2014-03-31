<?php
namespace Light\Service\IO;
use Light\Service\Exception\ServiceContainerException;

use Light\Service\Exception;

class JsonInputHandler extends InputHandler
{
	/**
	 * (non-PHPdoc)
	 * @see Light\Service\IO.InputHandler::getMethodName()
	 */
	public function getMethodName()
	{
 		throw new ServiceContainerException("Method name must be passed as part of the URL", 404);
	}
	
	/**
	 * Returns an associative array of decoded parameters for the method call.
	 * @return array
	 */
	public function getMethodParameters()
	{
		$postdata = $this->getHttpRequest()->getBody(true);
		$decoded = json_decode($postdata, true);
		return $decoded;
	}
	
	public function getServiceParameters()
	{
		// TODO
		return array();
	}
}
