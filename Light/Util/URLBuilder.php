<?php
namespace Light\Util;

/**
 * A helper for constructing URL objects.
 * 
 */
final class URLBuilder
{
	protected $protocol;
	
	protected $user;
	
	protected $password;
	
	protected $host;
	
	protected $port;
	
	protected $path;
	
	/** @var array<string, string> */
	protected $query = array();
	
	protected $reference;
	
	public function __construct(URL $url = null)
	{
		if ($url)
		{
			$this->protocol = $url->getProtocol();
			$this->user		= $url->getUsername();
			$this->password	= $url->getPassword();
			$this->host		= $url->getHost();
			$this->port		= $url->getPort();
			$this->path		= $url->getPath();
			$this->reference= $url->getReference();
			$this->query	= $url->getQueryVariables();
		}
	}
	
	/**
	 * Sets the protocol.
	 * @param string	$protocol
	 * @return \Light\Util\URLBuilder
	 */
	public function setProtocol($protocol)
	{
		$this->protocol = $protocol;
		return $this;
	}
	
	/**
	 * Sets the query part.
	 * @param array $query
	 * @return \Light\Util\URLBuilder
	 */
	public function setQuery(array $query)
	{
		$this->query = $query;
		return $this;
	}
	
	// @todo Add more setters.
	
	/**
	 * Builds the resulting URL object.
	 * @return \Light\Util\URL
	 */
	public function build()
	{
		if (empty($this->query))
		{
			$query = null;
		}
		else
		{
			$query = "";
			foreach($this->query as $key => $value)
			{
				$query .= "&" . urlencode($key) . "=" . urlencode($value);
			}
			$query = substr($query, 1);
		}
		
		return URL::fromParts($this->protocol,
							  $this->host,
							  $this->port,
							  $this->path,
							  $query,
							  $this->reference,
							  $this->user,
							  $this->password);
	}
}
