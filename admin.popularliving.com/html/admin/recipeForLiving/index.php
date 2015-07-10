<?php

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles - Recipe For Living Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

// Check user permission to access this page
if (hasAccessRight($iMenuId) || isAdmin()) {
		
	if ($sDelete) {
		$sDeleteQuery = "DELETE FROM recipes WHERE id = '$iId'";
		$rResult = dbQuery($sDeleteQuery);
		$iId = '';
		//echo "$rResult is result";
		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sDeleteQuery) . "\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
	}
	
	$sSelectQuery = "SELECT * FROM recipes";
	$rSelectResult = dbQuery($sSelectQuery);
	$sList = '';
	while ($oRow = dbFetchObject($rSelectResult)) {
		if ($sBgcolorClass=="ODD") {
			$sBgcolorClass="EVEN";
		} else {
			$sBgcolorClass="ODD";
		}
		/*
		$sCheckQuery = "SELECT * FROM links WHERE campaignId = '$oRow->id'";
		$rCheckResult = dbQuery($sCheckQuery);
		$sTempDeleteCode = "&nbsp;&nbsp;&nbsp;Delete";
		if (dbNumRows($rCheckResult) == 0) {
			$sTempDeleteCode = "&nbsp;&nbsp;&nbsp;<a href='JavaScript:confirmDelete(this,$oRow->id);' >Delete</a>";
		}*/
		
		$sContent = substr($oRow->directions,0,30);
		
		//$sTempDeleteCode
		$sList .= "<tr class=$sBgcolorClass>
						<td>$oRow->title</td>
						<td>$oRow->header</td>
						<td>$sContent...</td>
						<td><a href='JavaScript:void(window.open(\"addRecipe.php?iMenuId=$iMenuId&id=".$oRow->id."\", \"AddContent\", \"height=300, width=650, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a> | 
						<a href='JavaScript:void(confirmDelete(\"\",$oRow->id));'>Delete</a>
						
						</td></tr>";
	}
	
	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Records Exist...";
	}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>
			<input type=hidden name=sDelete value=''>";

	$sAddButton ="<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addRecipe.php?iMenuId=$iMenuId\", \"\", \"height=300, width=650, scrollbars=yes, resizable=yes, status=yes\"));'>";
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
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=5 align=left><?php echo $sAddButton;?></td></tr>
<tr><td class=header>Recipe Title</td><td class=header>Recipe Header</td><td class=header>Description</td>
</tr>
<?php echo $sList;?>
<tr><td colspan=5 align=left><?php echo $sAddButton;?></td></tr>
</table>
</form>
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
