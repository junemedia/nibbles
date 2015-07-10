<?php 

include("../../includes/paths.php");

$sPageTitle = "Exclude Offers By Flow / Link";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
session_start();
if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($delete) {
		$deleteQuery = "DELETE FROM excludedOffers WHERE id='$id'";
		$deleteResult = mysql_query($deleteQuery);

		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($deleteQuery) . "\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
	}

	$selectQuery = "SELECT * FROM excludedOffers ORDER BY id DESC";
	$result = mysql_query($selectQuery);
	while ($row = mysql_fetch_object($result)) {
		$sOfferCode = str_replace(',',', ',$row->offerCode);
		$sLinkId = explode(',',$row->linkId);
		$sFlowId = explode(',',$row->flowId);
		$sSourceCode = '';
		$sFlowName = '';
		if ($bgcolorClass == "ODD") {
			$bgcolorClass = "EVEN";
		} else {
			$bgcolorClass = "ODD";
		}

		
		
		if (count($sLinkId) > 0) {
			foreach ($sLinkId as $asdf) {
				$rGetSrc = mysql_query("SELECT sourceCode FROM links WHERE id='$asdf'");
				while ($oSrcRow = mysql_fetch_object($rGetSrc)) {
					$sSourceCode .= "$oSrcRow->sourceCode, ";
				}
			}
			if ($sSourceCode !='') {
				$sSourceCode = substr($sSourceCode,0,strlen($sSourceCode)-2);
			}
		}
		
		if (count($sFlowId) > 0) {
			foreach ($sFlowId as $asdf) {
				$rGetFlow = mysql_query("SELECT flowName FROM flows WHERE id='$asdf'");
				while ($oFlowRow = mysql_fetch_object($rGetFlow)) {
					$sFlowName .= "$oFlowRow->flowName, ";
				}
			}
			if ($sFlowName !='') {
				$sFlowName = substr($sFlowName,0,strlen($sFlowName)-2);
			}
		}

		$ReportList .= "<tr class=$bgcolorClass>
				<td>$sFlowName</td>
				<td>$sSourceCode</td>
				<td>$sOfferCode</td>
				<td><a href='JavaScript:void(window.open(\"excludeOffers.php?iMenuId=$iMenuId&id=".$row->id."\", \"AddContent\", \"height=700, width=650, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
				&nbsp;&nbsp;&nbsp;
				<a href='JavaScript:confirmDelete(this,$row->id);'>Delete</a>
				</td></tr>";
	}

	if (mysql_num_rows($result) == 0) {
		$sMessage = "No Records Exist...";
	}
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"excludeOffers.php?iMenuId=$iMenuId\", \"\", \"height=700, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";

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
<td class=header>Flow Name</td>
<td class=header>Source Code</td>
<td class=header>Excluded Offers</td>
</tr>
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

