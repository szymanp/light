<?php

namespace Light\UI\View;

use Light\UI\Util\Href;

use Light\UI\Component;
use Light\UI\Container;
use Light\UI\ViewContext;
use Light\UI\Framework\Element;
use Light\UI\Framework\Input\Request;
use Light\Exception;

/**
 * A base class for all views.
 * 
 * A view is a container in which UI components can be executed. A page generated
 * by PHP can contain one or more views, allowing UI components to be inserted into
 * multiple locations on a page.
 *
 */
abstract class AbstractView extends Element
{
	/** @var string */
	protected $name;
	
	/** @var Exception[] */
	protected $exceptions = array();

	private $asynchronousRequest = false;

	/**
	 * @param	string	$name	Name of this View.
	 */
	public function __construct($name = "")
	{
		parent::__construct();

		$this->name	= $name;
		ViewContext::getInstance()->registerView($this, $name);
	}
	
	/**
	 * Informs the <kbd>View</kbd> that this is an asynchronous (AJAX) request.
	 * @param boolean	$async
	 * @return Light\UI\View\AbstractView	For fluent API.
	 */
	public function setAsynchronousRequest($async = true)
	{
		$this->asynchronousRequest = $async;
		return $this;
	}

	/**
	 * Checks whether this <kbd>View</kbd> is invoked for an asynchronous request.
	 * @return boolean
	 */
	public function isAsynchronousRequest()
	{
		return $this->asynchronousRequest;
	}
	
	/**
	 * Returns then name of this View.
	 * @return	string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Runs and renders the View.
	 * @param Container	$c
	 * @param Request	$r
	 */	
	abstract public function run(Container $c, Request $r);

	
	/**
	 * Notifies the view that an exception has been caught during component processing. 
	 * @param \Exception $e
	 */
	public function exceptionCaught(\Exception $e)
	{
		$this->exceptions[] = $e;
	}
	
	// redirections

	public function redirectToComponent(Container $c)
	{
		throw new RedirectException($c);
	}
	
	/**
	 * @param	string|Light\UI\Util\Href	$url
	 */
	public function redirectToUrl($url)
	{
		throw new RedirectException($url);
	}
	
	/**
	 * Reloads the current component but without any actions.
	 */
	public function reload()
	{
		$url = Href::toSelf();
		$this->redirectToUrl($url);
	}
}