<?php

namespace Light\UI\Framework\Input;

/**
 * Produces a Request object by reading the GET and POST superglobals. 
 *
 */
class GetPostRequestDecoder
{
	const ACTION_TARGET	= "do";
	
	/**
	 * @return Light\UI\Framework\Input\Request
	 */
	public function read()
	{
		$request = new Request();
		$this->readArray($_POST, $request);
		$this->readArray($_GET, $request);
		return $request;
	}
	
	private function readArray(array $data, Request $request)
	{
		// read the action to be executed
		if (isset($data[self::ACTION_TARGET]))
		{
			$target = $data[self::ACTION_TARGET];
			$this->readActionTarget(key($target), current($target), $request);
		}
		
		// read the state of all components
		$stateUnit = $request->getState();
		if (!$stateUnit)
		{
			$request->setState($stateUnit = new StateUnit());
		}
		$this->readState($data, $stateUnit);
	}
	
	private function readState(array $data, StateUnit $target)
	{
		foreach($data as $key=>$value)
		{
			if ($key == self::ACTION_TARGET) continue;
			
			if (!is_array($value))
			{
				$target->addData(new DataUnit($key, $value));
			}
			else if ($key == "_")
			{
				// load values for the current component
				foreach($value as $name => $arguments)
				{
					$target->addData(new DataUnit($name, $arguments));
				}
			}
			else
			{
				// load values for a child component
				$stateUnit = new StateUnit();
				$this->readState($value, $stateUnit);
				$target->addChildState($key, $stateUnit);
			}
		}
	}
	
	private function readActionTarget($targetStr, $argStr, Request $request)
	{
		$targetStr = explode('.', $targetStr);
		$target = array_slice($targetStr, 0, count($targetStr) - 1);
		$name	= $targetStr[count($targetStr) - 1];
		$args	= $argStr;

		$dataUnit = new DataUnit($name, $args);
		$request->setAction($target, $dataUnit);
	}
}