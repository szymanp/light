<?php
/**
 * An empty target for logging messages.
 *
 * @package	log
 * @author	Piotr Szymański <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */

class Log_EmptyTarget implements Log_Target {

	function handleEvent( Log_LogEvent $e ) {
		return true;
	}

}
