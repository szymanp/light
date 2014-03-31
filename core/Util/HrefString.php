<?php

namespace Light\Util;

/**
 * Represents a HREF string.
 * A HREF string could be an URL or a Javascript call.
 */
class HrefString
{
	/**
	 * The full Href string.
	 * @var string
	 */
	private $href;
	
	/**
	 * Specifies whether the Href string is a Javascript call (true), or an URL (false).
	 * @var boolean
	 */
	private $isJavascript = false;
	
	/**
	 * If the Href is an URL, this may contain the URL without the argument part.
	 * @var string
	 */
	private $baseUrl;
	
	/**
	 * If the Href is an URL, this may contain a list of arguments.
	 * @var array
	 */
	private $urlArguments;
	
	public function __construct($href, $isJavascript = false)
	{
		$this->href			= $href;
		$this->isJavascript	= $isJavascript;
	}
	
	/**
	 * Provides the base URL (without arguments) and the arguments as an array.
	 * This method can only be called for URL HREFs (as opposed to Javascript ones).
	 * @param string	$url
	 * @param array		$arguments	A name-value mapping of arguments
	 */
	public function setUrlArguments($url, array $arguments)
	{
		if ($this->isJavascript)
		{
			throw new \Exception("This HREF is not an URL");
		}
		
		$this->baseUrl		= $url;
		$this->urlArguments	= $arguments;
		
		if (substr($this->href, 0, strlen($this->baseUrl)) != $this->baseUrl)
		{
			throw new \Exception("The supplied base URL does not match the full one");
		}
	}
	
	/**
	 * Prints an A HREF link.
	 * @param string	$content	The content of the A tag.
	 * @param string	$attribs	Optional attributes for the A tag.
	 */
	public function printLink($content, $attribs = "")
	{
		$str = "<a ";

		if ($this->isJavascript)
		{
			$str .= "href=\"#\" onClick=\"" . $this->href . "\"";
		}
		else
		{
			$str .= "href=\"" . $this->href . "\"";
		}
		
		if (!empty($attribs))
		{
			$str .= " " . $attribs;
		}
		
		$str .= ">" . (string) $content . "</a>";
		
		print $str;
	}

	/**
	 * @return string	The full Href string.
	 */	
	public function getHref()
	{
		return $this->href;
	}
	
	/**
	 * @return boolean	True, if the Href is a javascript call; otherwise, false.
	 */
	public function isJavascript()
	{
		return $this->isJavascript;
	}

	/**
	 * Returns the base URL -- without the query section.
	 * Calling this method on a non-URL HREF results in an exception.
	 * @return string
	 */
	public function getBaseUrl()
	{
		if ($this->isJavascript)
		{
			throw new \Exception("This HREF is not an URL");
		}
		
		if (is_null($this->baseUrl))
		{
			$url = $this->toUrl();
			$this->urlArguments	= array(); 
			foreach($url->getQueryVars() as $pair)
			{
				$this->urlArguments[$pair[0]] = $pair[1];
			}
			$url->clearQueryVars();
			$this->baseUrl = $url->__toString();
		}
		
		return $this->baseUrl;
	}
	
	/**
	 * Returns the arguments in the URL's query section.
	 * Calling this method on a non-URL HREF results in an exception.
	 * @return array	A name-value pair mapping of arguments.
	 */
	public function getUrlArguments()
	{
		if ($this->isJavascript)
		{
			throw new \Exception("This HREF is not an URL");
		}
		
		if (is_null($this->urlArguments))
		{
			$url = $this->toUrl();
			$this->urlArguments	= array(); 
			foreach($url->getQueryVars() as $pair)
			{
				$this->urlArguments[$pair[0]] = $pair[1];
			}
			$url->clearQueryVars();
			$this->baseUrl = $url->__toString();
		}
		
		return $this->urlArguments;
	}
	
	/**
	 * @return	Light\Util\URL
	 */	
	public function toURL()
	{
		if ($this->isJavascript)
		{
			throw new \Exception("This HREF is not an URL");
		}
		
		return URL::fromString($this->href);
	}
	
	public function __toString()
	{
		return $this->href;
	}
}
