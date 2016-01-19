<?php

include("../../includes/paths.php");

if (hasAccessRight($iMenuId) || isAdmin()) {
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sPageTitle = "Foreign IP Handling Administration";

	if ($sSaveOfferStatus && $sVarValue != '') {
		$sUpdateQuery = "UPDATE vars SET varValue = '$sVarValue' 
					WHERE system='foreignIp' 
					AND varName='blockForeignIp' LIMIT 1";
		$rUpdateResult = dbQuery($sUpdateQuery);
		if ($rUpdateResult) {
			$sMessage = "Record Updated...";
			// start of track users' activity in nibbles 
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"$sUpdateQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery);
			// end of track users' activity in nibbles
		}
	}
	
	$sSelectQuery = "SELECT * FROM vars 
				WHERE system='foreignIp' 
				AND varName='blockForeignIp' LIMIT 1";
	$rResult = dbQuery($sSelectQuery);
	if ($rResult) {
		while ($oRow = dbFetchObject($rResult)) {
			$sBlockAllChecked = '';
			$sNoChecked = '';
			$sSourceCodeChecked = '';
			$sURLChecked = '';
			
			if ($oRow->varValue == 'blockAll') {
				$sBlockAllChecked = "checked";
			} else if ($oRow->varValue =='no') {
				$sNoChecked = "checked";
			} else if ($oRow->varValue =='src') {
				$sSourceCodeChecked = "checked";
			} else if ($oRow->varValue == 'url') {
				$sURLChecked = "checked";
			}

			$sVarList .= "<tr class=$sBgcolorClass><td nowrap>
				<input type=radio name='sVarValue' value='blockAll' $sBlockAllChecked> &nbsp; Block All Foreign IP&nbsp;<br><br>
				<input type=radio name='sVarValue' value='no' $sNoChecked> &nbsp; Do Not Block Foreign IP&nbsp;<br><br>
				<input type=radio name='sVarValue' value='src' $sSourceCodeChecked> &nbsp; Block Source Code&nbsp;<br><br>
				<input type=radio name='sVarValue' value='url' $sURLChecked> &nbsp; Block URL&nbsp;<br><br></td></tr>";
		}
	}

	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
		<input type=hidden name=iId value='$iId'>";
	include("../../includes/adminHeader.php");
?>
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<table bgcolor=c9c9c9 width=95%>
<tr><td>&nbsp;</td></tr><tr><td>&nbsp;</td></tr>
<?php echo $sVarList;?><tr><td>&nbsp;</td></tr>
<tr><td><input type=submit name=sSaveOfferStatus value='  Update  '></td></tr>
</table>
</form>
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>