<?php

namespace Light\Util\Templating;

/**
 * A DocumentPart provides a hierarchical structure for providing data to a template.
 */
interface DocumentPart
{
	/**
	 * Returns a DocumentPart for the given name.
	 * @param string	$name
	 */
	function getDocumentPart($name);
	
	/**
	 * Returns a list of variables to assign to the current template context.
	 * @return array<string, mixed>
	 */
	function getDocumentVariables();
}

