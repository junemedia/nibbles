<?php

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();


$sPageTitle = "Nibbles Links - Add/Edit Link";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
if (($sSaveClose || $sSaveNew || $sSaveContinue) && $iId) {
	if (is_array($aOfferBgColor)) {
		$sValToInsert = implode(',',$aOfferBgColor);
		$sUpdateQuery = "UPDATE links
					SET offerColor = \"$sValToInsert\"
					WHERE id = '$iId'";
		$rUpdateResult = dbQuery($sUpdateQuery);
	}
}


if ($sSaveClose && $sMessage == '') {
	if ($bKeepValues != true) {
		echo "<script language=JavaScript>				
			 self.close();
			</script>";			
			exit();
	}
}
if ($sSaveContinue) {
	if ($bKeepValues != true) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			</script>";
	}
}


if ($iId) {
	$sSelectQuery = "SELECT * FROM links
				     WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oRow = dbFetchObject($rSelectResult)) {
		$aOfferColor = explode(',',$oRow->offerColor);
		$sFlowDetails = "SELECT * FROM flowDetails WHERE flowId='$oRow->flowId'";
		$rResult = dbQuery($sFlowDetails);
		$sColorHtml = '';
		$iRow = 1;
		$x = 0;
		$iFieldId = 0;
		while ($oRow1 = dbFetchObject($rResult)) {
			$sVal1 = $aOfferColor[$x];
			$x++;
			$sVal2 = $aOfferColor[$x];
			$x++;
			
			if ($sVal1 == '') {
				$sVal1 = '#FFFFFF';
			}
			if ($sVal2 == '') {
				$sVal2 = '#EEEEEE';
			}
			
			$sColorHtml .= "<tr><td>Offer Background Color $iRow</td>
					<td colspan=3><input onblur='javascript:this.value=this.value.toUpperCase();' type=text name=aOfferBgColor[] id='$iFieldId' value='$sVal1' size=10 maxlength=7> 
						<input type=button onClick='Javascript:void(window.open(\"colorPalette.php?id=$iFieldId\",\"\",\"width=100 height=450, scrollbars=no,resizable=no, status=no\"));' Value='...'>";
			
			$iFieldId++;
			$sColorHtml .= "<td colspan=3><input onblur='javascript:this.value=this.value.toUpperCase();' type=text name=aOfferBgColor[] id='$iFieldId' value='$sVal2' size=10 maxlength=7> 
						<input type=button onClick='Javascript:void(window.open(\"colorPalette.php?id=$iFieldId\",\"\",\"width=100 height=450, scrollbars=no,resizable=no, status=no\"));' Value='...'></td></tr>";
			$iRow++;
			$iFieldId++;
		}
	}
}


// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";


include("../../includes/adminAddHeader.php");

?>
<script language=JavaScript>


</script>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<?php echo $sColorHtml; ?>
</table>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD colspan=2 align=center >
		<input type=submit name=sSaveContinue value='Save & Continue'> &nbsp; &nbsp; 
		</td><td></td>
	</tr>
</table>
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
