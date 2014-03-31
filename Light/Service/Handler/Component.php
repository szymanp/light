<?php
namespace Light\Service\Handler;
use Light\Service\Service;
use Light\Service\Container;

class Component extends Handler
{
	public function init()
	{
		$this->service->init();
	}
	
	public function load(array $serviceParameters = array())
	{
		$this->service->load();
	}

	public function finish()
	{
		$this->service->finish();
	}
}