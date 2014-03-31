<?php

namespace Light\Data\Sql;

class SqlHelper
{
	/**
	 * Builds an IN value list.
	 * @param array $elems
	 * @return string
	 */
	public static function buildIn(array $elems)
	{
		if (empty($elems))
		{
			return "(NULL)";
		}
		
		$str .= "(";
		$first = true;
		foreach($elems as $e)
		{
			if (!$first) $str .= ",";
			else $first = false;
			
			$str .= (int) $e;
		}
		$str .= ")";
		
		return $str;
	}
}
