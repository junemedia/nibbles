<?php

/*********

Script to Display Add/Edit Banned Emails

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Popup Management - Add/Edit Popup";
if (hasAccessRight($iMenuId) || isAdmin()) {
	
if (($sSaveClose || $sSaveNew) && !($iId)) {
	// if new banned email added
	
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   popups
					WHERE  popup = '$sPopup'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Popup record already exists...";
		$bKeepValues = true;
	} else {
		$sAddQuery = "INSERT INTO popups(popup, weight, popupUrl)
				 VALUES('$sPopup', '$iWeight', \"$sPopupUrl\")";

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
	}
	
} else if (($sSaveClose || $sSaveNew) && ($iId)) {
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   popups
					WHERE  popup = '$sPopup'
					AND    id != '$iId'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Popup record already exists...";
		$bKeepValues = true;
	} else {
		$sEditQuery = "UPDATE popups
					   SET 	  popup = '$sPopup',
							  weight = '$iWeight',
							  popupUrl = \"$sPopupUrl\"
				  	   WHERE id = '$iId'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: " . addslashes($sEditQuery) . "\")"; 
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
		$iId = '';
		$sPopup = '';
		$iWeight = '';
		$sPopupUrl = '';
	}
}

if ($iId) {
	
	// If Clicked to edit, get the data to display in fields
	
	$sSelectQuery = "SELECT * FROM popups
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	echo dbError();
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$sPopup = $oSelectRow->popup;
		$iWeight = $oSelectRow->weight;
		$sPopupUrl = $oSelectRow->popupUrl;
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
		<tr><TD>Popup</td><td><input type=text name=sPopup value='<?php echo $sPopup;?>'></td></tr>		
		<tr><TD>Popup Weight</td><td><input type=text name=iWeight value='<?php echo $iWeight;?>'></td></tr>		
		<tr><TD>Popup URL</td><td><input type=text name=sPopupUrl value='<?php echo $sPopupUrl;?>' size=50></td></tr>		
		<Tr><Td colspan=2><R>Popup which has weight set to 0 won't be selected to display.</td></tr>
</table>
		
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>