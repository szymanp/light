<?php

class UI_Component_Page_Editor extends UI_Component_Page_Form {

	private $rootObject;
	
	private $currentObject;
	
	private $path;
	
	public function __construct($name,Data_Entity $obj) {
		parent::__construct($name);
		$this->rootObject	= $obj;
		$this->currentObject= $obj;
	}
	
	public function setPath($path) {

		$this->currentObject = $this->rootObject;	
	
		$exp = explode("/",$path);
		foreach($exp as $name) {
		}
		
		$this->path = $path;
		
		$this->add($this->getEditor($this->currentObject));
	}
	
	public function getPath() {
		return $this->path;
	}
	
	public function init() {
		parent::init();
		$this->persistElements	= false;
		$this->persistProperty("path");

		$this->add(new UI_Component_PDHolder());
	}
	
	public function load() {
		parent::load();

		if (is_null($this->path)) {
			$this->setPath("");
		}
	}
	
	public function render() {
		print( "<form>" );
		parent::render();
		print( "</form>" );
	}
	
//	public function restoreComponent(UI_Component_PersistentData $store) {
//		parent::restoreComponent($store);
//		$this->setPath($this->path);
//	}
	
	// events 
	
	protected function getEditor(Data_Entity $o) {
		$editorMgr = Introspect_Editor::getInstance();
		$editor = $editorMgr->getEditor($o->getType());

		$opts = new Introspect_Editor_Options();
		$opts->setNesting(2);
		$com = $editor->getComponent($opts);

		$com->addDataSource("main",$o);
		$com->setDataSource("main");
		
		return $com;
	}
	
}
