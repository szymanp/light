<?php

namespace Light\Service\Util;

use Light\Data\Service\Projection;
use Light\Service\ResultProcessor;
use Light\Service\Descriptor_Method;

class ProjectionResultProcessor implements ResultProcessor
{
	/** @var Light\Data\Service\Projection */
	private $projection;
	
	public function __construct(Projection $projection)
	{
		$this->projection = $projection;
	}
	
	public function processResult(Descriptor_Method $method, $result)
	{
		return $this->projection->getProjector()->project($result);
	}
	
	/**
	 * @return Light\Data\Service\Projection
	 */
	public function getProjection()
	{
		return $this->projection;
	}
}
