<?php

use Light\Autoloader;

$BASE_PATH = realpath(dirname(__FILE__));
$CORE_PATH = realpath(dirname(__FILE__) . "/../Light" ) . DIRECTORY_SEPARATOR;

@include_once "config-local.php";

include $CORE_PATH . "Framework.php";

error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);

// Set the working directory to the root of the tests
chdir($BASE_PATH);
