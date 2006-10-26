<?php
/* Display the movies in selected category */

$VCDClass = VCDClassFactory::getInstance("vcd_movie");
$SETTINGSclass = VCDClassFactory::getInstance("vcd_settings");

$cat_id = $_GET['category_id'];
$batch  = 0;

if (isset($_GET['batch']))
$batch = $_GET['batch'];

if (!is_numeric($batch)) {
	$batch = 0;
}

$imagemode = false;

// Check if Image View is default

if(VCDUtils::isLoggedIn()) {
	$userObj = $_SESSION['user'];
	if(!isset($_GET['viewmode']) && !isset($_SESSION['viewmode']) && $userObj->getPropertyByKey('DEFAULT_IMAGE')) {
		$_SESSION['viewmode'] = 'image';
	}
}

if (isset($_GET['viewmode']) || (isset($_SESSION['viewmode']) && strcmp($_SESSION['viewmode'], 'image') == 0)) {
	$imagemode = true;
	$js = "viewMode({$cat_id}, 'text', {$batch})";
	$viewbar = "(<a href=\"#\" onclick=\"{$js}\">".VCDLanguage::translate('movie.textview')."</a> / ".VCDLanguage::translate('movie.imageview').")";
} else {
	$js = "viewMode({$cat_id}, 'image', {$batch})";
	$viewbar = "(".VCDLanguage::translate('movie.textview')." / <a href=\"#\" onclick=\"{$js}\">".VCDLanguage::translate('movie.imageview')."</a>)";
}

$showmine = false;
$checked = "";
if (isset($_SESSION['mine']) && $_SESSION['mine'] == true) {
	$showmine = true;
	$checked = "checked=\"checked\"";
}

if (VCDUtils::isLoggedIn()) {
	$viewbar .= "&nbsp; | <input type=\"checkbox\" class=\"nof\" onclick=\"showonlymine(".$cat_id.")\" ".$checked."/>".VCDLanguage::translate('movie.mineonly')."";
}


$Recordcount = $SETTINGSclass->getSettingsByKey("PAGE_COUNT");
$offset = $batch*$Recordcount;


if ($showmine && VCDUtils::isLoggedIn()) {
	$movies = $VCDClass->getVcdByCategory($cat_id, $Recordcount, $offset, VCDUtils::getUserID());
} elseif (VCDUtils::isLoggedIn() && VCDUtils::isUsingFilter(VCDUtils::getUserID())) {
	$movies = $VCDClass->getVcdByCategoryFiltered($cat_id, $Recordcount, $offset, VCDUtils::getUserID());
} else {
	$movies = $VCDClass->getVcdByCategory($cat_id, $Recordcount, $offset);
}


?>
<h1><?=VCDLanguage::translate('movie.bycat')?></h1>
<?

if (sizeof($movies) > 0 || $showmine) {


	// Display the pager
	if ($imagemode) {
		$suburl = "category_id=".$cat_id."&amp;viewmode=img";
	} else {
		$suburl = "category_id=".$cat_id;
	}

	if ($showmine && VCDUtils::isLoggedIn()) {
		$categoryCount = $VCDClass->getCategoryCount($cat_id, false, VCDUtils::getUserID());
	} elseif (VCDUtils::isLoggedIn() && VCDUtils::isUsingFilter(VCDUtils::getUserID())) {
		$categoryCount = $VCDClass->getCategoryCountFiltered($cat_id, VCDUtils::getUserID());
	} else {
		$categoryCount = $VCDClass->getCategoryCount($cat_id);
	}


	print "&nbsp;<span class=\"bold\">".VCDLanguage::translate('movie.currcat')."</span>&nbsp;";
	print "&nbsp; (".$categoryCount." ".VCDLanguage::translate('misc.movies').") ".$viewbar."";


	pager($categoryCount, $batch, $suburl);

	if (!$imagemode) {

		print "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"100%\" class=\"displist\">";
		print "<tr><td class=\"header\">".VCDLanguage::translate('movie.title')."</td><td nowrap=\"nowrap\" class=\"header\">".VCDLanguage::translate('movie.year')."</td><td class=\"header\" nowrap=\"nowrap\">".VCDLanguage::translate('movie.mediatype')."</td></tr>";
		foreach ($movies as $movie) {
			print "<tr>
					   <td width=\"70%\"><a href=\"./?page=cd&amp;vcd_id=".$movie->getID()."\">".$movie->getTitle()."</a></td>
				       <td nowrap=\"nowrap\">".$movie->getYear()."</td>
			           <td nowrap=\"nowrap\">".fixFormat($movie->showMediaTypes())."</td>
				   </tr>";
		}
		print "</table>";

	} else {
		print "<hr/>";
		print "<div id=\"actorimages\">";
		foreach ($movies as $movie) {

			$coverObj = $movie->getCover('thumbnail');
			if ($coverObj instanceof cdcoverObj ) {
				print $coverObj->showCategoryImageAndLink("./?page=cd&amp;vcd_id=".$movie->getID()."",$movie->getTitle());

			}
		}

		print "</div>";

	}


} else {
	// Movie array has 0 entries, either because of filter or that user has no movies in this category.
	redirect();
}




?>