<?php

namespace Light\Util\Templating;

final class TemplateRuntimeHelper
{
	private function __construct()
	{
		// private constructor
	}
	
	/**
	 * Assigns a document part to the current context.
	 * @param string	$name
	 * @param array 	$context
	 */
	public static function assignDocumentPart($name, array & $context)
	{
		$currentDocumentPart = @ $context[TemplateEngine::DOCUMENT_PART_VARIABLE];
		
		if ($currentDocumentPart instanceof DocumentPart)
		{
			// We are inside a template part associated with a DocumentPart.
			// Therefore we ask the current DocumentPart for a subpart.
			
			$newDocumentPart = $currentDocumentPart->getDocumentPart($name);
			$context[TemplateEngine::DOCUMENT_PART_VARIABLE] = $newDocumentPart;
			self::initializeDocumentPartVariables($newDocumentPart, $context);
		}
		else
		{
			// There is no DocumentPart in scope. We instantiate the named class.
			// If this class is a DocumentPart, then we put it in context;
			// otherwise, we assign that class to the 'this' variable.
			
			$instance = new $name(null, $context);
			
			if ($instance instanceof DocumentPart)
			{
				$context[TemplateEngine::DOCUMENT_PART_VARIABLE] = $instance;
				self::initializeDocumentPartVariables($instance, $context);
			}
			else 
			{
				$context[TemplateEngine::ROOT_VARIABLE] = $instance;
			} 
		}
	}
	
	/**
	 * Assign variables for the root document part.
	 * @param DocumentPart $rootDocumentPart
	 * @param array $variables
	 */
	public static function assignRootVariables(DocumentPart $rootDocumentPart, array & $variables)
	{
		self::initializeDocumentPartVariables($rootDocumentPart, $variables);
	}
	
	private static function initializeDocumentPartVariables(DocumentPart $documentPart, array & $context)
	{
		$variables = $documentPart->getDocumentVariables();
		if (is_array($variables))
		{
			foreach($variables as $key => $value)
			{
				$context[$key] = $value;
			}
		}
	}
}

