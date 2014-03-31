<?php

namespace Light\Exception;

/**
 * A method returned an invalid value.
 *
 * @package	exception
 * @author	Piotr Szymański <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */
class InvalidReturnValue extends \LogicException {

	/**
	 * @param string	$classOrObject	Class or object involved.
	 * @param string	$method			Method that was called.
	 * @param mixed		$actual			Actual value returned.
	 * @param string	$reason			Explanation why the value is invalid.
	 */
    public function __construct($classOrObject, $method, & $actual, $reason = NULL ) {

        if (is_object( $actual ))
            $actual = "<" . get_class( $actual ) . ">";
        else
            $actual = (string) $actual;
        
		if (is_object($classOrObject)) {
			$callee = get_class($classOrObject) . "::" . $method . "()";
		} else {
			$callee = $classOrObject . "::" . $method . "()";
		}
		
        parent::__construct($callee . " returned an invalid value: `$actual`." .
            (is_null($reason)?"":" " . $reason) );
    
    }

}
