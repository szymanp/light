<?php

namespace Light\UI\Framework\Input;

/**
 * Distributes the information in the Request object to the appropriate RequestHandlers. 
 * 
 * 
 *
 */
use Light\Exception\Exception;

class RequestRouter
{
	/** @var Light\UI\Framework\Input\RequestHandler */
	private $root;
	
	/**
	 * List of exceptions thrown during processing.
	 * @var \Exception[]
	 */
	private $exceptions = array();
	
	/**
	 * @var RequestRouterListener[]
	 */
	private $listeners = array();
	
	public function __construct(RequestHandler $root)
	{
		$this->root = $root;
	}
	
	public function addListener(RequestRouterListener $l)
	{
		$this->listeners[] = $l;
	}
	
	public function routeState(Request $request)
	{
		if (!is_null($state = $request->getState()))
		{
			$this->feedStateToTarget($state, $this->root);
		}
	}
	
	/**
	 * Returns a list of exceptions that were caught when routing state information.
	 * @return \Exception[]
	 */
	public function getExceptions()
	{
		return $this->exceptions;
	}
	
	private function feedStateToTarget(StateUnit $state, RequestHandler $handler)
	{
		try
		{
			foreach($this->listeners as $l)
			{
				$l->beforeSetState($handler);
			}
			
			// When assigning state to a request handler, some data units might not represent
			// values for the handler's properties, but a default value for one of the handler's
			// children. For these cases, we expect the handler's setRequestHandlerState
			$childComponentDefaults = array();
			
			foreach($state->getIterator() as $dataUnit)
			{
				$found = $handler->setRequestHandlerState($dataUnit);
				if (!$found)
				{
					$childComponentDefaults[] = $dataUnit;
				}
			}
			
			foreach($this->listeners as $l)
			{
				$l->afterSetState($handler);
			}
			
			foreach($childComponentDefaults as $dataUnit)
			{
				$child = $handler->getRequestHandler($dataUnit->getName(), null);
				if (is_null($child))
				{
					throw new Exception("The state property '%1' could not be assigned to neither ".
										"the intended handler or as a default value for its child.",
										$dataUnit->getName());
				}
				
				$defaultValueDataUnit = new DataUnit("Value", $dataUnit->getValue());
				$child->setRequestHandlerState($defaultValueDataUnit);
			}
			
			foreach($state->getChildStateIterator() as $key => $stateUnit)
			{
				$name = $key;
				$index = null;
				$this->extractName($name, $index);
				
				$child = $handler->getRequestHandler($name, $index);
				if (is_null($child))
				{
					continue;
				}
				
				$this->feedStateToTarget($stateUnit, $child);
			}
		}
		catch (\Exception $e)
		{
			$this->exceptions[] = $e;
		}
	}
	
	/**
	 * @param string	$name
	 * @param integer	$index
	 */
	private function extractName(&$name, &$index)
	{
		$pos = strrpos($name, '_');
		if ($pos === false) return;
		
		$idx = substr($name, $pos+1);
		if (is_numeric($idx))
		{
			$name = substr($name, 0, $pos);
			$index = $idx;
		}
	}
	
	public function routeAction(Request $request)
	{
		$path = $request->getActionTarget();
		if (is_null($path))
		{
			return;
		}

		$target = $this->root;
		foreach($path as $name)
		{
			$index = null;
			$this->extractName($name, $index);

			foreach($this->listeners as $l)
			{
				$l->beforeRunAction($target);
			}
			
			$new_target = $target->getRequestHandler($name, $index);
			
			if (is_null($new_target))
			{
				throw new Exception("RequestHandler %1 (index=%2) not found in action path %3 (target is %4)",
									$name,
									$index,
									implode('.', $path),
									get_class($target));
			}
			
			$target = $new_target;
		}

		foreach($this->listeners as $l)
		{
			$l->beforeRunAction($target);
		}
		
		$target->invokeRequestHandlerAction($request->getActionData());

		foreach($this->listeners as $l)
		{
			$l->afterRunAction($target);
		}
	}
}