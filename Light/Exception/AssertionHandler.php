<?php

namespace Light\Exception;
use Light\Util;
use Light\Log\Logger;

final class AssertionHandler
{
	private function __construct()
	{
	}
	
    public static function setup($checkAssertions = true)
	{
		assert_options(\ASSERT_ACTIVE, $checkAssertions);
		assert_options(\ASSERT_WARNING, false);
		if ($checkAssertions)
		{
			assert_options(\ASSERT_CALLBACK, array("Light\Exception\AssertionHandler", "failure"));
		}
    }
	
	public static function failure($script, $line, $message)
	{
		Logger::getLogger(__CLASS__)
			->emerg("Assertion failure: %1 in %2:%3",
					$message,
					$script,
					$line);
	
		throw new AssertionFailure($message, $script, $line);
	}
}

class AssertionFailure extends \Exception
{
	public function __construct($message, $script, $line)
	{
		parent::__construct($message);
		$this->file	= $script;
		$this->line	= $line;
	}
}
