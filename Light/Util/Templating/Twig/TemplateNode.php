<?php

namespace Light\Util\Templating\Twig;

use Light\UI\Template;
use \Twig_Node;
use \Twig_Compiler;

/**
 * A node for the 'template' tag in Twig templates.
 */
class TemplateNode extends Twig_Node
{
	public function __construct($name, $lineno)
	{
		parent::__construct(array(), array('name' => $name), $lineno);
	}
	
	public function compile(Twig_Compiler $compiler)
	{
		$compiler
			->addDebugInfo($this)
			->write('$context[\'parent\'] = @ $context[\'this\']')
			->raw(";\n")
			->write('\Light\Util\Templating\TemplateRuntimeHelper::assignDocumentPart(\'' . $this->getAttribute('name') . '\', $context)')
			->raw(";\n");
	}
}
