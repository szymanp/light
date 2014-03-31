<?php
/**
 * A class representing a logged event.
 *
 * @package     log
 * @author      Piotr Szyma�ski <szyman@magres.net>
 * @license     http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */

namespace Light\Log;
use Light\Util;

class LogEvent {
    
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var mixed
     */
    private $message;
	
	/**
	 * @var array
	 */
	private $args;
    
    /**
     * @var integer
     */
    private $level;
    
    /**
     * @var integer
     */
    private $line;
    
    /**
     * @var string
     */
    private $file;
    
    /**
     * @var string
     */
    private $function;
    
    /**
     * @var string
     */
    private $class;
    
    /**
     * @var boolean
     */
    public static $useBacktrace = true;
    
    public function __construct( Logger $l, $message, $level, array $args = array() ) {

        $this->logger	= $l;
        $this->message	= $message;
        $this->level	= $level;
		$this->args		= $args;
        
        if (self::$useBacktrace) {
            // save backtrace information
            $backtrace = debug_backtrace();
            $depth = 2;
            if ($backtrace[$depth]['class'] == "Light\Log\Logger") $depth++;
            
            $this->line    = @$backtrace[$depth]['line'];
            $this->file    = @$backtrace[$depth]['file'];
            $this->function= @$backtrace[$depth]['function'];
            $this->class   = @$backtrace[$depth]['class'];
        }
    }
    
    /**
     * Returns the contents of the log message.
     * @return string
     */
    public function getMessage()
    {
        return Util\StringArgumentFormatter::format($this->message,$this->args);
    }
	
    /**
     * Returns the object passed for logging.
     * The object will usually be a string, but occassionally it can be some actual object. 
     * @return mixed
     */
    public function getObject() {
        return $this->message;
    }

    /**
     * Returns the severity level.
     * @return integer
     */
    public function getLevel() {
        return $this->level;
    }
    
    public function getLine() {
        return $this->line;
    }
    
    /**
     * Returns the file name.
     * @return string If information is unavailable, method returns NULL.
     */
    public function getFile() {
        return $this->file;
    }
    
    /**
     * Returns the function or method name.
     * @return string If information is unavailable, method returns NULL.
     */
    public function getFunction() {
        return $this->function;
    }
    
    /**
     * Returns the class name.
     * @return string If information is unavailable, method returns NULL.
     */
    public function getClass() {
        return $this->class;
    }
    
    /**
     * Returns the Logger that generated this message.
     * @return Logger
     */
    public function getLogger() {
        return $this->logger;
    }

    /**
     * Returns the severity level as a text message.
     *
     * @return string For example: <kbd>emergency</kbd>.
     */
    public function getLevelAsString() {
        switch ($this->level) {
                case Logger::ALERT:         return "alert";
                case Logger::CRIT:          return "critical";
                case Logger::DEBUG:         return "debug";
                case Logger::DEBUG_HI:      return "debug_hi";
                case Logger::EMERG:         return "emergency";
                case Logger::ERR:           return "error";
                case Logger::INFO:          return "info";
                case Logger::NOTICE:        return "notice";
                case Logger::WARN:          return "warning";
                default:                    return "unknown";
        }
    }

    /**
     * Returns the severity level as a short text message.
     *
     * @return string For example: <kbd>emerg</kbd>.
     */
    public function getLevelAsShortString() {
        switch ($this->level) {
                case Logger::ALERT:         return "alrt";
                case Logger::CRIT:          return "crit";
                case Logger::DEBUG:         return "debg";
                case Logger::DEBUG_HI:      return "debh";
                case Logger::EMERG:         return "emrg";
                case Logger::ERR:           return "erro";
                case Logger::INFO:          return "info";
                case Logger::NOTICE:        return "noti";
                case Logger::WARN:          return "warn";
                default:                    return "unkn";
        }
    }

}

