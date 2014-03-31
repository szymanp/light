<?php

namespace Light\UI\Util;

use Light\UI\Component;

/**
 * A class for storing components' environment information for client-side scripts.
 *
 */
class ClientEnvironment
{
	private $target;
	private $alias;
	private $env		= array();
	
	public function __construct(Component $c, $alias = NULL)
	{
		$this->target	= $c;
		$this->alias	= $alias;
	}

	/**
	 * Attaches this client environment to the Scene.
	 * @return Light\UI\Util\ClientEnvironment
	 */
	public function attach()
	{
		\UI_Scene::getInstance()
			->getAttachmentPoints()
			->get(\UI_Scene::AP_CLIENT_ENVIRONMENT)
			->add($this);
		return $this;
	}
	
	public function __set($name, $value)
	{
		$this->env[$name]	= $value;
	}
	
	public function __unset($name)
	{
		unset($this->env[$name]);
	}
	
	public function getName()
	{
		if (is_null($this->alias))
		{
			return $this->target->getName();
		}
		else
		{
			return $this->alias;
		}
	}
	
	/**
	 * Returns the owner of this environment.
	 * @return Light\UI\Component
	 */
	public function getTarget()
	{
		return $this->target;
	}
	
	public function getJsonArray()
	{
		$env = array();
		foreach($this->env as $k => $v)
		{
			if (is_object($v) && method_exists($v, "__toString")) $v = (string) $v;
			$env[$k] = $v;
		}
		return $env;
	}
	
	public function toJson()
	{
		$name = $this->getName();
		$env  = $this->getJsonArray();
		
		$str = "Light.Env.CE['" . $name . "'] = ";
		$str .= json_encode($env) . ";";
		
		return $str;
	}
	
	public function __toString()
	{
		return $this->toJson();
	}
}
