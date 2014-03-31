<?php

namespace Light\Service\Auth;

class DigestCredentials implements Credentials
{
	/** @var string */
	private $identifier;
	/** @var string */
	private $digest;
	/** @var string */
	private $realm;

	/**
	 * @param string	$identifier	User identifier, e.g. username.
	 * @param string	$digest		Digest.
	 * @param string	$realm
	 */
	public function __construct($identifier, $digest, $realm = null)
	{
		$this->identifier = $identifier;
		$this->digest = $digest;
		$this->realm = $realm;
	}
	
	/**
	 * Returns the identifier for the user, e.g. a username.
	 * @return string
	 */
	public function getIdentifier()
	{
		return $this->identifier;
	}
	
	/**
	 * Returns the realm used by the authentication backend.
	 * @return string
	 */
	public function getRealm()
	{
		return $this->realm;
	}

	/**
	 * Validates the digest.
	 * @param string	$digest
	 * @return boolean	True, if the digest is valid; otherwise, false.
	 */
	public function validateDigest($digest)
	{
		return $this->digest === $digest;
	}	
}
