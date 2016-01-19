<?php

/*******

Script to Add/Edit MyFree Footer links

*******/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$offerPageUrl = "http://[SERVER_NAME]/www/displayOffers.php";

$sPageTitle = "Footers Management - Add/Edit MyFree Footer Link";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {

if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	
	// Prepare url, if footer menu(Link) directs to display offers in the selected category
	if ($categoryId != '') {
		$url = $offerPageUrl."/offerCat/$categoryId";
	}
	
	$addQuery = "INSERT INTO edFooterLinks(linkText,categoryId, url, frontPageDisplay, offerPageDisplay)
				 VALUES('$linkText', '$categoryId', '$url', '$frontPageDisplay', '$offerPageDisplay')";
	
	
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $addQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
	
	
	$result = mysql_query($addQuery);
	if (! $result) {
		echo mysql_error();
	}
	
} else if ( ($sSaveClose || $sSaveNew) && ($id)) {
	
	// If record edited
	if ($categoryId != '') {
		$url = $offerPageUrl."/offerCat/$categoryId";
	}
	
	
	$editQuery = "UPDATE edFooterLinks
				  SET linkText = '$linkText',
				  categoryId = '$categoryId',";
	if ($frontPageDisplay == '' || $frontPageDisplay == '0') {
		$editQuery .= " frontPageDisplay = '$frontPageDisplay',	";
	}
	if ($offerPageDisplay == '' || $offerPageDisplay == '0') {
		$editQuery .= " offerPageDisplay = '$offerPageDisplay',	";
	}
	
	$editQuery .= " url = '$url'
				  WHERE id = '$id'";
	
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
	
	
	$result = mysql_query($editQuery);
	if (! $result) {
		echo mysql_error();
	}
}

if ($sSaveClose) {
	echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";				
	// exit from this script
	exit();
} else if ($sSaveNew) {
	$reloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";
	// Reset textboxes for new record
	$linkText ='';
	$url = '';
	$frontPageDisplay = '';
	$offerPageDisplay = '';
}

if ($id != '') {
	// If Clicked Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   edFooterLinks
			  		WHERE  id = '$id'";
	$result = mysql_query($selectQuery);	
	
	if ($result) {
		
		while ($row = mysql_fetch_object($result)) {
			$linkText = ascii_encode($row->linkText);
			$categoryId = $row->categoryId;
			$url = $row->url;
			$frontPageDisplay = $row->frontPageDisplay;
			$offerPageDisplay = $row->offerPageDisplay;
		}
		mysql_free_result($result);
	} else {
		echo mysql_error();
	}			
	
} else {
	$linkText = ascii_encode(stripslashes($linkText));
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=abandonNew value=' Abandon & New  '>";	
}

// Prepare Parent Caregory Options
$categoryOptions = "<option value=''>";
$categoryQuery = "SELECT *
					FROM   edOfferCategories";
$categoryResult = mysql_query($categoryQuery);
while ($categoryRow = mysql_fetch_object($categoryResult)) {
	if ($categoryRow->id == $categoryId) {
		$catSelected = "selected";
	} else {
		$catSelected = "";
	}
	
	$categoryOptions .= "<option value='".$categoryRow->id."' $catSelected>$categoryRow->category";
}

// In edit record, which Display options to be checked
if ($frontPageDisplay != '') {
	$frontPageDisplayChecked = "checked";
}
if ($offerPageDisplay != '') {
	$offerPageDisplayChecked = "checked";
}


// set values to 0 for checkbox value, to get some value (0), if checkbox checked
if ($frontPageDisplay == '') {
	$frontPageDisplay = 0;
}
if ($offerPageDisplay == '') {
	$offerPageDisplay = 0;
}

$frontPageDisplayCheckbox = "<input type=checkbox name=frontPageDisplay value='$frontPageDisplay' $frontPageDisplayChecked> Front Page Display";
$offerPageDisplayCheckbox = "<input type=checkbox name=offerPageDisplay value='$offerPageDisplay' $offerPageDisplayChecked> Offer Page Display";

// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";

include("../../includes/adminAddHeader.php");

?>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>Link Text</td>
		<td><input type=text name='linkText' value='<?php echo $linkText;?>'></td>
	</tr>

	<tr><td rowspan="2">Specify URL<BR>or<BR>Select Offer Page For</td>
		<td><input type=text name='url' value='<?php echo $url;?>' size=50></td>
	</tr>
	<tr>
		<!--<td>Offer Page For</td>-->
		<td><select name=categoryId>
		<?php echo $categoryOptions;?>
		</select></td>
	</tr>
	<tr><td></td><td><?php echo $frontPageDisplayCheckbox;?></td>
	</tr>
	<tr><td></td><td><?php echo $offerPageDisplayCheckbox;?></td>
	</tr>
				
</table>

<?php
	include("../../includes/adminAddFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>