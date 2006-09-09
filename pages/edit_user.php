<?
	$USERClass = VCDClassFactory::getInstance("vcd_user");
	$SETTINGSClass = VCDClassFactory::getInstance("vcd_settings");
	$user = $_SESSION['user'];
	$status = "";

	/* Process the registration form */
	if (sizeof($_POST) > 0) {
		$user->setName($_POST['name']);
		$user->setEmail($_POST['email']);
		if (isset($_POST['password']) && strlen($_POST['password']) > 4) {
			if ($user->isDirectoryUser()) {
				VCDException::display('Password cannot be changed for Directory authenticated users.', true);
				exit();

			}
			$user->setPassword(md5($_POST['password']));
		}


		// Check for properties
		$user->flushProperties();
		if (isset($_POST['property']) && is_array($_POST['property'])) {
			foreach ($_POST['property'] as $propID) {
				$user->addProperty($USERClass->getPropertyById($propID));
			}
		}


		if ($USERClass->updateUser($user)) {
			// update the user in session as well
			$_SESSION['user'] = $user;
			VCDUtils::setMessage("(".language::translate('SE_UPDATED').")");
		} else {
			VCDUtils::setMessage("(".language::translate('SE_UPDATE_FAILED').")");
		}


	}

/*
	Display and process registration
*/
?>
<form name="user" method="POST" action="./index.php?page=private&o=settings&action=update">
<h1><?=language::translate('MENU_SETTINGS')?></h1>
<fieldset id="settings" title="<?= language::translate('MENU_SETTINGS'); ?>">
<table width="100%" border="0" cellspacing="1" cellpadding="1" class="displist">
<tr>
	<td width="35%"><?=language::translate('REGISTER_FULLNAME')?>:</td>
	<td><input type="text" name="name" value="<?=$user->getFullname()?>"/></td>
</tr>
<tr>
	<td><?=language::translate('LOGIN_USERNAME')?>:</td>
	<td><input type="text" name="username" readonly value="<?=$user->getUsername()?>"/></td>
</tr>
<tr>
	<td><?=language::translate('REGISTER_EMAIL')?>:</td>
	<td><input type="text" name="email" value="<?=$user->getEmail()?>"/></td>
</tr>
<tr>
	<td><?=language::translate('LOGIN_PASSWORD')?>:</td>
	<td><input type="password" name="password"/></td>
</tr>
<tr>
	<td colspan="2">(<?=language::translate('LOGIN_INFO')?>)<br/><br/></td>
</tr>


<? /*
	Get all the custom user properties
   */
	$props = $USERClass->getAllProperties();
	$show_adult = (bool)$SETTINGSClass->getSettingsByKey('SITE_ADULT');

	foreach ($props as $propertyObj) {
		$checked = "";
		$viewfeed = "";
		if ($propertyObj->getpropertyName() == 'RSS' && $user->getPropertyByKey($propertyObj->getpropertyName())) {
			$viewfeed = "  <a href=\"rss/?rss=".$user->getUsername()."\">(".language::translate('SE_OWNFEED').")</a>";
		}

		if ($propertyObj->getpropertyName() == 'PLAYOPTION' && $user->getPropertyByKey($propertyObj->getpropertyName())) {
			$viewfeed = "  <a href=\"#\" onclick=\"adjustPlayer()\">(".language::translate('SE_PLAYER').")</a>";
		}

		if ($propertyObj->getpropertyName() == 'SHOW_ADULT' && !$show_adult) {

		} else {

			if ($user->getPropertyByKey($propertyObj->getpropertyName())) {
				$checked = "checked";
			}

			// Check if translation for property exists
			$langkey = "PRO_".$propertyObj->getpropertyName();
			$description = language::translate($langkey);
			if (strcmp($description, "undefined") == 0) {
				$description = $propertyObj->getpropertyDescription();
			}


			print "<tr>
						<td nowrap=\"nowrap\">".$description."</td>
						<td><input type=\"checkbox\" name=\"property[]\" class=\"nof\" value=\"".$propertyObj->getpropertyID()."\" $checked/>".$viewfeed."</td>
			       </tr>";
		}
	}

?>
<tr>
	<td><? print "<div class=\"info\">".VCDUtils::getMessage()."</div>"; ?>&nbsp;</td>
	<td><input type="submit" value="<?=language::translate('X_UPDATE')?>"/></td>
</tr>
</table>
</form>
</fieldset>
<br/>




<fieldset id="pagelook" title="<?= language::translate('SE_PAGELOOK'); ?>">
<legend class="bold"><?= language::translate('SE_PAGELOOK'); ?></legend>

<p style="padding:0px 0px 2px 2px">
&nbsp;<?= language::translate('SE_PAGEMODE')?>
<select name="template" onchange="switchTemplate(this.options[this.selectedIndex].value)"><?
	// Check if user has cookie set for template
	$selectedTemplate = "";
	if (isset($_COOKIE['template'])) {
		$selectedTemplate = $_COOKIE['template'];
	}


	foreach (VCDUtils::getStyleTemplates() as $templateItem) {
		if (strcmp($templateItem, $selectedTemplate) == 0) {
			print "<option value=\"{$templateItem}\" selected=\"selected\">{$templateItem}</option>";
		} else {
			print "<option value=\"{$templateItem}\">{$templateItem}</option>";
		}

	}
	?>
</select>
</p>
</fieldset>










<br/>
<?

	$arrBorrowers = $SETTINGSClass->getBorrowersByUserID($user->getUserID());

	$bEdit = false;
	$bid = "";
	if (isset($_GET['edit']) && strcmp($_GET['edit'], "borrower") == 0) {
		$bEdit = true;
		$bid = $_GET['bid'];
		$currObj = $SETTINGSClass->getBorrowerByID($bid);
	}


	if (is_array($arrBorrowers) && sizeof($arrBorrowers) > 0) {
		print "<a name=\"borrower\"></a>";
		print "<fieldset id=\"mainset\" title=".language::translate('MY_FRIENDS').">";
		print "	<legend class=\"bold\">".language::translate('MY_FRIENDS')."</legend>";

		print "<table cellpadding=\"1\" cellspacing=\"1\" width=\"100%\" border=\"0\">";
		print "<tr><td>";

		print "<form name=\"borrowForm\"><select name=\"borrowers\" size=1\">";
			print "<option value=\"null\">".language::translate('LOAN_SELECT')."</option>";
					foreach ($arrBorrowers as $obj) {
						$arr = $obj->getList();

						$selected = "";
						if ($arr['id'] == $bid)
							$selected = "selected";

						print "<option value=\"".$arr['id']."\" $selected>".$arr['name']."</option>";
					}
					unset($arr);
			print "</select>";

		print "&nbsp;<input type=\"button\" value=\"".language::translate('X_EDIT')."\" onclick=\"changeBorrower(this.form)\">";
		print "<img src=\"images/icon_del.gif\" hspace=\"4\" alt=\"\" align=\"absmiddle\" onclick=\"deleteBorrower(this.form)\" border=\"0\"/></form>";

	}

	print "</td>";

	if ($bEdit && ($currObj instanceof borrowerObj)) {

		print "<td>";

		print "<form name=\"update_borrower\" action=\"exec_form.php?action=edit_borrower\" method=\"post\"><table cellpadding=0 cellspading=0 border=0 class=list>";
		print "<tr><td>".language::translate('LOAN_NAME').":</td><td><input type=\"text\" name=\"borrower_name\" value=\"".$currObj->getName()."\"/></td>";
		print "<td>".language::translate('REGISTER_EMAIL').":</td><td><input type=\"text\" name=\"borrower_email\" value=\"".$currObj->getEmail()."\"/></td>";
		print "<td>&nbsp;</td><td><input type=\"submit\" value=\"".language::translate('X_UPDATE')."\" id=\"vista\" onclick=\"return val_borrower(this.form)\"/></td></tr>";
		print "</table><input type=\"hidden\" name=\"borrower_id\" value=\"".$currObj->getID()."\"/></form>";

		print "</td>";
	}


	print "</tr></table>";

?>
</fieldset>









<br/>
<fieldset id="mainset" title="<?=language::translate('RSS_TITLE')?>">
<legend class="bold"><?=language::translate('RSS_TITLE')?></legend>
<?
	$feeds = $SETTINGSClass->getRssFeedsByUserId($user->getUserID());
	if (sizeof($feeds) > 0) {
		print "<table cellspacing=\"1\" cellpadding=\"1\" border=\"0\" class=\"displist\" width=\"100%\">";
		foreach ($feeds as $rssfeed) {
			$pos = strpos($rssfeed['url'], "?rss=");
			if ($pos === false) {
			    $img = "<img src=\"images/rsssite.gif\" hspace=\"4\" title=\"".language::translate('RSS_SITE')."\" border=\"0\"/>";
			} else {
				$img = "<img src=\"images/rssuser.gif\" hspace=\"4\" title=\"".language::translate('RSS_USER')."\" border=\"0\"/>";
			}


			print "<tr><td align=\"center\">".$img."</td><td width=\"95%\">".$rssfeed['name']."</td><td><a href=\"".$rssfeed['url']."\"><img src=\"images/rss.gif\" border=\"0\" alt=\"".language::translate('RSS_VIEW')."\"/></a></td><td><img src=\"images/icon_del.gif\" onclick=\"deleteFeed(".$rssfeed['id'].")\"/></td></tr>";
		}
		print "</table>";
	} else {
		print "<p>" .language::translate('RSS_NONE') . "</p>";
	}
?>
<p>
<input type="button" value="<?=language::translate('RSS_ADD')?>" onclick="addFeed()"/>
</p>
</fieldset>
<br/>

<fieldset id="mainset" title="<?=language::translate('SE_CUSTOM')?>">
<legend class="bold"><?=language::translate('SE_CUSTOM')?></legend>
<?
	// Check for current values
	$uid = VCDUtils::getUserID();
	$metaObjA = $SETTINGSClass->getMetadata(0, $uid, 'frontstats');
	$metaObjB = $SETTINGSClass->getMetadata(0, $uid, 'frontbar');
	$metaObjC = $SETTINGSClass->getMetadata(0, $uid, 'frontrss');
	$isChecked = "checked=\"checked\"";
	$check1 = "";
	$check2 = "";
	$arrSelectedFeeds = array();
	if (is_array($metaObjA) && sizeof($metaObjA) == 1 && $metaObjA[0]->getMetadataValue() == 1) {
		$check1 = $isChecked;
	}
	if (is_array($metaObjB) && sizeof($metaObjB) == 1 && $metaObjB[0]->getMetadataValue() == 1) {
		$check2 = $isChecked;
	}
	if (is_array($metaObjC) && sizeof($metaObjC) == 1) {
		$strFeeds = $metaObjC[0]->getMetadataValue();
		$arrSelectedFeeds = split("#", $strFeeds);
	}
?>
<form name="choiceForm" method="post" action="exec_form.php?action=edit_frontpage">
<input type="hidden" name="rss_list" id="rss_list"/>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="displist">
<tr>
	<td width="40%"><?=language::translate('SE_SHOWSTAT')?></td>
	<td><input type="checkbox" <?=$check1?> name="stats" class="nof" value="yes"/></td>
</tr>
<tr>
	<td><?=language::translate('SE_SHOWSIDE')?></td>
	<td><input type="checkbox" <?=$check2?> name="sidebar" class="nof" value="yes"/></td>
</tr>
<tr>
	<td valign="top"><?=language::translate('SE_SELECTRSS')?></td>
	<td valign="top">
	<!-- Open rss selection  -->

	<table cellpadding="1" cellspacing="1">
	<tr>
		<td>
		<select name="rssAvailable" id="rssAvailable" size="5" style="width:300px;" onDblClick="moveOver(this.form, 'rssAvailable', 'rssChoices')">
		<?
		$arrFeeds = $SETTINGSClass->getRssFeedsByUserId(0);
		foreach ($arrFeeds as $item) {
			if (!in_array($item['id'], $arrSelectedFeeds))
				print "<option value=\"".$item['id']."\">".$item['name']."</option>";
		}
		?>
		</select>
		</td>
	</tr>
	<tr>
		<td align="center"><img src="images/move_down.gif" onclick="moveOver(document.choiceForm, 'rssAvailable', 'rssChoices');" hspace="4" border="0"/><img src="images/move_up.gif" onclick="removeMe(document.choiceForm, 'rssAvailable', 'rssChoices');" border="0"/></td>
	</tr>
	<tr>
		<td><select multiple name="rssChoices" id="rssChoices" style="width:300px;" size="5" class="input" ondblclick="removeMe(document.choiceForm, 'rssAvailable', 'rssChoices');">
		<?
		foreach ($arrFeeds as $item) {
			if (is_array($arrSelectedFeeds) && in_array($item['id'], $arrSelectedFeeds))
				print "<option value=\"".$item['id']."\">".$item['name']."</option>";
		}
		unset($arrFeeds);
		unset($arrSelectedFeeds);
		?>
		</select></td>
	</tr>
	</table>



	<!-- Close rss selection -->
	</td>

</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" value="<?=language::translate('X_UPDATE')?>" onclick="checkFieldsRaw(this.form,'rssChoices', 'rss_list')"/></td>
</tr>
</table>
</form>
</fieldset>
<br/>


















<?
	/*
		We only display the ignore list if more than 1 active users
		is using VCD-db.
	*/
	$CLASSUsers = VCDClassFactory::getInstance('vcd_user');
	if (sizeof($CLASSUsers->getActiveUsers()) > 1) {
?>


<fieldset id="mainset" title="<?=language::translate('IGN_LIST')?>">
<legend class="bold"><?=language::translate('IGN_LIST')?></legend>
<form name="ignore" method="post" action="exec_form.php?action=update_ignorelist">
<input type="hidden" name="id_list" id="id_list"/>
<?
	// Get current ignore list
	$ignorelist = array();
	$metaArr = $SETTINGSClass->getMetadata(0, VCDUtils::getUserID(), metadataTypeObj::SYS_IGNORELIST );
	if (sizeof($metaArr) > 0) {
		$ignorelist = split("#", $metaArr[0]->getMetadataValue());
	}

?>
<table cellpadding="1" cellspacing="1" border="0" width="100%">
<tr>
	<td width="44%" valign="top""><?=language::translate('IGN_DESC')?></td>
	<td width="10%"><select name="available" id="available" size="5" style="width:100px;" onDblClick="moveOver(this.form, 'available', 'choiceBox')">
		<?


		$arrUsers = $CLASSUsers->getActiveUsers();
		foreach ($arrUsers as $userObj) {
			if (!in_array($userObj->getUserID(), $ignorelist)) {
				if ($userObj->getUserID() != VCDUtils::getUserID()) {
					print "<option value=\"".$userObj->getUserID()."\">".$userObj->getUserName()."</option>";
				}
			}
		}
		?>
		</select></td>
	<td width="5%" align="center">
	<input type="button" value="&gt;&gt;" onclick="moveOver(this.form, 'available', 'choiceBox');" class="input" style="margin-bottom:5px;"/><br/>
	<input type="button" value="&lt;&lt;" onclick="removeMe(this.form, 'available', 'choiceBox');" class="input"/>
	</td>
	<td width="10%"><select multiple name="choiceBox" id="choiceBox" style="width:100px;" size="5" class="input">
		<?
		foreach ($arrUsers as $userObj) {
			if (in_array($userObj->getUserID(), $ignorelist)) {
				print "<option value=\"".$userObj->getUserID()."\">".$userObj->getUserName()."</option>";
			}
		}
		?>
		</select></td>
	<td align="left" valign="bottom"><input type="submit" value="<?=language::translate('X_UPDATE')?>" onclick="checkFieldsRaw(this.form, 'choiceBox', 'id_list')"/></td>
</tr>
</table>
</form>
</fieldset>
<br/>
<? } ?>










<fieldset id="mainset" title="<?=language::translate('META_MY')?>">
<legend class="bold"><?=language::translate('META_MY')?></legend>
<?
	$arrMyMeta = $SETTINGSClass->getMetadataTypes(VCDUtils::getUserID());
?>
<form name="metadata" method="post" action="exec_form.php?action=addmetadata">
<table cellpadding="1" cellspacing="1" border="0" width="100%">
<tr>
	<td valign="top" width="60%">
	<?
		if (!is_array($arrMyMeta) || sizeof($arrMyMeta) == 0) {
			print language::translate('META_NONE');
		} else {
			print "<table cellspacing=\"1\" cellpadding=\"1\" border=\"0\" class=\"displist\" width=\"100%\">";
			$i = 1;
			foreach ($arrMyMeta as $metaDataTypeObj) {
				print "<tr>";
				print "<td>{$i}</td>";
				print "<td>{$metaDataTypeObj->getMetadataTypeName()}</td>";
				print "<td>{$metaDataTypeObj->getMetadataDescription()}</td>";
				print "<td width=\"2%\"><img src=\"images/icon_del.gif\" onclick=\"deleteMetaType({$metaDataTypeObj->getMetadataTypeID()})\"/></td>";
				print "</tr>";
				$i++;
			}
			print "</table>";
		}
	?>
	</td>
	<td valign="top" width="40%">
		<table cellpadding="1" cellspacing="1" width="100%" border="0">
		<tr>
			<td><?=language::translate('META_NAME')?>: </td>
			<td><input type="text" name="metadataname"/></td>
		</tr>
		<tr>
			<td><?=language::translate('META_DESC')?>: </td>
			<td><input type="text" name="metadatadescription"/> &nbsp; <input type="submit" name="newmeta" value="<?= language::translate('X_SAVE') ?>"/></td>
		</tr>
		</table>
	</td>

</tr>
</table>




</form>
</fieldset>
</br>