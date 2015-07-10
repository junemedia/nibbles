<?php

/*********

Script to Display Add/Edit Valid Domains

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Valid Domains - Add/Edit Valid Domain";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if (($sSaveClose || $sSaveNew) && !($iId)) {
	// if new valid domain added
			
	// check if already exists
	$sCheckQuery = "SELECT *
					FROM   validDomains
					WHERE  domain = '$sDomain'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Domain already exists as valid domain...";
		$bKeepValues = true;
	} else {
		$sAddQuery = "INSERT INTO validDomains(domain) 
					 VALUES('$sDomain')";

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
					FROM   validDomains
					WHERE  domain = '$sDomain'
					AND    id != '$iId'";
	$rCheckResult = dbQuery($sCheckQuery);
	if (dbNumRows($rCheckResult)>0) {
		$sMessage = "Domain already exists as valid domain...";
		$bKeepValues = true;
	} else {		
		$sEditQuery = "UPDATE validDomains
					  SET domain = '$sDomain'
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
	if ($bKeepValues != true) {
	$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";	
	$sDomain = '';
	}
}

if ($iId) {
	
	// If Clicked to edit, get the data to display in fields 
	
	$sSelectQuery = "SELECT * FROM validDomains
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$sDomain = $oSelectRow->domain;
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
		<tr><TD>Valid Domain</td><td><input type=text name=sDomain value='<?php echo $sDomain;?>'></td></tr>
	</table>	
		
<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>