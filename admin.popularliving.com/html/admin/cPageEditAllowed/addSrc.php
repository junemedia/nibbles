<?php

/*********

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Allow Edit C Pages";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($sSaveClose || $sSaveNew) {
		if ($sSourceCode !='') {
			// check if already exists
			$sCheckQuery = "SELECT *
							FROM   cPageEditAllowed
							WHERE  sourceCode = '$sSourceCode'";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult) > 0) {
				$sMessage = "Source Code Already Exists...";
				$bKeepValues = true;
			} else {
				$sAddQuery = "INSERT INTO cPageEditAllowed (sourceCode,userName) 
								VALUES(\"$sSourceCode\",\"$sTrackingUser\")";
				$rAddResult = dbQuery($sAddQuery);
				
				// start of track users' activity in nibbles 
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $sAddQuery\")"; 
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles		
			}
		} else {
			$sMessage = "Please Select Source Code...";
			$bKeepValues = true;
		}
	}
	
	if ($sSaveClose) {
		if ($bKeepValues != true) {
			echo "<script language=JavaScript>
				window.opener.location.reload();
				self.close();
				</script>";			
			exit();
		}
	} else if ($sSaveNew) {
		if ($bKeepValues != true) {
			$sReloadWindowOpener = "<script language=JavaScript>
						window.opener.location.reload();
						</script>";			
			$sSourceCode = '';
		}
	}


	$sSourceCodeQuery = "SELECT sourceCode FROM links order by sourceCode";
	$rSourceCodeResult = mysql_query($sSourceCodeQuery);
	$sSourceCodeOption = "<option value=''>";
	while ($oSourceCodeRow = mysql_fetch_object($rSourceCodeResult)) {
		if ($oSourceCodeRow->sourceCode == $sSourceCode) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		$sSourceCodeOption .= "<option value='$oSourceCodeRow->sourceCode' $sSelected>$oSourceCodeRow->sourceCode";
	}

		
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iId value='$iId'>";
	
	include("../../includes/adminAddHeader.php");
	?>
	
	<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
	<?php echo $sHidden;?>
	<?php echo $sReloadWindowOpener;?>
	<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><td width=35%>Source Code</td>
			<td><select name='sSourceCode'>
			<?php echo $sSourceCodeOption;?>
			</select>
			</td>
		</tr>
	</table>	
		
	<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>