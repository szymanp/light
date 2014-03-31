<?php

namespace Light\Service\Auth;

use Light\Service\Exception\ServiceContainerException;
use Light\Service\Exception\AuthenticationException;
use Light\Service\Descriptor_Method;
use Light\Service\Container;
use Light\Util\HTTP;

class ServiceAuthenticator
{
	/** @var Light\Util\HTTP\Request */
	private $httpRequest;
	/** @var Light\Util\HTTP\Response */
	private $httpResponse;

	/** @var Light\Service\Auth\AuthBackend */	
	private $backend;
	
	public function __construct(HTTP\Request $request, HTTP\Response $response)
	{
		$this->httpRequest	= $request;
		$this->httpResponse	= $response;
	}

	/**
	 * Sets the authentication backend.
	 * @param Backend	$backend
	 */	
	public function setBackend(AuthBackend $backend)
	{
		$this->backend = $backend;
	}
	
	/**
	 * Validates the service method request.
	 * @throws Light\Service\Exception\AuthenticationException
	 *					This exception is thrown if the method cannot be executed due to
	 *					pending authentication. Note that this may mean that the client
	 *					has to repeat the request, providing valid credentials.
	 */
	public function validate(Descriptor_Method $method)
	{
		// Try to read the frontend from the method descriptor.
		$frontend = $method->getAuthFrontend();
		if (is_null($frontend))
		{
			// Try to autodetect among the registered frontends for this service.
			$authFrontends = $method->getDescriptor()->getAuthFrontends();
			
			foreach($authFrontends as $test)
			{
				if ($test->detect($this->httpRequest))
				{
					$frontend = $test;
					break;
				}
			}
		}
		
		if (is_null($frontend))
		{
			if (!$method->getRequireAuth())
			{
				// The method does not require authentication, and we could not detect any client
				// requests for authentication. Therefore we skip validation
				return;
			}
			else
			{
				// Authentication is required but could not be accomplished.
				throw new ServiceContainerException("Method requires authentication but no authentication frontend is set", 500);
			}
		}

		if (is_null($this->backend))
		{
			throw new ServiceContainerException("Method requires authentication but no authentication backend is set", 500);
		}
		
		// Check if the request is already authenticated
		if ($this->backend->isAuthenticated())
		{
			return;
		}
		
		// Ask the frontend for credentials
		$frontend->init($this->httpRequest, $this->httpResponse);
		$cred = $frontend->readCredentials();
		if (is_null($cred))
		{
			$frontend->tryAgain();
			throw new AuthenticationException("No credentials were given", true);
		}
		
		$result = $this->backend->authenticate($cred);
		if (!$result)
		{
			$frontend->tryAgain();
			throw new AuthenticationException("Credentials could not be validated", true);
		}
	}
}