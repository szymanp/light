<?php
/**
 * A tree for storing objects associated with a name in some namespace.
 * 
 * <kbd>NameHierarchy</kbd> is a structure that allows to store objects
 * associated with hierarchical names. The namespace does not have to be
 * complete, that is unassigned elements that are ancestors of assigned elements
 * in the hierarchy are perfectly valid.
 * 
 * For example:
 * <code>
 * $h = new Util_NameHierarchy();
 * $h->put( "my.name.space", new Object1() );
 * </code> 
 *
 * @package	collection
 * @author	Piotr Szyma�ski <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */

class Util_NameHierarchy {

    /**
     * @var string
     */
    private $separator;

    /**
     * @var array
     */
    private $list = array();

    /**
     * Constructs a new NameHierarchy.
     *
     * @param string $separator	Separator for name parts.
     */
    public function __construct( $separator = "." ) {
        $this->separator = $separator;
    }

    /**
     * @param string	$name
     * @param mixed	$object
     */
    public function put( $name, $object ) {
        
        $ref = & $this->list;
        if ($name !== "") {
            $parts = explode( $this->separator, $name );
            foreach( $parts as $part ) {
                if (isset( $ref[$part] )) {
                    $ref = & $ref[$part];
                } else {
                    $ref[$part] = array();
                    $ref = & $ref[$part];
                }
            }
        }
        
        $ref[0] = $object;
    }

    /**
     * @param string	$name
     * @return mixed If the specified object does not exist, this method
     * returns NULL.
     */
    public function get( $name ) {

        $ref = & $this->list;
        if ($name !== "") {
            $parts = explode( $this->separator, $name );
            foreach( $parts as $part ) {
                if (!isset( $ref[$part] ))
                    return NULL;
                $ref = & $ref[$part];
            }
        }
        if (isset( $ref[0] )) return $ref[0];
        return NULL;
    }
    
    /**
     * Returns the first assigned ancestor of the named object.
     * This method does not return the named object itself.
     *
     * @param string $name
     * @param string $ancestor  A variable that will receive the name
     * of the matched ancestor.
     * @return mixed    If no ancestor is found, this method returns NULL.
     */
    public function getAncestor( $name, & $ancestor = NULL ) {
        
        if ($name === "") return NULL;
        
        $parts = explode( $this->separator, $name );
        $ref   = & $this->list;
        $cand  = NULL;    // candidate
        $ancst = "";      // ancestor name
        foreach( $parts as $part ) {
            if (isset( $ref[0] )) {
                if (!is_null( $ancestor )) $ancestor = $ancst;
                $cand = $ref[0];
            }
            if (!isset( $ref[$part] )) return $cand;
            $ref = & $ref[$part];
            $ancst .= (empty($ancst)?"":$this->separator) . $part;
        }
        return $cand;
    }
    
    /**
     * Returns a list of all descendants of the named object.
     *
     * @param string $name
     * @return array
     */
    public function getDescendants( $name ) {
        $list = array();

        $ref = & $this->list;
        if ($name !== "") {
            $parts = explode( $this->separator, $name );
            foreach( $parts as $part ) {
                if (!isset( $ref[$part] )) return array();
                $ref = & $ref[$part];
            }
        }
        $this->getDescendantsRec( $list, $ref );
        return $list;
    }
    
    /**
     * @param array $list
     * @param array $ref
     */
    private function getDescendantsRec( array & $list, array & $ref ) {
        foreach( $ref as $k => & $el ) {
            if ($k === 0) continue;
            if (isset( $el[0] )) $list[] = $el[0];
            $this->getDescendantsRec( $list, $el );
        }
    }

}
