<?php
/**
 * A class for manipulating an image.
 *
 * @package	image
 * @author	Piotr Szyma�ski <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */

namespace Light\Util\Image;
use Light\Exception\Exception;

include 'Exceptions.php';

class Image {

    /**
     * Image resource.
     * @var resource
     */
    protected $res;
    
    /**
     * Type of input image.
     * One of IMAGETYPE_* constants.
     * @var integer
     */
    protected $type;

    protected function __construct()
    {
    }
    
    /**
     * Creates an empty Image of the given size.
     * @param integer   $width
     * @param integer   $height
     * @return Image
     */
    public static function createEmpty( $width, $height )
    {
    	$image = new self;
        $image->res = imagecreatetruecolor( $width, $height );
        return $image;
    }
    
    /**
     * Creates an Image from a file.
     * @param string    $file   Path to file.
     * @param integer   $type   Optional image type. One of IMAGETYPE_* constants.
     * @see exif_imagetype()
     * @return Image
     */
    public static function createFromFile( $file, $type = NULL )
    {
    	$image = new self;
    	
        if (!file_exists( $file ))
            throw new Exception("File not found: %1", $file);
            
        if (is_null( $type )) {
            $type = exif_imagetype( $file );
            
            if ($type === FALSE)
                throw new BadFormatException( "File '$file' is not a valid image." );
        }
        
        $image->type = $type;
        
        switch ($type) {
        case IMAGETYPE_GIF:
            if (!function_exists( 'imagecreatefromgif' ))
                throw new BadFormatException( $type );
                
            $image->res = imagecreatefromgif( $file );
            break;
        case IMAGETYPE_JPEG:
            $image->res = imagecreatefromjpeg( $file );
            break;
        case IMAGETYPE_PNG:
            $image->res = imagecreatefrompng( $file );
            break;
        default:
            throw new BadFormatException( $type );
        }
        
        return $image;
    }
    
    /**
     * Destructor.
     */
    public function __destruct() {
    
        imagedestroy( $this->res );
    }
    
    /**
     * Returns the PHP resource representing the image.
     * @return resource
     */
    public function &getResource() {
        return $this->res;
    }
    
    /**
     * Resizes the image to the desired resolution.
     * @param integer   $width
     * @param integer   $height
     */
    public function resize( $width, $height ) {
	
		if (($width < 1) || ($height < 1)) {
			throw new Exception("Invalid image dimensions: {$width}x{$height}");
		}
    
        $dst = imagecreatetruecolor( $width, $height );

        $r = imagecopyresampled( $dst, $this->res, 0, 0, 0, 0, $width, $height,
            imagesx( $this->res ), imagesy( $this->res ) );
            
        if (!$r)
            throw new TransformationException( "Image resizing to ({$width}x{$height}) failed." );

        imagedestroy( $this->res );
        $this->res = $dst;

    }
    
    /**
     * Crop the image to the desired rectangle.
     * @param integer	$left
     * @param integer	$top
     * @param integer	$width
     * @param integer	$height
     */
    public function crop($left, $top, $width, $height)
    {
    	if ($width < 1 || $height < 1 || $left < 0 || $top < 0) {
			throw new Exception("Invalid rectangle: {$left},{$top},{$width},{$height}");
		}
		
		$dst = imagecreatetruecolor( $width - $left, $height - $top);
		
		$r = imagecopy($dst, $this->res, 0, 0, $left, $top, $width, $height);
		
        if (!$r)
        {
			throw new TransformationException( "Cropping to ({$width}x{$height}) failed.");
        }
        
        imagedestroy($this->res);
        
        $this->res = $dst;
    }
	
	/**
	 * Rotate the image with a given angle.
	 * @param float		$angle	Rotation angle, in degrees. The rotation angle is
	 *							interpreted as the number of degrees to rotate the image anticlockwise. 
	 * @param integer	$color	Background color of uncovered zone.
	 */
	public function rotate($angle, $color = 0)
	{
		$dst = imagerotate($this->res, $angle, $color);
		if ($dst === false)
		{
			throw new TransformationException( "Rotation by {$angle}deg failed" );
		}
		
		imagedestroy($this->res);
		
		$this->res = $dst;
	}

	/**
	 * Flip the image.
	 * @param boolean	$vertical
	 * @param boolean	$horizontal
	 */
	public function flip($vertical, $horizontal)
	{
		$width	 		= imagesx ( $this->res );
		$height			= imagesy ( $this->res );

		$src_x			= 0;
		$src_y			= 0;
		$src_width		= $width;
		$src_height		= $height;

		if ($vertical && $horizontal)
		{
			$src_x		 = $width -1;
			$src_y		 = $height -1;
			$src_width	 = -$width;
			$src_height	 = -$height;
		}
		else if ($vertical)
		{
			$src_y		= $height -1;
			$src_height	= -$height;
		}
		else if ($horizontal)
		{
			$src_x		= $width -1;
			$src_width	= -$width;
		}
		else
		{
			return;
		}

		$dst = imagecreatetruecolor($width, $height);

		$r = imagecopyresampled( $dst, $this->res, 0, 0, $src_x, $src_y , $width, $height, $src_width, $src_height);
		if (!$r)
		{
			throw new TransformationException( "Flipping the image failed");
		}
		
		imagedestroy($this->res);
		$this->res = $dst;
	}
    
    /**
     * Saves the image to a JPEG file.
     * @param string    $filename       Name of the file.
     * @param integer   $quality        Quality factor. Max=100, min=0.
     */
    public function saveJPEG( $filename, $quality = 75 ) {

        return imagejpeg( $this->res, $filename, $quality );
    
    }
    
    /**
     * Saves the image to a PNG file.
     * @param string    $filename       Name of the file.
     * @param integer   $quality        Quality factor. Max=9, min=0.
     */
    public function savePNG( $filename, $quality = 7 ) {

        return imagepng( $this->res, $filename, $quality );
    
    }
    
    /** 
     * Returns the width of the image.
     * @return integer
     */
    public function getWidth() {
        return imagesx( $this->res );
    }
    
    /** 
     * Returns the height of the image.
     * @return integer
     */
    public function getHeight() {
        return imagesy( $this->res );
    }
	
	/**
	 * Returns the type of the input image.
	 * @return integer
	 */
	public function getType() {
		return $this->type;
	}

}
