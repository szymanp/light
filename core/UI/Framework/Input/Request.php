<?php

namespace Light\UI\Framework\Input;

/**
 * Stores the request data.
 * 
 * A request in the UI framework is composed of two parts:
 * - an action to be carried out (there can be only one action per request)
 * - a set of state information 
 *
 * When passing on the request data to RequestHandlers, the state is passed
 * first, and then an action is executed.
 *
 */
class Request
{
	/**
	 * Path to the target component for the action.
	 * @var string[]
	 */
	private $actionTarget;
	
	/**
	 * @var Light\UI\Framework\Input\DataUnit
	 */
	private $actionData;
	
	/**
	 * State for the root component.
	 * @var Light\UI\Framework\Input\StateUnit
	 */
	private $state;
	
	public function setAction(array $target, DataUnit $data)
	{
		$this->actionTarget = $target;
		$this->actionData	= $data;
	}
	
	public function setState(StateUnit $state)
	{
		$this->state = $state;
	}
	
	/**
	 * @return string[]
	 */
	public function getActionTarget()
	{
		return $this->actionTarget;
	}
	
	/**
	 * @return Light\UI\Framework\Input\DataUnit
	 */
	public function getActionData()
	{
		return $this->actionData;
	}
	
	/**
	 * @return var Light\UI\Framework\Input\StateUnit
	 */
	public function getState()
	{
		return $this->state;
	}
}
