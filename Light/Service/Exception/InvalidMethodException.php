<?php
namespace Light\Service\Exception;

/**
 * An attempt to call an invalid method.
 */
class InvalidMethodException extends Light\Exception\Exception {

	public function __construct($name)
	{
		if (is_null( $name ))
		{
			parent::__construct("No method name was supplied", 405);
		}
		else if ($name == "") 
		{
			parent::__construct( "Empty method name was supplied", 405);
		}
		else
		{
			parent::__construct(array("Method <%1> unavailable", $name), 405);
		}
	}

}
