<?php

namespace Light\UI\Framework\Input;

class StateUnit
{
	/**
	 * @var Light\UI\Framework\Input\DataUnit[]
	 */
	private $ownState = array();
	
	/**
	 * @var Light\UI\Framework\Input\StateUnit[] 
	 */
	private $childState = array();
	
	public function addChildState($childName, StateUnit $stateUnit)
	{
		$this->childState[$childName] = $stateUnit;
	}
	
	public function addData(DataUnit $dataUnit)
	{
		$this->ownState[] = $dataUnit;
	}
	
	/**
	 * Returns an iterator over this StateUnit's DataUnits.
	 * @return Light\UI\Framework\Input\DataUnit[]
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->ownState);
	}
	
	/**
	 * Returns an iterator over the child StateUnits.
	 * @return Light\UI\Framework\Input\StateUnit[]
	 */
	public function getChildStateIterator()
	{
		return new \ArrayIterator($this->childState);
	}
	
	
}