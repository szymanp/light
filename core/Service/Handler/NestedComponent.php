<?php
namespace Light\Service\Handler;
use Light\Service\Service;
use Light\Service\Container;
use Light\UI;

class NestedComponent extends Handler
{
	private $root;

	public function __construct(Container $container, UI\Container $root, Service $service)
	{
		parent::__construct($container, $service);
		
		$this->root	= $root;
	}

	public function init()
	{
	}
	
	public function load(array $serviceParameters = array())
	{
		$this->root->load();
	}

	public function finish()
	{
		$this->root->finish();
	}
}