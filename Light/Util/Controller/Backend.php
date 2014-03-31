<?php
namespace Light\Util\Controller;
use Light\Service;

class Backend extends Plugin
{
	const METHOD	= "_method";
	
	private $container;
	
	public function __construct($name = NULL, Controller $ctrl = NULL)
	{
		parent::__construct($name, $ctrl);
		$this->container = new Service\Container();
	}

	/**
	 * @return	Light\Service\Container
	 */
	public function getContainer()
	{
		return $this->container;
	}

	public function invoke(Request $request)
	{
		$path = $request->getDecodedUri();

		$pos = strrpos($path, "/");
		if ($pos === false)
		{
			throw new \Exception("No method specified");
		}
		
		$classPath	= substr($path, 0, $pos);
		$methodName	= substr($path, $pos+1);
	
		$class = $this->getClassMap()->findClass($classPath);
		
		if (class_exists($class))
		{
			$this->invokeClass($class, $methodName);
		}
		else
		{
			throw new \Exception("No handler found for <$class>.");
		}
	}
	
	/**
	 * Returns an URL for the specified class.
	 * @param string	$class	Class name.
	 * @param array		$params	List of parameters to pass to the class.
	 * @return string	An URL for the specified class, if it was found;
	 *					otherwise, NULL.
	 */
	public function getHref($class, array $params = array())
	{
		$uri = $this->getClassMap()->findURI($class);
		
		if (is_null($uri))
		{
			return NULL;
		}
		
		if (isset($params[self::METHOD]))
		{
			$uri .= "/" . $params[self::METHOD];
			unset($params[self::METHOD]);
		}
		else
		{
			$uri .= "/index";
		}
		
		$uri .= $this->getHrefParams($params);
				
		return $uri;
	}
	
	public function getHrefParams(array $params)
	{
		$uri = "";
		
		$first = true;
			
		foreach($params as $key => $value)
		{
			$uri .= $first?"?":"&";
			$first = false;
			
			$uri .= htmlspecialchars($key) . "=" . htmlspecialchars((string) $value);
		}
		
		return $uri;
	}	
	
	protected function invokeClass($class, $method)
	{
		$refl = new \ReflectionClass($class);
		if (!$refl->implementsInterface("Light\Service\Service"))
		{
			throw new \Exception("Not a Service.");
		}
		
		$instance = new $class($refl->getName());
		
		$this->getController()->notifyInvokedClassChange($class,array());

		$this->container->run($instance, $method);
	}
}