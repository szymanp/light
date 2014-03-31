<?php

namespace Light\UI\Util;
use Light\Exception\Exception;
use Light\Util\HrefString;

use Light\Util\Controller\Nested;
use Light\Util\Controller\Backend;
use Light\Util\Controller\Controller;
use Light\UI\ViewContext;
use Light\UI;

/**
 * Represents an URI that defers the generation of the actual URI string until render time.
 * 
 */
class Href
{
	private $asynchronous =	false;
	private $class;
	private $uri;
	private $component;
	private $action;
	private $params = array();
	private $callback;
	private $javascript = false;
	private $suppressRequestState = false;
	private $plugin = null;
	private $serviceMethod = null;
	private $dynamic;
	
	const SELF = "__SELF__";

	public static function dynamic($callback)
	{
		$href = new self();
		$href->dynamic = $callback;
		return $href;
	}
	
	public static function toSelf($params = NULL, $plugin = null)
	{
		return self::toClass(self::SELF, $params, $plugin);
	}
	
	public static function toClass($class, $params = NULL, $plugin = "Frontend")
	{
		$href = new self();
		$href->class = $class;
		if ($params instanceof \Closure)
		{
			$href->callback = $params;
		} 
		else if (is_array($params))
		{
			$href->params = $params;
		}
		$href->plugin = $plugin;
		return $href;
	}
	
	/**
	 * Creates a link to a service method provided by a top-level service.
	 * @param string	$class
	 * @param string	$method
	 * @param array		$params
	 * @param string 	$plugin
	 * @return Light\UI\Util\Href
	 */
	public static function toService($class, $method, $params = NULL, $plugin = "Backend")
	{
		$href = self::toClass($class, $params, $plugin);
		$href->serviceMethod = $method;
		return $href; 
	}
	
	/**
	 * Creates a link to a service method provided by a nested component.
	 * @param Light\UI\Component	$c		
	 * @param string 				$method
	 * @param array					$params
	 * @param string				$plugin
	 * @return Light\UI\Util\Href
	 */
	public static function toComponent(UI\Component $c, $method, $params = NULL, $plugin = "Nested")
	{
		$class = get_class($c->getRootContainer());
		$href = self::toClass($class, $params, $plugin);
		$href->component		= $c;
		$href->serviceMethod	= $method;
		return $href; 
	}
	
	public static function toUri($uri, $params, $plugin = NULL)
	{
		$href = new self();
		$href->uri = $uri;
		if ($params instanceof \Closure)
		{
			$href->callback = $params;
		} 
		else if (is_array($params))
		{
			$href->params = $params;
		}
		$href->plugin = $plugin;
		return $href;
	}
	
	public static function toAction(UI\Component $c, $action, $params = array(), $plugin = "Frontend")
	{
		$href = new self();
		$href->component = $c;
		$href->action = $action;
		if ($params instanceof \Closure)
		{
			$href->callback = $params;
		} 
		else if (is_array($params))
		{
			$href->params = $params;
		}
		else
		{
			$href->params = array($params);
		}
		$href->plugin = $plugin;
		return $href;
	}
	
	public function setSuppressRequestState($suppress = true)
	{
		$this->suppressRequestState = $suppress;
		return $this;
	}
	
	public function setJavascript($js = true)
	{
		$this->javascript = $js;
		return $this;
	}
	
	public function setClass($class)
	{
		$this->class = $class;
		return $this;
	}
	
	public function setParam($name,$value)
	{
		$this->params[$name] = $value;
		return $this;
	}
	
	/**
	 * Sets the controller plugin that will be used to handle this request.
	 * @param string	$plugin	Plugin name.
	 * @return Light\UI\Util\Href	For fluent API.
	 */
	public function setPlugin($plugin)
	{
		$this->plugin = $plugin;
		return $this;
	}
	
	public function setAsynchronous($asynchronous)
	{
		$this->asynchronous = $asynchronous;
		return $this;
	}
	
	/** @return string */
	public function evaluate($value = NULL)
	{
		// dynamic creation of a href
		if (!is_null($dynamic = $this->dynamic))
		{
			$href = $dynamic($this,$value);
			if ($href instanceof Href)
			{
				return $href->evaluate($value);
			}
			elseif ($href instanceof HrefString)
			{
				return $href;
			}
			else
			{
				return new HrefString((string) $href, false);
			}
		}
		
		$callback = $this->callback;
		if ($callback != null)
		{
			$callback($this,$value);
		}
		
		if ($this->class != null)
		{
			$class = $this->class;
			if ($class == self::SELF)
			{
				$class = Controller::getInstance()->getInvokedClass();
			}

			$params = $this->params;
			if (!$this->suppressRequestState)
			{
				ViewContext::getInstance()->appendUriArguments($class, $params);
			}

			if ($this->component && $this->serviceMethod)
			{
				$params[Nested::METHOD] 	= $this->serviceMethod;
				$params[Nested::COMPONENT]	= $this->component;
			}
			else if ($this->serviceMethod)
			{
				$params[Backend::METHOD]	= $this->serviceMethod;
			}
			
			$uri = Controller::getInstance()->getHref($class, $params, $this->plugin);
		}
		else if ($this->uri != null)
		{
			$uri = $this->uri;
		}
		else if ($this->action !== null)
		{
			$class = Controller::getInstance()->getInvokedClass();
			
			if ($this->asynchronous)
			{
				$params[Nested::METHOD]		= "handleEvent";
				$params[Nested::COMPONENT]	= $this->component;
				$params["name"]				= $this->action;
				$params["data"]				= current($this->params);
			}
			else
			{
				$actionStr = $this->component->createAction($this->action);
				if (empty($this->params))
				{
					$actionPar = "1";
				}
				else
				{
					$actionPar = current($this->params);
				} 
				$params[$actionStr] = $actionPar;
			}
			
			if (!$this->suppressRequestState)
			{
				ViewContext::getInstance()->appendUriArguments($class, $params);
			}
			
			$uri = Controller::getInstance()->getHref($class, $params, $this->plugin);
		}
		
		if (is_null($uri))
		{
			throw new Exception(
				"An URI could not be constructed (%1, %2, %3, %4)",
				$this->class,
				$this->uri,
				$this->event,
				$this->plugin);
		}
		
		if ($this->javascript)
		{
			$uri = "window.location.href='" . htmlentities($uri, ENT_QUOTES, "UTF-8") . "';";
			$hrefstr = new HrefString($uri, true);
		}
		else
		{
			$hrefstr = new HrefString($uri, false);
		}
		
		return $hrefstr;
	}
	
	public function __toString()
	{
		try
		{
			$str = $this->evaluate()->__toString();
		}
		catch (\Exception $e)
		{
			return $e->getMessage();
		}
		return $str;
	}
}
