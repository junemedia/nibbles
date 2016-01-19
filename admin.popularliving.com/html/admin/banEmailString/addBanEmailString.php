<?php

/*********
Script to Display Add/Edit Banned Email String
**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Banned Emails String - List/Delete Banned Email String";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];


if (hasAccessRight($iMenuId) || isAdmin()) {	
	
if (($sSaveClose || $sSaveNew) && !($iId)) {
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   bannedEmailString
					WHERE  emailString = '$sEmailString'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Record already exists as banned email string with...";
		$bKeepValues = true;
	} else {
		$sAddQuery = "INSERT INTO bannedEmailString(emailString) VALUES('$sEmailString')";
		$rResult = dbQuery($sAddQuery);
		if (!($rResult)) {
			$sMessage = dbError();
		}

		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $sAddQuery\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
	}
} else if (($sSaveClose || $sSaveNew) && ($iId)) {
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   bannedEmailString
					WHERE  emailString = '$sEmailString'
					AND    id != '$iId'";
	$rCheckResult = dbQuery($sCheckQuery);
	echo dbError();
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Record already exists as banned email string with...";
		$bKeepValues = true;
	} else {
		$sEditQuery = "UPDATE bannedEmailString
				  SET emailString = '$sEmailString'
				  WHERE id = '$iId'";
		$rResult = dbQuery($sEditQuery);
		if (!($rResult)) {
			$sMessage = dbError();
		}
		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $sEditQuery\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
	}
}

if ($sSaveClose) {
	if ($bKeepValues != true) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";			
		exit();
	}
} else if ($sSaveNew) {
	if ($bKeepValues != true) {
		$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";	
		$sStartsWith = '';
	}
}

if ($iId) {
	// If Clicked to edit, get the data to display in fields
	$sSelectQuery = "SELECT * FROM bannedEmailString
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$sEmailString = $oSelectRow->emailString;
	}
} else {
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iId value='$iId'>";

include("../../includes/adminAddHeader.php");
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><td>Ban Emails String</td><td><input type=text name=sEmailString value='<?php echo $sEmailString;?>'></td></tr>
	</table>
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>