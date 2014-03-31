<?php

namespace Light\Util;

require_once 'config.php';

class URLTest extends \PHPUnit_Framework_TestCase
{
	public function testCleanUrlComponent()
	{
		$this->assertEquals("Tajlandia-032012", URL::cleanUrlComponent("Tajlandia - 03.2012"));
	}
}
