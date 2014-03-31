<?php

namespace Light\Util\Templating;

interface TemplateEngine
{
	const ROOT_VARIABLE				= "this";
	const DOCUMENT_PART_VARIABLE	= "documentPart";

	/**
	 * Returns the underlying engine object.
	 * @return	object
	 */
	public function getEngine();
	
	/**
	 * Loads template data from a file.
	 * @return \Light\Util\Templating\TemplateEngine	For fluent API.
	 */
	public function loadTemplateFromFile($file);
	
	/**
	 * Loads template data from a string.
	 * @return \Light\Util\Templating\TemplateEngine	For fluent API.
	 */
	public function loadTemplateFromString($data);
	
	/**
	 * Sets the root DocumentPart for the template to be rendered.
	 * @param DocumentPart $rootDocumentPart
	 * @return \Light\Util\Templating\TemplateEngine	For fluent API.
	 */
	public function setRootDocumentPart(DocumentPart $rootDocumentPart);
	
	/**
	 * @return \Light\Util\Templating\TemplateEngine	For fluent API.
	 */
	public function setVariable($name, $value);

	/**
	 * Renders the template.
	 * @param boolean	$display	If true, send the rendered output to the browser
	 * @return	mixed	If <kbd>$display</kbd> is false, returns the rendered output;
	 * 					otherwise, the output is sent to the browser and null is returned. 
	 */
	public function render($display = false);
}