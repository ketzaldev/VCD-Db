<? 
	require_once("../classes/includes.php");
	if (!VCDUtils::isLoggedIn()) {
		VCDException::display("User must be logged in");
		print "<script>self.close();</script>";
		exit();
	}
	$language = new VCDLanguage();
	if (isset($_SESSION['vcdlang'])) {
		$language->load($_SESSION['vcdlang']);
	}
	VCDClassFactory::put($language, true);
	
	$jsaction = "return getFileName(this.form)";
	if (isset($_GET['field'])) {
		$jsaction = "return getFileName(this.form, '{$_GET['field']}')";
	}
	
	if (isset($_GET['from']) && strcmp($_GET['from'], "player") == 0) {
		$jsaction = "return getPlayerFileName(this.form)";
	}
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>VCD-db</title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?=VCDUtils::getCharSet()?>"/>
	<link rel="stylesheet" type="text/css" href="../<?=STYLE?>style.css"/>
	<script src="../includes/js/main.js" type="text/javascript"></script>
</head>
<body onload="window.focus()">
<h2><?=VCDLanguage::translate('manager.browse')?></h2>
<form name="browse" action="" method="POST" onsubmit="return false">
<table cellspacing="1" cellpadding="1" border="0" class="plain">
<tr>
	<td><?=VCDLanguage::translate('player.path')?>:</td>
	<td><input size="40" type="file" name="filename"/></td>
</tr>
<tr>
	<td></td>
	<td align="right"><input type="submit" value="<?=VCDLanguage::translate('misc.save')?>" onclick="<?=$jsaction?>"/></td>
</tr>
</table>
</form>
</body>
</html>