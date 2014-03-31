<?php

namespace Light\Data;

class Criteria implements Iterator {

	const EQUAL			= 0x1;
	const NOT_EQUAL		= 0x2;
	const GREATER_THAN	= 0x3;
	const LESS_THAN		= 0x4;
	const GREATER_EQUAL	= 0x5;
	const LESS_EQUAL	= 0x6;
	const LIKE			= 0x7;
	const NOT_LIKE		= 0x8;
	const IN			= 0x9;
		
	protected $criteria = array();
	
	protected $limit;
	protected $offset;
	
	protected $order = array();
	
	public function __construct() {
	}
	
	public function add($element,$value,$operator = self::EQUAL) {
		$this->criteria[$element] = array($operator,$value);
		return $this;
	}
	
	public function addAscendingOrder($element) {
		$this->order[] = array($element,true);
		return $this;
	}

	public function addDescendingOrder($element) {
		$this->order[] = array($element,false);
		return $this;
	}
	
	public function getOrdering() {
		return $this->order;
	}
	
	public function setOffset($o) {
		$this->offset = (integer) $o;
		return $this;
	}
	
	public function setLimit($l) {
		$this->limit = (integer) $l;
		return $this;
	}
	
	public function getLimit() {
		return $this->limit;
	}
	
	public function getOffset() {
		return $this->offset;
	}
	
	/**
	 * @return	Data_Criteria	For fluent API.
	 */
	public function append(Criteria $c)
	{
		foreach($c as $elem => $data) {
			$this->add($elem,$data[1],$data[0]);
		}
		$this->limit	= $c->limit;
		$this->offset	= $c->offset;
		$this->order	= array_merge($c->order,$this->order);
		
		return $this;
	}
	
	/*
	 * Iterator impl
	 */
	 
	public function current() {
		return current($this->criteria);
	}
	
	/**
	 * @return	Introspect_Type
	 */
	public function key() {
		return key($this->criteria);
	}
	
	public function next() {
		next($this->criteria);
	}
	
	public function rewind() {
		reset($this->criteria);
	}
	
	public function valid() {
		return current($this->criteria) !== NULL;
	}

}