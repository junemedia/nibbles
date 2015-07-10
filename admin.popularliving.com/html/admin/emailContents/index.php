<?php

/*********

Script to Display List/Delete Email Contents

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Email Contents - List/Delete Email Content";

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	if ($sDelete) {
		// if record deleted
		
		$sDeleteQuery = "DELETE FROM emailContents
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
	
	// set default order by column
	if (!($sOrderColumn)) {
		$sOrderColumn = "emailPurpose";
		$sEmailPurposeOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($sOrderColumn) {
		
		case "emailFrom" :
		$sCurrOrder = $sEmailFromOrder;
		$sEmailFromOrder = ($sEmailFromOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "emailSub" :
		$sCurrOrder = $sSubjectOrder;
		$sSubjectOrder = ($sSubjectOrder != "DESC" ? "DESC" : "ASC");
		break;
		default:
		$sCurrOrder = $sEmailPurposeOrder;
		$sEmailPurposeOrder = ($sEmailPurposeOrder != "DESC" ? "DESC" : "ASC");	
	}
		
	// Select Query to display list of payment methods
	
	$sSelectQuery = "SELECT * FROM emailContents
					 ORDER BY $sOrderColumn $sCurrOrder";
	
	$rSelectResult = dbQuery($sSelectQuery);
	
	while ($oRow = dbFetchObject($rSelectResult)) {
		
		// For alternate background color
		if ($sBgcolorClass=="ODD") {
			$sBgcolorClass="EVEN";
		} else {
			$sBgcolorClass="ODD";
		}
		$sEmailContentsList .= "<tr class=$sBgcolorClass><td>$oRow->system</td><TD>$oRow->emailPurpose</td><td>$oRow->emailFrom</td><td>$oRow->emailSub</td>
						<TD><a href='JavaScript:void(window.open(\"addEmail.php?iMenuId=$iMenuId&iId=".$oRow->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					    &nbsp;<a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a>
						</td></tr>";
	}
	
	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Records Exist...";
	}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

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
</script>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<input type=hidden name=sDelete>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td align=left><?php echo $sAddButton;?></td></tr>
<tr><td><a href='<?php echo $sSortLink;?>&sOrderColumn=system&sSystemOrder=<?php echo $sSystemOrder;?>' class=header>System</a></td>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=emailPurpose&sEmailPurposeOrder=<?php echo $sEmailPurposeOrder;?>' class=header>Purpose</a></td>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=emailFrom&sEmailFromOrder=<?php echo $sEmailFromOrder;?>' class=header>Email From</a></td>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=subject&sSubjectOrder=<?php echo $sSubjectOrder;?>' class=header>Subject</a></td>
</tr>

<?php echo $sEmailContentsList;?>
<tr><td align=left><?php echo $sAddButton;?></td></tr>
</table>

</form>
	
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>