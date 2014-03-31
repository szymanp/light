<?php

namespace Light\Util\Templating;

class StrRepl implements TemplateEngine
{
	private $template;
	private $variables = array();
	
	/**
	 * Returns the underlying engine object.
	 * @return	object
	 */
	public function getEngine()
	{
		return $this;
	}
	
	/**
	 * Loads template data from a file.
	 * @return Light\Util\Templating\TemplateEngine	For fluent API.
	 */
	public function loadTemplateFromFile($file)
	{
		$this->template = file_get_contents($file);
	}
	
	/**
	 * Loads template data from a string.
	 * @return Light\Util\Templating\TemplateEngine	For fluent API.
	 */
	public function loadTemplateFromString($data)
	{
		$this->template = $data;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Light\Util\Templating\TemplateEngine::setRootDocumentPart()
	 */
	public function setRootDocumentPart(DocumentPart $rootDocumentPart)
	{
		$this->variables[self::DOCUMENT_PART_VARIABLE] = $rootDocumentPart;
		return $this;
	}
	
	/**
	 * @return Light\Util\Templating\TemplateEngine	For fluent API.
	 */
	public function setVariable($name, $value)
	{
		$this->variables[$name] = $value;
		return $this;
	}

	/**
	 * Renders the template.
	 * @param boolean	$display	If true, send the rendered output to the browser
	 * @return	mixed	If <kbd>$display</kbd> is false, returns the rendered output;
	 * 					otherwise, the output is sent to the browser and null is returned. 
	 */
	public function render($display = false)
	{
		$out = $this->template;
		foreach($this->variables as $key => $value)
		{
			$out = str_replace($key, (string) $value, $out);
		}
		
		if ($display)
		{
			print $out;
		}
		else
		{
			return $out;
		}
	}

}