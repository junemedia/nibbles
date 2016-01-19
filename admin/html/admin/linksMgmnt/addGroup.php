<?php

/*********
Script to Display Add/Edit Campaign Group
**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Nibbles Campaigns - Add/Edit Campaign Group";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if (($sSaveClose || $sSaveNew) && !($iId)) {
		// if new record added
		if (ctype_alnum($sGroupName) && $sGroupName != '') {
			$sCheckQuery = "SELECT * FROM campaignsGroup WHERE groupName='$sGroupName'";
			$rResult = dbQuery($sCheckQuery);
			
			if (mysql_num_rows($rResult) > 0) {
				$bKeepValues = true;
				$sMessage .= "Group Name Already Exist";
			} else {
				$sGroupDesc = addslashes($sGroupDesc);
				$sInsertQuery = "INSERT INTO campaignsGroup (groupName,groupDesc)
							VALUES ('$sGroupName', \"$sGroupDesc\")";
				$rInsertResult = dbQuery($sInsertQuery);
			}
		} else {
			$bKeepValues = true;
			$sMessage .= "Group Name Must Be AlphaNumeric: A-Z and/or 0-9";
		}
	} elseif (($sSaveClose || $sSaveNew) && ($iId)) {
		// edit current entry
		if (ctype_alnum($sGroupName) && $sGroupName != '') {
			$sCheckQuery = "SELECT * FROM campaignsGroup WHERE groupName='$sGroupName'";
			$rResult = dbQuery($sCheckQuery);
			
			if (mysql_num_rows($rResult) > 0) {
				$bKeepValues = true;
				$sMessage .= "Group Name Already Exist";
			} else {
				$sGroupDesc = addslashes($sGroupDesc);
				$sUpdateQuery = "UPDATE campaignsGroup 
								SET groupName = \"$sGroupName\",
								groupDesc = \"$sGroupDesc\"
								WHERE id = '$iId'";
				$rUpdateResult = dbQuery($sUpdateQuery);
			}
		} else {
			$bKeepValues = true;
			$sMessage .= "Group Name Must Be AlphaNumeric: A-Z and/or 0-9";
		}
	}

	//$sPageReloadUrl = "index.php?iMenuId=$iMenuId&sFilter=$sFilter&sAlpha=$sAlpha&sExactMatch=$sExactMatch&sShowActive=$sShowActive&iRecPerPage=$iRecPerPage&iPage=$page&sSourceCode=$sSourceCode&sShowRedirect=true";
	
if ($sSaveClose) {
	if ($bKeepValues != true) {
		
		// don't reload the list page if show active was checked. As page loading takes time 
		if ($sShowActive != 'Y') {
			echo "<script language=JavaScript>
				self.close();
				</script>";			
			// exit from this script
				exit();
		} else {
			echo "<script language=JavaScript>				
				 self.close();
				</script>";			
			// exit from this script
				exit();
		}
	}
} else if ($sSaveNew) {
	if ($bKeepValues != true) {
		// don't reload the list page if show active was checked. As page loading takes time 
		if ($sShowActive != 'Y') {
			$sReloadWindowOpener = "<script language=JavaScript>
								</script>";	
		}
	}
		$sGroupName = '';
		$sGroupDesc = '';
}


if ($iId) {
	// If Clicked to edit, get the data to display in fields
	
	$sSelectQuery = "SELECT * FROM campaignsGroup
				     WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {		
		$sGroupName = $oSelectRow->groupName;
		$sGroupDesc = $oSelectRow->groupDesc;
	}
} else {
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";
}

include("../../includes/adminAddHeader.php");

?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr>
		<td>Group: </td>
		<td><input type=text name=sGroupName value='<?php echo $sGroupName;?>' size=15 maxlength="10">&nbsp;&nbsp;Group Name Must Be AlphaNumeric: A-Z and/or 0-9</td>
	</tr>

	<tr>
		<td>Group Description: </td>
		<td><textarea name=sGroupDesc rows=10 cols=40><?php echo $sGroupDesc;?></textarea></td>
	</tr>
</table>	
		
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>