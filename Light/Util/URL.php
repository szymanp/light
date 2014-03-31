<?php
namespace Light\Util;

/**
 * Class <kbd>URL</kbd> represents a Uniform Resource Locator.
 * 
 * Objects of this class are immutable.
 *
 */
class URL
{
	private static $normalization = array(
    'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj','Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A',
    'Å'=>'AA','Æ'=>'AE','Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I',
    'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'OE','Ù'=>'U', 'Ú'=>'U',
    'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss','à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a',
    'å'=>'aa','æ'=>'ae','ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
    'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'oe','ù'=>'u',
    'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y', 'ƒ'=>'f', 'Ę'=>'E', 'Ą'=>'A', 'Ś'=>'S',
	'Ł'=>'L', 'Ż'=>'Z', 'Ź'=>'Z', 'Ć'=>'C', 'Ń'=>'N', 'ę'=>'e', 'ą'=>'a', 'ś'=>'s', 'ł'=>'l', 'ż'=>'z',
	'ź'=>'z', 'ć'=>'c', 'ń'=>'n');
	
	/**
	 * Is the parsed form of the URL available?  
	 * @var boolean
	 */
	private $parsedForm = false;
	
	protected $protocol;
	
	protected $user;
	
	protected $password;
	
	protected $host;
	
	protected $port;
	
	protected $path;
	
	protected $query;
	
	protected $reference;
	
	/** @var array<string, string> */
	protected $queryVariables;
	
	/**
	 * The string form of the URL.
	 * If the string form has not yet been created, this field is NULL.
	 * @var string
	 */
	private $stringForm;

	/**
	 * Constructs an empty URL object.
	 */
	protected function __construct(URL $copyFrom = null)
	{
		if ($copyFrom)
		{
			$this->protocol 	= $copyFrom->protocol;
			$this->host			= $copyFrom->host;
			$this->user 		= $copyFrom->user;
			$this->password 	= $copyFrom->password;
			$this->port			= $copyFrom->port;
			$this->path			= $copyFrom->path;
			$this->query		= $copyFrom->query;
			$this->reference	= $copyFrom->reference;
			
			$this->parsedForm	= $copyFrom->parsedForm;
			$this->stringForm	= $copyFrom->stringForm;
		}
	}
	
	/**
	 * Constructs a URL from a string representation.
	 *
	 * @param string			$url	An absolute URL.
	 * @return \Light\Util\URL
	 */
	public static function fromString($url)
	{
		$self	= new self;
		$self->stringForm = $url;
		return $self;
	}
	
	/**
	 * Constructs a URL from the specified protocol, host, port and path.
	 * @param string	$protocol
	 * @param string	$host
	 * @param integer	$port
	 * @param string	$path
	 * @param string	$query
	 * @return \Light\Util\URL
	 */
	 public static function fromParts($protocol, $host, $port, $path, $query = null)
	 {
	 	$self = new self;
	 	$self->parsedForm = true;
		$self->protocol = $protocol;
		$self->host		= $host;
		$self->port		= $port;
		$self->path		= $path;
		$self->query	= $query;
		return $self;
	}
	
	/**
	 * Converts special characters in the URL component into "safe" equivalents.
	 * 
	 * Note that calling this method on a string that is already "safe" will return the same string.
	 * 
	 * @param string	$str
	 * @return string
	 */
	public static function cleanUrlComponent($str)
	{
		$str = strtr($str, self::$normalization);
		$str = trim(preg_replace('/[^\w\d_ -]/si', '', $str));	// remove all illegal chars
		$str = str_replace(' ', '-', $str);
		while (strpos($str, '--') > -1)
		{
			$str = str_replace('--', '-', $str);
		}
		return $str;
	}
	
	/**
	 * Joins path fragments into one URL.
	 * @param array	$paths	E.g. ['/path/', '/to', 'something']
	 * @return string	E.g. /path/to/something
	 */
	public static function joinPaths(array $paths)
	{
		$url = $paths[0];
		for($i=1;$i<count($paths);$i++)
		{
			$nextPart = $paths[$i];
			$endsWithSlash = !empty($url) && substr($url, -1, 1) == "/";
			
			$startsWithSlash = $nextPart[0] == "/";
			if (!$endsWithSlash && !$startsWithSlash)
			{
				$url .= "/" . $nextPart;
			}
			elseif ($endsWithSlash && $startsWithSlash)
			{
				$url .= substr($nextPart, 1);
			}
			else
			{
				$url .= $nextPart;
			}
		}
		return $url;
	}
	
	/**
	 * Returns a new URL pointing to the parent folder.
	 * @return \Light\Util\URL	A new URL object
	 */
	final public function getParent()
	{
		$this->makeParsedForm();
		
		// Create a copy of the current URL
		$url = new self($this);
		
		// Clear the parts that come after the path
		$url->query 	= null;
		$url->reference = null;
		
		// Remove the last part of the path
		$path = explode("/", $this->path);
		array_pop($path);
		$url->path = implode("/", $path);
		
		return $url;
	}
	
	/**
	 * Returns a new URL that is equal to the current one but with the path fragment appended.
	 * @param string|array	$path
	 * @return \Light\Util\URL	A new URL object
	 */
	final public function appendPath($path)
	{
		$this->makeParsedForm();
		
		// Create a copy of the current URL
		$url = new self($this);
		
		// Clear the parts that come after the path
		$url->query 	= null;
		$url->reference = null;
		
		// Append to the path
		if (is_string($path))
		{
			$path = array($path);
		}
		array_unshift($path, $url->path);
		
		$url->path = self::joinPaths($path);
		
		return $url;
	}
	
	/**
	 * Returns a new URL that is equal to the current one but relative to the given URL.
	 * 
	 * If the two URLs are too different, then the current URL will be returned.
	 * 
	 * @param URL $relativeTo
	 * @return \Light\Util\URL	A URL object
	 */
	final public function makeRelative(URL $relativeTo)
	{
		$this->makeParsedForm();
		$relativeTo->makeParsedForm();
		
		if ($this->protocol == $relativeTo->protocol
			&& $this->host == $relativeTo->host
			&& $this->password == $relativeTo->password
			&& $this->user == $relativeTo->user)
		{
			$url = new self;
			$url->parsedForm = true;
			$url->query 		= $this->query;
			$url->reference		= $this->reference;

			if ($this->path == $relativeTo->path)
			{
				// If the URLs are exactly equal, return one with an empty reference.
				if (empty($url->query) && empty($url->reference))
				{
					$url->reference = "";
				}
			}
			else if (empty($relativeTo->query) && empty($relativeTo->reference))
			{
				// TODO For now, the URLs are always host-relative
				$url->path = $this->path;
			}
			
			return $url;
		}

		// The URLs differ too much.
		
		return $this;
	}
	
	/**
	 * Returns the fully-qualified string representation of the URL.
	 * @return string
	 */
	public function toString()
	{
		$this->makeStringForm();
		return $this->stringForm;
	}

	/**
	 * Returns the string representation of the object.
	 * @return string
	 */
	final public function __toString()
	{
		try
		{
			return $this->toString();
		}
		catch (\Exception $e)
		{
			return "<URL Exception: " . $e->getMessage() . ">";
		}
	}
	
	/**
	 * Returns the protocol part of the URL.
	 * @return string
	 */
	final public function getProtocol()
	{
		$this->makeParsedForm();
		return $this->protocol;
	}
	
	/**
	 * Returns the host part of the URL.
	 * @return string
	 */
	final public function getHost()
	{
		$this->makeParsedForm();
		return $this->host;
	}

	/**
	 * Returns the port part of the URL.
	 * @return integer	If no port is set, this method returns 0.
	 */
	final public function getPort()
	{
		$this->makeParsedForm();
		return (integer) $this->port;
	}
	
	/**
	 * @return string
	 */
	final public function getUsername()
	{
		$this->makeParsedForm();
		return $this->user;
	}
	
	/**
	 * @return string
	 */
	final public function getPassword()
	{
		$this->makeParsedForm();
		return $this->password;
	}
	
	/**
	 * Returns the document path fragment of the URL.
	 * @return string
	 */
	final public function getPath()
	{
		$this->makeParsedForm();
		return $this->path;
	}
	
	/**
	 * Returns the query fragment of the URL.
	 * @return string
	 */
	final public function getQuery()
	{
		$this->makeParsedForm();
		return $this->query;
	}
	
	/**
	 * Returns a list of name-value pairs defined in the query part.
	 * @return array<string, string>
	 */
	final public function getQueryVariables()
	{
		$this->makeParsedForm();
		if (is_null( $this->query ))
		{
			return array();
		}
			
		if (is_null($this->queryVariables))
		{
			$this->queryVariables = explode( "&", $this->query );
				
			foreach( $this->queryVariables as $k => $v ) {
				$expl = explode( "=", $v );
				$expl[0] = urldecode( $expl[0] );
				$expl[1] = urldecode( $expl[1] );
				$this->queryVariables[$k] = $expl;
			}
		}
	
		return $this->queryVariables;
	}	
	
	/**
	 * Returns the reference part of the URL.
	 * @return string
	 */
	final public function getReference()
	{
		$this->makeParsedForm();
		return $this->reference;
	}

	private function makeStringForm($force = false)
	{
		if (!is_null($this->stringForm) && !$force)
		{
			return;
		}
		
		if (!is_null($this->protocol))
		{
			$this->stringForm = $this->protocol ."://";
	
			if (!is_null( $this->user ))
			{
				if (!is_null( $this->password ))
				{
					$this->stringForm .= $this->user . ":" . $this->password . "@";
				}
				else
				{
					$this->stringForm .= $this->user . "@";
				}
			}
	
			$this->stringForm .= $this->host . (empty($this->port)||$this->port===80?"":":" . $this->port);
		}
		else
		{
			$this->stringForm = "";
		}
			
		$this->stringForm .= $this->path;
	
		if (!empty( $this->query ))
		{
			$this->stringForm .= "?" . $this->query;
		}
		if (!is_null( $this->reference ))
		{
			$this->stringForm .= "#" . $this->reference;
		}
	}
	
	private function makeParsedForm($force = false)
	{
		if ($this->parsedForm && !$force)
		{
			return;
		}
	
		$this->parseURL($this->stringForm);
		
		$this->parsedForm = true;
	}
	
	/**
	 * Parses a URL.
	 * @param string	$str
	 * @return boolean	TRUE on success, FALSE if the URL was invalid.
	 */
	private function parseURL($str)
	{
		if (($full = strpos( $str, "://" )) === FALSE)
		{
			$r_proto = "";
			$r_auth  = "";
			$r_host  = "";
		}
		else
		{
			$r_proto = "(([^:]*):\/\/)?";
			$r_auth  = "((([^:]*):([^@]*)@)|(([^@]*)@))?";
			$r_host  = "((([^:]*):([^\/]*))|(([^\/]*)))?";
		}
		$r_path  = "([^?#]*)";
		$r_query = "(\?([^#]*))?";
		$r_ref	 = "(#(.*))?";
	
		$regex = "/" . $r_proto . $r_auth . $r_host . $r_path . $r_query . $r_ref . "/";
	
		$r = array();
		if (preg_match( $regex, $str, $r ) == 0) return FALSE;
	
		if ($full) {
			if (!empty( $r[2] ))	$this->protocol = $r[2];
			else					$this->protocol = NULL;
				
			if (!empty( $r[5] ))	$this->user		= $r[5];
			elseif (!empty($r[8]))	$this->user		= $r[8];
			else					$this->user		= NULL;
				
			if (!empty( $r[6] ))	$this->password = $r[6];
			else					$this->password = NULL;
				
			if (!empty( $r[11] ))	$this->host		= $r[11];
			elseif (!empty($r[13])) $this->host		= $r[13];
			else					$this->host		= NULL;
	
			if (!empty( $r[12] ))	$this->port		= (integer) $r[12];
			else					$this->port		= NULL;
				
			if (!empty( $r[15] ))	$this->path		= $r[15];
			else					$this->path		= NULL;
	
			if (!empty( $r[17] ))	$this->query	= $r[17];
			else					$this->query	= NULL;
	
			if (!empty( $r[19] ))	$this->reference= $r[19];
			else					$this->reference= NULL;
		} else {
			$this->protocol		= NULL;
			$this->user			= NULL;
			$this->password		= NULL;
			$this->host			= NULL;
			$this->port			= NULL;
	
			if (!empty( $r[1] ))	$this->path		= $r[1];
			else					$this->path		= NULL;
	
			if (!empty( $r[3] ))	$this->query	= $r[3];
			else					$this->query	= NULL;
	
			if (!empty( $r[5] ))	$this->reference= $r[5];
			else					$this->reference= NULL;
	
		}
	
		return TRUE;
	}
}
