<?php 

namespace Light\Exception;
use Light\Util;

/**
 * An exception that indicates an error on which the user can act upon.
 *
 */
class UserException extends Exception
{
	private $field;
	
	public function __construct($message, array $args = array(), $field = null)
	{
		$this->args = $args;
		$this->message = $message;
		$this->field = $field;
		
		$userMessage = Util\StringArgumentFormatter::format($message,$this->args);
        
        parent::__construct($userMessage);
	}
	
	/**
	 * Returns the name of the field that this exception pertains to.
	 * @return string
	 */
	public function getField()
	{
		return $this->field;
	}
}