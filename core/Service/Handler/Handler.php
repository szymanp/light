<?php
namespace Light\Service\Handler;
use Light\Service\Service;
use Light\Service\Container;

abstract class Handler
{
	protected $service;
	protected $container;

	public function __construct(Container $container, Service $service)
	{
		$this->service		= $service;
		$this->container	= $container;
	}
	
	/**
	 * Executed to initialize the Service.
	 */
	abstract public function init();
	
	/**
	 * Executed to load the Service.
	 * @param	array	$serviceParameters
	 */
	abstract public function load(array $serviceParameters = array());

	/**
	 * Executed to shutdown the Service.
	 */
	abstract public function finish();
}