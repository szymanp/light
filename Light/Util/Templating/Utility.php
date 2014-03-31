<?php

namespace Light\Util\Templating;
use Light\UI\Util\Href;
use Light\UI;
use Light\Util\Controller\Controller;

/**
 * A collection of utility methods for use in templates.
 */
class Utility
{
	/**
	 * @return Light\Util\Templating\Utility_Href
	 */
	public function href()
	{
		return new Utility_Href();
	}
	
	/**
	 * @return Light\Util\Templating\Utility_Env
	 */
	public function env()
	{
		return new Utility_Env();
	}
	
}

class Utility_Href
{
	public function page($page, $params = NULL, $plugin = NULL)
	{
		$href = Href::toClass($page, $params, $plugin);
		return (string) $href;
	}
	
	public function component(UI\Component $c, $method, $params = NULL, $plugin = "Nested")
	{
		$href = Href::toComponent($c, $method, $params, $plugin);
		return (string) $href;
	}
	
	public function post_component(UI\Component $c, $method, $params = NULL, $plugin = "Nested")
	{
		$href = Href::toComponent($c, $method, $params, $plugin);
		$href->setSuppressRequestState();
		return (string) $href;
	}
}

class Utility_Env
{
	public function uriArgsAsObject($class = "")
	{
		if (empty($class))
		{
			$class = Controller::getInstance()->getInvokedClass();
		}
		if ($class[0] != "\\") $class = "\\" . $class;

		$a = array();
		\UI_Scene::getInstance()->appendUriArguments($class, $a);
		return json_encode($a, JSON_FORCE_OBJECT);
	}
}