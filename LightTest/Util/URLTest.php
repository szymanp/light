<?php

namespace Light\Util;

require_once 'config.php';

class URLTest extends \PHPUnit_Framework_TestCase
{
	public function testCleanUrlComponent()
	{
		$this->assertEquals("Tajlandia-032012", URL::cleanUrlComponent("Tajlandia - 03.2012"));
	}
	
	public function testModify()
	{
		$url = URL::fromString("http://www.magres.net/q?a=5");
		$builder = $url->modify();
		
		$this->assertInstanceOf("Light\Util\URLBuilder", $builder);
		
		$builder->setProtocol("https");
		$builder->setQuery(array("b" => 4, "c" => 6));
		$newUrl = $builder->build();
		
		$this->assertEquals("https://www.magres.net/q?b=4&c=6", $newUrl->toString());
	}
}
