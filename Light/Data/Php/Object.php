<?php
/**
 * Represents a single element node within an XML document. 
 * 
 * @package dcx.data.introspect
 * @author	Piotr Szymański <szyman@magres.net>
 * @license http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */
 
/*
$o				o: $o, 			p: NULL
$o->field		o: $o, 			p: field
$o->field->x	o: $o->field,	p: x
$o->field[x]	o: $o->field,	

*/

namespace Light\Data\Php;
use Light\Data;
use Exception;

class Object implements Data\Object {
	
	/**
	 * @var mixed
	 */
	protected $object;
	
	/**
	 * @var string
	 */
	protected $property;
	
	/**
	 * @var mixed
	 */
	protected $index;
	
	/**
	 * @param mixed		$object An object, or a PHP built-in type.
	 */
	public function __construct(&$object,$property=NULL,$index=NULL) {
		$this->object 	= $object;
		$this->property	= $property;
		$this->index	= $index;
	}
	
	
	public function getValue($dotName = "")
	{
		if (empty($dotName))
		{
			return $this->object;
		}
		
		$object = $this->object;
		$parsed = Data\Helper::parsePath($dotName);
		
		foreach($parsed as $elem) {
			if (is_object( $object )) {
				if ($object instanceof Object)
				{
					$object = $object->getValue($elem->name);
				}
				else
				{
					$object = $this->getPropertyValue($elem->name,$object);
				}
			} else if (is_array( $object )) {
				$object = $object[$elem->name];
			}
			if (!is_null( $elem->index )) {
				$object = $object[$elem->index];
			}
			
			if (is_null($object)) {
				return NULL;
			}
		}
		return $object;
	}
	
	public function setValue($dotName,$value) {

		$object = $this->object;
		$parsed = Data\Helper::parsePath($dotName);
		$count  = count($parsed);

		foreach($parsed as $elem) {
			if (--$count == 0) {
				break;
			}

			if (is_object( $object )) {
				$object = & $this->getPropertyValue($elem->name,$object);
			} else if (is_array( $object )) {
				$object = & $object[$elem->name];
			}
			if (!is_null( $elem->index )) {
				$object = & $object[$elem->index];
			}
			
			if (is_null($object)) {
				throw new Exception("NULL object found");
			}
		}
		
		if (!is_null( $elem->index )) {
			if (is_object( $object )) {
				$object = & $this->getPropertyValue($elem->name,$object);
			} else if (is_array( $object )) {
				$object = & $object[$elem->name];
			}
			$object[$elem->index] = $value;
			return;
		}
		
		if (is_object( $object )) {
			$this->setPropertyValue($elem->name,$value,$object);
		} else if (is_array( $object )) {
			$object[$elem->name] = $value;
		} else {
			throw new Exception("Target is a primitive type");
		}
		
	}
	
	public function getIdentifier($asArray=false) {
		return array();
	}
	
	public function hasProperty($dotName)
	{
		$object = $this->object;
		$parsed = Data\Helper::parsePath($dotName);

		foreach($parsed as $elem) {
			try
			{
				if (is_object( $object )) {
					$object = $this->getPropertyValue($elem->name,$object);
				} else if (is_array( $object )) {
					$object = $object[$elem->name];
				}
				if (!is_null( $elem->index )) {
					$object = $object[$elem->index];
				}
			}
			catch (Exception $e)
			{
				return false;
			}
			
			if (is_null($object)) {
				return false;
			}
		}
		return true;		
	}
	
	/**
	 * Reads a value of a named property.
	 *
	 * @internal This parameter does not accept a namespace, as a class
	 * does not have properties with namespaces anyhow.
	 *
	 * @param 	string	$localName		Name of the property.
	 * @throws 	Exception
	 * @return 	mixed	An object, or a PHP built-in type.
	 */
	protected function & getPropertyValue( $localName, $target = NULL ) {
	
		if (is_null($target)) {
			$target = $this->object;
		}
	
		if (property_exists( $target, $localName ))
		{
			$refl = new \ReflectionProperty(get_class($target), $localName);
			if ($refl->isPublic())
			{
				return $target->$localName;
			}
		}
	
		if (method_exists( $target, $m = "get" . $localName )) {
			$v = $target->$m();
			return $v;
		}
		
		if (method_exists( $target, $m = "is" . $localName )) {
			$v = $target->$m();
			return $v;
		}
		
		throw new Exception( "Property \"$localName\" is not available for reading." );

	}
	
	protected function setPropertyValue( $localName, $value, $target = NULL ) {

		if (is_null($target)) {
			$target = $this->object;
		}
	
		if (property_exists( $target, $localName )) {
			$refl = new \ReflectionProperty(get_class($target), $localName);
			if ($refl->isPublic())
			{
				$target->$localName = $value;
				return;
			}
		}
	
		if (method_exists( $target, $m = "set" . $localName )) {
			$target->$m($value);
			return;
		}
		
		throw new Exception( "Property \"$localName\" is not available for writing." );
	
	}

}
