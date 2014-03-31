<?php
namespace Light\UI\Util;

/**
 * <kbd>Pager</kbd> divides a continuous set of numbers into pages of fixed length.
 *
 * This class can be used to easily calculate the indexes of items for each page in
 * a list of some items.
 *
 * Suppose we have a list consisting of 1537 items that we want to divide into pages.
 * Each page should contain at most 15 items, and there should not be more than
 * 7 links to pages displayed at any time.
 *
 * <code>
 * $p = new Pager( 0, 1537, 15, 7 );
 * $p->setCurrentPage( 0 );
 *
 * foreach( $p as $page=>$index ) {
 *		if ($p->isPageCurrent( $page ))
 *		   print( "[" . ($page+1) . "] " );
 *		else
 *		   print( "<a href='page.php?item=$index'>" . ($page+1) . "</a> " );
 * }
 * </code>
 *
 * The above code will produce a list of links similar to:
 * <pre>[1] 2 3 4 5 6 7</pre>
 */
class Pager implements \Iterator, \Countable {

	private $minimum;
	private $maximum;
	private $perpage;
	private $maxpages;
	
	/**
	 * Index of the current item.
	 * @var integer
	 */
	private $currentItem;
	
	/**
	 * Index of the current page.
	 * @var integer
	 */
	private $currentPage		= 0;
	
	/**
	 * Index of the last page.
	 * @var integer
	 */
	private $lastPage;
	
	/**
	 * Index of the first item that falls on the selected page.
	 * @var integer
	 */
	private $rangeStart;

	/**
	 * Index of the last item that falls on the selected page.
	 * @var integer
	 */
	private $rangeEnd;
	
	/**
	 * Page iterator index.
	 * @var integer
	 */
	private $iterator;
	
	private $iteratorStart;
	
	private $iteratorEnd;
	
	/**
	 * Constructs a new Pager object.
	 * @param integer	$minimum		Index of the first item.
	 * @param integer	$maximum		Index of the last item.
	 * @param integer	$perpage		Number of items per single page.
	 * @param integer	$maxpages		Maximum number of pages to return at once.
	 */
	public function __construct( $minimum, $maximum, $perpage = 10, $maxpages = 10 ) {
	
		$this->minimum	= $minimum;
		$this->maximum	= $maximum;
		$this->perpage	= $perpage;
		$this->maxpages = $maxpages;
		
		$this->update( $minimum );
	}
	
	/**
	 * Performs the actual calculation of pages.
	 * @param integer	$currentItem
	 */
	private function update( $currentItem ) {
	
		// sanitize the item index
		if ($currentItem < $this->minimum)
			$currentItem = $this->minimum;
		if ($currentItem > $this->maximum)
			$currentItem = $this->maximum;
	
		// current item and page indexes
		$this->currentPage		= floor( ($currentItem - $this->minimum) / $this->perpage );
		$this->currentItem		= $currentItem;
		$this->rangeStart		= $this->currentPage * $this->perpage + $this->minimum;
		$this->rangeEnd			= $this->rangeStart + $this->perpage - 1;
		if ($this->rangeEnd > $this->maximum) $this->rangeEnd = $this->maximum;
		
		$this->lastPage			= floor( ($this->maximum - $this->minimum) / $this->perpage );
		
		// iterator range
		if ($this->lastPage < $this->maxpages) {
			// there are less pages than maxpages
			
			$this->iteratorStart	= 0;
			$this->iteratorEnd		= $this->lastPage;
		} else {
			// there are more pages than maxpages
			
			$halfLo = (integer) floor( ($this->maxpages-1) / 2 );
			$halfHi = (integer) ceil( ($this->maxpages-1) / 2 );

			$this->iteratorStart	= $this->currentPage - $halfLo;
			$this->iteratorEnd		= $this->currentPage + $halfHi;
			
			if ($this->iteratorStart < 0) {
				$halfHi += $halfLo - $this->currentPage;
				$this->iteratorStart = 0;
				$this->iteratorEnd	 = $this->currentPage + $halfHi;
			} else if ($this->iteratorEnd > $this->lastPage) {
				$halfLo += $halfHi - ($this->lastPage - $this->currentPage);
				$this->iteratorStart = $this->currentPage - $halfLo;
				$this->iteratorEnd	 = $this->lastPage;
			}
		}
		
		$this->iterator = $this->iteratorStart;
	}
	
	/**
	 * Checks if the Pager has any pages.
	 * @return boolean	TRUE if the Pager doesn't have any pages.
	 */
	public function isEmpty() {
		return ($this->iteratorStart == $this->iteratorEnd);
	}
	
	/**
	 * Number of pages that will be displayed.
	 * @return integer
	 */
	public function count() {
		return $this->iteratorEnd - $this->iteratorStart;
	}
	
	/**
	 * Sets the currently selected page.
	 * @param integer	$page	Page number.
	 */
	public function setCurrentPage( $page ) {
		if ($page < 0) $page = 0;
		$this->update( $this->minimum + $page * $this->perpage );
	}
	
	/**
	 * Sets the currently selected item.
	 * @param integer	$item	Item index.
	 */
	public function setCurrentItem( $item ) {
		$this->update( $item );
	}
	
	/**
	 * Tests if the given page is currently selected.
	 * @param integer	$page	Page number.
	 * @return boolean
	 */
	public function isPageCurrent( $page ) {
		return $this->currentPage == $page;
	}
	
	/**
	 * Tests if the given item index lies on the currently selected page.
	 * @param integer	$item	Item index.
	 * @return boolean
	 */
	public function isItemCurrent( $item ) {
		return ($item >= $this->rangeStart) and ($item <= $this->rangeEnd);
	}
	
	/**
	 * Returns the index of the first item on the first page.
	 * @return integer
	 */
	public function getFirstPageItem() {
		return $this->minimum;
	}

	/**
	 * Returns the index of the first item on the current page.
	 * @return integer
	 */
	public function getCurrentPageItem() {
		return $this->rangeStart;
	}

	/**
	 * Returns the page size.
	 * @return integer
	 */	
	public function getPageSize() {
		return $this->perpage;
	}
	
	/**
	 * Returns the index of the first item on the last page.
	 * @return integer
	 */
	public function getLastPageItem() {
		return (integer) $this->lastPage * $this->perpage + $this->minimum;
	}
	
	/**
	 * Returns the number of the first page.
	 * The pages are numbered from 0.
	 * @return integer	This is always 0.
	 */
	public function getFirstPage() {
		return 0;
	}
	
	/**
	 * Returns the number of the current page.
	 * @return integer
	 */
	public function getCurrentPage() {
		return $this->currentPage;
	}
	
	/**
	 * Returns the number of the last page.
	 * @return integer
	 */
	public function getLastPage() {
		return $this->lastPage;
	}
	
	/**
	 * Moves the iterator to the last position.
	 */
	public function end() {
		$this->iterator = $this->iteratorEnd;
	}
	
	/**
	 * Moves the iterator to the previous position.
	 */
	public function prev() {
		$this->iterator--;
	}
	
	/**
	 * Moves the iterator to the currently selected page.
	 */
	public function seekCurrent() {
		$this->iterator = $this->currentPage;
	}
	
	/*
	 * Iterator interface
	 */
	
	/**
	 * Returns the index of the first item on the current page.
	 * @return integer
	 */
	public function current() {
		if (is_null( $this->iterator ))
			$this->update();
			
		return $this->minimum + $this->iterator * $this->perpage;
	}
	
	/**
	 * Returns the number of the current page.
	 * @return integer
	 */
	public function key() {
		if (is_null( $this->iterator ))
			$this->update();
		
		return $this->iterator;
	}
	
	/**
	 * Moves the iterator to the next page.
	 */
	public function next() {
		if (is_null( $this->iterator ))
			$this->update();
			
		$this->iterator++;
	}
	
	/**
	 * Resets the iterator.
	 */
	public function rewind() {
		if (is_null( $this->iterator ))
			$this->update();
		
		$this->iterator = $this->iteratorStart;
	}
	
	/**
	 * Checks if the iterator is within range.
	 * @return boolean
	 */
	public function valid() {
		return ($this->iterator >= $this->iteratorStart) and
			($this->iterator <= $this->iteratorEnd);
	}

}
