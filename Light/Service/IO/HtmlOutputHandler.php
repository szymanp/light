<?php
namespace Light\Service\IO;

class HtmlOutputHandler extends OutputHandler
{
	/**
	 * Sends a reply containing the method return value.
	 * @param mixed	$resp
	 */
	public function sendResponse( $resp )
	{
		$this->getHttpResponse()->sendBody($resp);
	}
	
	/**
	 * Sends a reply informing about a fault that occurred during execution.
	 * @param Exception	$e
	 */
	public function sendFault(\Exception $e )
	{
		$this->getHttpResponse()->sendStatus(500);
		
		while (ob_get_length()>0) ob_end_clean();

		?>
		<html>
		<head>
			<title>Service Container</title>
		</head>
		<body>
			<h1><?=get_class($e)?></h1>
			<p>The application encountered an exception while processing
			your request:</p>
			
			<p><blockquote><i><?=nl2br($e->getMessage())?></i></blockquote></p>
			<p>Details follow:</p>
			<p><code><?=nl2br($e)?></code></p>
		</body>
		</html>
		<?php
		
	}

}
