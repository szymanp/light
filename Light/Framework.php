<?php

$dirname = dirname(__FILE__);
include_once $dirname . DIRECTORY_SEPARATOR . 'Autoloader.php';

Light\Autoloader::addPath($dirname, "Light");

// common functions

/**
 * <code>
 * object(new MyClass)->doSomething()
 * </code>
 */
function object($obj) {
	return $obj;
}
