<?php

namespace Light\UI\Framework;

use Light\UI\Framework\Persist\StoreManager;

use Light\UI\View\AbstractView;
use Light\UI\Framework\Input\Request;
use Light\UI\Framework\Input\RequestHandler;
use Light\UI\Framework\Input\RequestRouter;
use Light\UI\Framework\Input\RequestRouterListener;
use Light\UI\Framework\LifecycleObject;
use Light\UI\ViewContext;
use Light\UI\Component;
use Light\UI\View\RedirectException;
use Light\UI\Util\Href;
use Light\Util\Controller\Controller;
use \Exception;

class ComponentMachine implements RequestRouterListener
{
	/** @var Light\UI\View\AbstractView */
	private $view;
	/** @var Light\UI\Framework\Input\Request */
	private $nextRequest;
	/** @var Light\UI\Framework\LifecycleObject */
	private $nextObject;
	
	/**
	 * List of exceptions caught during execution.
	 * @var Exception[]
	 */
	private $exceptions = array();
	
	/**
	 * @var string
	 */
	private $renderedOutput = NULL;
	
	public function __construct(AbstractView $view)
	{
		$this->view = $view;
	}
	
	final public function run(LifecycleObject $object, Request $request)
	{
		$html = NULL;

		$this->nextObject	= $object;
		$this->nextRequest	= $request;
		
		$storeManager = ViewContext::getInstance()->getStoreManager();
		$storeManager->open();
		
		while(!is_null($c = $this->nextObject))
		{
			$requestData = $this->nextRequest;

			if (empty($requestData))
			{
				$requestData = new Request();
			}
			
			$this->nextRequest	= NULL;
			$this->nextObject	= NULL;
			
			if ($c instanceof Component)
			{
				$c->attachToView($this->view);
			}
	
			try
			{
				if (($viewName = $this->view->getName()) == "")
				{
					// the view doesn't have a name, therefore we treat the component as the request root
					$requestRouter = $this->getRequestRouter($c);
				}
				else
				{
					// the view is also part of the request handling tree
					$requestRouter = $this->getRequestRouter($this->view);
				}
				
				// @todo restore component from store manager
				
				$requestRouter->addListener($this);
				$requestRouter->routeState($requestData);
				$this->exceptions = array_merge($this->exceptions, $requestRouter->getExceptions());
				$requestRouter->routeAction($requestData);
					
				// If there was no state/actions, the component might not be loaded yet.
				// Normally, the component will be LOADED before running an action.
				$c->setLifecycleStage("LOAD");
				
				$storeManager->closeClientStores();
				$c->persistComponent($storeManager, true);

				ob_start();
				try
				{
					$c->setLifecycleStage("RENDER");
					$this->renderedOutput = ob_get_contents();
				}
				catch (\Exception $e)
				{
					ob_end_clean();
					throw $e;
				} 
				ob_end_clean();
				
				if (!empty( $this->exceptions ))
				{
					$this->caughtExceptions($this->exceptions);
					$this->exceptions = array();
				}
			}
			catch (RedirectException $e)
			{
				$storeManager->closeClientStores();
				$c->persistComponent($storeManager, true);
				
				$this->redirect($e);
			}
			catch (Exception $e)
			{
				// invoke $c->finish() only if the component has been loaded
				if ($c->getLifecycleStage() >= LifecycleObject::STATE_LOADED)
				{
					$c->finish();
				}
				
				$this->uncaughtException($e);
			}
			
			$c->finish();
			$c->persistComponent($storeManager, false);
		}
		
		$storeManager->closeServerStores();
	}
	
	/**
	 * Sets the next LifecycleObject to be executed.
	 * @param LifecycleObject	$object
	 * @param Request			$request
	 */
	final public function setNextObject(LifecycleObject $object, Request $request = null)
	{
		$this->nextObject = $object;
		$this->nextRequest = $request;
	}
	
	/**
	 * Returns the next LifecycleObject to be executed.
	 * @return Light\UI\Framework\LifecycleObject
	 */
	final public function getNextObject()
	{
		return $this->nextObject;
	}
	
	final public function setRenderedOutput($t)
	{
		$this->renderedOutput = $t;
	}
	
	final public function getRenderedOutput()
	{
		return $this->renderedOutput;
	}
	
	/**
	 * Adds an exception that was caught and suppressed by the component being executed.
	 * @param Exception $e
	 */
	final public function addException(Exception $e)
	{
		$this->exceptions[] = $e;
	}
	
	/**
	 * Handles a redirection to another component or URL.
	 *
	 * Override this method to implement your own redirection functionality.
	 *
	 * @param RedirectException	$e
	 */
	protected function redirect(RedirectException $e)
	{
		$target = $e->getTarget();

		if ($target instanceof LifecycleObject)
		{
			$this->nextObject = $target;
			Controller::getInstance()->notifyInvokedClassChange(get_class($target),array());
		}
		else if ($target instanceof Href)
		{
			Header("Location: " . $target->__toString());
			$this->setRenderedOutput(null);
		}
		else if (is_string($target))
		{
			Header("Location: " . $target);
			$this->setRenderedOutput(null);
		}
		else
		{
			throw new \Light\Exception\Exception("Invalid redirection target: %1", $target);
		}
	}
	
	/**
	 * Handles an exception caught during component execution that wasn't caught by any component code.
	 *
	 * Override this method to implement your own exception handling.
	 *
	 * @param Exception $e
	 */
	protected function uncaughtException(Exception $e)
	{
		throw $e;
	}
	
	/**
	 * Handles exceptions caught during component execution.
	 *
	 * @param Exception[] $e
	 */
	protected function caughtExceptions(array $exceptions)
	{
		foreach($exceptions as $e)
		{
			$this->view->exceptionCaught($e);
		}
	}
	
	protected function getRequestRouter(RequestHandler $handler)
	{
		return new RequestRouter($handler);
	}

	// RequestRouterListener implementation

	final public function beforeSetState(RequestHandler $target)
	{
	}
	
	final public function afterSetState(RequestHandler $target)
	{
		$target->setLifecycleStage("INIT");
	}
	
	final public function beforeRunAction(RequestHandler $target)
	{
		$target->setLifecycleStage("LOAD");
	}
	
	final public function afterRunAction(RequestHandler $target)
	{
	}
	
}
