<?php

namespace Light\Data;

class Helper
{
	private static $wrappers = array(
		array("Light\Data\ModelWrapper", "Light\Data\Model")
	);

	/**
	 * @param	mixed		$o
	 * @return	Light\Data\Object
	 */
	public static function wrap($o)
	{
		if ($o instanceof Object)
		{
			return $o;
		}
		
		for($i=count(self::$wrappers)-1;$i>=0;$i--)
		{
			$class = self::$wrappers[$i][0];
			$target= self::$wrappers[$i][1];
			if (is_a($o,$target)) {
				return new $class($o);
			}
		}
		
		return new Php\Object($o);
	}
	
	public static function registerWrapper($wrapperClass,$target)
	{
		self::$wrappers[] = array($wrapperClass,$target);
	}
	
	/**
	 * Read a field of an object.
	 * @param object	$o		Object to read
	 * @param string	$path	Path to the field
	 * @return mixed
	 */
	public static function get($o,$path)
	{
		$w = Helper::wrap($o);
		return $w->getValue($path);
	}
	
	/**
	 * Set a field of an object.
	 * @param object	$o
	 * @param string	$path	Path to the field
	 * @param mixed		$value	Value to set
	 */
	public static function set($o,$path,$value)
	{
		$w = Helper::wrap($o);
		$w->setValue($path,$value);
	}
	
	/**
	 * @param	string	$path
	 * @param	integer	$limit	Maximum number of path elements to parse.
	 * @throws	Exception_InvalidParameterValue
	 * @return	array<Data_Helper_ParsedElement>
	 */
	public static function parsePath(&$path,$limit=NULL) {
	
		$elements = array();
	
		if ($limit > 0) {
			$exp = explode(".",$path, $limit+1);
		} else {
			$exp = explode(".",$path);
		}
		
		$count = count($exp);
		
		foreach($exp as $elem) {
			if (($limit>0) && (--$count == 0)) {
				$path = $elem;
				break;
			}
			if (empty($elem)) {
				throw new Exception_InvalidParameterValue('$path',$path,"Empty path element");
			}
			$bracket = strpos($elem,"[");
			if ($bracket === false) {
				$elements[] = new Helper_ParsedElement($elem);
			} else {
				$endbracket = strpos($elem,"]",$bracket);
				if ($endbracket === false) {
					throw new Exception_InvalidParameterValue('$path',$path,"Missing ending bracket");
				}
				$element = new Data_Helper_ParsedElement;
				$element->name	= substr($elem,0,$bracket);
				$element->index	= substr($elem,$bracket+1,$endbracket-$bracket-1);
				$elements[] = $element;
			}
		}
		
		return $elements;
	}

}

class Helper_ParsedElement
{
	public $name;
	public $index;
	
	public function __construct($name=NULL,$index=NULL)
	{
		$this->name		= $name;
		$this->index	= $index;
	}

}
