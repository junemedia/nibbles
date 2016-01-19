<?php


include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles - Campaign Text";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

// Check user permission to access this page
if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if($sDelete == 'Delete' && $iId != ''){
		$sql = "DELETE FROM campaignText WHERE id = '$iId'";
		$res = dbQuery($sql);
	}
		
				//	WHERE popups.popType !='' 
	$sSelectQuery = "SELECT * FROM campaignText WHERE campaignId = '$campaignId' ORDER BY pageOrder ASC";
	$rSelectResult = dbQuery($sSelectQuery);
	$sList = '';
	while ($oRow = dbFetchObject($rSelectResult)) {
		if ($sBgcolorClass=="ODD") {
			$sBgcolorClass="EVEN";
		} else {
			$sBgcolorClass="ODD";
		}
		
		
		$sList .= "<tr class=$sBgcolorClass><td>$oRow->pageOrder</td>
						<td>".addslashes($oRow->text1)."</td>
						<td>".addslashes($oRow->text2)."</td>
						<td><a href='JavaScript:void(window.open(\"/admin/campaignsManagement/addCampaignText.php?iMenuId=259&campaignId=$campaignId&iId=$oRow->id\", \"\", \"height=800, width=1000, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
						 | <a href='Javascript:void(confirmDelete(this, $oRow->id));'>Delete</a></td></tr>";
	}
	
	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Records Exist...";
	}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId>
			<input type=hidden name=sDelete>";

	$sourceCodeSelect = "<select name='campaignId' onChange='reloadWithSource(this.value);'>";
	$sGetSourceCodeSQL = "SELECT * FROM campaigns ORDER BY campaignName";
	$rGetSourceCode = dbQuery($sGetSourceCodeSQL);
	while($oGetSourceCode = dbFetchObject($rGetSourceCode)){
		if($campaignId != '' && $campaignId == $oGetSourceCode->id) $idSelected = 'selected';
		else  $idSelected = '';
		$sourceCodeSelect .= "<option value='$oGetSourceCode->id' $idSelected>$oGetSourceCode->campaignName</option>";
	}
	$sourceCodeSelect .= "</select>";
	
	
	include("../../includes/adminHeader.php");
	
	$addButton = "<input type='button' onClick='JavaScript:void(window.open(\"/admin/campaignsManagement/addCampaignText.php?iMenuId=259&campaignId=$campaignId\", \"\", \"height=800, width=1000, scrollbars=yes, resizable=yes, status=yes\"));' value='Add'>";

	
	
	echo "<script type='text/javascript'>
function reloadWithSource(src){
	//src = document.form1.campaignId.value;
	document.location = '/admin/campaignsManagement/campaignText.php?PHPSESSID=".session_id()."&iMenuId=$iMenuId&campaignId='+src;
}
	
	function confirmDelete(form1,id){
		if(confirm('Are you sure you want to delete this record ?'))
		{
			document.form1.elements['sDelete'].value='Delete';
			document.form1.elements['iId'].value=id;
			document.form1.submit();
		}
	}
	</script>"
	?>
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=5 align=left><?php echo $addButton;?></td></tr>
<tr><td colspan=5 align=left><?php echo $sourceCodeSelect;?></td></tr>
<tr><td class=header>Order</td>
<td class=header>Text 1</td>
<td class=header>Text 2</td></tr>
<?php echo $sList;?>
<tr><td colspan=5 align=left><?php echo $addButton;?></td></tr>
</table>
</form>
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>

