<?php
/**
 * VCD-db - a web based VCD/DVD Catalog system
 * Copyright (C) 2003-2006 Konni - konni.com
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 * 
 * @author  Hákon Birgisson <konni@konni.com>
 * @package Kernel
 * @subpackage WebFetch
 * @version $Id$
 */
 
?>
<? 
class VCDFetch_bush extends VCDFetch {
	
	
	protected $regexArray = array(
		'title' 	  => 'class="sdTitle" nowrap="nowrap">([^<]*)</td>',
		'year'  	  => '<th>Production Year:</th><td align="center">([^<]*)</td>',
		'studio'	  => 'searchtype=Browse&Studio_ID=([0-9]{1,5})">([^<]*)</td>',
		'genre'	 	  => 'CAT: <a href=search_result.asp?([^<]*)>([^<]*)</a><BR>',
		'cast' 		  => 'PerformerTalent_ID=([0-9]{1,5})">([^<]*)</a></li>',
		'thumbnail'	  => null
		//'frontcover'  => null,
		//'backcover'   => null
		);
	
			
	protected $multiArray = array(
		'cast', 'genre', 'poster'
	);
		
		
		
	private $servername = 'www.bushdvd.com';
	private $searchpath = '/search.asp?_searchtype=Title&SearchTitle=[$]';
	private $itempath   = '/stock_detail.asp?Title_ID=[$]';
	private $image_id = null;
	
	public function __construct() {
		$this->useSnoopy();
		$this->setSiteName("bush");
		$this->setFetchUrls($this->servername, $this->searchpath, $this->itempath);
		$this->setAdult();
	}
	
	protected function processResults() {
			if (!is_array($this->workerArray) || sizeof($this->workerArray) == 0) {
				$this->setErrorMsg("No results to process.");
				return;
			}
					
		$obj = new adultObj();
		$obj->setObjectID($this->getItemID());
		$obj->addImage('VCD Front Cover', $this->getImagePath('frontcover'));
		$obj->addImage('VCD Back Cover', $this->getImagePath('backcover'));
				
		foreach ($this->workerArray as $key => $data) {
			
			$entry = $data[0];
			$arrData = $data[1];
			
			switch ($entry) {
				case 'title':
					$title = $arrData[1];
					$obj->setTitle($title);
					break;
				
				case 'year':
					print_r($arrData);
					die();
					$year = $arrData[3];
					$obj->setYear($year);		
					break;
					
				case 'studio':
					$studio = $arrData[2];
					$obj->setStudio($studio);
					break;
										
				case 'thumbnail':
										
					$obj->setImage($arrData);
					break;
					
				case 'genre':
					foreach ($arrData as $item) {
						$genre = $item[2];
						$obj->addCategory($genre);
					}
					break;
					
				case 'cast':
					if (isset($arrData[0][1])) {
						$pornstars = explode(",", $arrData[0][1]);
						foreach ($pornstars as $pornstar) {
							$obj->addActor(ltrim($pornstar, "\."));
						}
					}
					break;
					
							
					
				default:
					break;
			}
			
		}
		
		$this->fetchedObj = $obj;
	}
	
	
	protected function fetchDeeper($entry) {
		
		switch ($entry) {
			case 'thumbnail':
				$this->discoverImageID();	
				$value = $this->getImagePath($entry);
				array_push($this->workerArray, array($entry, $value));
				break;
				
			case 'frontcover':
				$this->discoverImageID();
				$value = $this->getImagePath($entry);
				array_push($this->workerArray, array($entry, $value));
				break;
				
			case 'backcover':
				$this->discoverImageID();
				$value = $this->getImagePath($entry);
				array_push($this->workerArray, array($entry, $value));
				break;
				
		
			default:
				break;
		}
		
	}
	
	public function search($title) { 
		return parent::search($title);
	}
	
	public function showSearchResults() {
		
		$this->setMaxSearchResults(50);
		$regx = 'class="tgTitle">([^<]*)</td></tr><tr><td valign="top" align="center" class="tgImage"><div style="margin-top:5px;"><a href="stock_detail.asp([^<]*)Title_ID=([0-9]{1,6})">';
		$results = parent::generateSimpleSearchResults($regx,3,1);
		
		parent::generateSearchSelection($results);
					
	}
	
	
	/**
	 * Get the Full HTTP image path for the asked for image on the JadedVideo server.
	 * Valid image types are thumbnail, frontcover, backcover.
	 * All except screenshots return strings, screenshots returns an array of all screenshot images for that movie.
	 *
	 * @param string $image_type
	 * @return mixed.
	 */
	private function getImagePath($image_type) {
	
		if (is_null($this->image_id)) {
			return;
		}
		
							
		switch ($image_type) {
			case 'thumbnail':
				$img = "http://66.63.152.194/fif={$this->image_id}&obj=iip,1.0&wid=135&cvt=jpeg";
				die($img);
				return $img;
				break;
				
			case 'frontcover':
				$img = "http://66.63.152.194/fif={$this->image_id}&obj=iip,1.0&hei=520&cvt=jpeg";
				return $img;
				break;
				
			case 'backcover':
				$backcover_id = str_replace('a', 'b', $this->image_id);
				$img = "http://66.63.152.194/fif={$backcover_id}&obj=iip,1.0&hei=520&cvt=jpeg";
				return $img;
				break;
						
			default:
				return false;
				break;
		}
	
	}
	
	private function discoverImageID() {
		if (is_null($this->image_id)) {
			$regx = 'var pictureSpecA = "[^"]*.fpx';
			if ($this->getItem($regx) == self::ITEM_OK) {
				$res = $this->getFetchedItem();
				$this->image_id = substr(strstr($res[0], '"'), 1);
			}
		}
	}
	
	
	
	
}




?>