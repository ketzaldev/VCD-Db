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
 * @package Kernel
 * @subpackage WebFetch
 * @version $Id$
 */
 
?>
<? 
class VCDFetch_imdb extends VCDFetch {
	
	public function __construct() {
		$this->setSiteName("imdb");
	}
	
	
	public function showSearchResults() {
				
		
	}
	
	
	public function search() {
		//http://www.imdb.com/title/tt0377092/
		$this->fetchPage('/title/tt0377092/', 'www.imdb.com', 'http://akas.imdb.com');
	}
	
	
	
	
	
	
	
	
	
}









?>