<?php

namespace Light\UI\Component\Table;
use Light\UI\Component;
use Light\UI\Util;

class SimpleTable extends AbstractTable {

	private $rowAttributes;
	
	protected function construct()
	{
		parent::construct();
		$this->rowAttributes = new Util\TagAttributes;
	}
	
	public function load()
	{
		$this->import("js","Light.UI.Common");
		$this->import("js","Light.UI.Component.Table.Table");

		parent::load();
	}
	
	public function getRowAttributes()
	{
		return $this->rowAttributes;
	}
	
	public function setRowAttribute($name,$value)
	{
		$this->rowAttributes->set($name,$value);
		return $this;
	}
	
	// events
	
	public function onCellClick($data)
	{
		$data = explode(".", $data, 2);
		
		$row = $this->Source[$data[1]];
		$this->raiseUserEvent("cellClick", $row, (integer) $data[0] );
	}
	
	public function onColumnClick($data)
	{
		$col = (integer) $data;
		if (($col < 0) || ($col >= count($this->columns))) {
			return;
		}
		
		$this->raiseUserEvent("columnClick", $this->columns[$col]);
	}
	
}

