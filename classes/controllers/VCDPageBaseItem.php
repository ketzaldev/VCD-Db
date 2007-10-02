<?php
/**
 * VCD-db - a web based VCD/DVD Catalog system
 * Copyright (C) 2003-2007 Konni - konni.com
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 * 
 * @author  Hákon Birgisson <konni@konni.com>
 * @package Kernel
 * @version $Id: VCDPageBaseItem.php 1066 2007-08-15 17:05:56Z konni $
 * @since 0.90
 */
?>
<?php

/**
 * This class acts as a base controller for all the different items VCD-db can contain
 * but share common data and methods..
 * 
 *
 */

abstract class VCDPageBaseItem extends VCDBasePage {
	
	/**
	 * The baseItem object
	 *
	 * @var vcdObj
	 */
	protected $itemObj;
	
	/**
	 * The IMDB object container
	 *
	 * @var imdbObj
	 */
	protected $sourceObj = null;
	
	
	public function __construct(_VCDPageNode $node) {
		parent::__construct($node);

		// load the requested item
		$this->loadItem();
		$this->doCoreElements();
		
	}
	
		
	
	/**
	 * Handle all POST requests to this controller.
	 */
	public function handleRequest() {
		
		
	}
	
	
	/**
	 * Assigns the data from the sourceObject, needs to be called from the child controller
	 *
	 */
	protected function doSourceSiteElements() {
		if (!is_null($this->sourceObj)) {
			$this->assign('sourceTitle',$this->sourceObj->getTitle());
			$this->assign('sourceAltTitle',$this->sourceObj->getAltTitle());
			$this->assign('sourceGrade',$this->sourceObj->getRating());
			$this->assign('sourceCountries',$this->sourceObj->getCountry());
			$this->assign('sourceCategoryList',$this->sourceObj->getGenre());
			$this->assign('sourceRuntime',$this->sourceObj->getRuntime());
			$this->assign('sourceActors', $this->sourceObj->getCast(true));
			$this->assign('sourceDirector', $this->sourceObj->getDirectorLink());
			$this->assign('sourcePlot', $this->sourceObj->getPlot());
		}
	}
	
	
	/**
	 * Assign the global attributes such as title,production year,category .. etc
	 *
	 */
	private function doCoreElements() {
		
		$this->assign('itemTitle',$this->itemObj->getTitle());
		$this->assign('itemYear',$this->itemObj->getYear());
		$this->assign('itemCategoryName',$this->itemObj->getCategory()->getName(true));
		$this->assign('itemCategoryId',$this->itemObj->getCategoryID());
		$this->assign('itemCopyCount',$this->itemObj->getNumCopies());
		
				
		// Assign base data
		$this->doThumbnail();
		$this->doComments();
		$this->doMetadata();
		$this->doCopiesList();
		$this->doWishlist();
		$this->doSimilarList();
		$this->doSeenLink();
		$this->doManagerLink();
		$this->doSourceSiteLink();
		$this->doCovers();

		// Set the item ID
		$this->assign('itemId', $this->itemObj->getID());
		
	}
	
	
	/**
	 * Assign thumbnail data to the view
	 *
	 */
	private function doThumbnail() {
		$coverObj = $this->itemObj->getCover('thumbnail');
		if (!is_null($coverObj)) {
			$this->assign('itemThumbnail',$coverObj->showImage());
		} 
	}	
	
	
	/**
	 * Assign comments data to the view
	 *
	 */
	private function doComments() {
		
		$comments = SettingsServices::getAllCommentsByVCD($this->itemObj->getID());
		if (is_array($comments)) {
			$results = array();
			foreach ($comments as $commentObj) {
				$results[] = array(
					'id' => $commentObj->getID(),
					'date' => $commentObj->getDate(),
					'private' => $commentObj->isPrivate(),
					'comment' => $commentObj->getComment(),
					'owner' => $commentObj->getOwnerName(),
					'isOwner' => $commentObj->getOwnerID() == VCDUtils::getUserID()
				);
			}
			$this->assign('itemComments',$results);
		}
	}
	
	/**
	 * Assign metadata to the view
	 *
	 */
	private function doMetadata() {
		
	}
	
	/**
	 * Assign list of available copies to the view
	 *
	 */
	private function doCopiesList() {
		
		$metadata = SettingsServices::getMetadata($this->itemObj->getID(), null, null, null);
		$layerResults = $this->getstuff($this->itemObj, $metadata);
				
		$this->assign('itemCopies', $layerResults);
		
	}
	
	
	private function getstuff(cdobj &$vcdObj, &$metadataArr) {
		
		$results = array();
		
		// First get all available owners and mediatypes
		$arrData = $vcdObj->getInstanceArray();
		if (isset($arrData['owners']) && isset($arrData['mediatypes'])) {
	
			$arrOwners = $arrData['owners'];
			$arrMediatypes = $arrData['mediatypes'];
			$i = 0;
	
			foreach ($arrMediatypes as $mediaTypeObj) {
	
				if ($mediaTypeObj instanceof mediaTypeObj && VCDUtils::isDVDType(array($mediaTypeObj))) {
	
					$arrDVDMeta = metadataTypeObj::filterByMediaTypeID($metadataArr, $mediaTypeObj->getmediaTypeID(), $arrOwners[$i]->getUserId());
					$arrDVDMeta = metadataTypeObj::getDVDMeta($arrDVDMeta);
	
					if (is_array($arrDVDMeta) && sizeof($arrDVDMeta) > 0) {
	
						$dvdObj = new dvdObj();
	
						$dvd_region = VCDUtils::getDVDMetaObjValue($arrDVDMeta, metadataTypeObj::SYS_DVDREGION);
						$dvd_format = VCDUtils::getDVDMetaObjValue($arrDVDMeta, metadataTypeObj::SYS_DVDFORMAT);
						$dvd_aspect = VCDUtils::getDVDMetaObjValue($arrDVDMeta, metadataTypeObj::SYS_DVDASPECT);
						$dvd_audio  = VCDUtils::getDVDMetaObjValue($arrDVDMeta, metadataTypeObj::SYS_DVDAUDIO);
						$dvd_subs   = VCDUtils::getDVDMetaObjValue($arrDVDMeta, metadataTypeObj::SYS_DVDSUBS);
	
						if (strcmp($dvd_region, "") != 0) {
							//$dvd_region = $dvd_region.". (". $dvdObj->getRegion($dvd_region) . ")";
							$dvd_region = $dvd_region. ".";
						}
	
						if (strcmp($dvd_aspect, "") != 0) {
							$dvd_aspect = $dvdObj->getAspectRatio($dvd_aspect);
						}
	
						if (strcmp($dvd_audio, "") != 0) {
							$arrAudio = explode("#", $dvd_audio);
							$dvd_audio = "<ul class=\"ulnorm\">";
							foreach ($arrAudio as $audioType) {
								$dvd_audio .= "<li class=\"linorm\">" . $dvdObj->getAudio($audioType) . "</li>";
							}
							$dvd_audio .= "</ul>";
						}
	
						if (strcmp($dvd_subs, "") != 0) {
							$arrSubs = explode("#", $dvd_subs);
							$dvd_subs = "<ul class=\"ulnorm\">";
							foreach ($arrSubs as $subTitle) {
								$imgsource = $dvdObj->getCountryFlag($subTitle);
								$langName = $dvdObj->getLanguage($subTitle);
								$img = "<img src=\"{$imgsource}\" alt=\"{$langName}\" hspace=\"1\"/>";
								$dvd_subs .= "<li class=\"linorm\">".$img . " " . $langName . "</li>";
							}
							$dvd_subs .= "</ul>";
						}
	
						$divid = "x". $mediaTypeObj->getmediaTypeID()."x".$arrOwners[$i]->getUserId();
						
						$results[] = array(
							'layer'	 => $divid,
							'region' => $dvd_subs,
							'format' => $dvd_format,
							'aspect' => $dvd_aspect,
							'audio'	 => $dvd_audio,
							'subs'	 => $dvd_subs
						);
					}
				}
				$i++;
			}
		} 
		
		return $results;
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * Assign the "Add to wishlist" link to the view
	 *
	 */
	private function doWishlist() {
		
	}
	
	/**
	 * Assign the "Similar items" dropdown data to the view
	 *
	 */
	private function doSimilarList() {
		
		$list = MovieServices::getSimilarMovies($this->itemObj->getID());
		$results = array();
		if (is_array($list) && sizeof($list) > 0) {
			$results[null] = VCDLanguage::translate('misc.select');
			foreach ($list as $obj) {
				$results[$obj->getId()] = $obj->getTitle();
			}
			$this->assign('itemSimilar',$results);
		}
		
	}
	
	/**
	 * Assign the "Seen movie" link to the view
	 *
	 */
	private function doSeenLink() {
		
	}
	
	/**
	 * Assign the "Manager" link to the view
	 *
	 */
	private function doManagerLink() {
		if (VCDUtils::hasPermissionToChange($this->itemObj)) {
			$this->assign('isOwner', true);
		}
	}
	
	/**
	 * Assign the sourcesite link and image to the view
	 *
	 */
	private function doSourceSiteLink() {
		
	}
	
	/**
	 * Assign available covers data to the view
	 *
	 */
	private function doCovers() {
		
		$covers = $this->itemObj->getCovers();
		if (is_array($covers)) {
			$results = array();
			foreach ($covers as $coverObj) {
				if (!$coverObj->isThumbnail()) {
					
					$results[] = array(
						'id' => $coverObj->getId(),
						'title' => $this->itemObj->getTitle(),
						'covertype' => $coverObj->getCoverTypeName(),
						'size' => human_file_size($coverObj->getFilesize()),
						'link' => './?page=file&cover_id='.$coverObj->getId()
					);
				}
			}
			$this->assign('itemCovers',$results);
		}
		
	}
	
	/**
	 * Load the requested objects bases on query parameters.
	 * If parameter is incorrect, user is redirected to the frontpage.
	 *
	 */
	private function loadItem() {
		$itemId = $this->getParam('vcd_id');
		if (!is_numeric($itemId)) {
			redirect();
			exit();
		}

		$this->itemObj = MovieServices::getVcdByID($itemId);
		if (!$this->itemObj instanceof cdObj) {
			redirect();
			exit();
		}
		
		
		$this->sourceObj = $this->itemObj->getIMDB();
		if (!is_null($this->sourceObj)) {
			$this->assign('itemSource',true);
		}
		
	}
	
}
?>