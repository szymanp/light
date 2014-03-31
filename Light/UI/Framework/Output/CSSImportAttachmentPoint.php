<?php

namespace Light\UI\Framework\Output;

use Light\Util\CSSImport;

class CSSImportAttachmentPoint extends FileImportAttachmentPoint
{
	private $cssimporter;
	
	protected function newImporterInstance()
	{
		$this->cssimporter = new CSSImport();
	}
	
	protected function addImporterModule($module)
	{
		$this->cssimporter->addModule($module);
	}
	
	protected function addImporterFile($file)
	{
		$this->cssimporter->addFile($file);
	}
	
	protected function getImporterHtml()
	{
		return $this->cssimporter->getHTML();
	}
}
