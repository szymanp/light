<?php
/**
 * Javascript file importer.
 *
 * This class helps with the inclusion of Javascript files in HTML documents.
 * It provides methods to add and remove Javascript modules. It can also
 * return the list of modules in some useful forms.
 *
 * @package	util.javascript
 * @author	Piotr Szyma�ski <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */

namespace Light\Util\Javascript;
use \Exception;

class Import
{
	private $modules = array();
	
	/**
	 * Adds a new Javascript module to be imported.
	 * @param string	$jsmodule		For example:
	 * <kbd>dcx.javascript.validate.Numbers</kbd>
	 */
	public function addModule( $jsmodule ) {
	
		$file = \Light\Autoloader::find($jsmodule,"js",".");
		if (is_null($file)) {
			throw new Exception("Javascript module $jsmodule not found.");
		}
	
		if (in_array( $file, $this->modules )) return;
		
		$this->modules[] = $file;
	
	}
	
	public function addFile($filepath)
	{
		if (in_array( $filepath, $this->modules )) return;
		$this->modules[] = $filepath;
	}
	
	/**
	 * Returns HTML code that should be placed in the <<HEAD>> section to load the scripts.
	 * @return string
	 */
	public function getHTML() {
	
		$html = "";
		
		$site = \Light\Util\Site::getInstance();
	
		foreach( $this->modules as $mod ) {
		
			$url = $site->getUrlForOrThrow($mod);
		
			$html .= "<script type='text/javascript' src='" . $url . "'></script>\n";
				
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
