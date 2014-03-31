<?php

namespace Light\UI;

use Light\UI\Util\ResourceFinder;
use Light\UI\Framework\ComponentMachine;
use Light\UI\Framework\Input\Request;
use Light\UI\Framework\Input\GetPostRequestDecoder;
use Light\Exception;
use Light\UI\Util;
use Light\Util\Controller\Controller;

class View extends View\AbstractView
{
	protected function construct()
	{
		parent::construct();
		$this->setResourceFinder(new ResourceFinder($this));
	}
	
	public function run(Container $c, Request $r = null)
	{
		if (is_null($r))
		{
			$decoder = new GetPostRequestDecoder();
			$r = $decoder->read();
		}
	
		$componentMachine = new ComponentMachine($this);
		$componentMachine->run($c, $r);
		
		$html = $componentMachine->getRenderedOutput();
		
		if (!is_null($html))
		{
			$file = $this->getResourceFinder()->getDefaultResourceFile($this);
			include($file);
		}
	}	

}