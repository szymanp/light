<?php

namespace Light\Data\Propel;

use Light\Data\Collection;
use \Propel;

class PropelObjectCollection implements Collection
{
	private $className;
	private $queryString;
	private $params;
	private $source;
	
	public function __construct($className, $queryString, array $params = array())
	{
		$this->className	= $className;
		$this->queryString 	= $queryString;
		$this->params		= $params;
	}
	
	public function load()
	{
		$conn = Propel::getConnection();
		
		$stmt = $conn->prepare($this->queryString);
		$stmt->execute($this->params);
		
		$fmtr = new \PropelOnDemandFormatter();
		$fmtr->setClass($this->className);
		$fmtr->setPeer($this->className . "Peer");
		
		$this->source = $fmtr->format($stmt);
	}
	
	public function getModelObject()
	{
		return $this->getIterator();
	}
	
	public function getIterator()
	{
		return $this->source->getIterator();
	}

}