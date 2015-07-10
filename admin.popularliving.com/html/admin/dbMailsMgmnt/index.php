<?php

/*********

Script to Display List/Delete Email Contents

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Date Based Message Management";

// Check user permission to access this page

if ($sReceiveTestEmails || $sReceiveSingleTest) {
	
	// you must pass arguments in proper order. 
	// Therefore if $iWithSub is blank, put 0 as second argument placeholder

	if (!($iWithSub)) {
		$iWithSub = '0';
	}
	//echo "$sGblRoot/crons/sendDbMail.php $sTestEmail $iWithSub $iId";
	if ($sTestEmail != '') {
		exec("php $sGblRoot/crons/sendDbMail.php $sTestEmail $iWithSub $iId");
		$sMessage = "Test Email(s) Sent To $sTestEmail...";	
	}
}

if ($sSave) {
	
	$sSelectQuery = "SELECT *
					 FROM   dbMails";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow= dbFetchObject($rSelectResult)) {
		$iTempId = $oSelectRow->id;
		$sActiveValue = $aIsActive[$iTempId];
		
		$sUpdateQuery = "UPDATE dbMails
						 SET    isActive = '$sActiveValue'
						 WHERE  id = '$iTempId'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sAddLogQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Update: $sUpdateQuery\")"; 
		$rLogResult = dbQuery($sAddLogQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		
		$rUpdateResult = dbQuery($sUpdateQuery);
		echo dbError();
	}
}

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	if ($sDelete) {
		// if record deleted
		
		$sDeleteQuery = "DELETE FROM dbMails
	 			   		WHERE  id = $iId"; 

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sAddLogQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteQuery\")"; 
		$rLogResult = dbQuery($sAddLogQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sDeleteQuery);
		if (!($rResult)) {
			$sMessage = dbError();
		}
		// reset $id
		$iId = '';
	}
	
	// set default order by column
	if (!($sOrderColumn)) {
		$sOrderColumn = "id";
		$sIdOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($sOrderColumn) {
		
		case "triggerDays" :
		$sCurrOrder = $sTriggerDaysOrder;
		$sTriggerDaysOrder = ($sTriggerDaysOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "triggerLookBackDays" :
		$sCurrOrder = $sTriggerLookBackDaysOrder;
		$sTriggerLookBackDaysOrder = ($sTriggerLookBackDaysOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "trigg" :
		$sCurrOrder = $sTriggerOrder;
		$sTriggerOrder = ($sTriggerOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "emailFormat" :
		$sCurrOrder = $sEmailFormatOrder;
		$sEmailFormatOrder = ($sEmailFormatOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "emailSub" :
		$sCurrOrder = $sEmailSubOrder;
		$sEmailSubOrder = ($sEmailSubOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "title":
		$sCurrOrder = $sTitleOrder;
		$sTitleOrder = ($sTitleOrder != "DESC" ? "DESC" : "ASC");
		case "isActive":
		$sCurrOrder = $sIsActiveOrder;
		$sIsActiveOrder = ($sIsActiveOrder != "DESC" ? "DESC" : "ASC");
		default:
		$sCurrOrder = $sIdOrder;
		$sIdOrder = ($sIdOrder != "DESC" ? "DESC" : "ASC");
	}
		
	// Select Query to display list of payment methods
	
	$sSelectQuery = "SELECT dbMails.*
					 FROM 	dbMails 
					 ORDER BY $sOrderColumn $sCurrOrder";
	
	$rSelectResult = dbQuery($sSelectQuery);
	
	while ($oRow = dbFetchObject($rSelectResult)) {
		
		// For alternate background color
		if ($sBgcolorClass=="ODD") {
			$sBgcolorClass="EVEN";
		} else {
			$sBgcolorClass="ODD";
		}
		if ($oRow->isActive) {
			$sIsActiveChecked = "checked";
		} else {
			$sIsActiveChecked = "";
		}
		
		$sDbMapList = '';
		$sListQuery = "SELECT joinLists.title
					   FROM   dbMailsMap, joinLists
					   WHERE  dbMailsMap.joinListId = joinLists.id
					   AND    dbMailsMap.dbMailId = '".$oRow->id."'
					   ORDER BY title";
		$rListResult = dbQuery ($sListQuery);
		echo dbError();
		while ($oListRow = dbFetchObject($rListResult)) {
			$sDbMapList .= $oListRow->title."<BR>";
		}
		
		
		$sEmailContentsList .= "<tr class=$sBgcolorClass><td>$oRow->id</td>
									<td>$sDbMapList</td>
									<TD>$oRow->triggerDays</td>
									<td>$oRow->triggerLookBackDays</td>
									<td>$oRow->trigg</td>
									<td>$oRow->emailFormat</td>
									<td>$oRow->emailSub</td>
									<td><input type=checkbox name=aIsActive[$oRow->id] value='1' $sIsActiveChecked></td>
									<td nowrap><a href='JavaScript:rcvTestEmail(this,".$oRow->id.");'>Receive Test Email</a></td>
									<TD nowrap><a href='JavaScript:void(window.open(\"addEmail.php?iMenuId=$iMenuId&iId=".$oRow->id."\", \"AddAEmail\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					    &nbsp;<a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a>
						</td></tr>";
	}
	
	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Records Exist...";
	}
	
	if ($sTestEmail == '') {
		$sTestEmail = "Recipient";
	}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId >";

	$sAddButton ="<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addEmail.php?iMenuId=$iMenuId\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
		
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId";
	
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

	function rcvTestEmail(form1,id)
	{
		document.form1.elements['sReceiveSingleTest'].value='Receive';
		document.form1.elements['iId'].value=id;
		document.form1.submit();								
		
	}	
	
</script>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<input type=hidden name=sDelete>
<input type=hidden name=sReceiveSingleTest>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td align=left colspan=7><?php echo $sAddButton;?></td></tr>
<tr>
	<td colspan=10>
	<table width=100%>
		<tr><td width=15%><b>Test Message: </b></td>
			<td> <input type=checkbox value='1' name=iWithSub> Test messages sent only when DBM conditions matched
			</td>
			<td><input type=text name=sTestEmail value='<?php echo $sTestEmail;?>'>
			</td><td align=right><input type=submit name=sReceiveTestEmails value='Receive Test Emails For All DBMs'>
			</td>
		</tr>
	</table><BR>
	</td></tr>
	
	
<tr><td><a href='<?php echo $sSortLink;?>&sOrderColumn=id&sIdOrder=<?php echo $sIdOrder;?>' class=header>Id</a></td>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=title&sTitleOrder=<?php echo $sTitleOrder;?>' class=header>Join List Title</a></td>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=triggerDays&sTriggerDaysOrder=<?php echo $sTriggerDaysOrder;?>' class=header>Trigger Days</a></td>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=triggerLookBackDays&sTriggerLookBackDaysOrder=<?php echo $sTriggerLookBackDaysOrder;?>' class=header>Trigger Look Back Days</a></td>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=action&sActionOrder=<?php echo $sActionOrder;?>' class=header>Action</a></td>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=emailFormat&sEmailFormatOrder=<?php echo $sEmailFormatOrder;?>' class=header>Email Format</a></td>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=emailSub&sEmailSubOrder=<?php echo $sEmailSubOrder;?>' class=header>Email Sub</a></td>
	<td nowrap><a href='<?php echo $sSortLink;?>&sOrderColumn=isActive&sIsActiveOrder=<?php echo $sIsActiveOrder;?>' class=header>Is Active</a></td>
	<td><input type=submit name=sSave value='Save'></td>
</tr>

<?php echo $sEmailContentsList;?>
<tr><td align=left colspan=10><?php echo $sAddButton;?></td></tr>
</table>

</form>
	
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
