<?php

namespace Light\UI;

use Light\UI\Util\ResourceFinder;
use Light\UI\Framework\Persist;
use Light\UI\Framework\Listener;
use Light\UI\Framework\Renderer;
use Light\UI\Framework\LifecycleObject;
use Light\Util\Templating\TemplateEngineRegistry;
use Light\Util\Templating\TemplateEngine;
use \Exception;

/**
 * A Template is visual component that cannot accept user input through events
 */
class Template extends LifecycleObject
{
	/** @var Light\UI\Framework\Renderer */
	private $renderer;
	
	/**
	 * Is the rendering of this template executed by the controller?
	 * @var boolean
	 */
	private $renderedByController = false;
	
	/**
	 * @param string	$name
	 * @param array		$args
	 */
	public function __construct($name = null, array $args = null)
	{
		$name = is_null($name)?get_class($this):$name;
		$this->renderer = new Renderer($this);
		
		parent::__construct($name);

		if (is_array($args))
		{
			$this->setArguments($args);
			$this->setLifecycleStage("init");
		}
	}
	
	/**
	 * @param array	$args
	 */
	public function setArguments(array $args)
	{
		foreach($args as $name => $arg)
		{
			if ($this->__isset($name))
			{
				$this->__set($name, $arg);
			}
		}
	}
	
	/**
	 * Returns the renderer for this template.
	 * @return Light\UI\Framework\Renderer
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}
	
	/**
	 * Sets a template engine to use for rendering this template.
	 * @param string	$name	A template engine name known by the TemplateEngineRegistry.
	 */
	public function setTemplateEngine($name = null)
	{
		$this->getRenderer()->setTemplateEngineName($name);
	}
	
	/**
	 * Sets whether rendering of this template is performed by an owning class.
	 * If this is set to true, then calling render() will not output any data.
	 * @param boolean	$v
	 */
	public function setRenderedByController($v = true)
	{
		$this->renderedByController = $v;
	}
	
	/**
	 * @param string	$name	Template file name to render.
	 */
	public function render($name = NULL)
	{
		if ($this->renderedByController)
		{
			return;
		}
		
		if ($this->getRenderer()->hasTemplateEngine())
		{
			$this->getRenderer()->render();
		}
		else
		{
			include($this->getRenderer()->getTemplateFileName());
		}
	}

	// RequestHandler interface implementation (partial)
	
	public function getRequestHandler($name, $index = null)
	{
		// a component does not have any children - only a container does
		return null;
	}

}
