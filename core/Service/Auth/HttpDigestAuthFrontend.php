<?php

namespace Light\Service\Auth;

use Light\Util\HTTP;

class HttpDigestAuthFrontend implements AuthFrontend
{
	/** @var Light\Util\HTTP\DigestAuth */
	private $httpDigestAuth;
	
	public function __construct()
	{
		$this->httpDigestAuth = new HTTP\DigestAuth();
	}
	
	/**
	 * Returns the HTTP Digest Auth backend.
	 * @return Light\Util\HTTP\DigestAuth
	 */
	public function getDigestAuth()
	{
		return $this->httpDigestAuth;
	}
	
	/**
	 * Checks if the request is compatible with this Auth frontend.
	 * @param HTTP\Request $request
	 * @return boolean
	 */
	public function detect(HTTP\Request $request)
	{
		// The client never initiates a Digest authentication, so it cannot be detected.
		return false;
	}

	/**
	 * Initializes the authentication frontend.
	 * @param HTTP\Request	$request
	 * @param HTTP\Response	$response
	 */
	public function init(HTTP\Request $request, HTTP\Response $response)
	{
		$this->httpDigestAuth->setHTTPRequest($request);
		$this->httpDigestAuth->setHTTPResponse($response);
		$this->httpDigestAuth->init();
	}

	/**
	 * Read user credentials.
	 *
	 * @return Light\Service\Auth\Credentials
	 *				If the user has not supplied any credentials,
	 *				then this method should return NULL.
	 */
	public function readCredentials()
	{
		$username = $this->httpDigestAuth->getUsername();
		if (!$username)
		{
			// No authentication headers are present
			return null;
		}
		
		return new HttpDigestAuthFrontend_Cred($this, $username);
	}
	
	/**
	 * Informs the frontend to issue a "try again" message to the user.
	 */
	public function tryAgain()
	{
		$this->httpDigestAuth->requireLogin();
	}
}

class HttpDigestAuthFrontend_Cred extends DigestCredentials
{
	/** @var Light\Service\Auth\HttpDigestAuthFrontend */
	private $owner;
	
	public function __construct(HttpDigestAuthFrontend $owner, $username)
	{
		parent::__construct($username, null);
		$this->owner = $owner;
	}
	
	public function getRealm()
	{
		return $this->owner->getDigestAuth()->getRealm();
	}
	
	public function validateDigest($digest)
	{
		return $this->owner->getDigestAuth()->validateA1($digest);
	}
}