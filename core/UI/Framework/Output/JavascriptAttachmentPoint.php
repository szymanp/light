<?php
namespace Light\UI\Framework\Output;
use Light\UI\Component;

class JavascriptAttachmentPoint extends AttachmentPointBase
{
	private $data = array();
	
	/**
	 * Adds new javascript code.
	 * @param string	$jscode
	 * @param Component	$owner
	 * @return Light\UI\Framework\Output\JavascriptAttachmentPoint
	 */
	public function add($jscode, Component $owner = NULL)
	{
		$this->data[] = array($owner, $jscode);
		return $this;
	}
	
	public function getHtml(Component $scope = NULL)
	{
		$buf = "<script type=\"text/javascript\">\n";
		$first = true;
		
		foreach($this->data as $el)
		{
			if (!$this->isWithinScope($scope, $el[0]))
			{
				continue;
			}
			
			$first = false;
			
			$buf .= $this->evaluateData($el[1]) . "\n";
		}
		
		if ($first)
		{
			return "";
		}
		
		$buf .= "</script>\n";
		
		return $buf;
	}
	
	protected function evaluateData($data)
	{
		return (string) $data;
	}
}
