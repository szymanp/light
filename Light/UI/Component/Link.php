<?php

namespace Light\UI\Component;
use Light\UI\Util;

class Link extends ContentControl
{
	private $href;
	private $hrefStr;
	
	public function getHrefString()
	{
		if (is_null($this->hrefStr))
		{
			$this->hrefStr = $this->getHref()->evaluate($this);
		}
		return $this->hrefStr;
	}
	
	public function getHref()
	{
		if (is_null($this->href))
		{
			$this->href = Util\Href::toSelf(array($this->getName() => 1));
		}
		return $this->href;
	}
	
	public function setHref(Util\Href $href)
	{
		$this->href = $href;
		return $this;
	}
	
	protected function onClick($data)
	{
		$this->raiseUserEvent("click",$data);	
	}
	
	protected function onDefaultEvent($data)
	{
		$this->handleEvent("click",$data);
	}	
}