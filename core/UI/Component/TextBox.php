<?php

namespace Light\UI\Component;

use Light\UI\Framework\ValueProperty;

use Light\Exception\Exception;

use Light\Service\Service;

use Light\Util\Controller\Nested;

use Light\UI\Util\Href;

/**
 * @property	string		Value
 * @property	boolean		Readonly
 * @property	string		Type
 * @property	boolean		EnableAutoComplete
 * @property	Callable	AutoCompleteCallback	array function($textbox, $search_term) 
 */
class TextBox extends Renderable implements Service
{
	protected function construct()
	{
		parent::construct();
		ValueProperty::create($this, "Value", "string")->external(true);
		ValueProperty::create($this, "Readonly", "boolean")->set(false);
		ValueProperty::create($this, "Type", "string")->set("text");
		ValueProperty::create($this, "EnableAutoComplete", "boolean")->set(false);
		ValueProperty::create($this, "AutoCompleteCallback");
		
		$this->getServiceDescriptor()
			->addMethods("getAutocompletion");
	}
	
	public function load()
	{
		if ($this->EnableAutoComplete)
		{
			$this->loadResources();
			
//			$this->getView()->import("js", "xx");
			$r = $this->getResources()->getStandaloneResource("autocomplete-js");
			$r->setVariable("{ID}", $this->getId());
			$r->setVariable("{SOURCE}", Href::toComponent($this, "getAutocompletion"));

			\UI_Scene::getInstance()->getAttachmentPoints()->get(\UI_Scene::AP_JS_TEXT)->add($r, $this);
		}
	}
	
	/* AJAX methods */
	
	public function getAutocompletion($term)
	{
		if (!is_callable($this->AutoCompleteCallback))
		{
			throw new Exception("No AutoCompleteCallback was specified");
		}
		
		return call_user_func_array($this->AutoCompleteCallback, array($this, $term));
	}
}
