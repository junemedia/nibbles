<?php 

include_once("../../includes/paths.php");
include_once("../../nibbles2/libs/pixel.php");

$sPageTitle = "Pixel Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($delete) {
		$deleteQuery = "DELETE FROM pixels WHERE id='$id'";
		$deleteResult = mysql_query($deleteQuery);
		
		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($deleteQuery) . "\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
			
	}
	
	$WHERE = '';
	
	if($src != ''){
		
		$pFactory = new PixelFactory();
		$list = $pFactory->pixelList($src);
		
		foreach($list as $row){
			if ($bgcolorClass == "ODD") {
				$bgcolorClass = "EVEN";
			} else {
				$bgcolorClass = "ODD";
			}
			
			//print_r($row);
			
			$partnerSQL = "SELECT companyName FROM partnerCompanies WHERE id = $row->partnerId";
			//echo "$partnerSQL";
			$partResp = dbQuery($partnerSQL);
			$partnerName = dbFetchObject($partResp);
			
			$campaignSQL = "SELECT campaignName FROM campaigns WHERE id = $row->campaignId";
			//echo "$partnerSQL";
			$campaignResp = dbQuery($campaignSQL);
			$campaign = dbFetchObject($campaignResp);
			
			$sTempDeleteVal = "&nbsp;&nbsp;&nbsp;<a href='JavaScript:confirmDelete(this,$row->id);'>Delete</a>";
			
	
			$ReportList .= "
	<tr class=$bgcolorClass>
		<td>$row->id</td><td>Partner: $partnerName->companyName</td><td colspan=3 align=right><a href='JavaScript:void(window.open(\"addPixel.php?iMenuId=$iMenuId&id=".$row->id."\", \"AddContent\", \"height=500, width=950, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					$sTempDeleteVal</td>
	</tr>
	<tr class=$bgcolorClass>
		<td colspan=5>".htmlspecialchars($row->pixelHtml)."</td>
	</tr>
	<tr class=$bgcolorClass>
		<td>Type: $row->type</td><td>Display: $row->displayOption</td><td>Campaign: $campaign->campaignName</td><td>Source Code: $row->sourceCode</td><td>Global: </td>
	</tr>
	"; 
		}
		
	} else {
	
		$selectQuery = "SELECT pixels.id as id, 
								pixels.pixelHtml as pixelHtml, 
								pixels.campaignId as campaignId, 
								pixels.sourceCode as sourceCode,
								pixels.displayOption as displayOption,
								pixels.type as type, 
								campaigns.campaignName as campaignName,
								partnerCompanies.companyName as companyName
						FROM pixels LEFT JOIN campaigns ON campaigns.id = pixels.campaignId LEFT JOIN partnerCompanies ON partnerCompanies.id = pixels.partnerId
						WHERE (pageId = '0' OR pageId is NULL) $WHERE";
		//echo "$selectQuery";
		$result = mysql_query($selectQuery);
		while ($row = mysql_fetch_object($result)) {
			if ($bgcolorClass == "ODD") {
				$bgcolorClass = "EVEN";
			} else {
				$bgcolorClass = "ODD";
			}
			
			
			$sTempDeleteVal = "&nbsp;&nbsp;&nbsp;<a href='JavaScript:confirmDelete(this,$row->id);'>Delete</a>";
			
	
			$ReportList .= "
	<tr class=$bgcolorClass>
		<td>$row->id</td><td>Partner: $row->companyName</td><td colspan=3 align=right><a href='JavaScript:void(window.open(\"addPixel.php?iMenuId=$iMenuId&id=".$row->id."\", \"AddContent\", \"height=500, width=950, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					$sTempDeleteVal</td>
	</tr>
	<tr class=$bgcolorClass>
		<td colspan=5>".htmlspecialchars($row->pixelHtml)."</td>
	</tr>
	<tr class=$bgcolorClass>
		<td>Type: $row->type</td><td>Display: $row->displayOption</td><td>Campaign: $row->campaignName</td><td>Source Code: $row->sourceCode</td><td>Global: </td>
	</tr>
	"; 
			
		}
		if (mysql_num_rows($result) == 0) {
			$sMessage = "No Records Exist...";
		}
	}

	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addPixel.php?iMenuId=$iMenuId\", \"\", \"height=500, width=950, scrollbars=yes, resizable=yes, status=yes\"));'>";

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