<?php

namespace Light\Exception;

/**
 * An parameter of invalid type was passed to a function or method.
 *
 * @package	exception
 * @author	Piotr Szymański <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */
class InvalidParameterType extends \InvalidArgumentException {

    public function __construct( $name, & $actual, $expected = NULL ) {

        if (is_object( $actual ))
            $actual = get_class( $actual );
        else
            $actual = gettype( $actual );
        
        parent::__construct( "$name has an invalid type <$actual>." .
            (is_null($expected)?"":" Expecting <$expected>.") );
    
    }

}

