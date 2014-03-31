<?php
namespace Light\Service\Handler;
use Light\Service\Service;
use Light\Service\Container;

class Simple extends Handler
{
	public function init()
	{
		if (method_exists($this->service, "attachToContainer"))
		{
			$this->service->attachToContainer($this->container);
		}
	}
	
	public function load(array $serviceParameters = array())
	{
	}

	public function finish()
	{
	}
}