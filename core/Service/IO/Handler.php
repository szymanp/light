<?php
namespace Light\Service\IO;

use Light\Util\HTTP;
use Light\Service;

/**
 * Base class for input and output handlers.
 *
 */
abstract class Handler
{
	/** @var Light\Service\Container */
	private $application;
	
	/** @var Light\Util\HTTP\Request */
	private $request;
	
	/** @var Light\Util\HTTP\Response */
	private $response;
	
	public function __construct(Service\Container $app, HTTP\Request $request, HTTP\Response $response)
	{
		$this->application	= $app;
		$this->request		= $request;
		$this->response		= $response;
	}
	
	/**
	 * Returns the HTTP Request object.
	 * @return Light\Util\HTTP\Request
	 */
	protected function getHttpRequest()
	{
		return $this->request;
	}

	/**
	 * Returns the HTTP Response object.
	 * @return Light\Util\HTTP\Response
	 */
	protected function getHttpResponse()
	{
		return $this->response;
	}

	/**
	 * @return string
	 */
	protected function getContentType()
	{
		return $this->request->getHeader('Content-type');
	}
	
	/**
	 * @return Light\Service\Container
	 */
	protected function getContainer()
	{
		return $this->application;
	}
}
