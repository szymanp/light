<?php
/**
 * A class that performs a more compact serialization than the standard serialize() function.
 *
 * The <kbd>TightSerialize</kbd> class provides methods for serializing and unserializing
 * objects similar to the <kbd>serialize()</kbd>/<kbd>unserialize()</kbd> PHP functions.
 * The main difference is that the serialized form generated by this class is much more
 * compact than the one generated by PHP.
 *
 * @package	persist
 * @author	Piotr Szyma�ski <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */

class Persist_TightSerialize {

	public $className;
	
	const BASE			  = 97;
	const COMPRESSED	  = 0x10;
	const T_NULL		  = 0x01;		// fixed
	const T_OBJECT		  = 0x02;
	const T_BOOLEAN		  = 0x03;		// fixed
	const T_DOUBLE		  = 0x04;
	const T_INTEGER		  = 0x05;
	const T_INTEGER_NEG   = 0x06;
	const T_STRING		  = 0x0b;
	const T_ARRAY		  = 0x0c;
	const T_ARRAY_VALUES  = 0x0d;
	
	private $ignorePropIndexes = array();
	
	private $lastPropertyCount = 0;

	/**
	 * Serializes the given object.
	 * @param object	$object Object to be serialized.
	 * @return string	Serialized form of the object.
	 */
	public function serialize( $object ) {
	
		$raw = serialize( $object );

		$exp = explode( ":", $raw, 5 );
		$raw = substr( $exp[4], 1, -1 );
		$len = strlen( $raw );

		if (is_null( $this->className )) {
			// store the class name
			$serialized = get_class( $object ) . ":";
		} else {
			// no class name is stored
			$serialized = "";
		}
		
		// test for hidden properties
		$omit = $this->getOmittedProperties( $object );
		if (!empty( $omit )) {
			$ro		= new ReflectionClass( $this->className );
			$props	= $ro->getProperties();

			$this->ignorePropIndexes = array();
			foreach ($props as $i=>$prop)
				if (in_array( $prop->getName(), $this->ignoreProperties))
					$this->ignorePropIndexes[] = $i;
		}
		
		$start = 0;
		$serialized .= $this->compress( $raw, $start, $len );
		
		return $serialized;
	}
	
	/**
	 * Unserializes an object previously serialized using {@link serialize()}.
	 * @param string	$string Serialized form of the object.
	 * @return object	Unserialized object.
	 */
	public function unserialize( $string ) {
	
		if (is_null( $this->className )) {
		
			// extract class name
			
			$pos = strpos( $string, ':' );
			$this->className = substr( $string, 0, $pos );
			$start = $pos + 1;
		} else {
			$start = 0;
		}
		
		$ro		= new ReflectionClass( $this->className );
		// use a proper case
		$this->className = $ro->getName();
		
		// read the properties
		$props	= $ro->getProperties();

		$serial = "O:" . strlen( $this->className ) . ":\"" . $this->className . "\":" . 
			count( $props ) . ":{";

		// decompress properties
		$len = strlen( $string );
		$serial .= $this->decompress( $string, $start, $len, $props );
		
		$serial .= "}";
		
//		print("<BR>");print($serial);
		
		return unserialize( $serial );
		
	
	}

	protected function compress( & $raw, & $i, $len, $mode = self::T_OBJECT ) {
	
//		  print( "*** \$i=$i, \$len=$len, \$mode=$mode<BR>" );
//		  print( substr( $raw, $i, $len-$i ) . "<BR>" );

		$isStart = ($i==0);
	
		$serialized = "";
		$fields = array();
		$break = false;
		$index = 0;
		
		// serialized form decomposition
		
		for (;$i<$len;$i++) {
		
			$ignoreField = false;
		
			if ($mode == self::T_OBJECT) {
				// skip field name
				$pos   = strpos( $raw, ';', $i );
				if ($pos === FALSE) throw new Exception( "Invalid format" );
				$i = $pos+1;
				
				if ($isStart) {
					// should the field be ignored?
					$ignoreField = in_array( $index, $this->ignorePropIndexes );
				}
				
			} else if ($mode == self::T_ARRAY) {
				// read array index
				
				$f_idxt = $raw[$i];
				$i += 2;
				
				switch ($f_idxt) {
				case "}":
					// end of current block
					$break = true;
					$i--;
					break;
				case "i":
					$pos	 = strpos( $raw, ';', $i );
					$f_idxv  = substr( $raw, $i, $pos-$i );
					$i		 = $pos+1;
					break;
				case "s":
					$pos	 = strpos( $raw, ':', $i );
					$f_len	 = substr( $raw, $i, $pos - $i );
					$f_idxv  = substr( $raw, $pos+2, $f_len );
					$i		 = $pos + $f_len + 4;
					break;
				}
			}

			if ($break) break;
			
			$f_type = $raw[$i];
			$i += 2;
			
			switch ($f_type) {
			case "s":
				$pos	 = strpos( $raw, ':', $i );
				$f_len	 = substr( $raw, $i, $pos - $i );
				$f_value = substr( $raw, $pos+2, $f_len );
				$i		 = $pos + $f_len + 3;
				break;
			case "a":
				$pos	 = strpos( $raw, ':', $i );
				$i=$pos+2;
				
				$f_value = $this->compress( $raw, $i, $len, self::T_ARRAY );
				$i--;
				$f_len	 = strlen( $f_value ) - 1;

				break;
			case "N":
				$f_len	 = NULL;
				$f_value = NULL;
				$i--;
				break;
			case "}":
				// end of current block
				$break = true;
				$i--;
				break;
			default:
				$pos	 = strpos( $raw, ';', $i );
				$f_len	 = $pos-$i;
				$f_value = substr( $raw, $i, $pos-$i );
				$i		 = $pos;
			}
			
			if ($break) break;

			$index++;
			
			if ($ignoreField) continue;
			
			if ($mode == self::T_OBJECT) {
				$fields[] = array( $f_type, $f_len, $f_value );
			} else if ($mode == self::T_ARRAY) {
				$fields[] = array( $f_type, $f_len, $f_value, $f_idxt, $f_idxv );
			}
			
		}
		
		// reserialization

		if ($mode == self::T_ARRAY) {
			// check if the array is index by integers from 0
			$match = true;
			$keys = array();
			for ($k=0;$k<count($fields);$k++) {
				if (($fields[$k][3] != 'i') or ($fields[$k][4] != $k)) {
					$match = false;
				}
				$keys[] = array( $fields[$k][3], 
					($fields[$k][3]=='s'?strlen( $fields[$k][4] ):NULL), 
					$fields[$k][4] );
			}
			
			// note
			if ($match) {
				$serialized .= chr( self::T_ARRAY_VALUES );
			} else {
				$serialized .= chr( self::T_ARRAY );
				$types = $this->typeCompress( $keys );
				$serialized .= self::encodeInt( strlen( $types ) ) . $types;
			}
			
			$serialized .= $this->typeCompress( $fields );
			
		} else if ($mode == self::T_OBJECT) {

			$serialized .= $this->typeCompress( $fields );
		
		}
		

		return $serialized;
		
	}

	protected function typeCompress( $fields ) {
	
		$tc_lasttype  = NULL;
		$tc_lastindex = 0;
		$fieldslen	  = count( $fields );
		$serialized   = "";
		if ($fieldslen == 0) return "";

		for ($k=0;$k<=$fieldslen;$k++) {
			
			if ($k<$fieldslen) {
				$field = & $fields[$k];
	
				// type mapping
				$n_type = $this->type2map( $field[0] );
				
				// negative integers have distinct type
				if (($n_type == self::T_INTEGER) && ($field[2] < 0))
					$n_type = self::T_INTEGER_NEG;
				
				// array type extraction	
				if ($n_type == self::T_ARRAY) {
					$n_type = ord( $field[2][0] );
					$field[2] = substr( $field[2], 1 );
				}
				
				// check if found a different type
				if ($tc_lasttype == $n_type) continue;
				if (is_null( $tc_lasttype )) {
					$tc_lasttype = $n_type;
					continue;
				}
				
			}
				
			// serialization part of common type fragment
			
			$tc_count = $k - $tc_lastindex;
			
			if ($tc_count == 1) {
				$serialized .= chr( $tc_lasttype + self::BASE );
			} else {
				$serialized .= chr( $tc_lasttype + self::BASE + self::COMPRESSED ) .
					self::encodeInt( $tc_count );
			}
			
//			  print( "compress $tc_lasttype ($tc_count)<BR>" );
				
			switch ($tc_lasttype) {
			case self::T_INTEGER:
			case self::T_INTEGER_NEG:
				// <type><count> <data><data>...
				for ($y=$tc_lastindex;$y<$k;$y++) {
					$serialized .= self::encodeInt( abs( (integer) $fields[$y][2] ) );
				}
				break;
			case self::T_STRING:
			case self::T_DOUBLE:
			case self::T_ARRAY:
			case self::T_ARRAY_VALUES:
				// <type><count> <len><data><len><data>...
				for ($y=$tc_lastindex;$y<$k;$y++) {
					$serialized .= self::encodeInt( (integer) $fields[$y][1] ) . $fields[$y][2];
				}
				break;
			case self::T_BOOLEAN:
				// <type><count> <data>
				$value = 0;
				for ($y=$tc_lastindex;$y<$k;$y++) {
					if ($fields[$y][2] == 1)
						$value += pow( 2, $y-$tc_lastindex );
				}
				$serialized .= self::encodeInt( $value );
				break;
			}
			
			$tc_lasttype  = $n_type;
			$tc_lastindex = $k;
			
		}

		return $serialized;
	
	}
	
	/**
	 * @todo Add support for property omitting
	 */
	protected function decompress( & $raw, & $i, $len, $props ) {
	
		$serial   = "";
		$cpr_left = 0;
		$cpr_init = 0;
		$index	  = 0;
		$t_bool   = 0;
		$p_serial = array();
		
		while ($i<$len) {
		
			if ($cpr_left == 0) {
				// read type

				$type = ord( $raw[$i] ) - self::BASE;
				$i++;
			
				if ($type > self::COMPRESSED) {
					$compressed = true;
					$type -= self::COMPRESSED;
				} else {
					$compressed = false;
				}
			
				$c_type = $this->map2type( $type ) . ($type==self::T_NULL?"":":");
				if (is_null( $c_type )) {
					throw new Exception( "Bad format" );
				}

				if ($compressed) {
				
					$cpr_left = $cpr_init = self::decodeInt( $raw, $i );
					
				} else {
					$cpr_left = $cpr_init = 1;
				}
				
			}
			
			// write the object property name / key name
			if (is_array( $props ) and !isset( $props[$index] )) {
				// an autonumerated array
				
				$serial .= "i:" . $index . ";";
				
			} else if (is_array( $props ) and is_object( $props[$index] )) {
				// an object property
				
				$name = $props[$index]->getName();
				
				// protected and private properties have modified names
				if ($props[$index]->isProtected()) {
					$name = "\0*\0" . $name;
				} else if ($props[$index]->isPrivate()) {
					$name = "\0" . $this->className . "\0" . $name;
				}
				
				$serial .= "s:" . strlen( $name ) . ":\"" . $name . "\";";
				
			} else if (is_array( $props ) and is_string( $props[$index] )) {
				// a serialized key name
			
				$serial .= $props[$index];
				
			}
			
			// write the data type
			$serial .= $c_type;
			
			switch ($type) {
			case self::T_INTEGER:
				$serial .= self::decodeInt( $raw, $i );
				break;
			case self::T_INTEGER_NEG:
				$serial .= - self::decodeInt( $raw, $i );
				break;
			case self::T_BOOLEAN:
				if ($cpr_left == $cpr_init) {
					$t_bool = self::decodeInt( $raw, $i );
				}
				$serial .= (($t_bool & pow( 2, $cpr_init - $cpr_left ))!=0?"1":"0");
				break;
			case self::T_STRING:
				$s_len = self::decodeInt( $raw, $i );
				$serial .= $s_len . ":\"" . substr( $raw, $i, $s_len ) . "\"";
				$i+=$s_len;
				break;
			case self::T_DOUBLE:
				$s_len = self::decodeInt( $raw, $i );
				$serial .= substr( $raw, $i, $s_len );
				$i+=$s_len;
				break;
			case self::T_ARRAY:
				// <s_len><t_len><types><data>
				//		  ******************** = s_len
				//				 *******	   = t_len
			
				$s_len = self::decodeInt( $raw, $i );
				$s_start = $i;
				$s_klen = self::decodeInt( $raw, $i );
				$s_len += $s_start - $i - $s_klen;
				
				$p_keys = $this->decompress( $raw, $i, $i+$s_klen, NULL );
				
				$serial .= $this->lastPropertyCount . ":{" . 
					$this->decompress( $raw, $i, $i+$s_len, $p_keys ) . "}";
					
				break;
			case self::T_ARRAY_VALUES:
				$s_len	= self::decodeInt( $raw, $i );

				$s_array = $this->decompress( $raw, $i, $i+$s_len, array() );
		
				$serial .= $this->lastPropertyCount . ":{" . $s_array  . "}";
				break;
			}
			
			if (($type != self::T_ARRAY) and ($type != self::T_ARRAY_VALUES)) 
				$serial .= ";";
			
			$cpr_left--;
			$index++;
			
			if (is_null( $props )) {
				$p_serial[] = $serial;
				$serial = "";
			}
			
		}
		
		$this->lastPropertyCount = $index;
	
		if (is_null( $props )) return $p_serial;
	
		return $serial;
	
	}
	
	protected function type2map( $type ) {
		switch ($type) {
		case "s": return self::T_STRING;
		case "N": return self::T_NULL;
		case "d": return self::T_DOUBLE;
		case "i": return self::T_INTEGER;
		case "b": return self::T_BOOLEAN;
		case "a": return self::T_ARRAY;
		}
	}

	protected function map2type( $map ) {
		switch ($map) {
		case self::T_STRING:		 return "s";
		case self::T_NULL:			 return "N";
		case self::T_DOUBLE:		 return "d";
		case self::T_INTEGER:		 return "i";
		case self::T_INTEGER_NEG:	 return "i";
		case self::T_BOOLEAN:		 return "b";
		case self::T_ARRAY:			 return "a";
		case self::T_ARRAY_VALUES:	 return "a";
		}
		return NULL;
	}

	/**
	 * Returns a compact ASCII armoured representation of a positive integer.
	 *
	 * This method creates a character representation of an integer that is composed
	 * of ASCII characters in the range 32 to 126, inclusive.
	 *
	 * NOTE: This method is intended to be used with positive integers only. It is
	 * also capable of encoding negative integers, however the resulting string will
	 * always be 7 bytes long.
	 *
	 * @param integer	$len	An integer to encode.
	 *
	 * @return string
	 *			The returned encoded string is of variable length:
	 *			- 1 char  @ 0..31,
	 *			- 2 chars @ 32..1023,
	 *			- 3 chars @ 1024..32767,
	 *			- 4 chars @ 32768..1048575,
	 *			- 5 chars @ 1048576..
	 *
	 */    
	public static function encodeInt( $len ) {
		if (!is_integer( $len )) throw new dcx_InvalidParameterException( "\$len must be an integer" );
		if ($len < 0) return chr( 64 + ($len & 31) ) . 
		self::encodeInt( (($len >> 1) ^ ((PHP_INT_MAX << 1) - PHP_INT_MAX)) >> 4);
		else if ($len < 32) return chr( 32 + $len );
		else return chr( 64 + ($len & 31) ) . self::encodeInt( $len >> 5 );
	}
	
	/**
	 * Decodes an integer encoded using {@link encodeInt()}.
	 *
	 * @param string	$str	String containing the encoded integer.
	 * @param integer	$pos	Position in the string at which the encoded integer starts.
	 *							After this method executes, this variable points to the index
	 *							just after the last position of the encoded integer.
	 * @return integer	An integer.
	 */
	public static function decodeInt( $str, & $pos ) {
		$n = $b = 0;
		while (true) {
			$c = ord( $str[$pos++] );
			if ($c < 64) return $n + (($c - 32) << ($b*5));
			$n += ($c - 64) << ($b*5);
			$b++;
		}
	}
	
	/**
	 * Decodes an integer encoded using {@link encodeInt()} from an input stream.
	 *
	 * @param du_InputStream   $str    Stream containing the encoded integer.
	 * @return integer	An integer.
	 */
	public static function decodeIntStream( du_InputStream $str ) {
		$n = $b = 0;
		while (true) {
			$c = ord( $str->read() );
			if ($c < 64) return $n + (($c - 32) << ($b*5));
			$n += ($c - 64) << ($b*5);
			$b++;
		}
	}
		

	/**
	 * Returns an array of omitted property names for a class.
	 *
	 * The class should define a constant named <kbd>SERIALIZE_OMIT</kbd> with
	 * a semicolon (;) separated list of property names that will not be serialized.
	 * If the constant is missing, then all properties will be serialized.
	 *
	 * @param string|object    $name   Class name or an object.
	 * @return array	A list of property names.
	 */
	public static function getOmittedProperties( $name ) {
	
		$n_const = (is_object( $name )?get_class( $name ):$name) . "::SERIALIZE_OMIT";
		if (defined( $n_const )) {
			return explode( ";", constant( $n_const ) );
		}
		return array();
	}
	
}
