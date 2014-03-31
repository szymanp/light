<?php

class UI_Component_Pager extends UI_Component_Renderable {

	/**
	 * @var integer
	 */
	protected $currentPage	= 0;

	/**
	 * The index of the first item.
	 * @var integer
	 */
	protected $minimum		= 0;
	
	/**
	 * The index of the last item, i.e. <kbd>$this->minimum</kbd> + item count - 1.
	 * @var integer
	 */
	protected $maximum		= 0;
	
	/**
	 * Number of items per each page.
	 * @var integer
	 */
	protected $perpage	   = 10;
	
	/**
	 * Maximum number of page links to display at once.
	 * @var integer
	 */
	protected $maxpages    = 10;

	/**
	 * Edge Navigation's first item in the list link text.
	 * @var string
	 */
	protected $firstItem   = "&lt;&lt";
	
	/**
	 * Edge Navigation's last item in the list link text.
	 * @var string
	 */
	protected $lastItem    = "&gt;&gt;";
	
	protected $prevItem    = "&lt;";
	
	protected $nextItem    = "&gt;";
	
	/**
	 * Should the selected item be an active link?
	 * @var boolean
	 */
	protected $currentActive	   = true;
	
	/**
	 * Show additional links to the first/last page.
	 * @var boolean
	 */
	protected $edgeNavigation	   = false;
	
	/**
	 * Show additional links to the previous/next page.
	 * @var boolean
	 */
	protected $neighbourNavigation = false;

	/**
	 * @var UI_Util_Pager
	 */
	private $pager;
	
	/**
	 * @var	Data_Collection
	 */
	private $output;
	
	protected function construct() {
		parent::construct();
		$this->registerProperty("Source","Data_Collection");
		$this->registerProperty("Output","Data_Collection");
	}
	
	public function init() {
		parent::init();
	}

	public function load() {
		parent::load();
		
		if ($this->hasProperty("Source")) {
			$this->maximum	= $this->Source->count() - 1;
		}

		$this->pager = new UI_Util_Pager(
			$this->minimum,
			$this->maximum,
			$this->perpage,
			$this->maxpages
			);
			
		$this->pager->setCurrentPage($this->currentPage);
	}
	
	public function getOutput() {
		if (is_null($this->output) && $this->hasProperty("Source")) {
			$this->gotoStage("load");
			$crit = new Data_Criteria();
			$crit->setOffset($this->pager->getCurrentPageItem());
			$crit->setLimit($this->pager->getPageSize());
			$this->output = $this->Source->criteria($crit);
		}
		return $this->output;
	}
	
	public function getPager() {
		return $this->pager;
	}

	public function render() {
		if (!$this->pager->isEmpty()) {
			parent::render();
		}
	}
	
	// events
	
	public function onClick($data) {
		$page = (integer) $data;
		$this->currentPage = $page;
		
		$this->raiseUserEvent("click",$page);
	}

}
