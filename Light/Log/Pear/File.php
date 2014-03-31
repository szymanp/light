<?php
/**
 * An interface to the PEAR Logging subsystem's File class.
 *
 * @package	pear
 * @author	Piotr Szyma�ski <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */

namespace Light\Log\Pear;

use Light\Log\Logger;
use Light\Log\LogEvent;
use Light\Log\Appender;

require_once 'Log.php';
require_once 'Log/file.php';

class File extends \Log_file implements Appender {

	/**
	 * @var Log_LogEvent
	 */
	private $lastEvent;

	/**
	 * String containing the format of a log line.
	 * @var string
	 * @access private
	 */
	var $_lineFormat = '%1$s %2$s [%3$s] %4$s';

	/**
	 * Logs a message.
	 * @param Log_LogEvent $e
	 * @return boolean TRUE if the message was successfully logged.
	 */
	public function handleEvent( LogEvent $e ) {
		$this->lastEvent = $e;
		// PEAR does not understand DEBUG_HI
		if (($lvl = $e->getLevel()) == Logger::DEBUG_HI) $lvl = Logger::DEBUG;

		$this->log( $e->getMessage(), $lvl );
	}

	function _format($format, $timestamp, $priority, $message)
	{
		/*
		 * If the format string references any of the backtrace-driven
		 * variables (%5, %6, %7), generate the backtrace and fetch them.
		 */
		if (strpos($format, '%5') || strpos($format, '%6') || strpos($format, '%7')) {
			$file = $this->lastEvent->getFile();
			$line = $this->lastEvent->getLine();
			$func = $this->lastEvent->getFunction();
			if (!is_null( $cls = $this->lastEvent->getClass() )) {
				$func = $cls . "::" . $func;
			}

		}

		/*
		 * Build the formatted string.  We use the sprintf() function's
		 * "argument swapping" capability to dynamically select and position
		 * the variables which will ultimately appear in the log string.
		 */
		return sprintf($format,
		$timestamp,
		$this->lastEvent->getLogger()->getName(),
		$this->priorityToString($priority),
                       $message,
                       isset($file) ? $file : '',
                       isset($line) ? $line : '',
                       isset($func) ? $func : '');
    }
    
}