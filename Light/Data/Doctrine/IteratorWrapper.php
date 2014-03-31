<?php

namespace Light\Data\Doctrine;

class IteratorWrapper implements Iterator {
		
	public function current() {
		if ($this->partial) throw new Exception("Collection is in append-only state.");
		if (!$this->executed) $this->execute();
		
		if ($this->iterData) {
			return current($this->data);
		}
		
		return current($this->added);
	}
	
	public function key() {
		if ($this->partial) throw new Exception("Collection is in append-only state.");
		if (!$this->executed) $this->execute();
		
		if ($this->iterData) {
			return key($this->data);
		}
		
		return 0;
	}
	
	public function next() {
		if ($this->partial) throw new Exception("Collection is in append-only state.");
		if (!$this->executed) $this->execute();
		
		if ($this->iterData) {
			$v = next($this->data);
			if ($v === FALSE) {
				$this->iterData = false;
			}
		} else {
			next($this->added);
		}
	}
	
	public function rewind() {
		if ($this->partial) throw new Exception("Collection is in append-only state.");
		if (!$this->executed) $this->execute();
		reset($this->data);
		reset($this->added);
		$this->iterData = true;
	}
	
	public function valid() {
		if ($this->partial) throw new Exception("Collection is in append-only state.");
		if (!$this->executed) $this->execute();
		
		if ($this->iterData) {
			return true;
		}
		
		return current($this->added) !== FALSE;
	}

}