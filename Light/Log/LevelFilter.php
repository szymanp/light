<?php
/**
 * Filter for logging messages based on their priority level.
 *
 * @package	log
 * @author	Piotr Szymański <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */

class Log_LevelFilter extends Log_Filter {

    public $minLevel = Log_Logger::EMERG;
    public $maxLevel = Log_Logger::DEBUG_HI;
    
    protected function test( Log_LogEvent $e ) {
	$l = $e->getLevel();
	return ($l >= $this->minLevel) && ($l <= $this->maxLevel);
    }

}
