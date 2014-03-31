<?php

namespace Light\UI\Util;

/**
 * A class which manages tag attributes.
 * @author Piotrek
 *
 */
class TagAttributes
{
	private $parent;
	private $attribs = array();
	private $callback;

	public function set($name,$value)
	{
		$this->attribs[$name] = $value;
		return $this;
	}
	
	public function setCallback(\Closure $callback)
	{
		$this->callback = $callback;
	}
	
	public function getInstance($value, $key)
	{
		$child = new self();
		$child->parent = $this;
		
		if (!is_null($callback = $this->callback))
		{
			$callback($child, $value, $key);
		}
		
		return $child;
	}

	public function __toString() {
		$str = "";
		$attribs = $this->getEffectiveAttribs();
		
		foreach($attribs as $n => $v) {
			$str .= " " . $this->sanitizeHtml($n) . "=\"" . $this->sanitizeHtml($v) . "\"";
		}
		return $str;
	}
	
	protected function sanitizeHtml($str) {
		return htmlspecialchars($str,ENT_COMPAT);
	}
	
	protected function getEffectiveAttribs()
	{
		if ($this->parent == NULL)
		{
			return $this->attribs;
		}
		
		return array_merge($this->parent->getEffectiveAttribs(), $this->attribs);
	}
}