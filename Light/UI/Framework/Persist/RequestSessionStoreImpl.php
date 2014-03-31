<?php

namespace Light\UI\Framework\Persist;
use Light\UI;

class RequestSessionStoreImpl implements RequestSessionStore
{
	private $sessionVar = "rsid";
	private $size = 15;
	private $store;
	private $currentId;
	private $nextId;
	private $hasSaved = false;
	
	public function open()
	{
		if (!isset( $_SESSION[__CLASS__] ))
		{
			$_SESSION[__CLASS__] = array();
		}
		
		$this->store = & $_SESSION[__CLASS__];
		
		if (isset( $_REQUEST[$this->sessionVar] ))
		{
			$this->hasSaved = true;
			$this->currentId = $_REQUEST[$this->sessionVar];
			if (!isset( $this->store[$this->currentId] ))
			{
				$this->currentId = NULL;
			}
		}
		else
		{
			$this->hasSaved = !empty($this->store);
		}
		
//		if (is_null($this->currentId))
//		{
//			$this->store = array();
//		}
		
		if (empty( $this->store ))
		{
			$this->nextId = 1;
		}
		else
		{
//			$this->nextId = max(array_keys($this->store)) + 1;
			$this->nextId = $this->currentId + 1;
		}
		
//		if (!is_null($this->currentId)) print("CURRENT STORE:");var_dump($this->store[$this->currentId]);print("<BR>");
		
	}
	
	public function close()
	{
		$c = count($this->store);
		if ($c > $this->size)
		{
			$this->store = array_slice($this->store, $c - $this->size, $this->size, true);
		}
	}
	
	public function save(UI\Component $c, Property $property, $value)
	{
		$this->hasSaved = true;
		$this->store[$this->nextId][get_class($c)][$c->getName()][$property->getName()] = $value;
	}
	
	public function load(UI\Component $c, Property $property, &$value)
	{
		if (is_null( $this->currentId ))
		{
			return false;
		}
		
		$name = $c->getName();
		$cls = get_class($c);
		$prop = $property->getName();
		
		if (!isset( $this->store[$this->currentId][$cls][$name] ))
		{
			return false;
		}
		
		if (!array_key_exists( $prop, $this->store[$this->currentId][$cls][$name] ))
		{
			return false;
		}
		
		$value = $this->store[$this->currentId][$cls][$name][$prop];

		return true;
	}
	
	public function appendArguments(array &$args)
	{
		if ($this->hasSaved)
		{
			$args[$this->sessionVar] = $this->nextId;
		}
	}
	
}