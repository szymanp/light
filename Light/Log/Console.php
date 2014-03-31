<?php
/**
 * An appender that outputs messages to the console.
 *
 * @package log
 * @author  Piotr Szymański <szyman@magres.net>
 * @license http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */

namespace Light\Log;

class Console implements Appender {
    
    public $stream      = STDOUT;
    
    private $colored    = false;
    
    public $lineFormat  = '%2$-15.15s [%8$-8.8s] %1$s %3$-4s %4$s';
    
    public $timeFormat  = '%H:%M:%S';
    
    public $colors      = array(
        Logger::EMERG   => "%k%1",
        Logger::ALERT   => "%R",
        Logger::CRIT    => "%R",
        Logger::ERR     => "%r",
        Logger::WARN    => "%p",
        Logger::NOTICE  => "%C",
        Logger::INFO    => "%c",
        Logger::DEBUG   => "%k",
        Logger::DEBUG_HI=> "%k"
    );
    
    public function __construct() {
    }
    
    public function setColor( $colored = true ) {
        if (!file_exists( "Console/Color.php"))
            $colored = false;
            
        $this->colored = $colored;
        
        if ($this->colored) {
            include_once( "Console/Color.php" );
        }
    }
    
    public function open() {
    }
    
    public function close() {
    }
    
    public function flush() {
    }
    
    public function handleEvent( LogEvent $e ) {
        
        $line = $this->formatLine($e);
        
        if ($this->colored) {
            $lvl = $e->getLevel();
            if (!isset( $this->colors[$lvl] )) $lvl = -1;
            if (isset( $this->colors[$lvl] )) {
                $color = $this->colors[$lvl];
                $line = Console_Color::convert( $color . $line . "%n" );
            }
        }
        
        fwrite( $this->stream, $line . PHP_EOL );
        
        return true;
    }
    
    private function formatLine( LogEvent $e ) {
        
        $timestamp = strftime($this->timeFormat);
        
        return sprintf($this->lineFormat,
                       $timestamp,
                       $e->getLogger()->getName(),
                       strtoupper($e->getLevelAsShortString()),
                       $e->getMessage(),
                       $e->getFile(),
                       $e->getLine(),
                       $e->getClass(),
                       $e->getFunction());
    }
    
}
