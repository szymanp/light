<?php

class Util_DocblockParser {

	protected $docblock;
	
	public function __construct($docblock) {
		$this->docblock	= $docblock;
	}
	
	/**
	 * @return	array<string,string>
	 */
	public function parse() {
	
		$docblock = $this->docblock;
		$docblock = preg_replace( "/(^\/\*\*)|(\*\/$)/", "", $docblock );
		$docblock = preg_replace( "/^\\s*\*/m", "", $docblock );
		$docblock = explode( "\n", $docblock );
		$lasttag  = 0;
		$tags	  = array( 0 => "", 1 => "" );
		foreach( $docblock as $line ) {
			$line = trim( $line );
			if ($lasttag == 0) {
				if (!empty($line) && $line[0] != "@") {
					$tags[0] = trim( $line );
					$lasttag = 1;
					continue;
				}
			}
			$line = strtr( $line, "\t", " " );
			if (empty( $line )) continue;
			if ($line[0] == "@") {
				$pos = strpos( $line, " ", 2 );
				if ($pos === false) {
					$lasttag = substr( $line, 1 );
					$tags[$lasttag] = "";
				} else {
					$lasttag = substr( $line, 1, $pos - 1);
					$tags[$lasttag] = trim(substr( $line, $pos + 1));
				}
			} else {
				$tags[$lasttag] .= (empty( $tags[$lasttag] )?"":" ") . $line;
			}
		}
		return $tags;
	
	}

}