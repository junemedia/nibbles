<?php

/*********

Script to Display List/Add/Edit/Delete Affiliate Management Company information

*********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Affiliate Management Company - Add/Edit Affiliate Management Company";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	
	$addQuery = "INSERT INTO edAffiliateMgmntCompanies(companyName, notes)
					 VALUES('$companyName', '$notes')";
	
			// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAdd2Query = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add New Entry: $addQuery\")";
		$rResult = dbQuery($sAdd2Query);
		echo  dbError();
		// end of track users' activity in nibbles
	
	
	
	$result = mysql_query($addQuery);
	if (! $result) {
		echo mysql_error();
	}
	
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
	//if record edited
	
	$editQuery = "UPDATE edAffiliateMgmntCompanies
				  SET 	 companyName='$companyName',					  	 
						 notes = '$notes'
				  WHERE  id = '$id'";
	
	
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAdd3Query = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit Entry: $editQuery\")";
		$rResult = dbQuery($sAdd3Query);
		echo  dbError();
		// end of track users' activity in nibbles
	
	
	
	$result = mysql_query($editQuery);
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
	if ($keepValues != true) {
		$companyName = "";
		$notes = '';
	}
}

if ($id != '') {
	// If Clicked on Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   edAffiliateMgmntCompanies										 
			  		WHERE  id = '$id'";
	$result = mysql_query($selectQuery);
	
	if ($result) {
		
		while ($row = mysql_fetch_object($result)) {
			$companyName = ascii_encode($row->companyName);
			$notes = ascii_encode($row->notes);
		}
		mysql_free_result($result);
	} else {
		echo mysql_error();
	}
}  else {
	$companyName = ascii_encode(stripslashes($companyName));
	$notes = ascii_encode(stripslashes($notes));
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=abandonNew value=' Abandon & New  '>";	
}

// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=id value='$id'>";


include("../../includes/adminAddHeader.php");

?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $sReloadWindowOpener;?>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>Company Name</td>
		<td><input type=text name='companyName' value='<?php echo $companyName;?>'></td>
	</tr>
	<tr><td>Notes</td>
		<td><textarea name='notes' rows=3 cols=40><?php echo $notes;?></textarea></td>
	</tr>
			
</table>


<?php
	include("../../includes/adminAddFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>