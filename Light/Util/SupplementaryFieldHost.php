<?php

namespace Light\Util;

class SupplementaryFieldHost implements SupplementaryFields
{
	/** @var SupplementaryFields */
	private $owner;
	
	/** @var array<string, mixed> */
	private $fields = array();
	
	public function __construct(SupplementaryFields $owner)
	{
		$this->owner = $owner;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Light\Util\SupplementaryFields::getSupplementaryValue()
	 */
	public function getSupplementaryValue($fieldName)
	{
		if (isset($this->fields[$fieldName]))
		{
			$value = $this->fields[$fieldName];
			
			if ($value instanceof \Closure)
			{
				return $value($this->owner);
			}
			else
			{
				return $value;
			}
		}
		else
		{
			return null;
		}
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Light\Util\SupplementaryFields::hasSupplementaryValue()
	 */
	public function hasSupplementaryValue($fieldName)
	{
		return isset($this->fields[$fieldName]);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Light\Util\SupplementaryFields::setSupplementaryValue()
	 */
	public function setSupplementaryValue($fieldName, $value)
	{
		$this->fields[$fieldName] = $value;
	}
}