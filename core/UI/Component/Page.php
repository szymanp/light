<?php

namespace Light\UI\Component;
use Light\UI\ViewContext;

use Light\Util;
use Light\Util\Controller;
use Light\Exception;

class Page extends ContentControl
{
	public function __construct($name=NULL)
	{
		if (is_null($name)) {
			$name = get_class($this);
		}
		parent::__construct($name);
	}

	public function setParameters(array $params)
	{
		foreach($params as $key => $value)
		{
			if ($this->hasProperty($key))
			{
				$this->setProperty($key,$value);
			} 
			else if (method_exists($this,$m = "set" . $key))
			{
				$this->$m($value);
			}
		}
	}
	
	public function redirectTo($class,array $params = array())
	{
		$controller = Controller\Controller::getInstance();
		ViewContext::getInstance()->appendUriArguments($class,$params);
		$url = $controller->getHref($class, $params, "Frontend");
		if (is_null($url))
		{
			throw new Exception\Exception("No URL found for class %1", $class);
		}
		$this->getView()->redirectToUrl($url);
	}
}
