<?php

namespace Light\UI\Component;

class Panel extends \UI_Component_Renderable {

	protected function construct() {
		parent::construct();
		$this
			->registerPart("elements:before")
			->registerPart("elements:after")
			->registerPart("elements:separator");
	}
	
}
