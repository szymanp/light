<?php

namespace Light\Exception;
use Light\Util;

/**
 * A generic exception.
 *
 */
class Exception extends \Exception {

	protected $args		= array();
	protected $message;

	/**
	 * Constructs a new Exception.
	 * @param mixed 	$message
	 */
    public function __construct($message)
	{
		$errorCode		 	= null;
		$previousException 	= null;
		$userMessage		= null;
		
		$arguments = func_get_args();
		
		if (count($arguments) > 0)
		{
			if (is_string($arguments[0]))
			{
				$this->message	= array_shift($arguments);
			}
			else if (is_array($arguments[0]))
			{
				$this->args 	= array_shift($arguments);
				$this->message	= array_shift($this->args);
			}
			else if (is_object($arguments[0]) && $arguments[0] instanceof \Exception)
			{
				$previousException = $arguments[0];
				$userMessage	= $previousException->getMessage();
			}
			
			foreach($arguments as $arg)
			{
				if (is_object($arg) && $arg instanceof \Exception)
				{
					$previousException = $arg;
				}
				else if (is_object($arg) && $arg instanceof ErrorCode)
				{
					$errorCode = $arg->getCode();
				}
				else
				{
					$this->args[] = $arg;
				}
			}
		}
		
		if (is_null($userMessage))
		{
			$userMessage = Util\StringArgumentFormatter::format($this->message,$this->args);
		}
        
        parent::__construct($userMessage, $errorCode, $previousException);
    }
    
    public function getRawMessage()
    {
    	return $this->message;
    }
    
    public function getMessageArguments()
    {
    	return $this->args;
    }

}
