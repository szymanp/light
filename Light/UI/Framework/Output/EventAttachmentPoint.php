<?php
namespace Light\UI\Framework\Output;
use Light\UI\Component;

class EventAttachmentPoint extends AttachmentPointBase
{
	private $data = array();
	
	/**
	 * @return Light\UI\Framework\Output\EventAttachmentPoint
	 */
	public function add(Component $owner, $event, $function, $capturingPhase = false)
	{
		$this->data[] = array($owner, $event, $function, $capturingPhase);
		return $this;
	}
	
	public function getHtml(Component $scope = NULL)
	{
		$buf = "<script type=\"text/javascript\">\n";
		$buf.= "window.addEventListener('load',function() {\n";
		$first = true;
		
		foreach($this->data as $el)
		{
			$owner 	= $el[0];
			$event 	= $el[1];
			$func  	= $el[2];
			$capture= $el[3];
			if (!$this->isWithinScope($scope, $el[0]))
			{
				continue;
			}
			
			$first = false;
			
			$id = $owner->getId();
			
			$buf .= "Light.UI.attachEvent('" . $id . "', '" . $event . "', " . $func . ", " . ($capture?"true":"false") . ");\n";
		}
		
		if ($first)
		{
			return "";
		}
		
		$buf .= "}, false);\n</script>\n";
		
		return $buf;
	}
}
