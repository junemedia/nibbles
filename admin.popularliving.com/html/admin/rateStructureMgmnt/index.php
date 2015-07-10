<?php 

include("../../includes/paths.php");

$sPageTitle = "Campaign Rate Structure Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($delete) {
		$deleteQuery = "DELETE FROM campaignRateStructure WHERE id='$id'";
		$deleteResult = mysql_query($deleteQuery);
		
		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($deleteQuery) . "\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
			
	}

	$selectQuery = "SELECT *
					FROM campaignRateStructure";
	$result = mysql_query($selectQuery);
	while ($row = mysql_fetch_object($result)) {
		if ($bgcolorClass == "ODD") {
			$bgcolorClass = "EVEN";
		} else {
			$bgcolorClass = "ODD";
		}
		
		
		$sTempDeleteVal = "&nbsp;&nbsp;&nbsp;<a href='JavaScript:confirmDelete(this,$row->id);'>Delete</a>";
		
		$sCaptureDisplay = '';
		switch($row->captureType){
			default:
			case 'emailCapture':
				$sCaptureDisplay = 'Email Capture';
				break;
			case 'memberCapture':
				$sCaptureDisplay = 'Member Capture';
				break;
			case 'neither':
				$sCaptureDisplay = 'Neither Email Capture nor Member Capture';
				break;
				
		}
		
		$ReportList .= "
<tr class=$bgcolorClass>
	<td>$row->description</td><td>$row->rateType</td><td>$sCaptureDisplay</td><td><a href='JavaScript:void(window.open(\"addRate.php?iMenuId=$iMenuId&id=".$row->id."\", \"AddContent\", \"height=300, width=800, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
				$sTempDeleteVal</td>
</tr>
"; 
	}

	if (mysql_num_rows($result) == 0) {
		$sMessage = "No Records Exist...";
	}
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addRate.php?iMenuId=$iMenuId\", \"\", \"height=300, width=800, scrollbars=yes, resizable=yes, status=yes\"));'>";

	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";
	
	include("$sGblIncludePath/adminHeader.php");	
?>
<script language=JavaScript>
function confirmDelete(form1,id) {
	if(confirm('Are you sure to delete this record ?')) {							
		document.form1.elements['delete'].value='Delete';
		document.form1.elements['id'].value=id;
		document.form1.submit();								
	}
}
</script>
<?php echo $sDisplayUrl;?>	
<form name=form1 action='<?php echo $PHP_SELF;?>'>

<input type=hidden name=delete>

<?php echo $hidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><th colspan=3 align=left><?php echo $addButton;?></th></tr>
<tr>
	<td>Description</td><td>Abbreviation</td><td>Capture Type</td>
</tr>
<!--
<tr>
<td class=header align=left>Flow Name</td>
<td class=header>Show Non-Revenue Offers</td>
</tr>-->
<?php echo $ReportList;?>
<tr><th colspan=7 align=left><?php echo $addButton;?></th></tr>
</table>

</form>

<?php
include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}				
?>	

