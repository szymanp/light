<?php

namespace Light\Exception;

/**
 * A parameter of an invalid value was passed to a function or method.
 *
 * @package	exception
 * @author	Piotr Szymański <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */
class InvalidParameterValue extends \InvalidArgumentException {

	/**
	 * @param string	$name	Parameter name.
	 * @param mixed		$actual	Actual value passed.
	 * @param string	$reason	Explanation why the value is invalid.
	 */
    public function __construct( $name, & $actual, $reason = NULL ) {

        if (is_object( $actual ))
            $actual = "<" . get_class( $actual ) . ">";
        else
            $actual = (string) $actual;
        
        parent::__construct( "Value for $name is invalid: `$actual`." .
            (is_null($reason)?"":" " . $reason) );
    
    }

}
