<?php
namespace Light\Service\Exception;

/**
 * An invalid parameter was passed to a method or a parameter was missing.
 */
class InvalidParameterException extends ServiceContainerException
{
	public function __construct( $method, $name, $index, $type = NULL)
	{
		if (is_null( $type ))
		{
			parent::__construct(array("Missing parameter %1 at index %2 for method %3()", $name, $index, $method), 405);
		}
	}

}
