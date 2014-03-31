<?php

namespace Light\Service\Auth;

interface AuthBackend
{
	/**
	 * Returns true if the session is authenticated.
	 * @return boolean
	 */
	function isAuthenticated();
	
	/**
	 * Authenticate the user with the given credentials.
	 * @throws Exception	If the type of credentials cannot be accepted.
	 * @return boolean	True, if the user was successfully authenticated;
	 *					otherwise, false.
	 */
	function authenticate(Credentials $cred);
	
	/**
	 * Informs the backend to close the session.
	 */
	function close();
}