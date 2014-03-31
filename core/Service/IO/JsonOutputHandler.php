<?php
namespace Light\Service\IO;
use Light\Exception\UserException;

use Light\Exception\CompositeUserException;

use Light\Service\Exception\ServiceContainerException;

use \stdClass;

class JsonOutputHandler extends OutputHandler {

	/**
	 * Sends a reply containing the method return value.
	 * @param mixed	$resp
	 */
	public function sendResponse( $resp )
	{
		if (is_null( $resp ))
			$resp = new stdClass();
		
		$this->sendValue( $resp );
	}
	
	/**
	 * Sends a reply informing about a fault that occurred during execution.
	 * @param Exception	$e
	 */
	public function sendFault(\Exception $e)
	{
		$code = ($e instanceof ServiceContainerException)?$e->getHttpErrorCode() : 500;
		$this->getHttpResponse()->sendStatus($code);
		
		$resp = new stdClass;
		$resp->__TYPE	= "exception";
		$resp->__CLASS	= get_class($e);
		$resp->message	= $e->getMessage();
		$resp->data		= $e->__toString();
		
		if ($e instanceof UserException)
		{
			$this->sendUserFault($e, $resp);
		}
		
		$this->sendValue( $resp );
	}
	
	private function sendUserFault(UserException $e, \stdClass $resp)
	{
		if (!is_null($field = $e->getField()))
		{
			$resp->fieldName = $field;
		}
		
		if ($e instanceof CompositeUserException)
		{
			$resp->inner = array();
			
			foreach($e->getExceptions() as $inner)
			{
				$result = new \stdClass;
				$result->__TYPE		= "exception";
				$result->__CLASS	= get_class($inner);
				$result->message	= $inner->getMessage();
				
				$this->sendUserFault($inner, $result);
				$resp->inner[] = $result;
			}
		}
	}
	
	private function sendValue( $value )
	{
		// do not issue a content-type that the browser does not accept
		$ct_send	= $this->getContentType();
		$ct_accept	= $this->getHttpRequest()->getHeader('Accept');
		if (!is_null($ct_accept) && (strpos($ct_accept, $ct_send) !== false))
		{
			$this->getHttpResponse()->setHeader("Content-type", $ct_send);
		}
		else
		{
			$this->getHttpResponse()->setHeader("Content-type", "text/plain");
		}
		
		$out = json_encode( $value );
		
		switch ($this->getContentType()) {
		case "text/json-comment-filtered":
			$out = "/*" . $out . "*/";
			break;
		case "text/plain":
			$out = $this->json_pretty($out);
			break;
		}
		
		$this->getHttpResponse()->sendBody($out);
	}

	private function json_pretty($json) {
	
		$tab = "  ";
		$new_json = "";
		$indent_level = 0;
		$in_string = false;
	
		$json_obj = json_decode($json);
	
		if($json_obj === false)
			return $json;
	
//		$json = json_encode($json_obj);
		$len = strlen($json);
	
		for($c = 0; $c < $len; $c++)
		{
			$char = $json[$c];
			switch($char)
			{
				case '{':
				case '[':
					if(!$in_string)
					{
						$new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
						$indent_level++;
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case '}':
				case ']':
					if(!$in_string)
					{
						$indent_level--;
						$new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case ',':
					if(!$in_string)
					{
						$new_json .= ",\n" . str_repeat($tab, $indent_level);
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case ':':
					if(!$in_string)
					{
						$new_json .= ":\t";
					}
					else
					{
						$new_json .= $char;
					}
					break;
				case '"':
					if($c > 0 && $json[$c-1] != '\\')
					{
						$in_string = !$in_string;
					}
				default:
					$new_json .= $char;
					break;				   
			}
		}
	
		return $new_json;
	}

}

