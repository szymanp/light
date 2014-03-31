<?php
namespace Light\Util\Controller;
use Light\UI\View\ComponentView;

use Light\UI;

use Light\Exception\InvalidParameterType;

use Light\Exception\Exception;

use Light\Service;
use Light\Service\Handler;

class Nested extends Backend
{
	const METHOD	= "_method";
	const COMPONENT	= "_component";
	
	public function invoke(Request $request)
	{
		$path = $request->getDecodedUri();
		
		$uripath = $path;
		$class	= $this->getClassMap()->findClassEx($uripath);
		if (!class_exists($class))
		{
			throw new \Exception("No handler found for <$path>.");
		}

		$pos = strrpos($uripath, "/");
		if ($pos === false)
		{
			throw new \Exception("No method specified");
		}
		$methodName	= substr($uripath, $pos+1);
		$compoPath	= explode("/", substr($uripath, 0, $pos));

		$this->invokeClass($class, $compoPath, $methodName);
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
		
		// component
		if (isset($params[self::COMPONENT]))
		{
			$c = $params[self::COMPONENT];
			unset($params[self::COMPONENT]);
			
			if (!($c instanceof UI\Component))
			{
				throw new InvalidParameterType(self::COMPONENT, $c, "Light\UI\Component");
			}
			
			$cp = $c->getLocalName();
			
			// include all except the root container
			while (!is_null($c = $c->getContainer()))
			{
				if (!$c->hasContainer()) break;
				$cp = $c->getLocalName() . "/" . $cp;
			}
			
			$uri .= "/" . $cp;
		}
		else
		{
			throw new Exception("Component to invoke was not specified");
		}
		
		// method
		if (isset($params[self::METHOD]))
		{
			$uri .= "/" . $params[self::METHOD];
			unset($params[self::METHOD]);
		}
		else
		{
			throw new Exception("Method to invoke was not specified");
		}
		
		$uri .= $this->getHrefParams($params);
				
		return $uri;
	}
	
	protected function invokeClass($class, $path, $method)
	{
		if (!is_subclass_of($class,"\Light\UI\Component"))
		{
			throw new \Exception("<$class> not a Component");
		}
		
		$view = new ComponentView();
		$view->setRootClass($class);
		$view->setTargetPath($path);
		
		// initialize the view
		$view->initialize();
		$view->setAsynchronousRequest(true);

		// we need to initialize $root and then just run the $instance
		$root = $view->getRoot();
		$instance = $view->getTargetComponent();
		
		$handler = new Handler\NestedComponent($this->getContainer(), $root, $instance);

		$this->getContainer()->setServiceHandler($handler);
		$this->getContainer()->run($instance, $method);
	}
}