<?php

namespace Light\UI\Framework\Output;

use Light\UI\Component;
use Light\UI\Container;

abstract class AttachmentPointBase implements AttachmentPoint
{
	public function printHtml(Component $scope = NULL)
	{
		print $this->getHtml($scope);
	}
	
	/**
	 * Tests if a component is a child of another one.
	 * @param Component $scope
	 * @param Component $target
	 * @return boolean
	 */
	protected function isWithinScope(Component $scope = NULL, Component $target = NULL)
	{
		if (is_null($scope))
		{
			return true;
		}

		// if target is NULL, then only include it if scope is also NULL
		if (is_null($target))
		{
			return false;
		}
		
		$parent = $target;
		
		while (!is_null($parent))
		{
			if ($parent === $scope)
			{
				return true;
			}
			
			if (!($parent instanceof Container))
			{
				return false;
			}
			
			$parent = $parent->getContainer();
		}
		
		if ($target === $scope)
		{
			return true;
		}

		return false;
	}
}

