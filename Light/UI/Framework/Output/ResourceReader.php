<?php

namespace Light\UI\Framework\Output;
use Light\Util\Templating\TemplateEngineRegistry;

use Light\UI;

class ResourceReader
{
	/** @var Light\UI\Component */
	private $owner;
	private $resources = array();
	
	public function __construct(UI\Component $owner)
	{
		$this->owner = $owner;
	}
	
	public function loadXml($file)
	{
		$xml = simplexml_load_file($file);
		
		foreach($xml as $res)
		{
			if ($res->getName() != "resource") continue;

			$resource = new ResourceReader_Resource();
			$resource->content 		= $res->__toString();
			$resource->templateType	= (string) $res->attributes()->processor;
			
			if (!is_null($name = $res->attributes()->name))
			{
				$this->resources[(string) $name] = $resource;
			}
			if (!is_null($point = $res->attributes()->{'attach-to'}))
			{
				$this->attachTo((string) $point, $resource);
			}
		}
	}
	
	/**
	 * Returns a resource by its name.
	 * @param string	$name
	 * @return mixed
	 */
	public function getResource($name, array $args = array())
	{
		$r = $this->resources[$name];
		return $r->getProcessedContent($this->owner, $args);
	}
	
	/**
	 * Returns a resource by its name packaged as an object, for lazy evaluation.
	 * @param string	$name
	 * @return Light\UI\Framework\Output\ResourceReader_StandaloneResource
	 */
	public function getStandaloneResource($name, array $args = array())
	{
		return new ResourceReader_StandaloneResource($this->resources[$name], $this->owner, $args);
	}
	
	private function attachTo($point, ResourceReader_Resource $resource)
	{
		$p = explode(":", $point, 2);
		if (count($p) == 1)
		{
			$target = "self";
			$name = $p[0];
		}
		else
		{
			$target = $p[0];
			$name = $p[1];
		}
		
		$content = new ResourceReader_StandaloneResource($resource, $this->owner, array());
		
		if ($target == "self")
		{
			\UI_Scene::getInstance()->getAttachmentPoints()->get($name)->add($content, $this->owner);
		}
		else
		{
			throw new \Light\Exception\Exception("Attachment target <%1> is not known in point for <%2>", $target, $this->owner->getLocalName());
		}
	}
}

class ResourceReader_Resource
{
	public $content;
	public $templateType;
	
	public function getProcessedContent(UI\Component $owner, array $args = array())
	{
		$content = $this->content;
		
		if (!empty($this->templateType))
		{
			$te = TemplateEngineRegistry::getInstance()->get($this->templateType);
			$te->loadTemplateFromString($content);
			$te->setVariable("this", $owner);
			foreach($args as $key => $value)
			{
				$te->setVariable($key, $value);
			}
			$content = $te->render(false);
		}
		
		return $content;
	}
	
}

class ResourceReader_StandaloneResource
{
	private $resource;
	private $owner;
	private $args = array();
	
	public function __construct(ResourceReader_Resource $resource, UI\Component $owner, array $args)
	{
		$this->resource = $resource;
		$this->owner	= $owner;
		$this->args		= $args;
	}
	
	public function setVariable($key, $value)
	{
		$this->args[$key] = $value;
		return $this;
	}
	
	public function __toString()
	{
		return $this->resource->getProcessedContent($this->owner, $this->args);
	}
}