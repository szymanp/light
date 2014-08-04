<?php

namespace Light\Exception;

class ErrorCode
{
	protected $code;
	
	public function __construct($code)
	{
		$this->code = $code;
	}
	
	public function getCode()
	{
		return $this->code;
	}
}