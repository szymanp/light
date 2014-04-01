<?php
/**
 * Filter for logging messages.
 *
 * @package	log
 * @author	Piotr Szyma�ski <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */

abstract class Log_Filter implements Log_Target {
    
    /**
     * @var Log_Target
     */
    protected $accepted;
    
    /**
     * @var Log_Target
     */
    protected $rejected;

    /**
     * Process a message.
     * @param Log_LogEvent $e
     * @return boolean
     */
    final public function handleEvent( Log_LogEvent $e ) {
        
        if ($this->test( $e ))
            if (!is_null( $this->accepted ))
                return $this->accepted->handleEvent($e);
            else
                return false;
        else
            if (!is_null( $this->rejected ))
                return $this->rejected->handleEvent($e);
            else
                return false;
        
    }
    
    /**
     * Test the message against the filter logic.
     *
     * @param Log_LogEvent $e
     * @return boolean TRUE if the message should be accepted,
     * FALSE if it should be rejected.
     */
    abstract protected function test( Log_LogEvent $e );
    
    /**
     * Sets the target for accepted messages.
     *
     * @param Log_Target $t
     */
    public function setAccepted( Log_Target $t ) {
        $this->accepted = $t;
    }

    /**
     * Sets the target for rejected messages.
     *
     * @param Log_Target $t
     */
    public function setRejected( Log_Target $t ) {
        $this->rejected = $t;
    }

}
