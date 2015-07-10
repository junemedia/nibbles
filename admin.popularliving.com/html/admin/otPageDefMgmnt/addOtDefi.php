<?php

/*********

Script to Add/Edit OT Page Definitions

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
$sPageTitle = "Nibbles OT Page Definitions - Add/Edit Ot Page Definition";

if (($sSaveClose || $sSaveNew) && !($iId)) {
	// if new email content added
	
	$sAddQuery = "INSERT INTO otPageDefinitions(definition, definedValue)
					 VALUES('$sDefinition', \"$sDefinedValue\")";

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
	
	
	
} else if (($sSaveClose || $sSaveNew) && ($iId)) {
	
	
	$sEditQuery = "UPDATE otPageDefinitions
					  SET definedValue = \"$sDefinedValue\"
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
		
		$sDefinition = '';
		$sDefinedValue = '';		
	}
}

if ($iId) {
	
	// If Clicked to edit, get the data to display in fields
	
	$sSelectQuery = "SELECT * FROM otPageDefinitions
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$sDefinition = $oSelectRow->definition;
		$sDefinedValue = ascii_encode($oSelectRow->definedValue);
		$sDefinitionField = $sDefinition;		
	}
} else {
	
	$sDefinitionField = "<input type=text name=sDefinition value='$sDefinition' size=35>";
	$sDefinedValue = ascii_encode(stripslashes($sDefinedValue));
	
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
		<tr><TD>Definition</td><td><?php echo $sDefinitionField;?></td></tr>		
		<tr><TD>Defined Value</td><td><textarea name=sDefinedValue rows=30 cols=60><?php echo $sDefinedValue;?></textarea></td></tr>
	</table>	
		
<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>