<?php

namespace Light\UI\Framework\Listener;

interface StageChange
{
	/**
	 * Called when a Component stage change has occurred.
	 * @param	integer	$stage
	 */
	public function onStageChanged($stage);
}
