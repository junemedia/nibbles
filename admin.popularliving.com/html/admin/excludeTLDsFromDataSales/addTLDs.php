<?php

/*********

Script to Display List/Delete Excluded TLDs from Data Sales

**********/

include("../../includes/paths.php");

session_start();

$sUserName = $_SERVER['PHP_AUTH_USER'];
$sPageTitle = "Exclude TLDs From Data Sales";

if (hasAccessRight($iMenuId) || isAdmin()) {
if (($sSaveClose || $sSaveNew) && !($iId)) {
	// if new excluded TLDs added
	if (ereg("^[\.]{1}[A-Za-z]+[A-Za-z]$", $sTLDs)) {
		// check if already exists
		$sCheckQuery = "SELECT *
						FROM   excludeTLDsDataSales
						WHERE  TLDs = '$sTLDs'";
		$rCheckResult = dbQuery($sCheckQuery);
		if (dbNumRows($rCheckResult)>0) {
			$sMessage = "TLD already exists as excluded from data sales...";
			$bKeepValues = true;
		} else {
				$sAddQuery = "INSERT INTO excludeTLDsDataSales (TLDs,userName)
						 VALUES('$sTLDs','$sUserName')";

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
	} else {
		$sMessage = "Invalid TLD.  Example: .com";
	}
} else if (($sSaveClose || $sSaveNew) && ($iId)) {
	
	
	if (ereg("^[\.]{1}[A-Za-z]+[A-Za-z]$", $sTLDs)) {
		// check if already exists
		$sCheckQuery = "SELECT *
						FROM   excludeTLDsDataSales
						WHERE  TLDs = '$sTLDs'
						AND    id != '$iId'";
		$rCheckResult = dbQuery($sCheckQuery);
		if (dbNumRows($rCheckResult)>0) {
			$sMessage = "TLDs already exists as excluded from data sales...";
			$bKeepValues = true;
		} else {
				$sEditQuery = "UPDATE excludeTLDsDataSales
						  SET TLDs = '$sTLDs'
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
	} else {
		$sMessage = "Invalid TLD.  Example: .com";
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
			$sTLDs = '';
		}
}

if ($iId) {
	
	// If Clicked to edit, get the data to display in fields
	
	$sSelectQuery = "SELECT * FROM excludeTLDsDataSales
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$sTLDs = $oSelectRow->TLDs;
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
		<tr><TD>Excluded TLDs From Data Sales</td><td><input type=text name=sTLDs value='<?php echo $sTLDs;?>' maxlength="10">
		<font size=2>Example: &nbsp;<b>.com</b></font>
		</td></tr>
	</table>

<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>