<?php

namespace Light\UI\Framework\Output;

use Light\UI\Util\ClientEnvironment;
use Light\UI\Component;

class ClientEnvironmentAttachmentPoint extends AttachmentPointBase
{
	private $envs = array();
	
	/**
	 * @param Component $owner
	 * @param ClientEnvironment $e
	 * @return Light\UI\Framework\Output\ClientEnvironmentAttachmentPoint
	 */
	public function add(ClientEnvironment $e)
	{
		$this->envs[] = $e;
		return $this;
	}
	
	public function getHtml(Component $scope = NULL)
	{
		$data = array();

		foreach($this->envs as $env)
		{
			if (!$this->isWithinScope($scope, $env->getTarget()))
			{
				continue;
			}
			
			$d = $env->getJsonArray();
			if (!empty($d))
			{
				$data[$env->getName()] = $d;
			}
		}
		
		if (empty($data))
		{
			return "";
		}
		
		$str = "<script type=\"text/javascript\">\n";
		$str .= "if (typeof (Light) == \"undefined\") { Light = function() {}; }\n";
		$str .= "if (typeof (Light.Env) == \"undefined\") { Light.Env = function() {}; }\n";
		$str .= "Light.Env.CE = " . json_encode($data) . ";\n";
		$str .= "</script>";
		
		return $str;
	}
}