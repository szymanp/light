<?php
/**
 * An interface do the PEAR Logging subsystem.
 *
 * @package	pear
 * @author	Piotr Szyma�ski <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */

require_once 'Log.php';
require_once 'Log/composite.php';

pkg::import( "dcx.util.log.Appender" );

class Log_Pear_Composite extends Log_composite implements Log_Appender {

    public function __construct() {
        parent::__construct( "" );
    }

    /**
     * Logs a message.
     * @param Log_LogEvent $e
     * @return boolean TRUE if the message was successfully logged.
     */
    public function handleEvent( Log_LogEvent $e ) {
        $this->log( $e->getObject(), $e->getLevel() );
    }

}