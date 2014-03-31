<?php

namespace Light\Service\Util;

/**
 * Decodes arrays passed in URLs into simple, JavaBean-like objects.
 *
 */
class DataObjectDecoder
{
	public function decode(\ReflectionClass $class, $value)
	{
		if (is_null($value))
		{
			return null;
		}
		
		if (is_scalar($value))
		{
			return $class->newInstance($value);
		}
		else if (!is_array($value))
		{
			throw new \Exception("Value needs to be an array");
		}
		else if ($class->getName() == "DateTime")
		{
			return $this->decodeDateTime($value);
		}
		
		$do = $class->newInstance();
		foreach($value as $par_name => $par_value)
		{
			if ($class->hasProperty($par_name) && $class->getProperty($par_name)->isPublic())
			{
				$do->$par_name = $par_value;
			}
			else if ($class->hasMethod($methodName = "set" . $par_name))
			{
				$method = $class->getMethod($methodName);
				if ($method->getNumberOfRequiredParameters() > 1
					|| $method->getNumberOfParameters() < 1)
				{
					throw new \Exception("Method $methodName should accept 1 parameter");
				}
				
				$params = $method->getParameters();
				$param = $params[0];
				
				if (!is_null($param->getClass()))
				{
					$par_value = $this->decode($param->getClass(), $par_value);
				}
				
				$do->$methodName($par_value);
			}
		}
		
		return $do;
	}
	
	/**
	 * Decodes an array assuming it has elements of a given type.
	 * @param \ReflectionClass	$class	Type of array elements.
	 * @param array				$values	Actual values
	 * @return array	Decoded values
	 */
	public function decodeArray(\ReflectionClass $class, $values)
	{
		if (!is_array($values)) $values = array($values);
		
		$result = array();
		foreach($values as $value)
		{
			$result[] = $this->decode($class, $value);
		}
		
		return $result;
	}
	
	/**
	 * Decodes a value assuming it is some date/time value.
	 * @param mixed	$value
	 * @return DateTime
	 */
	public function decodeDateTime($value)
	{
		if (is_numeric($value))
		{
			// we assume this is a UNIX timestamp
			return new \DateTime("@" . $value);
		}
		else
		{
			return new \DateTime($value);
		}
	}
}
