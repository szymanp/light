<?php

namespace Light\UI\Framework\Output;
use Light\Exception\Exception;

use Light\UI\Component;

class AttachmentPoints
{
	private $points = array();
	private $pointsByAlias = array();
	
	/**
	 * Defines a new attachment point set.
	 * @param string			$name
	 * @return Light\UI\Framework\Output\AttachmentPoints
	 */
	public function define($name)
	{
		$this->points[$name] = array();
		return $this;
	}
	
	/**
	 * Defines a new point in the attachment point set.
	 * @param string			$name
	 * @param string			$alias
	 * @param AttachmentPoint	$point
	 * @return Light\UI\Framework\Output\AttachmentPoints
	 */
	public function add($name, $alias, AttachmentPoint $point)
	{
		if (isset($this->pointsByAlias[$alias]))
		{
			throw new Exception("Attachment point with alias %1 already exists", $alias);
		}
		$this->points[$name][] = $point;
		$this->pointsByAlias[$alias] = $point;
		return $this;
	}
	
	/**
	 * Returns an attachment point with the given alias.
	 * @param string	$name
	 * @param string	$alias
	 * @return Light\UI\Framework\Output\AttachmentPoint
	 */
	public function get($alias)
	{
		if (!isset($this->pointsByAlias[$alias]))
		{
			throw new Exception("Attachment point with alias %1 is not defined", $alias);
		}
		
		return $this->pointsByAlias[$alias];
	}
	
	/**
	 * Returns the content of the attachment point.
	 * @param 	string		$point
	 * @param	Component	$scope
	 * @return	string
	 */
	public function getHtml($point, Component $scope = NULL)
	{
		$buf = "";
		
		foreach($this->points as $coll)
		{
			foreach($coll as $point)
			{
				$buf .= $point->getHtml($scope);
			}
		}
		
		return $buf;
	}
}