<?php
namespace Light\Service\Exception;

class AuthenticationException extends ServiceContainerException
{
	/** @var boolean */
	private $retry = false;
	
	public function __construct($message = "Request could not be authenticated", $retry = false)
	{
		parent::__construct($message, ServiceContainerException::CLIENT_FORBIDDEN);
		$this->retry = $retry;
	}
	
	/**
	 * Returns true if the client should retry the authentication attempt.
	 * @return boolean
	 */
	public function allowRetry()
	{
		return $this->retry;
	}

}
