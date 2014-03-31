<?php

namespace Light\Data\Propel;

class CustomCriterion extends \Criterion
{
	private $params;
	
	public function __construct(\Criteria $outer, $value, array $params)
	{
		parent::__construct($outer, "true", $value, \Criteria::CUSTOM);
		$this->params = $params;
	}

	protected function appendCustomToPs(&$sb, array &$params)
	{
		if ($this->value !== "")
		{
			$cond  = (string) $this->value;
			$index = count($params);
			foreach($this->params as $key => $value)
			{
				$cond = str_replace(":" . $key, ":p" . ++$index, $cond);
				$params[] = array("value" => $value);
			}
			
			$sb .= $cond;
		}
	}
}
