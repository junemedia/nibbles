<?php

/*********

Script to Display List/Delete Banned Email Starting
**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Popup Management - List/Delete Popups";

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {	

	// Make changes in popup script to apply the changes
	if ($sApplyChanges == 'Y') {
		
		$rFpPopupScript = fopen("$sGblLibsPath/jsPopFuncs.js", "r");
		
		if ($rFpPopupScript) {
			while (!feof($rFpPopupScript)) {
				$sScriptContent .= fread($rFpPopupScript, 1024);
			}
							
			fclose($rFpPopupScript);
			
			
			$iPosStart = strpos($sScriptContent, "//*** START SPECIFY POPUP ARRAYS ***//");
			$iPosEnd = strpos($sScriptContent, "//*** END SPECIFY POPUP ARRAYS ***//", $iPosStart);
			
			$iLength = $iPosEnd - $iPosStart;
			
			$sVarString = substr($sScriptContent, $iPosStart, $iLength + 36);

			// prepare javascript array code as per popup weight to put in script		
			$sPopupQuery = "SELECT *
							FROM   popups";
			$rPopupResult = dbQuery($sPopupQuery);
			while ($oPopupRow = dbFetchObject($rPopupResult)) {
				$iWeight = $oPopupRow->weight;
				
				if ($iWeight > 0) {
					for ($i=1; $i <= $iWeight; $i++) {
						$sPopupArrayElements .= "\"$oPopupRow->popup\",";
						$sUrlArrayElements .= "\"$oPopupRow->popupUrl\",";
					}
				}
			}

			if ($sPopupArrayElements != '') {
				$sPopupArrayElements = substr($sPopupArrayElements,0,strlen($sPopupArrayElements)-1);
			}
			if ($sUrlArrayElements != '') {
				$sUrlArrayElements = substr($sUrlArrayElements,0,strlen($sUrlArrayElements)-1);
			}

			$sNewVarString = "//*** START SPECIFY POPUP ARRAYS ***//\r\n\r\n";
			$sNewVarString .= "var popUpArray = new Array ($sPopupArrayElements);\r\n\r\n";
			$sNewVarString .= "var popUpUrlArray = new Array($sUrlArrayElements);\r\n\r\n";
			$sNewVarString .= "//*** END SPECIFY POPUP ARRAYS ***//";
			
			
			$sScriptContent = str_replace($sVarString, $sNewVarString, $sScriptContent);
			
			// rewrite popup script file with the changed content
			
			$rFpPopupScript = fopen("$sGblLibsPath/jsPopFuncs.js", "w");
			if ($rFpPopupScript) {
				fwrite($rFpPopupScript, $sScriptContent);
				fclose($rFpPopupScript);
				$sMessage = "Changes applied.  Please ask IT to run push to apply changes to all other servers.";
			}
	
		} else {
			$sMessage = "Error in opening the file.";
		}
	}
	
	
	if ($sDelete) {
		// if user record deleted
		
		$sDeleteQuery = "DELETE FROM popups
	 			   		WHERE  id = $iId"; 

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sDeleteQuery);
		if (!($rResult)) {
			$sMessage = dbError();
		}
		// reset $id
		$iId = '';
	}
	
	// Select Query to display list of Users
	
	$sSelectQuery = "SELECT * FROM popups WHERE popType=''";
	
	$rSelectResult = dbQuery($sSelectQuery);
	
	while ($oRow = dbFetchObject($rSelectResult)) {
		
		// For alternate background color
		if ($sBgcolorClass=="ODD") {
			$sBgcolorClass="EVEN";
		} else {
			$sBgcolorClass="ODD";
		}
		$sBannedEmailList .= "<tr class=$sBgcolorClass><TD>$oRow->popup</td>
								<TD>$oRow->weight</td>
								<TD>$oRow->popupUrl</td>
						<TD><a href='JavaScript:void(window.open(\"addPopup.php?iMenuId=$iMenuId&iId=".$oRow->id."\", \"popup\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					    &nbsp;<a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a>
						</td></tr>";
	}
	
	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Popups Exist...";
	}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

	$sAddButton ="<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addPopup.php?iMenuId=$iMenuId\", \"popup\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
		
	include("../../includes/adminHeader.php");	

	$sApplyChangesLink = "<a href='$PHP_SELF?iMenuId=$iMenuId&sApplyChanges=Y'>Apply Popup Changes</a>";
	
	?>
	
<script language=JavaScript>
	function confirmDelete(form1,id)
	{
		if(confirm('Are you sure to delete this record ?'))
		{							
			document.form1.elements['sDelete'].value='Delete';
			document.form1.elements['iId'].value=id;
			document.form1.submit();								
		}
	}						
</script>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<input type=hidden name=sDelete>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td align=left><?php echo $sAddButton;?></td><td colspan=2 align=right><?php echo $sApplyChangesLink;?></td></tr>
<tr><td class=header>Popup</td>
	<td class=header>Weight</td>
	<td class=header>Popup URL</td>
	<td></td>
</tr>

<?php echo $sBannedEmailList;?>
<tr><td colspan=3 align=left><?php echo $sAddButton;?></td></tr>
</table>

</form>
	
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>