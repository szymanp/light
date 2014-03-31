<?php

abstract class Data_GenericObject implements Data_Object {

	abstract protected function getValueLocal($name);
	
	abstract protected function setValueLocal($name,$value);
	
	public function getValue($path) {
	
		$dot = strpos($path,".");
		if ($dot === false) {
			return $this->getValueLocal($path);
		}
		
		$lname = substr($path,0,$dot);
		$path  = substr($path,$dot+1);
		
		$value = $this->getValueLocal($lname);
		return Data_Object::wrapper($value)->getValue($path);
	}

	public static function setProperty($path,$propValue,$value,$key=NULL) {
	
		$value = self::get($value);
		
		$expl = explode(".",$path);
		$lidx  = count($expl)-1;
		foreach($expl as $i => $step) {
			$last = $i == $lidx;
			if ($step == ":key") {
				$value = $key;
			} else if ($step == ":this") {
				continue;
			} else {
				if ($last) {
					$value->setValue($step,$propValue);
				} else {
					$value = $value->getValue($step);
				}
			}
		}
		
		return $value;
	
	}
}
