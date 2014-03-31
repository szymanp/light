<?php

namespace Light\UI;

use Light\UI\Util\ClientEnvironment;
use Light\UI\View\AbstractView;
use Light\UI\Framework\Output\EventAttachmentPoint;
use Light\UI\Framework\Output\CSSImportAttachmentPoint;
use Light\UI\Framework\Output\JavascriptImportAttachmentPoint;
use Light\UI\Framework\Output\ClientEnvironmentAttachmentPoint;
use Light\UI\Framework\Output\JavascriptAttachmentPoint;
use Light\UI\Framework\Output\HtmlAttachmentPoint;
use Light\UI\Framework\Output\AttachmentPoints;
use Light\UI\Framework\Persist;
use Light\UI\Framework;
use Light\Util\Javascript;

/**
 * ViewContext holds information which is common to all views in the generated HTML page.
 */
final class ViewContext
{
	/** An attachment point for HTML in the document's <<HEAD>> */
	const AP_HTML_HEAD			= "html-head";
	/** An attachment point for client environment JS in the document's <<HEAD>> */
	const AP_CLIENT_ENVIRONMENT	= "js-client";
	/** An attachment point for the CSS Importer in the document's <<HEAD>> */
	const AP_CSS_IMPORT			= "css-import";
	/** An attachment point for the Javascript Importer in the document's <<HEAD>> */
	const AP_JS_IMPORT			= "js-import";
	/** An attachment point for the Javascript events in the document's <<HEAD>> */
	const AP_JS_EVENT			= "js-event";
	/** An attachment point for the Javascript code in the document's <<HEAD>> */
	const AP_JS_TEXT			= "js";

	/**
	 * @var Light\UI\ViewContext
	 */
	private static $instance;
	
	/**
	 * Returns the singleton instance of the ViewContext class.
	 * @return Light\UI\ViewContext
	 */
	public function getInstance()
	{
		if (is_null( self::$instance ))
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	private $viewNames = array();
	
	private $elementIDs = array();
	
	/** @var Persist\StoreManager */
	private $storeManager;
	
	/** @var Light\UI\Framework\Output\AttachmentPoints */
	private $attachmentPoints;
	
	/**
	 * Private constructor.
	 */
	private function __construct()
	{
		$this->storeManager		= new Persist\StoreManager();
		$this->attachmentPoints	= new Framework\Output\AttachmentPoints();
		
		$this->attachmentPoints
			->define("head")
			->add("head",	self::AP_HTML_HEAD,				new HtmlAttachmentPoint())
			->add("head",	self::AP_CSS_IMPORT,			new CSSImportAttachmentPoint())
			->add("head",	self::AP_JS_IMPORT,				new JavascriptImportAttachmentPoint())
			->add("head",	self::AP_CLIENT_ENVIRONMENT,	new ClientEnvironmentAttachmentPoint())
			->add("head",	self::AP_JS_TEXT,				new JavascriptAttachmentPoint())
			->add("head",	self::AP_JS_EVENT,				new EventAttachmentPoint());
	}
	
	public function registerElementID($id, Component $c)
	{
		if (isset($this->elementIDs[$id])) {
			throw new Exception("Element ID '$id' is already in use.");
		}
		$this->elementIDs[$id] = $c;
	}
	
	public function assignElementID($suggestedId, Component $c)
	{
		$i = 0;
		$id = $suggestedId;
		while (isset($this->elementIDs[$id])) {
			$id = $suggestedId . (++$i);
		}
		
		$this->elementIDs[$id] = $c;
		return $id;
	}
	
	public function registerView(AbstractView $v,$name) {
		if (isset($this->viewNames[$name])) {
			throw new Exception("Name '$name' is already used by another view");
		}
		$this->viewNames[$name] = $v;
	}
	
	/**
	 * @return Light\UI\Framework\Output\JavascriptImportAttachmentPoint
	 */
	public function getJsImporter()
	{
		return $this->attachmentPoints->get(self::AP_JS_IMPORT);
	}
	
	/**
	 * @return Light\UI\Framework\Output\CSSImportAttachmentPoint
	 */
	public function getCssImporter()
	{
		return $this->attachmentPoints->get(self::AP_CSS_IMPORT);
	}
	
	/**
	 * @return Persist\StoreManager
	 */
	public function getStoreManager()
	{
		return $this->storeManager;
	}

	public function appendUriArguments($class, array &$args)
	{
		$store = $this->storeManager->getStore(Persist\Store::REQUEST + Persist\Store::URI);
		if ($store instanceof Persist\UriStore)
		{
			$store->appendArguments($class,$args);
		}
		
		$store = $this->storeManager->getStore(Persist\Store::REQUEST + Persist\Store::SERVER);
		if ($store instanceof Persist\RequestSessionStore)
		{
			$store->appendArguments($args);
		}
		
	}
	
	public function appendFormArguments($class, array &$args)
	{
		$this->appendUriArguments($class, $args);
		
		$store = $this->storeManager->getStore(Persist\Store::REQUEST + Persist\Store::FORM);
		if ($store instanceof Persist\FormStore)
		{
			$store->appendArguments($class,$args);
		}
	}		

	/**
	 * Returns the attachment points holder.
	 * @return Light\UI\Framework\Output\AttachmentPoints
	 */
	public function getAttachmentPoints()
	{
		return $this->attachmentPoints;
	}
}