<?php

namespace Light\UI\Component;
use Light\UI\Container;
use Light\UI\Framework\PropertyProperty;
use Light\UI\Framework\Renderer;

/**
 * @property boolean	Visible
 */
abstract class Renderable extends Container
{
	/** @var Light\UI\Framework\Renderer */
	private $renderer;
	
	/** @var boolean */
	protected $visible = true;
	
	protected function construct()
	{
		parent::construct();

		$this->renderer = new Renderer($this);
		
		PropertyProperty::create($this, "Visible", "boolean")
		->variable("visible");
	}

	/**
	 * Returns the renderer for this component.
	 * @return Light\UI\Framework\Renderer
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}
	
	public function setVisible($v)
	{
		$this->visible = $v;
		return $this;	// fluent API
	}

	/**
	 * Returns true if the component will be rendered.
	 * @return boolean
	 */	
	public function isVisible()
	{
		return $this->visible;
	}
	
	protected function render()
	{
		if (!$this->isVisible()) {
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
	
	/**
	 * Renders the component and returns as string.
	 * @return	string
	 */
	public function __toString() {
		ob_start();
		try
		{
			$this->render();
		}
		catch (\Exception $e)
		{
			print( "<pre>" . $e . "</pre>" );
			$this->getView()->exceptionCaught($e);
		}
		$c = ob_get_contents();
		ob_end_clean();
		return $c;
	}
}
