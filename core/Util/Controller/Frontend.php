<?php

namespace Light\Util\Controller;
use Light\UI;
use Light\Util\Templating\TemplateEngineRegistry;
use Light\Autoloader;

class Frontend extends Plugin
{
	protected $classSuffix = ".html";
	protected $templateEngines = array("twig.html" => "Twig");
	private $views = array("" => "Light\UI\View");

	public function getDefaultView()
	{
	    return $this->views[""];
	}

	public function setDefaultView($class)
	{
	    $this->views[""] = $class;
	}
	
	/**
	 * Registers a view class for a subset of classes.
	 * @param	string	$classPrefix	Prefix used to match the component's class name.
	 * @param	string	$viewClass		View class.
	 * @return	Controller	For fluent API.
	 */
	public function setViewFor($classPrefix, $viewClass)
	{
		if ($classPrefix[0] == "\\")
		{
			$classPrefix = substr($classPrefix, 1);
		}
		$this->views[$classPrefix] = $viewClass;
		return $this;
	}
	
	/**
	 * Registers a template engine to be used for files with a given extension.
	 * @param	string	$extension		File extension, e.g. "twig.html"
	 * @param	string	$engineName		Engine name known by the TemplateEngineRegistry.
	 */
	public function addTemplateEngine($extension, $engineName)
	{
		$this->templateEngines[$extension] = $engineName;
		return $this;
	}
	
	public function invoke(Request $request)
	{
		$path = $request->getDecodedUri();
		$params = $_GET;
		
		// sort the views array
		krsort($this->views);
		
		$class = $this->getClassMap()->findClass($path);
		
		if (class_exists($class))
		{
			$this->invokeClass($class, $params);
			return;
		}
		
		foreach($this->templateEngines as $extension => $engine)
		{
			$template = Autoloader::find($class, $extension);
			if ($template)
			{
				$this->invokeTemplate($template, $engine, $params);
				return;
			}
		}

		throw new \Exception("No handler found for <$class> at <$path>.");
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
	
	protected function invokeClass($class, array $params)
	{
		if (!is_subclass_of($class,"Light\UI\Component\Page"))
		{
			throw new \Exception("Not a Page.");
		}
		
		$instance = new $class();
//		$instance->setParameters($params);
		
		$this->getController()->notifyInvokedClassChange($class,$params);
		
		$view = $this->getView($instance);
		$view->run($instance);
	}
	
	protected function invokeTemplate($template, $engineName, array $params)
	{
		$engine = TemplateEngineRegistry::getInstance()->get($engineName);
		$engine->loadTemplateFromFile($template);
		$engine->setVariable("arguments", $params);
		$engine->render(true);
	}
	
	protected function getView($forPage)
	{
		$pageClass = get_class($forPage);
		
		foreach($this->views as $prefix => $class)
		{
			if (substr($pageClass, 0, strlen($prefix)) == $prefix)
			{
				break;
			}
		}
		return new $class();
	}
}