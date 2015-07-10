<?php

/*********

Script to Display List/Add/Edit/Delete Affiliate Management Company information

*********/

include("../../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Handcrafters Village Site Variables Management - Add/Edit Site Variable";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	// SELECT HCV DATABASE
	dbSelect($sGblHcvDBName);	
	

if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	if($varName == '') {
		$sMessage = "variable Name Is Required...";
		$keepValues = true;
	} else {
		//Check For Dupe		
		$checkQuery = "SELECT *
				   FROM   siteVars
				   WHERE  varName = '$varName'";
		$checkResult = dbQuery($checkQuery);
		
		if (dbNumRows($checkResult) > 0 ) {
			$sMessage = "Site Variable Already Exists...";
			$keepValues = true;
		} else {
			$addQuery = "INSERT INTO siteVars (varName, varInternalName, varText)
				 VALUES ('$varName', '$varName', '$varText')";
			
			// start of track users' activity in nibbles
			$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: siteVars.varName='$varName'\")";
			$rResult = dbQuery($sAddQuery);
			echo  dbError();
			// end of track users' activity in nibbles
			
			
			$result = dbQuery($addQuery);
			if (!($result)) {
				echo dbError();
			}
		}
	}
	
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
	//if record edited
	if ($varName == '') {
		$sMessage = "Variable Name Is Required...";
		$keepValues = true;
	} else {
		//Check For Dupe
		$checkQuery = "SELECT *
				   		FROM   siteVars
				   		WHERE  varName = '$varName'
						AND   id != '$id'";
		$checkResult = dbQuery($checkQuery);
		if (dbNumRows($checkResult) > 0 ) {
			$sMessage = "Site Variable Alredy Exists...";
			$keepValues = true;
		} else {
			
			$editQuery = "UPDATE siteVars
				  	  	  SET 	 varText = '$varText'							 
				  		  WHERE  id = '$id'";
			
			// start of track users' activity in nibbles
			$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Update: siteVars.id='$id'\")";
			$rResult = dbQuery($sAddQuery);
			echo  dbError();
			// end of track users' activity in nibbles
			
			
			$result = dbQuery($editQuery);
			if (!($result)) {
				echo dbError();
			}
		}
	}
}

if ($sSaveClose) {
	if ($keepValues !=true) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";					
		// exit from this script
		exit();
	}
} else if ($sSaveNew) {
	$reloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";
	// Reset textboxes for new record
	if ($keepValues != true) {
		$title = '';		
	}
}

if ($id != '') {
	// If Clicked on Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   siteVars
			  		WHERE  id = '$id'";
	$result = dbQuery($selectQuery);
	
	if ($result) {
		
		while ($row = dbFetchObject($result)) {
			$varName = $row->varName;
			$varText = $row->varText;
		}		
	} else {
		echo dbError();
	}
}  else {
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

$varName = ascii_encode(stripslashes($varName));
// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>
			<input type=hidden name=id value='$id'>";

	include("$sGblIncludePath/adminAddHeader.php");	
?>
<form action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $reloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td >Variable Name</td>
		<td><input type=text name='varName' value='<?php echo $varName;?>' size=45></td>		
	</tr>
	<tr><td >Variable Content</td>
		<td><textarea name='varText' rows=15 cols=70><?php echo $varText;?></textarea></td>		
	</tr>
			
</table>

	<?php
// include footer

	include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}				
?>	