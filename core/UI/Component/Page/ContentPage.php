<?php

namespace Light\UI\Component\Page;
use Light\UI\Component;
use Light\UI;
use Light\Util;

/**
 * A page that renders a single element - its Content.
 *
 * @property	mixed	Content
 */
class ContentPage extends Component\Page
{
	public function render()
	{
		if (!$this->getVisible()) {
			return;
		}
		
		print $this->propertyRender("Content");
	}
}
