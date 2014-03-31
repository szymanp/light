<?php
namespace Light\Service;

use Light\Exception\Exception;
use Light\Service\Auth\AuthFrontend;

class Descriptor
{
	/** @var Light\Service\Descriptor_Method[] */
	private $methods = array();
	private $invokedMethod;
	/** @var string */
	private $defaultOutput	= "text/json";
	/** @var boolean */
	private $defaultRequireAuth = false;
	/** @var Light\Service\Auth\AuthFrontend */
	private $defaultAuthFrontend;
	
	/**
	 * List of AuthFrontends to use when autodetecting authentication mode.
	 * @var Light\Service\Auth\AuthFrontend[]
	 */
	private $authFrontends = array();
	
	/**
	 * @return	Light\Service\Descriptor_Method
	 */
	public function addMethod($name)
	{
		$this->methods[$name] = $d = new Descriptor_Method($this);
		return $d;
	}
	
	/**
	 * Adds multiple methods to the descriptor.
	 * @param string|array	$names	One or more method names
	 * return	Light\Service\Descriptor	For fluent API.
	 */
	public function addMethods($names)
	{
		if (!is_array($names))
		{
			$names = func_get_args();
		}
		
		foreach($names as $name)
		{
			$this->addMethod($name);
		}
		
		return $this;
	}
	
	/**
	 * Sets the default content-type for output data.
	 * @param	string	$o
	 * return	Light\Service\Descriptor	For fluent API.
	 */
	public function setDefaultOutput($o)
	{
		$this->defaultOutput = $o;
		return $this;
	}

	/**
	 * Sets whether methods requires authentication.
	 * @param boolean	$v
	 */
	public function setDefaultRequireAuth($v)
	{
		$this->defaultRequireAuth = $v;
	}
	
	/**
	 * Returns true if methods requires authentication (by default).
	 * @return boolean
	 */
	public function getDefaultRequireAuth()
	{
		return $this->defaultRequireAuth;
	}

	/**
	 * Sets the authentication frontend to be used with this service.
	 * @param AuthFrontend $frontend
	 */
	public function setDefaultAuthFrontend(AuthFrontend $frontend)
	{
		$this->defaultAuthFrontend = $frontend;
		$this->defaultRequireAuth = true;
	}
	
	/**
	 * Adds an authentication frontend to be used with this service.
	 * @param AuthFrontend $frontend
	 */
	public function addAuthFrontend(AuthFrontend $frontend)
	{
		$this->authFrontends[] = $frontend;
	}
	
	
	// querying
	
	/**
	 * Returns the descriptor for the given method.
	 * @param	string	$name
	 * @return	Light\Service\Descriptor_Method
	 *			A method descriptor, if found; otherwise, NULL.
	 */
	public function getMethod($name)
	{
		if (isset($this->methods[$name]))
		{
			return $this->methods[$name];
		}
		return NULL;
	}
	
	public function getDefaultOutput()
	{
		return $this->defaultOutput;
	}
	
	/**
	 * Returns the method currently being invoked.
	 * @return Light\Service\Descriptor_Method
	 */
	public function getInvokedMethod()
	{
		return $this->invokedMethod;
	}
	
	/**
	 * Returns the authentication frontend for this service.
	 * @return Light\Service\Auth\AuthFrontend
	 */
	public function getDefaultAuthFrontend()
	{
		return $this->defaultAuthFrontend;
	}
	
	/**
	 * Returns a list of registered authentication frontends for this service.
	 * @return \Light\Service\Light\Service\Auth\AuthFrontend[]
	 */
	public function getAuthFrontends()
	{
		return $this->authFrontends;
	}
	
	/**
	 * @param Descriptor_Method $method
	 */
	public function __containerSetInvokedMethod(Descriptor_Method $method = null)
	{
		$this->invokedMethod = $method;
	}
}

class Descriptor_Method
{
	/** @var Light\Service\Descriptor */
	private $parent;
	/** @var string */
	private $outputType	= NULL;
	/** @var Light\Service\ResultProcessor */
	private $resultProcessor = NULL;
	/**
	 * Does this method require authentication?
	 * Note that this property can have 3 values: true, false or NULL.
	 * @var boolean
	 */
	private $requireAuth = NULL;
	/** @var Light\Service\Auth\AuthFrontend */
	private $authFrontend;
	
	/**
	 * Constructs a new Descriptor_Method object.
	 * @param Descriptor	$parent
	 */
	public function __construct(Descriptor $parent)
	{
		$this->parent = $parent;
	}
	
	/**
	 * Returns the Descriptor that this method is part of.
	 * @return \Light\Service\Descriptor
	 */
	public function getDescriptor()
	{
		return $this->parent;
	}
	
	/**
	 * Sets the content-type for output data.
	 * @param	string	$o
	 * return	Light\Service\Descriptor_Method	For fluent API.
	 */
	public function setOutput($o)
	{
		$this->outputType = $o;
		return $this;
	}
	
	public function getOutput()
	{
		return is_null($this->outputType) ? $this->parent->getDefaultOutput() : $this->output;
	}

	/**
	 * Sets a ResultProcessor for this method.
	 * @param ResultProcessor $processor
	 */
	public function setResultProcessor(ResultProcessor $processor)
	{
		$this->resultProcessor = $processor;
	}
	
	/**
	 * Returns a ResultProcessor, if any; otherwise, NULL.
	 * @return Light\Service\ResultProcessor
	 */
	public function getResultProcessor()
	{
		return $this->resultProcessor;
	}
	
	/**
	 * Sets whether this method requires authentication.
	 * @param boolean	$v	A value of NULL can albo be specified.
	 */
	public function setRequireAuth($v)
	{
		$this->requireAuth = $v;
	}
	
	/**
	 * Returns true if this method requires authentication.
	 * @return boolean
	 */
	public function getRequireAuth()
	{
		return is_null($this->requireAuth) ? $this->parent->getDefaultRequireAuth() : $this->requireAuth;
	}
	
	/**
	 * Sets the authentication frontend to be used with this method.
	 * @param AuthFrontend $frontend
	 */
	public function setAuthFrontend(AuthFrontend $frontend)
	{
		$this->authFrontend = $frontend;
		$this->requireAuth = true;
	}
	
	/**
	 * Returns the authentication frontend that is to be used for this method.
	 * @return Light\Service\Auth\AuthFrontend
	 */
	public function getAuthFrontend()
	{
		return is_null($this->authFrontend) ? $this->parent->getDefaultAuthFrontend() : $this->authFrontend;
	}
}

/**
 * An interface for classes that can process the result returned from a method invocation.
 */
interface ResultProcessor
{
	/**
	 * Process input and return the result.
	 * @param Descriptor_Method	$method
	 * @param mixed				$result
	 * @return mixed
	 */
	public function processResult(Descriptor_Method $method, $result);
}
