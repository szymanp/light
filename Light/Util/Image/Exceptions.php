<?php
/**
 * An exception that is thrown when an unsupported or unknown format is encountered.
 *
 * @package	image
 * @author	Piotr Szyma�ski <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */
namespace Light\Util\Image;
use \Exception;

class BadFormatException extends Exception {

    private $type;

    /**
     * Constructs a new BadFormatException.
     * @param integer|string   $type   One of IMAGETYPE_* constants,
     *                                 or a string description.
     */
    public function __construct( $type ) {
    
        if (is_string( $type )) {
            parent::__construct( $type );
            return;
        }
        
        $name = "(unknown/$type)";
        
        $this->type = $type;
        
        switch ($type) {
        case IMAGETYPE_GIF:     $name = "GIF"; break;
        case IMAGETYPE_JPEG:    $name = "JPEG"; break;
        case IMAGETYPE_PNG:     $name = "PNG"; break;
        case IMAGETYPE_SWF:     $name = "SWF"; break;
        case IMAGETYPE_PSD:     $name = "PSD"; break;
        case IMAGETYPE_BMP:     $name = "BMP"; break;
        case IMAGETYPE_TIFF_II: $name = "TIFF_II"; break;
        case IMAGETYPE_TIFF_MM: $name = "TIFF_MM"; break;
        case IMAGETYPE_JPC:     $name = "JPC"; break;
        case IMAGETYPE_JP2:     $name = "JP2"; break;
        case IMAGETYPE_WBMP:    $name = "WBMP"; break;
        }
            
        parent::__construct( "Unsupported image type: " . $name );
    
    }
    
    public function getRequestedType() {
        return $this->type;
    }

}

/**
 * An exception that is thrown if an image transformation operation fails.
 *
 * @package	image
 * @author	Piotr Szyma�ski <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */
class TransformationException extends Exception {

}