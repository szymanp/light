<?php

namespace Light\UI\Framework\Output;

use Light\UI\Component;

interface AttachmentPoint
{
	/**
	 * Returns the HTML content of this AttachmentPoint.
	 * @param Component $scope	Restrict to the content that is valid to this component and its children.
	 * @return string
	 */
	public function getHtml(Component $scope = NULL);
	
	/**
	 * Prints the HTML content of this AttachmentPoint.
	 * @param Component $scope	Restrict to the content that is valid to this component and its children.
	 */
	public function printHtml(Component $scope = NULL);
}
