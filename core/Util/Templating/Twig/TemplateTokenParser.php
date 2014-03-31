<?php

namespace Light\Util\Templating\Twig;

use Light\UI\Template;
use \Twig_Token;
use \Twig_TokenParser;

/**
 * A parser for the 'template' tag in Twig templates.
 */
class TemplateTokenParser extends Twig_TokenParser
{
	public function parse(Twig_Token $token)
	{
		$lineno = $token->getLine();
		
		$name = $this->parser->getStream()->expect(Twig_Token::STRING_TYPE)->getValue();
		$name = strtr($name, "/", "\\");
		$this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

		return new TemplateNode($name, $value, $lineno, $this->getTag());
	}

	public function getTag()
	{
		return 'template';
	}
}
