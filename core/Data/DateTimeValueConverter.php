<?php

namespace Light\Data;
use Light\Exception;

class DateTimeValueConverter implements ValueConverter
{
	private $format;
	
	public function __construct($format = "Y-m-d H:i:s")
	{
		$this->format = $format;
	}
	
	public function convertFrom($value)
	{
		if ($value instanceof \DateTime)
		{
			return $value->format($this->format);
		}
		
		throw new Exception\InvalidParameterType('$value', $value, "DateTime");
	}
	
	public function convertTo($value)
	{
		if (is_string($value))
		{
			return \DateTime::createFromFormat($this->format, $value);
		}

		throw new Exception\InvalidParameterType('$value', $value, "string");
	}
}