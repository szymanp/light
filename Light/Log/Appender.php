<?php
/**
 * An interface for Appender classes.
 *
 * @package	log
 * @author	Piotr Szymañski <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */

namespace Light\Log;

interface Appender extends Target {

    /**
     *
     */
    function open();
    
    /**
     *
     */
    function flush();
    
    /**
     *
     */
    function close();

}

