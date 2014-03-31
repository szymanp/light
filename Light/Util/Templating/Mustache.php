<?php

namespace Light\Util\Templating;

use \Mustache_Engine;
use \Mustache_Loader_FilesystemLoader;
use \Mustache_Loader_StringLoader;
use Light\Exception\NotImplementedException;

/**
 * Mustache interface for TemplateEngine.
 * 
 * See: https://github.com/bobthecow/mustache.php/wiki
 *
 */
class Mustache implements TemplateEngine
{
	/** @var \Mustache_Engine */
	private static $defaultEngine;
	
	/**
	 * Sets the default engine to be used with these TemplateEngine instances.
	 * @param Mustache_Engine $engine
	 */
	public static function setDefaultEngine(Mustache_Engine $engine = NULL)
	{
		self::$defaultEngine = $engine;
	}
	
	/** @var \Mustache_Engine */
	private $engine;
	private $template;
	private $options = array(
		'basepath'	=> '/'
	);
	private $variables = array();
	
	public function __construct()
	{
		$this->engine = self::$defaultEngine;
		$this->setVariable("light", new Utility());
	}
	
	/**
	 * Sets an option to be passed to the Mustache_Engine constructor.
	 * @param string	$name
	 * @param mixed		$value
	 * @return Light\Util\Templating\Mustache
	 */
	public function setOption($name, $value)
	{
		$this->options[$name] = $value;
		return $this;
	}
	
	/**
	 * Returns the underlying engine object.
	 * @return	object
	 */
	public function getEngine()
	{
		if (is_null($this->engine))
		{
			$this->engine = new Mustache_Engine($this->options);
		}
		
		return $this->engine;
	}
	
	/**
	 * Loads template data from a file.
	 * @return Light\Util\Templating\TemplateEngine	For fluent API.
	 */
	public function loadTemplateFromFile($file)
	{
		$engine = $this->getEngine();
		$loader = new Mustache_Loader_FilesystemLoader($this->options['basepath']);
		$engine->setLoader($loader);
		$this->template = $engine->loadTemplate($file);
		return $this;
	}
	
	/**
	 * Loads template data from a string.
	 * @return Light\Util\Templating\TemplateEngine	For fluent API.
	 */
	public function loadTemplateFromString($data)
	{
		$engine = $this->getEngine();
		$loader = new Mustache_Loader_StringLoader();
		$engine->setLoader($loader);
		$this->template = $engine->loadTemplate($data);
		return $this;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Light\Util\Templating\TemplateEngine::setRootDocumentPart()
	 */
	public function setRootDocumentPart(DocumentPart $rootDocumentPart)
	{
		throw new NotImplementedException("Mustache templates do not support DocumentParts");
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

		$data = $this->variables;
		
		if ($display)
		{
			print $this->template->render($data);
		}
		else
		{
			return $this->template->render($data);
		}
	}

}