<?php

/*********

Script to Display Add/Edit Banned Phone Numbers
**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Banned Phone Numbers - Add/Edit Banned Phone Number";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	$bKeepValues = false;
	
if (($sSaveClose || $sSaveNew) && !($iId)) {
	// if new banned phone no. added
			
	if ( !(eregi("^[0-9]{3}[-]{1}[0-9]{3}[-]{1}[0-9]{4}$", $sPhone))) {
		$sMessage = "Please Enter Valid Phone No...";
		$bKeepValues = true;
	} else {
		$sAddQuery = "INSERT INTO bannedPhones(phone) 
					 VALUES('$sPhone')";

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
			//$bKeepValues = true;
		}
	
} else if (($sSaveClose || $sSaveNew) && ($iId)) {
	if ( !(eregi("^[0-9]{3}[-]{1}[0-9]{3}[-]{1}[0-9]{4}$", $sPhone))) {
		$sMessage = "Please Enter Valid Phone No...";
		$bKeepValues = true;
		
	} else {
		$sEditQuery = "UPDATE bannedPhones
					  SET phone = '$sPhone'
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
			//$bKeepValues = true;
			
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
	
	$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";	
}


if ($iId && !($bKeepValues)) {
	
	// If Clicked to edit, get the data to display in fields 
	
	$sSelectQuery = "SELECT * FROM bannedPhones
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$sPhone = $oSelectRow->phone;
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
		<tr><TD>Banned Phone No</td><td><input type=text name=sPhone value='<?php echo $sPhone;?>'></td></tr>		
	</table>
		
<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
