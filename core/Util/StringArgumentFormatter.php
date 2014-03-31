<?php

namespace Light\Util;

class StringArgumentFormatter
{
	public static function format($text, array $args)
	{
		for($i=count($args);$i>0;$i--)
		{
			$arg = $args[$i-1];
			
			if (is_string($arg))
			{
				if ("" === $arg)
				{
					$arg = "<empty>";
				}
				else if (trim($arg) === "")
				{
					$arg = "\"" . $arg . "\"";
				}
			}
			else if (is_null($arg))
			{
				$arg = "<NULL>";
			}
			else if (is_object($arg))
			{
				if (method_exists($arg, "__toString"))
				{
					$arg = $arg->__toString();
				}
				else
				{
					$arg = "<" . get_class($arg) . ">";
				}
			}
			else if (is_array($arg))
			{
				$arg = implode($arg, ",");
			}
			else if (!is_scalar($arg))
			{
				$arg = "<" . gettype($arg) . ">";
			}
			
			$text = str_replace("%" . $i, $arg, $text);
		}
		
		return $text;
	}
}