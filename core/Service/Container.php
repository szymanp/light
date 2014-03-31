<?php
namespace Light\Service;

use Light\Service\Exception\ServiceContainerException;
use Light\Service\Exception\AuthenticationException;
use Light\Service\IO\InputHandler;
use Light\Service\Util\DataObjectDecoder;
use Light\Service\IO;
use Light\Service\Handler;
use Light\Service\Auth\ServiceAuthenticator;
use Light\Util\HTTP;
use Light\Log;
use Light\Exception;

class Container
{
	private $service;
	private $descriptor;
	private $serviceHandler;
	private $inputHandler;
	private $outputHandler;
	/** @var Light\Service\Auth\ServiceAuthenticator */
	private $authenticator;
	
	/** @var Light\Util\HTTP\Request */
	private $request;
	/** @var Light\Util\HTTP\Response */
	private $response;
	
	private $debug = false;
	
	private $inputHandlers		= array(
		"url/encoded"			=> "Light\Service\IO\UrlInputHandler",
		"text/json"				=> "Light\Service\IO\JsonInputHandler",
		"application/json"		=> "Light\Service\IO\JsonInputHandler",
		"application/x-www-form-urlencoded"	=> "Light\Service\IO\UrlInputHandler",
		"multipart/form-data"	=> "Light\Service\IO\UrlInputHandler"
	);
	private $outputHandlers		= array(
		"text/json"				=> "Light\Service\IO\JsonOutputHandler",
		"text/json-comment-filtered" => "Light\Service\IO\JsonOutputHandler",
		"text/html"				=> "Light\Service\IO\HtmlOutputHandler"
	);
	private $serviceHandlers	= array(
		"Light\Service\Service"	=>	"Light\Service\Handler\Simple",
		"Light\UI\Component"	=>	"Light\Service\Handler\Component"
	);
	
	/**
	 * Constructs a new service container.
	 */
	public function __construct()
	{
		$this->request		= new HTTP\Request();
		$this->response		= new HTTP\Response();
		$this->authenticator = new ServiceAuthenticator($this->request,
														$this->response);
	}
	
	public function registerInputHandler($contentType, $handlerClass)
	{
		$this->inputHandlers[$contentType] = $handlerClass;
		return $this;
	}

	public function registerOutputHandler($contentType, $handlerClass)
	{
		$this->outputHandlers[$contentType] = $handlerClass;
		return $this;
	}
	
	/**
	 * Registers a service handler.
	 * @param	string	$baseClass		The Service class to use the handler for.
	 * @param	string	$handlerClass	The handler class to instantiate.
	 * @return	Light\Service\Container	For fluent API.
	 */
	public function registerServiceHandler($baseClass, $handlerClass)
	{
		$this->serviceHandlers[$baseClass] = $handler;
		return $this;
	}
	
	/**
	 * Sets a service handler to be used to service the request.
	 * @param	Handler	$handler
	 * @return	Light\Service\Container	For fluent API.
	 */
	public function setServiceHandler(Handler\Handler $handler)
	{
		$this->serviceHandler = $handler;
		return $this;
	}
	
	/**
	 * Sets an input handler to be used to service the request.
	 * @param	InputHandler	$handler
	 * @return	Light\Service\Container	For fluent API.
	 */
	public function setInputHandler(InputHandler $handler)
	{
		$this->inputHandler = $handler;
		return $this;
	}
	
	/** 
	 * Returns the service authenticator object.
	 * @return Light\Service\Auth\ServiceAuthenticator
	 */
	public function getAuthenticator()
	{
		return $this->authenticator;
	}
	
	public function run(Service $service, $method = NULL)
	{
		$this->service	= $service;
		
		// set an error handler to catch errors as exceptions
		set_error_handler(function($errno, $errstr, $errfile, $errline)
		{
			if (!(error_reporting() & $errno)) return;
			throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
		}, E_ALL ^ E_NOTICE);
		
		array_reverse($this->inputHandlers, true);
		array_reverse($this->outputHandlers, true);
		array_reverse($this->serviceHandlers, true);
		
		if ($_REQUEST['_debug'] == 1)
		{
			$this->debug = true;
		}
		
		try
		{
			if (is_null($this->serviceHandler))
			{
				$this->serviceHandler	= $this->findServiceHandler($service);
			}
			if (is_null($this->serviceHandler))
			{
				throw new Exception\Exception("No service handler found for class <%1>", get_class($service));
			}
		
			// initialize
			self::logger()->debug("Initializing service " . get_class($service));
			$this->serviceHandler->init();
			
			$this->descriptor = $service->getServiceDescriptor();
			
			// input handler
			$inputContentType = $this->request->getHeader('Content-type');
			if (is_null($inputContentType))
			{
				$inputContentType	= "url/encoded";
			}
			else if (($sep = strpos($inputContentType, ";")) !== false)
			{
				$inputContentType = substr($inputContentType, 0, $sep);
			}
			
			if (is_null($this->inputHandler))
			{
				$this->inputHandler	= $this->findInputHandler($inputContentType);
			}
			if (is_null($this->inputHandler))
			{
				throw new ServiceContainerException(array("No input handler found for <%1>", $inputContentType),
													ServiceContainerException::CLIENT_UNSUPPORTED_MEDIA_TYPE);
			}
			
			// find method to be executed
			
			if (is_null($method))
			{
				$methodName	= $this->inputHandler->getMethodName();
			}
			else
			{
				$methodName	= $method;
			}
			$methodArgs	= $this->inputHandler->getMethodParameters();
			$methodDesc	= $this->descriptor->getMethod($methodName);
			if (is_null($methodDesc))
			{
				throw new ServiceContainerException(array("No method descriptor found for <%1>", $methodName),
													ServiceContainerException::CLIENT_FORBIDDEN);
			}
			
			$this->descriptor->__containerSetInvokedMethod($methodDesc);

			// find an output handler
			$outputContentType = $methodDesc->getOutput();
			if (is_null($outputContentType))
			{
				$outputContentType = "text/html";
			}
			$this->outputHandler	= $this->findOutputHandler($outputContentType);
			if (is_null($this->outputHandler))
			{
				throw new Exception\Exception("No output handler found for <%1>", $outputContentType);
			}

			// prepare method call
			$xlatedArgs	= $this->prepareMethodCall($methodName, $methodArgs);

			// authenticate the method call
			$this->authenticator->validate($methodDesc);
			
			// load
			$this->serviceHandler->load(array());
			self::logger()->info(get_class($service) . "::" . $methodName . "()");

			// call the method
			$result = call_user_func_array(array($service, $methodName), $xlatedArgs);
			
			// process returned result
			if (!is_null($processor = $methodDesc->getResultProcessor()))
			{
				$result = $processor->processResult($methodDesc, $result);
			}

			self::logger()->debughi( "Returned: " . print_r( $result, true ) );
			
			// check if the method changed the output type
			$newOutputContentType = $methodDesc->getOutput();
			if (!is_null($newOutputContentType) && $newOutputContentType != $outputContentType)
			{
				$this->outputHandler	= $this->findOutputHandler($newOutputContentType);
			}
			
			// finish
			$this->serviceHandler->finish();
			$this->descriptor->__containerSetInvokedMethod(null);

			// output the result			
			$this->outputHandler->sendResponse($result);
		}
		catch (AuthenticationException $e)
		{
			if (!$e->allowRetry())
			{
				$this->printError($e);
			}
			else
			{
				$this->response->sendBody($e->getMessage());
			}
		}
		catch (\Exception $e)
		{
			$this->printError($e);
		}
	}

	/**
	 * Sends an error to the caller.
	 * @param	Exception $e
	 */
	protected function printError(\Exception $e)
	{
		self::logger()->err($e);
		
		if (!is_null( $this->outputHandler ))
		{
			$this->outputHandler->sendFault($e);
		}
		else
		{
			if ($e instanceof ServiceContainerException)
			{
				$code = $e->getHttpErrorCode();
			}
			else
			{
				$code = 500;
			}
			
			$response = $this->response;
			$response->sendStatus($code);
			$response->setHeader("Content-type", "text/plain");
			$response->sendBody("The service container encountered an error:\n\n" . $e->__toString());
		}
	}

	/**
	 * Finds a service handler for the given service.
	 * @param	Service	$service
	 * @return	Light\Service\Handler	A service handler, if found; otherwise, NULL.
	 */
	protected function findServiceHandler(Service $service)
	{
		foreach($this->serviceHandlers as $baseClass => $handler)
		{
			if (is_a($service, $baseClass))
			{
				return new $handler($this, $service);
			}
		}
		return NULL;
	}

	protected function findInputHandler($contentType)
	{
		$contentType = strtolower($contentType);
		foreach($this->inputHandlers as $ct => $handler)
		{
			if (strtolower($ct) == $contentType)
			{
				return new $handler($this, $this->request, $this->response);
			}
		}
		return NULL;
	}
	
	protected function findOutputHandler($contentType)
	{
		$contentType = strtolower($contentType);
		foreach($this->outputHandlers as $ct => $handler)
		{
			if (strtolower($ct) == $contentType)
			{
				return new $handler($this, $this->request, $this->response);
			}
		}
		return NULL;
	}
	
	/**
	 * Checks if the method name is valid.
	 * If not, this method throws appropriate exceptions.
	 */
	private function prepareMethodCall($methodName, array $arguments)
	{
		if (!method_exists($this->service, $methodName))
		{
			throw new Exception\Exception("Method %1 is not implemented", $methodName);
		}

		$r = new \ReflectionObject($this->service);
		$method = $r->getMethod( $methodName );

		if (!$method->isPublic())
		{
			throw new Exception\Exception("Method %1 is not public", $methodName);
		}
		
		$xlated = $this->translateParameters( $method, $arguments );
		
		return $xlated;
	}

	/**
	 * Translates raw parameters into ones suitable for a method call.
	 *
	 * @param ReflectionMethod $method
	 * @param array $rawParams
	 * @return array
	 */
	protected function translateParameters(\ReflectionMethod $method, array $rawParams)
	{
		$xlatedParams = array();
		
		$i = 0;
		reset( $rawParams );
		foreach( $method->getParameters() as $param )
		{
			if (isset( $rawParams[$param->getName()] ))
			{
				$xlatedParams[$i] = $rawParams[$param->getName()];
			}
			else if (isset($rawParams[$i]))
			{
				$xlatedParams[$i] = $rawParams[$i];
			}
			else if (!$param->isDefaultValueAvailable())
			{
				throw new Exception\Exception("Missing required parameter %1 for method %2", $param->getName(), $method->getName());
			}
			else
			{
				$xlatedParams[$i] = $param->getDefaultValue();
			}
			
			if (!is_null($param->getClass()))
			{
				// the parameter is declaring a typehint class
				$decoder = new DataObjectDecoder();
				$xlatedParams[$i] = $decoder->decode($param->getClass(), $xlatedParams[$i]);
			}
			else if ($param->isDefaultValueAvailable())
			{
				$defval = $param->getDefaultValue();
				$actval = $xlatedParams[$i];
				if (is_bool($defval))
				{
					if ($actval == "true" || $actval == "1")
					{
						$xlatedParams[$i] = true;
					}
					elseif ($actval == "false" || $actval == "0")
					{
						$xlatedParams[$i] = false;
					}
				}
				else if (is_integer($defval))
				{
					if (is_numeric($actval))
					{
						$xlatedParams[$i] = (integer) $actval;
					}
				}
			}
			
			$i++;
		}
		return $xlatedParams;
	}

	/**
	 * @var Light\Log\Logger
	 */
	private static $logger;
	 
	/**
	 * @return Light\Log\Logger
	 */
	private static function logger()
	{
		return !is_null( self::$logger )?
				self::$logger:
				self::$logger = Log\Logger::getLogger(get_class($this));
	}

}