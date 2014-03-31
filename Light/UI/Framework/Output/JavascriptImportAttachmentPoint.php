<?php

namespace Light\UI\Framework\Output;

use Light\Util\Javascript\Import;

class JavascriptImportAttachmentPoint extends FileImportAttachmentPoint
{
	private $jsimporter;
	
	protected function newImporterInstance()
	{
		$this->jsimporter = new Import();
	}
	
	protected function addImporterModule($module)
	{
		$this->jsimporter->addModule($module);
	}
	
	protected function addImporterFile($file)
	{
		$this->jsimporter->addFile($file);
	}
	
	protected function getImporterHtml()
	{
		return $this->jsimporter->getHTML();
	}
}
