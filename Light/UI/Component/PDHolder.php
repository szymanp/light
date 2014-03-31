<?php

/**
 * Persistent Data Holder
 */
class UI_Component_PDHolder extends UI_Component_Renderable {

	private $data;
	
	public function __construct() {
		parent::__construct("_S");
	}

	public function render() {
	
		if (is_null( $this->data )) {
			$this->data = $this->getContainer()->serializeComponent();
		}
		
		print( "<input type='hidden' name='" . $this->getName() . "' value=\""
			. htmlspecialchars($this->data,ENT_COMPAT) . "\"/>" );
	
	}

	public function persistComponent(UI_Framework_PersistentData $store) {
	}
	
	public function restoreComponent(UI_Framework_PersistentData $store) {
	}

}
