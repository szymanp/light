<?php

namespace Light\UI\Component\Lists;
use Light\UI;

class BasicList extends \UI_Component_Renderable {

	protected $items;
	
	protected function construct()
	{
		parent::construct();
		$this->registerProperty("Source","Data_Collection");
		$this->items = new UI\Collection($this);
		$this->addStageChangeListener($this->items);
	}

	public function hasElement($name)
	{
		// FIXME - This does not support the new Collection mechanism yet.
		
		if (parent::hasElement($name))
		{
			return true;
		}
		
		return false;
		
		if (!$this->hasRows())
		{
			return false;
		}
		
		$key = $this->itemTemplate->extractKeyFromName($name);
		if ($key === NULL)
		{
			return false;
		}
		
		$object = $this->Source->offsetGet($key);
		if ($object == NULL)
		{
			return false;
		}
		
		$this->itemTemplate->getTemplateInstance($object, $key, true);
		
		return true;
	}
	
	public function getItems()
	{
		return $this->items;
	}

}

