<?php

namespace Light\UI\Component;
use Light\UI;

abstract class AbstractList extends \UI_Component_Standard {

	protected $itemTemplate;
	
	protected function construct() {
		parent::construct();
		$this->registerProperty("SelectedKey");
		$this->registerProperty("Source","Data_Collection");
		$this->itemTemplate = new UI\DataTemplateProvider($this);
		$this->persistProperty("SelectedKey");
	}

	public function load()
	{
		parent::load();

		foreach($this->getRows() as $key => $row)
		{
			$this->itemTemplate->getTemplateInstance($row,$key);
		}
	}
			
	/**
	 * @return UI\DataTemplateProvider
	 */
	public function getItemTemplate()
	{
		return $this->itemTemplate;
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

