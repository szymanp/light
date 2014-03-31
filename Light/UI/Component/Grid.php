<?php

namespace Light\UI\Component;
use Light\Util;
use Light\UI;

/**
 * 
 * @author Piotrek
 * @property boolean	AutoLabels	Setting this to true will result in the component's label decorator being removed and
 * 									instead, the label will be printed in an additional TD to the left of the component.
 * @property integer	Columns		Number of columns to render.
 * @property integer	Rows		Number of rows to render.
 */
class Grid extends \UI_Component_Renderable {
	
	const RowProperty = "Light\UI\Component\Grid::Row";
	const ColumnProperty = "Light\UI\Component\Grid::Column";
	
	protected $rowAttributes = array();
	protected $colAttributes = array();
	
	private $currentRow = 0;
	
	protected function construct()
	{
		parent::construct();
		$this->registerProperty("Columns","integer",1);
		$this->registerProperty("Rows","integer",1);
		$this->registerProperty("AutoLabels","boolean",true);
	}
	
	public function add(\Light\UI\Component $c)
	{
		parent::add($c);
		if (!$c->hasProperty(self::RowProperty))
		{
			$c->setProperty(self::RowProperty, $this->currentRow++);
			if ($this->currentRow > $this->Rows)
			{
				$this->Rows = $this->currentRow;
			}
		}
		if (!$c->hasProperty(self::ColumnProperty))
		{
			$c->setProperty(self::ColumnProperty, 0);
		}
		return $this;
	}
	
	public function getRowAttributes($index)
	{
		if (!isset($this->rowAttributes[$index]))
		{
			$this->rowAttributes[$index] = new UI\Util\TagAttributes();
		}
		return $this->rowAttributes[$index];
	}

	public function getColumnAttributes($index)
	{
		if (!isset($this->colAttributes[$index]))
		{
			$this->colAttributes[$index] = new UI\Util\TagAttributes();
		}
		return $this->colAttributes[$index];
	}
	
	public function render()
	{
		if (!$this->getVisible()) {
			return;
		}
		
		$this->renderPart("before");
		
		$table = array_fill(0,$this->Rows,array_fill(0,$this->Columns,array()));
	
		$autolabels = $this->AutoLabels;
		
		foreach($this->elements as $el)
		{
			$row = (integer) $el->getProperty(self::RowProperty);
			$col = (integer) $el->getProperty(self::ColumnProperty);
			$table[$row][$col][] = $el;
			
			if ($autolabels)
			{
				$el->removePart("before", "Light\UI\Decorator\Label");
			}
		}

		?><table id='<?=$this->getId()?>'<?php if (isset($this->Description)): ?>
		summary='<?=$this->sanitizeHtml($this->Description)?>'<?php endif ?><?=$this->getAttributeStr()?>><?php

		if ($this->hasProperty("Label"))
		{
			print("<caption>" . $this->propertyGet("Label") . "</caption>");
		}
		
		foreach($table as $rowIndex => $row)
		{
			print("<tr"
				. (isset($this->rowAttributes[$rowIndex])?" " . $this->rowAttributes[$rowIndex] : "")
				. ">");
			
			foreach($row as $colIndex => $col)
			{
				if ($autolabels)
				{
					$colIndex *= 2;
					
					print("<td"
						. (isset($this->colAttributes[$colIndex])?" " . $this->colAttributes[$colIndex] : "")
						. ">");
										
					if (!empty($col) && $col[0]->hasProperty("Label"))
					{
						print($col[0]->propertyGet("Label") . ":");
					}
					print("</td>");
					
					$colIndex++;
				}
				
				print("<td"
					. (isset($this->colAttributes[$colIndex])?" " . $this->colAttributes[$colIndex] : "")
					. ">");
				foreach($col as $el)
				{
					print $el;
				}
				print("</td>\n");
			}
			print("</tr>\n");
		}
		
		print("</table>");
		
		$this->renderPart("after");				
	}

}
