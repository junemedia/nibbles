<?php

/*********
Script to Display Add/Edit Type Codes
**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");


session_start();

$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
$sPageTitle = "Nibbles Campaign Types - Add/Edit Campaign Type";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if (($sSaveClose || $sSaveNew) && !($iId)) {
	// if new record added
	
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   campaignTypes
					WHERE  campaignType = '$sCampaignType'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Record already exists with this campaign type...";
		$bKeepValues = true;
	} else {
		$sAddQuery = "INSERT INTO campaignTypes(campaignType, description)
				 VALUES('$sCampaignType', '$sDescription')";

		// start of track users' activity in nibbles 
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $sAddQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sAddQuery);
		if (!($rResult))
		$sMessage = dbError();
	}
} else if (($sSaveClose || $sSaveNew) && ($iId)) {
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   campaignTypes
					WHERE  campaignType = '$sCampaignType'
					AND    id != '$iId'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Record already exists with this campaign type...";
		$bKeepValues = true;
	} else {
		$sEditQuery = "UPDATE campaignTypes
				  	   SET    campaignType = '$sCampaignType',
						      description = '$sDescription'
				  	   WHERE id = '$iId'";

		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  		VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $sEditQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles		
		
		$rResult = dbQuery($sEditQuery);
		if (!($rResult)) {
			$sMessage = dbError();
		}
	}
}

if ($sSaveClose) {
	if ($bKeepValues != true) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";			
		// exit from this script
		exit();
	}
} else if ($sSaveNew) {
	if ($bKeepValues != true) {
		$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";	
	
		$sCampaignType = '';
		$sDescription = '';
	}
}

if ($iId) {
	
	// If Clicked to edit, get the data to display in fields
	
	$sSelectQuery = "SELECT * FROM campaignTypes
				     WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$sCampaignType = $oSelectRow->campaignType;
		$sDescription = ascii_encode($oSelectRow->description);
	}
} else {
	
	ascii_encode(stripslashes($sDescription));
	
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
		<tr><TD>Campaign Type</td><td><input type=text name=sCampaignType value='<?php echo $sCampaignType;?>'></td></tr>
		<tr><TD>Description</td><td><input type=text name=sDescription value='<?php echo $sDescription;?>'></td></tr>
	</table>		
		
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>