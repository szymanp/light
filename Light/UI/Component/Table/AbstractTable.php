<?php

namespace Light\UI\Component\Table;
use Light\UI;

abstract class AbstractTable extends \UI_Component_Standard {
	
	protected $columns 	= array();
	
	protected function construct() {
		parent::construct();
		$this->registerProperty("SelectedKey");
		$this->registerProperty("Source","Light\Data\Collection");
		$this->persistProperty("SelectedKey");
	}
	
	public function init()
	{
		parent::init();
		$this->removePart("before", "Light\UI\Decorator\Label");
	}
	
	public function load()
	{
		parent::load();
		
		foreach($this->getRows() as $key => $row)
		{
			foreach($this->getColumns() as $column)
			{
				$column->getInstance($row, $key);
			}
		}			
	}
	
	public function addColumn(Column $col) {
		$this->columns[] = $col;
		$col->attach($this);
		return $this;
	}
	
	public function getColumns() {
		return $this->columns;
	}	
				
	public function getSelectedRow() {
		if (is_null( $this->SelectedKey )) {
			return NULL;
		}
		return $this->getRows()->offsetGet($this->SelectedKey);
	}
	
	public function getValue() {
		return $this->getSelectedRow();
	}
	
	public function getRows() {
		return $this->getProperty("Source");
	}
	
	/**
	 * Checks if the datasource has any rows.
	 * @return 	boolean
	 */
	public function hasRows() {
		if (is_null( $this->Source )) {
			return false;
		}
		return $this->Source->count() > 0;
	}

}

