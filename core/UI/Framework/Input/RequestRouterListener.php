<?php

namespace Light\UI\Framework\Input;

interface RequestRouterListener
{
	public function beforeSetState(RequestHandler $target);
	
	public function afterSetState(RequestHandler $target);
	
	public function beforeRunAction(RequestHandler $target);
	
	public function afterRunAction(RequestHandler $target);
}