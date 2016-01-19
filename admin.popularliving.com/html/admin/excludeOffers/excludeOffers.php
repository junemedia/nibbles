<?php

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$sPageTitle = "Exclude Offers By Flow / Link";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($sSaveClose || $sSaveNew) {
		if (is_array($aOfferCode) && is_array($aLinkId) && is_array($aFlowId)) {
			if (count($aOfferCode) > 0) {
				// at least 1 offer is checked
				$sOffersToInsertUpdate = '';
				foreach ($aOfferCode as $asdf) {
					if ($asdf !='') {
						$sOffersToInsertUpdate .= "$asdf,";
					}
				}
				$sOffersToInsertUpdate = substr($sOffersToInsertUpdate,0,strlen($sOffersToInsertUpdate)-1);
				
				$bLinkOkay = false;
				$sLinkId = '';
				if (count($aLinkId) > 0) {
					foreach ($aLinkId as $asdf) {
						if ($asdf !='') {
							$sLinkId .= "$asdf,";
							$bLinkOkay = true;
						}
					}
					$sLinkId = substr($sLinkId,0,strlen($sLinkId)-1);
				}
				
				$bFlowOkay = false;
				$sFlowId = '';
				if (count($aFlowId) > 0) {
					foreach ($aFlowId as $asdf){
						if ($asdf !='') {
							$sFlowId .= "$asdf,";
							$bFlowOkay = true;
						}
					}
					$sFlowId = substr($sFlowId,0,strlen($sFlowId)-1);
				}
				
				if ($bFlowOkay == false && $bLinkOkay == false) {
					$sMessage .= "Please select Flow or Source Code.";
					$bKeepValues = true;
				} else {
					if ($id !='') {
						$sInsertUpdate = "UPDATE excludedOffers
								SET flowId = \"$sFlowId\",
								linkId = \"$sLinkId\",
								offerCode = \"$sOffersToInsertUpdate\"
								WHERE id = '$id'";
					} else {
						$sInsertUpdate = "INSERT INTO excludedOffers (flowId,linkId,offerCode)
								VALUES (\"$sFlowId\",\"$sLinkId\",\"$sOffersToInsertUpdate\")";
					}
					$rDbResult = dbQuery($sInsertUpdate);
				}
			} else {
				$sMessage .= "Please select OfferCode.";
				$bKeepValues = true;
			}
		}
	}
	
	if ($sSaveClose) {
		if ($bKeepValues != true) {
			echo "<script language=JavaScript>
				window.opener.location.reload();
				self.close();
				</script>";			
			exit();
		}
	} else if ($sSaveNew) {
		if ($bKeepValues != true) {
			$sReloadWindowOpener = "<script language=JavaScript>
						window.opener.location.reload();
						</script>";			
		}
	}
	
	
	if ($id !='') {
		$sGetData = "SELECT * FROM excludedOffers WHERE id='$id'";
		$rDataResult = dbQuery($sGetData);
		while ($oDataRow = dbFetchObject($rDataResult)) {
			$aLinkId = explode(',', $oDataRow->linkId);
			$aFlowId = explode(',', $oDataRow->flowId);
			$aOfferCode = explode(',', $oDataRow->offerCode);
		}
	} else {
		$aLinkId = array();
		$aFlowId = array();
		$aOfferCode = array();
	}

	// prepare offers list
	$sOffersQuery = "SELECT offerCode FROM offers
				 	 ORDER BY offerCode";
	$rOffersResult = dbQuery($sOffersQuery);
	$sOffersOptions = '';
	while ($oOffersRow = dbFetchObject($rOffersResult)) {
		$sSelected = '';
		for ($i=0;$i<count($aOfferCode);$i++) {
			if ($oOffersRow->offerCode == $aOfferCode[$i] ) {
				$sSelected = "selected";
				break;
			}
		}
		$sOffersOptions .= "<option value='$oOffersRow->offerCode' $sSelected>$oOffersRow->offerCode";
	}
	
	
	
	// prepare flows list
	$sFlowNameQuery = "SELECT id,flowName FROM flows
					WHERE nibblesVersion='2'
				 	 ORDER BY flowName";
	$rFlowsResult = dbQuery($sFlowNameQuery);
	$sSelected = '';
	if (count($aFlowId) == 0 || $aFlowId[0] == '') {
		$sSelected = "selected";
	}
	$sFlowsOptions = "<option value='' $sSelected>";
	while ($oFlowsRow = dbFetchObject($rFlowsResult)) {
		$sSelected = '';
		for ($i=0;$i<count($aFlowId);$i++) {
			if ($oFlowsRow->id == $aFlowId[$i] ) {
				$sSelected = "selected";
				break;
			}
		}
		$sFlowsOptions .= "<option value='$oFlowsRow->id' $sSelected>$oFlowsRow->flowName";
	}
	
	
	// prepare links list
	$sLinksQuery = "SELECT id,sourceCode FROM links
				 	 ORDER BY sourceCode";
	$rLinksResult = dbQuery($sLinksQuery);
	$sSelected = '';
	if (count($aLinkId) == 0 || $aLinkId[0] == '') {
		$sSelected = "selected";
	}
	$sLinksOptions = "<option value='' $sSelected>";
	while ($oLinksRow = dbFetchObject($rLinksResult)) {
		$sSelected = '';
		for ($i=0;$i<count($aLinkId);$i++) {
			if ($oLinksRow->id == $aLinkId[$i] ) {
				$sSelected = "selected";
				break;
			}
		}
		$sLinksOptions .= "<option value='$oLinksRow->id' $sSelected>$oLinksRow->sourceCode";
	}

	
	
	
	include("$sGblIncludePath/adminAddHeader.php");	
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=id value='$id'>";
	echo $sReportJavaScript;
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>Flow Name: </td>
		<td><select name='aFlowId[]' multiple size=10>
		<?php echo $sFlowsOptions;?>
		</select>
	</td></tr>

	<tr><td>Source Code: </td>
		<td><select name='aLinkId[]' multiple size=10>
		<?php echo $sLinksOptions;?>
		</select>
	</td></tr>

	<tr><td>Offer Code: </td>
		<td><select name='aOfferCode[]' multiple size=10>
		<?php echo $sOffersOptions;?>
		</select>
	</td></tr>

	<tr><td colspan=2 align=center >
		<input type=submit name=sSaveClose value='Save & Close'> &nbsp; &nbsp; 
		<input type=button name=sAbandonClose value='Abandon & Close' onclick="self.close();">
		</td>
	</tr>
</table>
</form>

<?php
} else {
	echo "You are not authorized to access this page...";
}
?>
