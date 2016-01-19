<?php

/*********

Script to Add/Edit HandCraftersVillage Footer Link

*********/

include("../../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Handcrafters Village Footer Management - Add/Edit Footer Link";

$imageFilePath = $sGblHcvWebRoot ."/projectImages/";
$imageUrl = $sGblHcvSiteRoot."/projectImages";

$thFilePath = $sGblHcvWebRoot ."/thumb/";
$thUrl = $sGblHcvSiteRoot."/thumb";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	// SELECT HCV DATABASE
	dbSelect($sGblHcvDBName);	
	

if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
		
	$addQuery = "INSERT INTO footerLinks(linkText, url, sortOrder)
				 VALUES('$linkText', '$url', '$sortOrder')";
	
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $addQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
	
	
	$result = dbQuery($addQuery);
	if (! $result) {
		echo dbError();
	}	
} else if ( ($sSaveClose || $sSaveNew) && ($id)) {		
	
	$editQuery = "UPDATE footerLinks
				  SET linkText = '$linkText',				  
				  sortOrder = '$sortOrder',				  
				  url = '$url'
				  WHERE id = '$id'";
	
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
	
	
	$result = dbQuery($editQuery);
	if (! $result) {
		echo dbError();
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
	$sortOrder = '';	
}

if ($id != '') {
	// If Clicked Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   footerLinks
			  		WHERE  id = '$id'";
	$result = dbQuery($selectQuery);
	
	if ($result) {
		
		while ($row = dbFetchObject($result)) {
			$linkText = ascii_encode($row->linkText);			
			$url = $row->url;
			$sortOrder = $row->sortOrder;			
		}
		dbFreeResult($result);
	} else {
		echo dbError();
	}	
} else {
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>
			<input type=hidden name=id value='$id'>";

	include("$sGblIncludePath/adminAddHeader.php");	
?>

<?php echo $reloadWindowOpener;?>
</form>
<form action='<?php echo $PHP_SELF;?>' method=post enctype="multipart/form-data">
<?php echo $hidden;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	
	<tr><td>Link Text</td>
		<td><input type=text name='linkText' value='<?php echo $linkText;?>'></td>
	</tr>

	<tr><td>Specify URL</td>
		<td><input type=text name='url' value='<?php echo $url;?>' size=50></td>
	</tr>
	<tr><td>Sort Order</td>
		<td><input type=text name='sortOrder' value='<?php echo $sortOrder;?>'></td>
	</tr>
	

	<?php
// include footer

	include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}				
?>	