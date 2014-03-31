<?php

namespace Light\UI\Framework\Output;
use Light\UI\Component;

class HtmlAttachmentPoint extends AttachmentPointBase
{
	private $data = array();
	private $separator;
	
	public function __construct($separator = "\n")
	{
		$this->separator = $separator;
	}
	
	/**
	 * Adds a new value to this attachment point.
	 * @param mixed		$html	HTML value.
	 * @param Component $owner
	 * @return Light\UI\Framework\Output\HtmlAttachmentPoint
	 */
	public function add($html, Component $owner = NULL)
	{
		$this->data[] = array($owner, $html);
		return $this;
	}

	public function getHtml(Component $scope = NULL)
	{
		$buf = "";
		$first = true;
		
		foreach($this->data as $el)
		{
			$c = $el[0];
			$v = $el[1];
			
			if (!$this->isWithinScope($scope, $c))
			{
				continue;
			}
			
			if (!$first) $buf .= $separator;
			else $first = false;
			
			if ($el instanceof AttachmentPoint)
			{
				$el = $el->getHtml($scope);
			}
			else if (is_object($el) && method_exists($el, "printHTML"))
			{
				$el = $el->printHTML();
			}
			
			$buf .= ((string) $el);
		}
		
		return $buf;
	}
}