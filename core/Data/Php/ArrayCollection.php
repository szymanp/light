<?php

namespace Light\Data\Php;

class ArrayCollection extends TraversableCollection {

	public function __construct(array &$array) {
		parent::__construct($array);
	}
	
//	protected function execute() {
//
//		if (is_null( $this->criteria )) {
//			return;
//		}
//		
//		// apply limit
//		$offset = $this->criteria->getOffset();
//		$limit	= $this->criteria->getLimit();
//		if (($offset > 0) && ($limit <= 0)) {
//			$this->array = array_slice($this->array,$offset);
//		} else if (($offset >= 0) && ($limit > 0)) {
//			$this->array = array_slice($this->array,$offset,$limit);
//		}
//
//	}
}