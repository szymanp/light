<?php 

namespace Light\Util;

class String
{
	private $str;
	
	/**
	 * @return \Light\Util\String
	 */
	public static function create($str)
	{
		return new self($str);
	}
	
	/**
	 * @param string	$str
	 */
	public function __construct($str)
	{
		$this->str = $str;
	}
	
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->str;
	}
	
	/**
	 * Tests if this string is empty.
	 * @return boolean
	 */
	public function isEmpty()
	{
		return $this->str == "";
	}
	
	/**
	 * Tests if this strings starts with another string.
	 * @param string	$string
	 * @return boolean
	 */
	public function startsWith($string)
	{
		return (substr($this->str, 0, strlen($string)) == $string);
	}
	
	/**
	 * Converts text indentation with spaces to tabs.
	 * 2 spaces are converted to 1 tab.
	 * @return \Light\Util\String
	 */
	public function spacesToTabs()
	{
		return new self(preg_replace('/(?:^|\G)  /um', "\t", $this->str));
	}
}