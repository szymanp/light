<?php

namespace Light\UI\Framework\Input;

/**
 * An interface for objects that can accept UI framework requests.
 *
 */
interface RequestHandler
{
	/**
	 * Invokes the specified action.
	 * @param Light\UI\Framework\Input\DataUnit $actionData
	 */
	public function invokeRequestHandlerAction(DataUnit $actionData);
	
	/**
	 * Sets the state of the RequestHandler.
	 * @param Light\UI\Framework\Input\DataUnit[] $dataUnits
	 * @return boolean	This method should return <b>true</b> if the named state property is recognized
	 * 					by the RequestHandler (regardless of whether the value was accepted or not),
	 * 					and <b>false</b> otherwise. A return value of <b>false</b> indicates that
	 *  				the RequestRouter should treat this data unit as a default value for a child handler.
	 */
	public function setRequestHandlerState(DataUnit $dataUnit);
	
	/**
	 * Returns a child RequestHandler.
	 * 
	 * @param string	$name
	 * @param integer	$index 
	 * @return Light\UI\Framework\Input\RequestHandler	A RequestHandler object, if found; otherwise, NULL.
	 */
	public function getRequestHandler($name, $index = null);
}