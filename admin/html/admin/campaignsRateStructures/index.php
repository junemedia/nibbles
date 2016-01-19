<?php

/*********

Script to Display List/Delete Type Codes
**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");


session_start();

$sPageTitle = "Nibbles Campaign Rate Structure - List/Delete Campaign Rate Structure";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {		
	if ($sDelete) {
		// if record deleted
		$sDeleteQuery = "DELETE FROM campaignRateStructure WHERE  id = $iId"; 
		$rResult = dbQuery($sDeleteQuery);
		if (!($rResult)) {
			$sMessage = dbError();
		}

		// start of track users' activity in nibbles 
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		// end of track users' activity in nibbles		
		$iId = '';
	}

	// Select Query to display list of reason codes
	$sSelectQuery = "SELECT * FROM campaignRateStructure";
	$rSelectResult = dbQuery($sSelectQuery);
	
	while ($oRow = dbFetchObject($rSelectResult)) {
		// For alternate background color
		if ($sBgcolorClass=="ODD") {
			$sBgcolorClass="EVEN";
		} else {
			$sBgcolorClass="ODD";
		}
		$sCampaignRateList .= "<tr class=$sBgcolorClass><TD>$oRow->rateType</td>
						<TD>".ascii_encode($oRow->description)."</td>
						<TD><a href='JavaScript:void(window.open(\"addRateStructure.php?iMenuId=$iMenuId&iId=".$oRow->id."\", \"campTypes\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					    &nbsp;<a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a>
						</td></tr>";
	}

	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Records Exist...";
	}
	
	// Hidden fields to pass with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

	$sAddButton ="<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addRateStructure.php?iMenuId=$iMenuId\", \"campTypes\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";

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
<tr><td class=header>Rate Structure</td>
<td class=header>Description</td>
</tr>

<?php echo $sCampaignRateList;?>
<tr><td colspan=7 align=left><?php echo $sAddButton;?></td></tr>
</table>

</form>
	
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>