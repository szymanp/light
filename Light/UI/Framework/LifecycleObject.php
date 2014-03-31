<?php
namespace Light\UI\Framework;
use Light\UI\Framework\Listener;
use Light\UI\Framework\Input\RequestHandler;
use Light\Exception\InvalidParameterValue;
use Light\Exception\Exception;

/**
 * Introduces a lifecycle into the object's activation.
 *
 * A UI component has the following life cycle:
 *
 * 1) CONSTRUCT. The construct() method should define all the properties of the object.
 * 2) The component receives its own (*) state via RequestHandler::setRequestHandlerState().
 * 3) INIT. The init() method should define all the static components and data sources.
 * 4) Queries for child components will be executed via RequestHandler::getRequestHandler().
 * 5) LOAD. At this stage, the load() method can access fully initialized child components.
 * 6) The action is executed, if any, via RequestHandler::invokeRequestHandlerAction().
 * 7) RENDER.
 * 8) FINISH.
 *
 * (*) Note that in step 2. we assume that the component can fully accept its own state into variables,
 *     and not any subcomponents. 
 */
abstract class LifecycleObject extends BindingObject implements RequestHandler
{
	/**
	 * Light\UI\Framework\Listener\StageChange[]
	 */
	private $listeners = array();
	
	private $stage	= self::STATE_CONSTRUCTED;
	
	const STATE_CONSTRUCTED	= 0;
	const STATE_INITED		= 1;
	const STATE_LOADED		= 2;
	const STATE_RENDERED	= 3;
	const STATE_FINISHED	= 4;		

	/**
	 * Sets the lifecycle stage of the object.
	 *
	 * This method will advance the lifecycle stage of an object. By doing this,
	 * it will execute all the necessary lifecycle methods.
	 *
	 * @param string|integer	$stage	A stage string (e.g. "INIT") or one of the STAGE_* constants.
	 */
	public function setLifecycleStage($stage)
	{
		if (is_string($stage))
		{
			$stage = strtolower($stage);
			switch ($stage)
			{
				case "init":	$stage = self::STATE_INITED; break;
				case "load":	$stage = self::STATE_LOADED; break;
				case "render":	$stage = self::STATE_RENDERED; break;
				case "finish":	$stage = self::STATE_FINISHED; break;
				default:		throw new InvalidParameterValue('$stage',$stage);
			}
		}
		
		if ($stage > $this->stage && $this->stage < self::STATE_INITED)
		{
			$this->init();
		}
		if ($stage > $this->stage && $this->stage < self::STATE_LOADED)
		{
			$this->load();
		}
		if ($stage > $this->stage && $this->stage < self::STATE_RENDERED)
		{
			$this->render();
		}
		if ($stage > $this->stage && $this->stage < self::STATE_FINISHED)
		{
			$this->finish();
		}
	}
	
	/**
	 * Returns the current lifecycle stage.
	 * @return integer	One of the STAGE_* constants.
	 */
	public function getLifecycleStage()
	{
		return $this->stage;
	}
	
	// stage handlers

	/**
	 * Override to define component properties.
	 *
	 * When this method is executed, no state data has been provided yet to the component.
	 *
	 * @override	Originally defined in Element.php
	 */
	protected function construct()
	{
		parent::construct();
	}

	/**
	 * Override to initialize component.
	 *
	 * When this method is executed, all state data for this component has already been set.
	 * The child components, however, have not yet been initialized.
	 */
	protected function init()
	{
		$this->stage = self::STATE_INITED;
		foreach($this->listeners as $l) $l->onStageChanged($this->stage);
	}
	
	/**
	 * Override to finish component initialization.
	 *
	 * When this method is executed, the component and all child components are initialized with state data.
	 */ 
	protected function load()
	{
		$this->stage = self::STATE_LOADED;
		foreach($this->listeners as $l) $l->onStageChanged($this->stage);
	}
	
	/**
	 * Override to render the component.
	 */
	protected function render()
	{
		$this->stage = self::STATE_RENDERED;
		foreach($this->listeners as $l) $l->onStageChanged($this->stage);
	}
	
	/**
	 * Override to do any clean-up.
	 *
	 * This method will always be called, even if not all of the other lifecycle methods have been executed.
	 * It is the only <i>public</i> lifecycle method.
	 */
	public function finish()
	{
		$this->stage = self::STATE_FINISHED;
		foreach($this->listeners as $l) $l->onStageChanged($this->stage);
	}
	
	// listeners
	
	/**
	 * Adds a new Stage Change Listener.
	 * @param StageChange	$l
	 */
	public function addStageChangeListener(Listener\StageChange $l)
	{
		if (!in_array($l, $this->listeners, true))
		{
			$this->listeners[] = $l;
		}
	}
}
