<?php

/*********

Script to Display Add/Edit Payment Method

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Nibbles Email Contents - Add/Edit Email Content";
if (hasAccessRight($iMenuId) || isAdmin()) {
if (($sSaveClose || $sSaveNew) && !($iId)) {
	// if new email content added
	
	$sAddQuery = "INSERT INTO joinListEmailContents(joinListId, emailPurpose, emailFrom, emailSub, emailBody)
					 VALUES('$iJoinListId', '$sEmailPurpose', '$sEmailFrom', \"$sEmailSub\", \"$sEmailBody\")";

	
	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: " . addslashes($sAddQuery) . "\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles		

				
	$rResult = dbQuery($sAddQuery);
	if (!($rResult))
	$sMessage = dbError();	
	echo dbError();
	
	
} else if (($sSaveClose || $sSaveNew) && ($iId)) {
	
	
	$sEditQuery = "UPDATE joinListEmailContents
					  SET joinListId = '$iJoinListId',
						  emailPurpose = '$sEmailPurpose',
						  emailFrom = '$sEmailFrom',
						  emailSub = \"$sEmailSub\",
						  emailBody = \"$sEmailBody\"
					  WHERE id = '$iId'";
	$rResult = dbQuery($sEditQuery);

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: " . addslashes($sEditQuery) . "\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles		

	
	echo $sEditQuery;
	
	if (!($rResult)) {
		$sMessage = dbError();
	}
	
}

if ($sSaveClose) {
	if ($bKeepValues != true) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
		//	self.close();
			</script>";			
		// exit from this script
		exit();
	}
} else if ($sSaveNew) {
	if ($bKeepValues != true) {
		$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";	
		$iJoinListId = '';
		$sEmailPurpose = '';
		$sEmailFrom = '';
		$sEmailSub = '';
		$sEmailBody = '';
	}
}

if ($iId) {
	
	// If Clicked to edit, get the data to display in fields
	
	$sSelectQuery = "SELECT * FROM joinListEmailContents 
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	echo dbError();
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$iJoinListId = $oSelectRow->joinListId;
		$sEmailPurpose = $oSelectRow->emailPurpose;
		$sEmailFrom = $oSelectRow->emailFrom;
		$sEmailSub = ascii_encode($oSelectRow->emailSub);
		$sEmailBody = ascii_encode($oSelectRow->emailBody);
	}
} else {
	
		$sSubject = ascii_encode(stripslashes($sSubject));
		$sMessageBody = ascii_encode(stripslashes($sMessageBody));
	
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}


$sJoinListQuery = "SELECT *
					   FROM   joinLists";
	$rJoinListResult = dbQuery($sJoinListQuery);
	while ($oJoinListRow = dbFetchObject($rJoinListResult)) {
		if ($oJoinListRow->id == $iJoinListId) {
			$sSelected = "selected";
		} else {
			$sSelected = '';
		}
		$sJoinListOptions .= "<option value='$oJoinListRow->id' $sSelected>$oJoinListRow->title";
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
	    <tr><TD>Join List</td><td><select name=iJoinListId><?php echo $sJoinListOptions;?></select></td></tr>
		<tr><TD>Email Purpose</td><td><input type=text name=sEmailPurpose value='<?php echo $sEmailPurpose;?>'></td></tr>
		<tr><TD>Email From</td><td><input type=text name=sEmailFrom value='<?php echo $sEmailFrom;?>'></td></tr>
		<tr><TD>Email Subject</td><td><input type=text name=sEmailSub value='<?php echo $sEmailSub;?>'></td></tr>				
		<tr><TD>Message Body</td><td><textarea name=sEmailBody rows=10 cols=50><?php echo $sEmailBody;?></textarea></td></tr>
	</table>	
		
<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>