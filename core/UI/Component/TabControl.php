<?php

namespace Light\UI\Component;
use Light\UI\Util\ClientEnvironment;

use Light\Service\Service;
use Light\UI\Util\Href;
use Light\Util\HrefString;
use Light\UI;

/**
 * @property boolean	IsAjax
 */
class TabControl extends \UI_Component_Renderable implements Service
{
	protected $tabs = array();
	private $tabHref;
	
	/**
	 * @var integer
	 */
	protected $activePage = 0;

	protected function construct()
	{
		$this->setAttribute("class", "tabs");
		$this->registerProperty("IsAjax","boolean",false);
		parent::construct();
	}
	
	public function init()
	{
		$this->persistProperty("activePage");
		$this->persistElements = false;
		
		$self = $this;
		
		$this->tabHref = Href::dynamic(function($href, $value) use ($self)
		{
			if ($self->IsAjax)
			{
				return new HrefString("Light.TabControl.click(this, $value);", true);
			}
			else
			{
				return Href::toEvent($self, "click", $value);
			}
		});

		$this->enableAjaxEvents();
		
		parent::init();
	}

	public function load()
	{
		$this->import("js","Light.UI.Common");
		$this->import("js","Light.UI.Component.TabControl");
		$this->import("css","Light.UI.Component.TabControl");

		if ($this->IsAjax)
		{
			$env = new ClientEnvironment($this);
			$env->attach();
			$env->href = Href::toEvent($this, "click", "")
				->setAsynchronous(true)
				->setPlugin("Nested");
		}
		
		// load the active page
		if (!is_null( $active = $this->getActiveComponent() ))
		{
			$active->load();
		}
	}
	
	public function add(UI\Component $c)
	{
		$this->tabs[] = $c;
		return parent::add($c);
	}
		
	public function setActivePage($tab)
	{
		if ($tab instanceof UI\Component)
		{
			$tab = array_search($tab,$this->tabs,true);
		}
		$this->activePage	= (integer) $tab;
	}
	
	public function render()
	{
		if (empty( $this->elements ))
		{
			return;
		}
		parent::render();
	}
	
	/**
	 * @return Light\UI\Component
	 */
	public function getActiveComponent() {
		return @ $this->tabs[$this->activePage];
	}
	
	public function getHref()
	{
		return $this->tabHref;
	}
	
	// event
	
	public function onClick($tabid)
	{
		$tabid = (integer) $tabid;
		$el = $this->tabs[$tabid];
		$this->setActivePage($el);
		$this->raiseUserEvent("click",$el);
		
		$event = $this->getCurrentEvent();
		if ($event->isAsynchronous())
		{
			ob_start();
			$this->getActiveComponent()->gotoStage(UI\Component::STATE_RENDERED);
			$event->setResult(ob_get_contents(), "text/html");
			ob_end_clean();
		}
	}
	
}
