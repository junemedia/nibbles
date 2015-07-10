<?php

/*********

Script to Display List/Delete Fraud Alert Emails
**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Fraud Alert Emails - List/Delete Fraud Alert Emails";

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {	
		
	if ($sDelete) {
		// if user record deleted
		
		$sDeleteQuery = "DELETE FROM alertEmails
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
	
	$sSelectQuery = "SELECT * FROM alertEmails";
	
	$rSelectResult = dbQuery($sSelectQuery);
	
	while ($oRow = dbFetchObject($rSelectResult)) {
		
		// For alternate background color
		if ($sBgcolorClass=="ODD") {
			$sBgcolorClass="EVEN";
		} else {
			$sBgcolorClass="ODD";
		}
		$sAlertEmailList .= "<tr class=$sBgcolorClass><TD>$oRow->alertEmailName</td>		
						<td>$oRow->fraudTriggerGroupSize</td><td>$oRow->alertTriggerPercent</td><td>$oRow->enabledStatus</td><TD><a href='JavaScript:void(window.open(\"addFraudAlertEmail.php?iMenuId=$iMenuId&iId=".$oRow->id."\", \"fraudAlertEmail\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					    &nbsp;<a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a>
						</td></tr>";
	}
	
	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Alert Emails Exist...";
	}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

	$sAddButton ="<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addFraudAlertEmail.php?iMenuId=$iMenuId\", \"alertEmail\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
		
	include("../../includes/adminHeader.php");	
	
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
<tr><td colspan=7 align=left><?php echo $sAddButton;?></td></tr>
<tr>
<td class=header>Alert Email Name</td>
<td class=header>Fraud Group Size</td>
<td class=header>Alert Trigger Pct</td>
<td class=header>Enabled Status</td>
<td>&nbsp;</td>
</tr>

<?php echo $sAlertEmailList;?>
<tr><td colspan=7 align=left><?php echo $sAddButton;?></td></tr>
</table>

</form>
	
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>