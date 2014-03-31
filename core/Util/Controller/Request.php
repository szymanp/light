<?php

namespace Light\Util\Controller;

final class Request extends \Light\Util\HTTP\Request
{
	/**
	 * The URI decoded for use with a particular plugin.
	 * @var string
	 */
	private $decodedUri;
	
	public function __construct($decodedUri, array $serverData = null, array $postData = null)
	{
		parent::__construct($serverData, $postData);
		
		$this->decodedUri = $decodedUri;
	}
	
	/**
	 * Returns the URI decoded for use with a particular plugin.
	 * @return string
	 */
	public function getDecodedUri()
	{
		return $this->decodedUri;
	}
}