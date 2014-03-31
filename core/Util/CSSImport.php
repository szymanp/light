<?php
namespace Light\Util;

class CSSImport
{
	private $stylesheets = array();
	
	/**
	 * Adds a new Javascript module to be imported.
	 * @param string	$jsmodule		For example:
	 * <kbd>dcx.javascript.validate.Numbers</kbd>
	 * @return Light\Util\CSSImport
	 */
	public function addModule($stylesheet) {
	
		$file = \Light\Autoloader::find($stylesheet,"css",".");
		if (is_null($file)) {
			throw new Exception("Stylesheet $stylesheet not found.");
		}
	
		if (in_array( $file, $this->stylesheets )) return;
		
		$this->stylesheets[] = $file;
	}
	
	/**
	 * @return Light\Util\CSSImport
	 */
	public function addFile($file)
	{
		$this->stylesheets[] = $file;
		return $this;
	}
	
	/**
	 * Returns HTML code that should be placed in the <<HEAD>> section to load the scripts.
	 * @return string
	 */
	public function getHTML() {
	
		$html = "";
		
		$site = Site::getInstance();
	
		foreach( $this->stylesheets as $mod ) {
		
			$url = $site->getUrlForOrThrow($mod);
			$html .= "<link href=\"$url\" rel=\"stylesheet\" type=\"text/css\"/>\n";

		}
		
		return $html;
	}
	
	/**
	 * Outputs the HTML code that will load the scripts.
	 */
	public function printHTML() {
		print( $this->getHTML() );
	}

}
