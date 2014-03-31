<?php
/**
 * An interface for Appender classes.
 *
 * @package	log
 * @author	Piotr Szymański <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */

namespace Light\Log;

interface Target {

    /**
     * Process a message.
     * @param LogEvent $e
     * @return boolean TRUE if the message was successfully processed.
     */
    function handleEvent( LogEvent $e );

}
