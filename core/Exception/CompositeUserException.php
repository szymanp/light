<?php 

namespace Light\Exception;

/**
 * An exception that indicates an error on which the user can act upon.
 *
 */
class CompositeUserException extends UserException
{
	/**
	 * @var array
	 */
	private $exceptions = array();
	
	public function __construct()
	{
		parent::__construct("See individual exceptions for details");		
	}
	
	/**
	 * Adds a new UserException.
	 * @param UserException $exception
	 * @return Light\Exception\CompositeUserException	For fluent API.
	 */
	public function addException(UserException $exception)
	{
		$this->exceptions[] = $exception;
		return $this;
	}
	
	public function getExceptions()
	{
		return $this->exceptions;
	}
}