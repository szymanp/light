<?php

namespace Light\UI\Framework;

use Light\Util\Templating\TemplateEngineRegistry;
use Light\Util\Templating\TemplateEngine;

class Renderer
{
	/** @var Light\UI\Framework\Element */
	private $owner;

	/**
	 * @var string
	 */
	private $templateEngineName = null;
	
	/**
	 * @var string
	 */
	private $resourceFileName;
	
	public function __construct(Element $owner)
	{
		$this->owner = $owner;
	}
	
	/**
	 * Sets the filename of the template to be rendered.
	 * @param string	$filename
	 * @return Light\UI\Framework\Renderer	For fluent API
	 */
	public function setResourceFileName($filename)
	{
		$this->resourceFileName = $filename;
		return $this;
	}
	
	/**
	 * Sets a template engine to use for rendering this template.
	 * @param string	$name	A template engine name known by the TemplateEngineRegistry.
	 * @return Light\UI\Framework\Renderer	For fluent API
	 */
	public function setTemplateEngineName($name)
	{
		$this->templateEngineName = $name;
		return $this;
	}

	/**
	 * Returns the full path to the template file name.
	 * @return string
	 */
	public function getTemplateFileName()
	{
		if (is_null($this->resourceFileName))
		{
			if (is_null($this->templateEngineName))
			{
				$file = $this->owner->getResourceFinder()->getDefaultResourceFile($this->owner);
			}
			else
			{
				$suffix = "." . strtolower($this->templateEngineName) . ".html";
				$file = $this->owner->getResourceFinder()->getDefaultResourceFile($this->owner, $suffix);
			}
		}
		else
		{
			$file = $this->owner->getResourceFinder()->getResourceFile($this->owner, $this->resourceFileName);
		}
		
		return $file;
	}
	
	/**
	 * Returns true if a template engine has been setup for this renderer.
	 * @return boolean
	 */
	public function hasTemplateEngine()
	{
		return !is_null($this->templateEngineName);
	}

	public function render()
	{
		$file = $this->getTemplateFileName();
	
		if (is_null($this->templateEngineName))
		{
			$self = $this->owner;
			include($file);
		}
		else
		{
			$tpl = TemplateEngineRegistry::getInstance()->get($this->templateEngineName);
			$tpl->loadTemplateFromFile($file);
			$tpl->setVariable(TemplateEngine::ROOT_VARIABLE, $this->owner);
			$tpl->render(true);
		}
	}
}