<?php

namespace Light\UI\Component\Page;
use Light\Util\Controller\Controller;
use Light\UI\Component;
use Light\Util;

/**
 * 
 * @author Piotrek
 * @property boolean	AutoLabels	Setting this to true will result in the component's label decorator being removed and
 * 									instead, the label will be printed in an additional TD to the left of the component.
 * @property boolean	ShowLabel	Should the TableForm's own label be printed?
 * @property integer	Columns		Number of columns to render.
 * @property integer	Rows		Number of rows to render.
 */
class TableForm extends Component\Page {
	
	const RowProperty = "Light\UI\Component\Page\TableForm::Row";
	const ColumnProperty = "Light\UI\Component\Page\TableForm::Column";
	
	private $currentRow = 0;
	
	protected function construct()
	{
		parent::construct();
		$this->registerProperty("Columns","integer",1);
		$this->registerProperty("Rows","integer",1);
		$this->registerProperty("AutoLabels","boolean",true);
		$this->registerProperty("ShowLabel","boolean",false);
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
	
	public function render()
	{
		if (!$this->getVisible()) {
			return;
		}
		
		$this->renderPart("before");
		
		if (is_null($this->getForm()))
		{
			$invokedClass = Controller::getInstance()->getInvokedClass();
			$url = Controller::getInstance()->getHref($invokedClass, array());
			$args = array();
			\UI_Scene::getInstance()->appendFormArguments($invokedClass, $args);
			
			// print a form
			print( "<form name='" . $this->getName() . "' title='"
				. $this->sanitizeHtml($this->Label) . "' action='"
				. $this->sanitizeHtml($url) ."'>\n" );
				
			foreach($args as $name => $value)
			{
				print("<input type='hidden' name='" . $this->sanitizeHtml($name) . "' value='" .
					$this->sanitizeHtml($value) . "'/>\n");
			}
		}
		
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

		if ($this->hasProperty("Label") && $this->getProperty("ShowLabel"))
		{
			print("<caption>" . $this->propertyGet("Label") . "</caption>");
		}
		
		foreach($table as $row)
		{
			print("<tr>");
			foreach($row as $col)
			{
				if ($autolabels)
				{
					print("<td>");
					if (!empty($col) && $col[0]->hasProperty("Label"))
					{
						print($col[0]->propertyGet("Label") . ":");
					}
					print("</td>");
				}
				
				print("<td>");
				foreach($col as $el)
				{
					print $el;
				}
				print("</td>\n");
			}
			print("</tr>\n");
		}
		
		print("</table>");
		
		if (!$this->hasContainer()) {
			print( "</form>" );
		}
		
		$this->renderPart("after");				
	}

}
