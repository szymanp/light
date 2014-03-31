<?php

namespace Light\UI\Framework;
use Light\UI\Component;

/**
 * A store for a component's persistent data.
 */
class PersistentData {

	private $counter = 0;
	
	protected $data = array();

	public function save(Component $c,array $data) {
//		$this->data[$this->counter++] = $data;
		$this->data[$c->getName()] = $data;
	}
	
	public function load(Component $c) {
//		return $this->data[$this->counter++];
		return $this->data[$c->getName()];
	}
	
	public function __sleep() {
		return array("counter","data");
	}
	
	public function __wakeup() {
		$this->counter = 0;
	}
	
	/**
	 * @internal	This method is user for unit testing.
	 * @return	array
	 */
	public function getData() {
		return $this->data;
	}

}
