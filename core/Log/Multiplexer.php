<?php
/**
 * An appender that outputs messages to other appenders.
 *
 * @package log
 * @author  Piotr Szymański <szyman@magres.net>
 * @license http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */

namespace Light\Log;

class Multiplexer implements Appender {
    
    private $open = false;
    
    private $targets = array();
    
    public function add( Target $t ) {
        if ($this->open)
            throw new dcx_Exception( "Multiplexer is already open." );
        if (in_array( $t, $this->targets, true )) return;
        $this->targets[] = $t;
    }
    
    public function remove( Target $t ) {
        if ($this->open)
            throw new dcx_Exception( "Multiplexer is already open." );
        $k = array_search( $t, $this->targets, true );
        if ($k === false) return;
        unset( $this->targets[$k] );
    }
    
    public function open() {
        $this->open = true;
        foreach( $this->targets as $target )
            if ($target instanceof Appender) $target->open();
    }
    
    public function close() {
        $this->open = false;
        foreach( $this->targets as $target )
            if ($target instanceof Appender) $target->close();
    }
    
    public function flush() {
        foreach( $this->targets as $target )
            if ($target instanceof Appender) $target->flush();
    }
    
    function handleEvent( LogEvent $e ) {
        foreach( $this->targets as $target ) $target->handleEvent($e);
        return true;
    }
    
}

