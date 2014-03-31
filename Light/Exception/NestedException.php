<?php

namespace Light\Exception;
use Light\Util;

/**
 * A nested exception.
 */
class NestedException extends \Exception {

	protected $args;
	protected $message;
	protected $nested;

    public function __construct(\Exception $e, $message)
	{
		$this->args = func_get_args();
		array_shift($this->args);
		array_shift($this->args);
		$this->message = $message;
		$this->nested = $e;
		
		$userMessage = Util\StringArgumentFormatter::format($message,$this->args);
		if (!empty($userMessage)) $userMessage .= ": ";
		$userMessage .= $e->getMessage() . "\nStack trace:\n" . $e->getTraceAsString();

        parent::__construct($userMessage);
    }
	
	/**
	 * @return \Exception
	 */
	public function getNestedException()
	{
		return $this->nested;
	}

}
