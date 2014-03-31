<?php

namespace Light\Service\Auth;

use Light\Util\HTTP;

interface AuthFrontend
{
	/**
	 * Checks if the request is compatible with this Auth frontend.
	 * This method will be called before {@see init()}.
	 * @param HTTP\Request $request
	 * @return boolean
	 */
	function detect(HTTP\Request $request);
		
	/**
	 * Initializes the authentication frontend.
	 * @param HTTP\Request	$request
	 * @param HTTP\Response	$response
	 */
	function init(HTTP\Request $request, HTTP\Response $response);

	/**
	 * Read user credentials.
	 *
	 * @return Light\Service\Auth\Credentials
	 *				If the user has not supplied any credentials,
	 *				then this method should return NULL.
	 */
	function readCredentials();
	
	/**
	 * Informs the frontend to issue a "try again" message to the user.
	 */
	function tryAgain();
}