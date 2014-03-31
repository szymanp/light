<?php

namespace Light\UI\Framework\Output;

use Light\UI\Component;

abstract class FileImportAttachmentPoint extends AttachmentPointBase
{
	private $files = array();
	
	/**
	 * @param string	$module
	 * @param Component	$owner
	 * @return Light\UI\Framework\Output\FileImportAttachmentPoint
	 */
	public function addModule($module, Component $owner = NULL)
	{
		$this->files[] = array($owner, $module, true);
		return $this;
	}
	
	/**
	 * @param string	$file
	 * @param Component	$owner
	 * @return Light\UI\Framework\Output\FileImportAttachmentPoint
	 */
	public function addFile($file, Component $owner = NULL)
	{
		$this->files[] = array($owner, $file, false);
		return $this;
	}
	
	public function getHtml(Component $scope = NULL)
	{
		$this->newImporterInstance();
		
		foreach($this->files as $data)
		{
			if (!$this->isWithinScope($scope, $data[0]))
			{
				continue;
			}
			
			if ($data[2])
			{
				$this->addImporterModule($data[1]);
			}
			else
			{
				$this->addImporterFile($data[1]);
			}
			
		}
		
		return $this->getImporterHtml();
	}
	
	abstract protected function newImporterInstance();
	
	abstract protected function addImporterModule($module);
	
	abstract protected function addImporterFile($file);
	
	abstract protected function getImporterHtml();
}
