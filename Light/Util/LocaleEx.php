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
	private $language;
	
	/**
	 * ISO-3166 country code.
	 *
	 * @var string
	 */
	private $country;
	
	/**
	 * Locale variant.
	 *
	 * @var string
	 */
	private $variant;

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
	 * @param string $variant	Language variant.
	 * @return Util_Locale
	 */
	public static function getInstance( $language, $country = "", $variant = "" ) {
	
		if (($country === "") && ($variant === "") && (strpos( $language, "_" ) !== FALSE))
			return self::getInstanceFromString( $language );
		
		$id = self::getIdentifier( $language, $country, $variant );
		if (isset( self::$locales[$id] ))
			return self::$locales[$id];
		
		$o = new self();
		$o->language	= $language;
		$o->country		= $country;
		Data_Repository::find($o);
		
		return self::$locales[$id] = new self( $language, $country, $variant, $id );
		
	}

	/**
	 * Returns a locale object.
	 * @param string $locale	A locale string. For example: <kbd>pl_PL<kbd>.
	 * @return Util_Locale
	 */
	public static function getInstanceFromString( $locale ) {
		
		$locale = explode( "_", $locale, 3 );
		switch (count( $locale )) {
			case 1: return self::getInstance($locale[0]);
			case 2: return self::getInstance($locale[0],$locale[1]);
			case 3: return self::getInstance($locale[0],$locale[1],$locale[2]);
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
	 * @param string $variant
	 * @param string $id		Identifier constructed out of the above parameters.
	 */
	private function __construct( $language, $country, $variant, $id ) {
		
		$this->language = $language;
		$this->country	= $country;
		$this->variant	= $variant;
		$this->id		= $id;
		
	}

	/**
	 * Constructs a locale identifier out of parameters.
	 *
	 * @param string $language
	 * @param string $country
	 * @param string $variant
	 * @return string For example: "es_ES_standard"
	 */
	private static function getIdentifier( $language, $country = "", $variant = "" ) {
		
		return strtolower( $language ) . ($country!=""?"_".strtoupper($country):"") . 
			($variant!=""?"_".$variant:"");
		
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
		
		if (!empty( $this->variant ))
			return self::getInstance( $this->language, $this->country );
		
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
	 * Returns the locale variant.
	 * @return string
	 */
	public function getVariant() {
		return $this->variant;
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getId();
	}
	
}
