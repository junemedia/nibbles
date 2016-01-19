<?php

/*********

Script to Display Add/Edit Fraud Alert Emails

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Fraud Alert Emails - Add/Edit Fraud Alert Email";
if (hasAccessRight($iMenuId) || isAdmin()) {
	
if (($sSaveClose || $sSaveNew) && !($iId)) {
	// if new banned email added
	
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   alertEmails
					WHERE  alertEmailName = '$sAlertEmailName'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Record already exists as fraud alert email...";
		$bKeepValues = true;
	} else {
		$sAddQuery = "INSERT INTO alertEmails(alertEmailName, fraudTriggerGroupSize, alertTriggerPercent, enabledStatus)
				 VALUES('$sAlertEmailName', '$sFraudTriggerGroupSize', '$sAlertTriggerPercent', '$sEnabledStatus')";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $sAddQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sAddQuery);
		if (!($rResult))
		$sMessage = dbError();
	}
	
} else if (($sSaveClose || $sSaveNew) && ($iId)) {
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   alertEmails
					WHERE  alertEmailName = '$sAlertEmailName'
					AND    id != '$iId'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Record already exists as fraud alert email...";
		$bKeepValues = true;
	} else {
		$sEditQuery = "UPDATE alertEmails
				  SET alertEmailName = '$sAlertEmailName',
				  fraudTriggerGroupSize = '$sFraudTriggerGroupSize',
				  alertTriggerPercent = '$sAlertTriggerPercent',
				  enabledStatus = '$sEnabledStatus'
				  WHERE id = '$iId'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $sEditQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
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
	if ($bKeepValues != '') {
		$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";	
	
		$sEmail = '';
	}
}

if ($iId) {
	
	// If Clicked to edit, get the data to display in fields
	
	$sSelectQuery = "SELECT * FROM alertEmails
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$sAlertEmailName = $oSelectRow->alertEmailName;
		$sFraudTriggerGroupSize = $oSelectRow->fraudTriggerGroupSize;
		$sAlertTriggerPercent = $oSelectRow->alertTriggerPercent;
		$sEnabledStatus = $oSelectRow->enabledStatus;
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
		<tr><TD>Alert Email Name</td><td><input type=text name=sAlertEmailName value='<?php echo $sAlertEmailName;?>'></td></tr>		
		<tr><TD>Fraud Trigger Group Size</td><td><input type=text name=sFraudTriggerGroupSize value='<?php echo $sFraudTriggerGroupSize;?>'></td></tr>		
		<tr><TD>Fraud Trigger Pct</td><td><input type=text name=sAlertTriggerPercent value='<?php echo $sAlertTriggerPercent;?>'></td></tr>		
		<tr><TD>Enabled Status</td><td><select name=sEnabledStatus>
		<option value="I" <?php if( $sEnabledStatus == 'I' ) { echo 'selected'; } ?>>Internal Only
		<option value="C" <?php if( $sEnabledStatus == 'C' ) { echo 'selected'; } ?>>Client Only
		<option value="B" <?php if( $sEnabledStatus == 'B' ) { echo 'selected'; } ?>>Both
		<option value="D" <?php if( $sEnabledStatus == 'D' ) { echo 'selected'; } ?>>Disabled
		</select></td></tr>		
</table>
		
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>