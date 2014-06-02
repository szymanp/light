<?php

namespace Light\Util\Templating;
use Light\Autoloader;
use \Twig_Environment;
use \Twig_Loader_Filesystem;
use \Twig_Loader_String;

require_once "Twig/Environment.php";

class Twig implements TemplateEngine
{
	private $template;
	private $variables = array();
	/** Indicates whether the customizers have been executed. */
	private $customizers = false;
	
	public function __construct()
	{
		$this->setVariable("light", new Utility());
	}

	/**
	 * Returns the underlying engine object.
	 * @return	object
	 */
	public function getEngine()
	{
		return $this->template;
	}
	
	/**
	 * Loads template data from a file.
	 * @return Light\Util\Templating\TemplateEngine	For fluent API.
	 */
	public function loadTemplateFromFile($file)
	{
		$loader = new Twig\Loader();
		$twig = new Twig_Environment($loader);
		TemplateEngineRegistry::getInstance()->runCustomizers($this, "environment", $twig);
		$twig->addTokenParser(new Twig\TemplateTokenParser());
//		$twig->setCache("/tmp/twig");
		$this->template = $twig->loadTemplate($file);
		return $this;
	}
	
	/**
	 * Loads template data from a string.
	 * @return Light\Util\Templating\TemplateEngine	For fluent API.
	 */
	public function loadTemplateFromString($data)
	{
		$loader = new Twig_Loader_String();
		$twig = new Twig_Environment($loader);
		TemplateEngineRegistry::getInstance()->runCustomizers($this, "environment", $twig);
		$this->template = $twig->loadTemplate($data);
		return $this;
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
		if (!$this->customizers)
		{
			TemplateEngineRegistry::getInstance()->runCustomizers($this, "render");
			$this->customizers = true;
		}
		
		// Initialize variables for the root document part
		if (isset($this->variables[self::DOCUMENT_PART_VARIABLE]))
		{
			TemplateRuntimeHelper::assignRootVariables($this->variables[self::DOCUMENT_PART_VARIABLE], $this->variables);
		}

		if ($display)
		{
			$this->template->display($this->variables);
		}
		else
		{
			return $this->template->render($this->variables);
		}
	}

}