<?php

namespace Light\UI\Framework;
use Light\Exception\Exception;
use Light\Service\Descriptor;

abstract class Service extends LifecycleObject
{
	private $serviceDescriptor;
	
	public function getServiceDescriptor()
	{
		if (is_null($this->serviceDescriptor))
		{
			$this->serviceDescriptor = new Descriptor();
		}
	
		return $this->serviceDescriptor;
	}
	
	// RequestHandler interface implementation (partial)
	
	public function getRequestHandler($name, $index = null)
	{
		// a component does not have any children - only a container does
		return null;
	}
}