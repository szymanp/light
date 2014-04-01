<?php
/**
 * A <kbd>Locale</kbd> object represents a specific international profile.
 *
 * @package	i18n
 * @author	Piotr Szyma�ski <szyman@magres.net>
 * @license	http://www.gnu.org/copyleft/gpl.html  GPL License, Version 2
 *
 */

class Util_Locale {
	
	/**
	 * Locale identifier
	 * For example: "en_US"
	 * @var string.
	 */
	private $id;
	
	/**
	 * ISO-639 language code.
	 *
	 * @var string
	 */
	protected $language;
	
	/**
	 * ISO-3166 country code.
	 *
	 * @var string
	 */
	protected $country;
	
	/**
	 * A list of instantiated locales.
	 *
	 * @var array
	 */
	private static $locales = array();
	
	/**
	 * Returns a locale object.
	 *
	 * @param string $language	ISO-639 language code.
	 * @param string $country	ISO-3166 country code.
	 * @return Util_Locale
	 */
	public static function getInstance( $language, $country = "" ) {
	
		if (($country === "") && (strpos( $language, "_" ) !== FALSE))
			return self::getInstanceFromString( $language );
		
		$id = self::getIdentifier( $language, $country );
		if (isset( self::$locales[$id] ))
			return self::$locales[$id];
			
		$o = new self( $language, $country, $id );
		
		
		
/*		
		$repo = Data_Repository::get(__CLASS__);
		$c = $repo->newCriteria();
		$c->add("language", $language);
		$c->add("country", $country);
		
		pros:
		- nice isolation from Doctrine_Query
		cons:
		- cannot execute more complicated queries
*/
		
		return self::$locales[$id] = $o;
		
	}

	/**
	 * Returns a locale object.
	 * @param string $locale	A locale string. For example: <kbd>pl_PL<kbd>.
	 * @return Util_Locale
	 */
	public static function getInstanceFromString( $locale ) {
		
		$locale = explode( "_", $locale, 2 );
		switch (count( $locale )) {
			case 1: return self::getInstance($locale[0]);
			case 2: return self::getInstance($locale[0],$locale[1]);
		}
		
	}
	
	/**
	 * Returns the current locale as determined by system and user profile.
	 * @TODO Implement this correctly
	 * @return Util_Locale
	 */
	public static function getCurrent() {
		return self::getInstance( "en", "US" );
	}
	
	/**
	 * A private constructor.
	 *
	 * @param string $language
	 * @param string $country
	 * @param string $id		Identifier constructed out of the above parameters.
	 */
	private function __construct( $language, $country, $id ) {
		
		$this->language = $language;
		$this->country	= $country;
		$this->id		= $id;
		
	}

	/**
	 * Constructs a locale identifier out of parameters.
	 *
	 * @param string $language
	 * @param string $country
	 * @return string For example: "es_ES"
	 */
	private static function getIdentifier( $language, $country = "" ) {
		
		return strtolower( $language ) . ($country!=""?"_".strtoupper($country):"");
		
	}
	
	/**
	 * Returns the full identifier of this locale.
	 * @return string For example: <kbd>en_US</kbd>
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * Returns the parent locale.
	 * A parent locale is a more general locale than the current one.
	 * @return Util_Locale
	 */
	public function getParent() {
		
		if (!empty( $this->country ))
			return self::getInstance( $this->language );
		
		return self::getInstance("");
	}
	
	/**
	 * Checks if the given locale is a child of the current one.
	 *
	 * @param Util_Locale $locale	Potential child.
	 * @return boolean
	 */
	public function isParentOf( Util_Locale $locale ) {
		while ($locale->hasParent()) 
			if ($locale->getParent() === $this) return true;
			else $locale = $locale->getParent();
		return false;
	}
	
	/**
	 * Checks if this locale has a parent.
	 * @return boolean All locales have a parent, except for the most general
	 * locale ("").
	 */
	public function hasParent() {
		if ($this->language == "") return false;
		return true;
	}
	
	/**
	 * Returns the ISO-639 language code.
	 * @return string
	 */
	public function getLanguageCode() {
		return $this->language;
	}
	
	/**
	 * Returns the ISO-3166 country code.
	 * @return string
	 */
	public function getCountryCode() {
		return $this->country;
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getId();
	}
	
}
