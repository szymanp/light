<?php

namespace Light\UI;

use Light\UI\Util\ResourceFinder;
use Light\UI\Framework\Input\DataUnit;
use Light\UI\Framework\Output\ResourceReader;
use Light\UI\Framework\Persist;
use Light\UI\Framework\Listener;
use Light\UI\Framework\ValueProperty;
use \Exception;

/**
 * @property string		Label
 * @property string		Description
 */
class Component extends Framework\Service {

	private $id;

	private $enabled = true;
	
	/** @var Light\UI\Framework\Output\ResourceReader */
	private $resources;

	/**
	 * Note: Tracked properties are case-sensitive.
	 */
	private $persistentProperties = array();
	
	/**
	 * This can be used to override the container's View object.
	 * @var	UI_View
	 */
	protected $view;
	
	protected function construct()
	{
		parent::construct();
		ValueProperty::create($this, "Label","string");
		ValueProperty::create($this, "Description","string");
	}

	/**
	 * Returns the view attached to this component or its ancestor.
	 * @return	Light\UI\View\AbstractView
	 */
	public function getView() {
		if (!is_null( $this->view )) {
			return $this->view;
		}
		$p = $this->getContainer();
		while (!is_null($p)) {
			if (!is_null($p->view)) {
				return $p->view;
			}
			$p = $p->getContainer();
		}
		return NULL;
	}
	
	public function getRootContainer()
	{
		$p = $this;
		while (true) {
			$c = $p->getContainer();
			if (is_null($c)) return $p;
			$p = $c;
		}
		return $p;
	}
	
	/**
     * Finds a {@link Light\UI\Component\Form} container that this component is a part of.
     * @return Light\UI\Component\Form
	 */
	public function getForm()
	{
		return $this->findContainer("Light\UI\Component\Form");
	}
	
	public function getName() {
		if (!is_null( $this->view )) {
			return $this->view->getName();
		} else {
			return parent::getName();
		}
	}
	
	/**
	 * Imports a given resource required by this component.
	 * @param	string	$type	Resource type. Supported types are "javascript" and "css".
	 * @param	string	$name	Dot-path to the resource.
	 * @return	Light\UI\Component	For fluent API.
	 */
	public function import($type, $name, $isModule = true)
	{
		$type = strtolower($type);
		$ap = ViewContext::getInstance()->getAttachmentPoints();
		
		switch ($type) {
		case "js":
		case "javascript":
			$js = $ap->get(ViewContext::AP_JS_IMPORT);
			if ($isModule)	$js->addModule($name, $this);
			else			$js->addFile($name, $this);
			return $this;
		case "css":
			$css = $ap->get(ViewContext::AP_CSS_IMPORT);
			if ($isModule)	$css->addModule($name, $this);
			else			$css->addFile($name, $this);
			return $this;
		default:
			throw new Exception("Unknown resource $type");
		}
	}
	
	// standard attributes
	
	public function getId() {
		if (empty( $this->id )) {
			$this->id = ViewContext::getInstance()->assignElementId($this->getLocalName(),$this);
		}
		return $this->id;
	}
	
	public function hasId() {
		return !empty($this->id);
	}
	
	public function setId($id) {
		ViewContext::getInstance()->registerElementId($id,$this);
		$this->id = $id;
		return $this;	// fluent API
	}
	
	public function setEnabled($v) {
		$this->enabled = $v;
		return $this;	// fluent API
	}
	
	public function getEnabled() {
		return $this->enabled;
	}

	public function attachToView(View\AbstractView $view)
	{
		$this->view = $view;
	}

	// events

	public function enableAjaxEvents()
	{
		$this->getServiceDescriptor()->addMethod("handleEvent");
	}
	
	// persistence
	
	/**
	 * Marks a property as persistent.
	 */
	public function persistProperty($name)
	{
		return $this->persistentProperties[$name] = new Persist\Property($name, $this->getDefaultPropertyRange());
	}
	
	/**
	 * Override this method in a component or its ancestor to return a different default property range.
	 */
	protected function getDefaultPropertyRange()
	{
		$rootContainer = $this->getRootContainer();
		if ($rootContainer === $this)
		{
			return "\\" . get_class($this);
		}
		else
		{
			return $rootContainer->getDefaultPropertyRange();
		}
	}
	
	public function persistComponent(Persist\StoreManager $storemgr, $clientOnly)
	{
		return; // @todo
		$pdefined	= $this->getDefinedRawProperties();
		$pvalues 	= $this->getAssignedRawProperties();
		foreach($this->persistentProperties as $name => $property)
		{
			$store = $storemgr->getStoreFor($property);
			if ($clientOnly == !($store instanceof Persist\ClientStore))
			{
				continue;
			}
			
			if (isset($pdefined[$name]))
			{
				$value = @ $pvalues[$name];
			}
			else if (!is_null($refl = $this->findPropertyDefinition($name)))
			{
				$refl->setAccessible(true);
				$value = $refl->getValue($this);
			}
			else
			{
				throw new Exception("Cannot persist non-existant property '$name'");
			}
//			print($property->getName() . " ==> ");var_dump($value);
			$store->save($this,$property,$value);
		}
	}
	
	public function restoreComponent(Persist\StoreManager $storemgr)
	{
		return; // @todo
		$pdefined	= $this->getDefinedRawProperties();
		foreach($this->persistentProperties as $name => $property)
		{
			$store = $storemgr->getStoreFor($property);
			
			$value = NULL;
			$hasValue = $store->load($this,$property,$value);
			
//			print("$name => ");var_dump($value);
			if (!$hasValue)
			{
				continue;
			}
						
			if (isset($pdefined[$name]))
			{
				$this->setRawProperty($name,$value);
			} 
			else 
			{
				$this->$name = $value;
			}
		}
	}
	
	private function findPropertyDefinition($propertyName)
	{
		$class = get_class($this);
		while ($class)
		{
			if (property_exists($class,$propertyName))
			{
				return new \ReflectionProperty($class,$propertyName);
			}
			
			$class = get_parent_class($class);
		}
		
		return NULL;
	}
	
	public function serializeComponent() {
		$store = new UI_Framework_PersistentData;
		$this->persistComponent($store);
			
		$sz = new Persist_TightSerialize;
		$sz->className = "UI_Framework_PersistentData";
		return $sz->serialize($store);
	}
	
	// special property getters
	
	/**
	 * Prints the value of a property.
	 * @param	string	$name
	 */
	public function propertyRender($name) {
		$this->getPropertyAsText($name,true);
	}

	/**
	 * Returns the value of a property.
	 * If the property is a component, then it is rendered first.
	 * @param	string	$name
	 */
	public function propertyGet($name) {
		return $this->getPropertyAsText($name,false);
	}
	
	public function propertySafe($name) {
		$v = $this->getPropertyAsText($name,false);
		return $this->sanitizeHtml($v);
	}
	
	public function propertyQuoted($name) {
		$v = $this->getPropertyAsText($name,false);
		$v = $this->sanitizeHtml($v);
		$v = strtr($v,"\r\n","  ");
		return $v;
	}
	
	private function getPropertyAsText($name, $print = TRUE) {

		$v = $this->getProperty($name);
		if ($v == null) {
			if (!$print) {
				return "";
			}
		} else if ($v instanceof UI_Component) {
			if (!$print) {
				ob_start();
			}
			try {
				$v->gotoStage("render");
			} catch (Exception $e) {
				print( "<pre>" . $e . "</pre>" );
			}
			if (!$print) {
				$c = ob_get_contents();
				ob_end_clean();
				return $c;
			}
		} else {
			if ($print) {
				print $v;
				return;
			}
			return (string) $v;
		}

	}
	
	// resources
	
	public function loadResources()
	{
		$xmlfile = $this->getResourceFinder()->getDefaultResourceFile($this, ".res.xml");
		$this->resources = new ResourceReader($this);
		$this->resources->loadXml($xmlfile);
	}
	
	public function getResources()
	{
		return $this->resources;
	}

	// RequestHandler interface implementation (partial)
	
	public function getRequestHandler($name, $index = null)
	{
		// a component does not have any children - only a container does
		return null;
	}

}
