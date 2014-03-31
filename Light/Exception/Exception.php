<?php

namespace Light\Exception;
use Light\Util;

/**
 * A generic exception.
 *
 * @author	Piotr Szymański <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */
class Exception extends \Exception {

	protected $args;
	protected $message;

    public function __construct( $message )
	{
		if (is_string($message))
		{
			$this->args = func_get_args();
			array_shift($this->args);
			$this->message = $message;
		}
		else if (is_array($message))
		{
			$this->args = $message;
			$this->message = $message = array_shift($this->args);
		}
		
		$userMessage = Util\StringArgumentFormatter::format($message,$this->args);
        
        parent::__construct($userMessage);
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
