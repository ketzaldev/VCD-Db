<?php
/**
 * VCD-db - a web based VCD/DVD Catalog system
 * Copyright (C) 2003-2004 Konni - konni.com
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 * 
 * @author  H�kon Birgsson <konni@konni.com>
 * @package Settings
 * @version $Id$
 */
 
?>
<?php

class metadataObj extends metadataTypeObj  {

	private $metadata_id;
	private $record_id;
	private $user_id;
	private $metadata_value;
	
	/**
	 * Object constructor
	 *
	 * @param array $dataArr
	 */
	public function __construct($dataArr) {
		$this->metadata_id	     = $dataArr[0];
		$this->record_id	     = $dataArr[1];
		$this->user_id 		     = $dataArr[2];
		$this->setMetadataTypeName($dataArr[3]);
		$this->metadata_value    = $dataArr[4];
		
		if (isset($dataArr[5]))	{
			$this->metatype_id   = $dataArr[5];	
		}
		
		if (isset($dataArr[6])) {
			$this->metatype_level = $dataArr[6];
		}
		
		
		//print_r($this);
	}


	/**
	 * Get the metadata ID
	 *
	 * @return int
	 */
	public function getMetadataID() {
		return $this->metadata_id;
	}
	
	/**
	 * Set the metadata ID
	 *
	 * @param int $id
	 */
	public function setMetadataID($id) {
		$this->metadata_id = $id;
	}
	
	/**
	 * Get the record ID associated with this metadata object
	 *
	 * @return int
	 */
	public function getRecordID() {
		return $this->record_id;
	}
	
	/**
	 * Get the user ID of the metadata object
	 *
	 * @return int
	 */
	public function getUserID() {
		return $this->user_id;
	}
	
	/**
	 * Get the metadata key
	 *
	 * @return string
	 */
	public function getMetadataName() {
		return $this->getMetadataTypeName();
	}
	
	/**
	 * Get the metadata value
	 *
	 * @return string
	 */
	public function getMetadataValue() {
		return $this->metadata_value;
	}

	/**
	 * Set the metadata value
	 *
	 * @param string $strValue
	 */
	public function setMetadataValue($strValue) {
		$this->metadata_value = $strValue;
	}

}


class metadataTypeObj {
	
	CONST LEVEL_SYSTEM = 0;
	CONST LEVEL_USER   = 1;

	CONST SYS_LANGUAGES    = 1;
	CONST SYS_LOGTYPES     = 2;
	CONST SYS_FRONTSTATS   = 3;
	CONST SYS_FRONTBAR     = 4;
	CONST SYS_DEFAULTROLE  = 5;
	CONST SYS_PLAYER	   = 6;
	CONST SYS_PLAYERPATH   = 7;
	CONST SYS_FRONTRSS 	   = 8;
	CONST SYS_IGNORELIST   = 9;
	CONST SYS_MEDIAINDEX   = 10;
	CONST SYS_FILELOCATION = 11;
	CONST SYS_SEENLIST 	   = 12;
	
	
	protected $metatype_id;
	protected $metatype_name;
	protected $metatype_level = metadataTypeObj::LEVEL_SYSTEM;
	
	public function __construct($id, $name, $level = self::LEVEL_SYSTEM) {
		$this->metatype_id = $id;
		$this->metatype_name = $name;
		$this->metatype_level = $level;
	}

	
	
		/**
	 * Get the Type ID associated with this metadata object
	 *
	 * @return string
	 */
	public function getMetadataTypeID() {
		return $this->metatype_id;
	}

	/**
	 * Get the Type Name associated with this metadata object
	 *
	 * @return string
	 */
	public function getMetadataTypeName() {
		return $this->metatype_name;
	}
	
	
	/**
	 * Sets the metadatatype name of the object.
	 * If any of the SYS_constants is being used as $typename parameter
	 * the typename and id will be automatically set and $level set to SYSTEM.
	 *
	 * @param mixed $typename | Either use any of the SYS_constants or string for USER level data.
	 */
	public function setMetadataTypeName($typename) {
		if (is_numeric($typename)) {
			$this->metatype_name = $this->getSystemTypeMapping($typename);
			$this->metatype_id = $typename;
			$this->metatype_level = self::LEVEL_SYSTEM;
		} else {
			$this->metatype_name = $typename;
			$this->metatype_level = self::LEVEL_USER;
			if (!isset($this->metatype_id) || !is_numeric($this->metatype_id)) {
				$this->metatype_id = -1;
			}
		}
	
	}

	/**
	 * Get the Type Level associated with this metadata object
	 *
	 * @return string
	 */
	public function getMetadataTypeLevel() {
		return $this->metatype_level;
	}
	
	public function isSystemObj() {
		return ($this->metatype_level == self::LEVEL_SYSTEM);
	}
	
	
	/**
	 * Get the string mapping for the current SYSTEM constant.
	 * If the $SYS_CONST does not match with any of the SYS_ constants defined
	 * in metadataTypeObj, function returns false.
	 *
	 * @param int $SYS_CONST | any of the SYS_ constants defined in metadataTypeObj
	 * @return string
	 */
	public static function getSystemTypeMapping($SYS_CONST) {
		switch ($SYS_CONST) {
			case self::SYS_LANGUAGES: 	 return 'languages'; 	 break;
			case self::SYS_LOGTYPES: 	 return 'logtypes';  	 break;
			case self::SYS_FRONTSTATS:   return 'frontstats';  	 break;
			case self::SYS_FRONTBAR: 	 return 'frontbar';  	 break;
			case self::SYS_DEFAULTROLE:  return 'default_role';  break;
			case self::SYS_PLAYER: 		 return 'player';  		 break;
			case self::SYS_PLAYERPATH: 	 return 'playerpath';  	 break;
			case self::SYS_FRONTRSS: 	 return 'frontrss';  	 break;
			case self::SYS_IGNORELIST: 	 return 'ignorelist';  	 break;
			case self::SYS_MEDIAINDEX: 	 return 'mediaindex';  	 break;
			case self::SYS_FILELOCATION: return 'filelocation';  break;
			case self::SYS_SEENLIST: 	 return 'seenlist';  	 break;
			default: 					 return false; 			 break;
			
		
		}
	}
			

}

?>