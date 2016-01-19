<?php

/*********

Script to Display Add/Edit Payment Terms

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Status Email Recipients - Add/Edit Status Email Recipients";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if (($sSaveClose || $sSaveNew) && !($iId)) {
	// if new payment term added
	
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   emailRecipients
					WHERE  purpose = '$sPurpose'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Email Recipients for this purpose already exists...";
		$bKeepValues = true;
	} else {
		
		$sAddQuery = "INSERT INTO emailRecipients(purpose, emailRecipients)
					 VALUES('$sPurpose', '$sEmailRecipients')";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
		$sAddLogQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Inserted: $sAddQuery\")"; 
		$rResult = dbQuery($sAddLogQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		$rResult = dbQuery($sAddQuery);
		if (!($rResult)) {			
			$sMessage = dbError();
			$bKeepValues = true;
		}		
	}

} else if (($sSaveClose || $sSaveNew) && ($iId)) {
	
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   emailRecipients
					WHERE  purpose = '$sPurpose'
					AND    id != '$iId'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Email Recipients for this purpose already exists...";
		$bKeepValues = true;
	} else {
		$sEditQuery = "UPDATE emailRecipients
					   SET 	  purpose = '$sPurpose',
							  emailRecipients = '$sEmailRecipients'	
					   WHERE  id = '$iId'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
		$sAddLogQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Updated: $sEditQuery\")"; 
		$rResult = dbQuery($sAddLogQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		

		
		$rResult = dbQuery($sEditQuery);
		
		if (!($rResult)) {
			$sMessage = dbError();
		}
	}
}

if ($sSaveClose) {
	if ($bKeepValues != true ) {
		
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
				
		
		$sPurpose = '';
		$sEmailRecipients = '';
	}
}

if ($iId) {
	
	// If Clicked to edit, get the data to display in fields
	
	$sSelectQuery = "SELECT * FROM emailRecipients
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$sPurpose = $oSelectRow->purpose;
		$sEmailRecipients = $oSelectRow->emailRecipients;
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
		<tr><TD>Purpose</td>
			<td><input type=text name=sPurpose value='<?php echo $sPurpose;?>'></td>
		</tr>
		<tr><TD>Email Recipients</td>
			<td><input type=text name=sEmailRecipients value='<?php echo $sEmailRecipients;?>' size=60></td>
		</tr>
	</table>	
		
<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>