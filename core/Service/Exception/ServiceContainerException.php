<?php
namespace Light\Service\Exception;

use Light\Exception\Exception;

class ServiceContainerException extends Exception 
{
	const CLIENT_FORBIDDEN				= 403;
	const CLIENT_UNSUPPORTED_MEDIA_TYPE	= 415;
	
	private $httpErrorCode;
	
	public function __construct($message, $httpErrorCode)
	{
		parent::__construct($message);
		
		$this->httpErrorCode = $httpErrorCode;
	}
	
	/**
	 * Returns the HTTP status code for this exception.
	 * @return integer
	 */
	public function getHttpErrorCode()
	{
		return $this->httpErrorCode;
	}
}
