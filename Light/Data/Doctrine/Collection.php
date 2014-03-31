<?php

namespace Light\Data\Doctrine;
use Light\Data;

class Collection implements \Light\Data\Collection
{
	private $select;
	private $from;
	private $where;
	private $identifier;
	private $properties = array();
	private $params = array();
	
	private $data;
	
	/**
	 * @return Collection
	 */
	public static function query()
	{
		return new self();
	}
	
	protected function __construct()
	{
	}
	
	/* Query definition */
	
	/**
	 * 
	 * @param $selectStr "u.fullname, u.handle"
	 */
	public function select($selectStr)
	{
		$this->select = $selectStr;
		return $this;
	}
	
	public function identifier($field)
	{
		$this->identifier = $field;
		return $this;
	}
	
	public function from($fromStr)
	{
		$this->from = $fromStr;
		return $this;
	}
	
	public function where($whereStr)
	{
		$this->where = $whereStr;
		return $this;
	}
	
	public function setParameter($name,$value)
	{
		$this->params[$name] = $value;
		return $this;
	}
	
	public function property($v)
	{
		if (!is_array($v))
		{
			$v = func_get_args();
		}
		foreach($v as $field)
		{
			$this->properties[] = $field;	
		}
		return $this;
	}
	
	/* Query construction */
	
	protected function execute()
	{
		$dql = "select " . $this->select . " from " . $this->from;
		if (!empty( $this->where )) $dql .= " where " . $this->where;
		
		$query = \Config::em()->createQuery($dql)
			->setHint(\Doctrine\ORM\Query::HINT_FORCE_PARTIAL_LOAD, true);
		
		$this->prepareParameters();
		$this->data = array();
		
		$idMethod = "get" . substr($this->identifier,strpos($this->identifier,".")+1);
			
		foreach($query->iterate($this->params) as $key => $ob)
		{
			$key = $ob[0]->$idMethod();
			$this->data[$key] = $ob[0];
		}
	}
	
	protected function prepareParameters()
	{
		foreach($this->params as $key=>$value)
		{
			if ($value instanceof Data\BoundValue)
			{
				$this->params[$key] = $value->get();
			}
		}
	}
	
	/* Manipulation */
	
	public function invalidate()
	{
		$this->data = NULL;
	}
	
	/* ArrayAccess */
	
	public function offsetSet($k,$v)
	{
	}
	
	public function offsetUnset($k)
	{
	}
	
	public function offsetExists($k)
	{
	}
	
	public function offsetGet($k)
	{
		if (is_null($this->data))
		{
			// try to retrieve this element directly
			
			$dql = "select " . $this->select . " from " . $this->from . 
				" where " . $this->identifier . " = :QQ_IDENTIFIER";
			if (!empty( $this->where )) $dql .= " and " . $this->where;
						
			$this->prepareParameters();
			$query = \Config::em()->createQuery($dql)
				->setHint(\Doctrine\ORM\Query::HINT_FORCE_PARTIAL_LOAD, true)
				->setParameters($this->params)
				->setParameter("QQ_IDENTIFIER", $k);
				
			$result = $query->getSingleResult();
			
			return $result;
		}
		
		return $this->data[$k];
	}
	
	/* Countable */
	
	public function count()
	{
		$dql = "select count(" . $this->identifier . ") from " . $this->from;
		if (!empty( $this->where )) $dql .= " where " . $this->where;

		$query = \Config::em()->createQuery($dql);
		$this->prepareParameters();
		$query->setParameters($this->params);
		
		return (integer) $query->getSingleScalarResult();
	}
	
	/* IteratorAggregate */
	
	public function getIterator()
	{
		if (is_null($this->data)) $this->execute();
		
		return new \ArrayIterator($this->data);
	}

	/**
	 * Returns a new Collection that uses the given Criteria.
	 * @return	Data_Collection
	 */
	public function criteria(\Light\Data\Criteria $c)
	{
	}
}