<?php
/**
 *
 *
 * @package	log
 * @author	Piotr Szyma�ski <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */

namespace Light\Log;

class Logger {
    
    const EMERG     = 0;     /** System is unusable */
    const ALERT     = 1;     /** Immediate action required */
    const CRIT 	    = 2;     /** Critical conditions */
    const ERR       = 3;     /** Error conditions */
    const WARN      = 4;     /** Warning conditions */
    const NOTICE    = 5;     /** Normal but significant */
    const INFO      = 6;     /** Informational */
    const DEBUG     = 7;     /** Debug-level messages */
    const DEBUG_HI  = 8;     /** Hi-verbosity debug messages */

    /**
     * Hierarchical structure of loggers.
     * @var Util_NameHierarchy
     */
    private static $loggers;

    /**
     * Returns a Logger for the given namespace.
     *
     * @param string $name
     * @return Light\Log\Logger
     */
    public static function getLogger( $name = "" ) {

        if (is_null( self::$loggers )) {
            self::$loggers = new \Util_NameHierarchy();
            self::$loggers->put( "", new self("") );
        }
        
        $logger = self::$loggers->get($name);
        if (is_null( $logger )) {
            self::$loggers->put( $name, $logger = new self( $name ) );
            
            foreach( self::$loggers->getDescendants($name) as $d ) {
                $d->ancestor = $logger;
            }
            
        }
        return $logger; 
    }
    
    /**
     * Destroys the whole active Logger hierarchy.
     */
    public static function destroyAll() {
        self::$loggers = NULL; 
    }
	
	public static function enableConsole() {
		$con = new Console();
		$con->setColor();
		self::getLogger()->setTarget($con);
	}
    
    /**
     * The ancestor from which properties are inherited.
     *
     * @var Logger
     */
    protected $ancestor;

    /**
     * @var Target
     */
    protected $target;
    
    /**
     * @var string
     */
    protected $name;
    
    protected function __construct( $name = "" ) {
        $this->name = $name;
        if ($name !== "") {
            $this->ancestor = self::$loggers->getAncestor($name);
        }
    }
    
    /**
     * Returns the name of this logger.
     * @return string
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Returns the ancestor Logger after which this one inherits properties.
     *
     * @return Logger
     */
    public function getAncestor() {
        return $this->ancestor;
    }

    /**
     * Sets a target for this Logger.
     *
     * @param Target $t
     * @return Logger	For fluent API.
     */
    public function setTarget( Target $t ) {
        $this->target = $t;
        return $this;
    }

    /**
     * @return Target
     */
    public function getTarget() {
        if (is_null( $this->target ) && !is_null( $this->ancestor ))
            return $this->ancestor->getTarget();
        return $this->target;
    }

    /**
     * Logs a message with the given priority.
     * @param mixed	$message
     * @param integer	$priority du_Logger::EMERG | du_Logger::CRIT | ...
	 * @param array	$args	Arguments to the message
     */
    public function log($message, $priority = self::INFO, $args = array()) {
        if (is_null( $target = $this->getTarget() )) return false;
        
        $le = new LogEvent($this,$message,$priority,$args);
        return $target->handleEvent($le);
    }

    /**
     * A convenience public function for logging a emergency event.  It will log a
     * message at Logger::EMERG level.
     * @param mixed	$message
     * @return boolean TRUE if the event was logged successfully, FALSE otherwise.
     */
    public function emerg($message) {
		$args = func_get_args();
		array_shift($args);
        return $this->log($message,self::EMERG,$args);
    }

    /**
     * A convenience public function for logging an alert event.  It will log a
     * message at Logger::ALERT level.
     * @param mixed	$message
     * @return boolean TRUE if the event was logged successfully, FALSE otherwise.
     */
    public function alert($message) {
        return $this->log($message,self::ALERT);
    }

    /**
     * A convenience public function for logging a critical event.  It will log a
     * message at Logger::CRIT level.
     * @param mixed	$message
     * @return boolean TRUE if the event was logged successfully, FALSE otherwise.
     */
    public function crit($message) {
        return $this->log($message,self::CRIT);
    }

    /**
     * A convenience public function for logging a error event.  It will log a
     * message at Logger::ERR level.
     * @param mixed	$message
     * @return boolean TRUE if the event was logged successfully, FALSE otherwise.
     */
    public function err($message) {
		$args = func_get_args();
		array_shift($args);
        return $this->log($message,self::ERR,$args);
    }

    /**
     * A convenience public function for logging a warning event.  It will log a
     * message at Logger::WARN level.
     * @param mixed	$message
     * @return boolean TRUE if the event was logged successfully, FALSE otherwise.
     */
    public function warn($message) {
		$args = func_get_args();
		array_shift($args);
        return $this->log($message,self::WARN,$args);
    }

    /**
     * A convenience public function for logging a notice event.  It will log a
     * message at Logger::NOTICE level.
     * @param mixed	$message
     * @return boolean TRUE if the event was logged successfully, FALSE otherwise.
     */
    public function notice($message) {
        return $this->log($message,self::NOTICE);
    }

    /**
     * A convenience public function for logging a information event.  It will log a
     * message at Logger::INFO level.
     * @param mixed	$message
     * @return boolean TRUE if the event was logged successfully, FALSE otherwise.
     */
    public function info($message) {
		$args = func_get_args();
		array_shift($args);
        return $this->log($message,self::INFO,$args);
    }

    /**
     * A convenience public function for logging a debug event.  It will log a
     * message at Logger::DEBUG level.
     * @param mixed	$message
     * @return boolean TRUE if the event was logged successfully, FALSE otherwise.
     */
    public function debug($message) {
		$args = func_get_args();
		array_shift($args);
    	return $this->log($message,self::DEBUG,$args);
    }

    /**
     * A convenience public function for logging a hi-debug event.  It will log a
     * message at Logger::DEBUG_HI level.
     * @param mixed	$message
     * @return boolean TRUE if the event was logged successfully, FALSE otherwise.
     */
    public function debughi($message) {
        return $this->log($message,self::DEBUG_HI);
    }

}
