<?php

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Advertisers Location - List/Delete Advertisers Location";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	if ($sDelete) {
		$sDeleteQuery = "DELETE FROM advertisersLocation WHERE id='$iId'";
		$rResult = dbQuery($sDeleteQuery);

		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"$sDeleteQuery\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
		$iId = '';
	}
	
	// Select Query to display list banned domains
	//echo phpinfo();
	
	$sSelectQuery = "SELECT * FROM advertisersLocation ORDER BY companyName";
	$rSelectResult = dbQuery($sSelectQuery);
	$sList = '';
	while ($oRow = dbFetchObject($rSelectResult)) {
		if ($sBgcolorClass == "ODD") {
			$sBgcolorClass = "EVEN";
		} else {
			$sBgcolorClass = "ODD";
		}
		$sList .= "<tr class=$sBgcolorClass><TD>$oRow->companyName</td>
						<TD>$oRow->contactName</td>
						<TD>$oRow->email</td>
						<TD><a href='JavaScript:void(window.open(\"addLocation.php?iMenuId=$iMenuId&iId=".$oRow->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					    &nbsp;<a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a>
						</td></tr>";
	}
	
	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Records Exist...";
	}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

	$sAddButton ="<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addLocation.php?iMenuId=$iMenuId\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
		
	include("../../includes/adminHeader.php");	
	
?>
	
<script language=JavaScript>
	function confirmDelete(form1,id) {
		if(confirm('Are you sure to delete this record ?')) {							
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
<tr><td class=header>Company Name</td>
<td class=header>Contact Name</td>
<td class=header>Email</td>
</tr>

<?php echo $sList;?>
<tr><td colspan=7 align=left><?php echo $sAddButton;?></td></tr>
</table>

</form>
	
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>