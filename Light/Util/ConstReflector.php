<?php
namespace Light\Util;
use Light\Exception\InvalidParameterType;

/**
 * Class which helps in determining the name of a class constant having some value.
 *
 * @author	Piotr Szymański <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */
class ConstReflector
{
	/**
	 * @var ReflectionClass
	 */
	private $refl;
	
	/**
	 * @var string
	 */
	private $prefix;

	/**
	 * Constructs a new ConstReflector object.
	 * @param string|object		$subject		Either an object instance
	 *											or a class name.
	 * @param string			$prefix			Prefix for a constant group.
	 */
	public function __construct( $subject, $prefix = "" )
	{
		if (is_string( $subject ))
			$this->refl = new \ReflectionClass( $subject );
		else if (is_object( $subject ))
			$this->refl = new \ReflectionObject( $subject );
		else
			throw new InvalidParameterType('$subject',$subject, "string or object");

		$this->prefix = $prefix;
	}
	
	/**
	 * Returns the name of a constant having a given value.
	 * @param mixed		$value
	 * @return string			If no such constant is found, this method
	 *							returns an empty string (""). Otherwise,
	 *							the full name of the constant is returned.
	 */
	public function getNameOf( $value ) {

		foreach( $this->refl->getConstants() as $name => $qvalue ) {
			if (!empty( $this->prefix ) and 
				(substr( $name, 0, strlen( $this->prefix ) ) != $this->prefix)) continue;
			if ($qvalue === $value) return $name;
		}
		return "";
	}
	
	/**
	 * Returns the name of constants representing bits in a given value.
	 * @param integer	$value
	 * @return string
	 */
	public function getNamesOf( $value ) {
	
		$c = "";
		
		foreach( $this->refl->getConstants() as $name => $qvalue ) {
			if (!empty( $this->prefix ) and 
				(substr( $name, 0, strlen( $this->prefix ) ) != $this->prefix)) continue;
			if (!is_integer( $qvalue )) continue;
			
			if (($value & $qvalue) != 0)
				$c .= "|" . $name;
		}
		return substr( $c, 1 );
	
	}
	
}
