<?php

/*********

Script to Display

**********/


include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

$sPageTitle = "ECPM Summary Report";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	// prepare offers list
	
	$sOffersQuery = "SELECT *
				 	 FROM   offers
				 	 ORDER BY offerCode";
	$rOffersResult = dbQuery($sOffersQuery);
	if (count($aOfferCode) == 0 || $aOfferCode[0] == 'all') {
		$sSelected = "selected";
	}
	$sOffersOptions = "<option value='all' $sSelected>All Offers Having Leads Within Date Range ";
	while ($oOffersRow = dbFetchObject($rOffersResult)) {
		$iTempOfferId = $oOffersRow->id;
		$sTempOfferCode = $oOffersRow->offerCode;
		
		$sSelected = '';
		for ($i=0;$i<count($aOfferCode);$i++) {
			if ($sTempOfferCode == $aOfferCode[$i] ) {
				$sSelected = "selected";
				break;
			}
		}
		
		$sOffersOptions .= "<option value='$sTempOfferCode' $sSelected>$sTempOfferCode";
	}		

	$sGrossLeadsChecked = '';
	if ($iGrossLeads) {
		$sGrossLeadsChecked = "checked";
	}
			
	
	include("../../includes/adminHeader.php");
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";	

	//<BR><a href='JavaScript:void(window.open("selectPages.php?iMenuId=<?php echo $iMenuId;>","page","scrollbars=yes, status=yes"));'>Select Pages</a>	

?>


<form name=form1 action='report.php' target=_BLANK>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
			
	<tr><td>Select Offers</td>
		<td colspan=3><select name='aOfferCode[]' multiple size=15>
		<?php echo $sOffersOptions;?>
		</select></td></tr>
	<tr><td></td>
		<td colspan=3><input type=checkbox name=iGrossLeads value='1' <?php echo $sGrossLeadsChecked;?>> Gross Leads</td></tr>
	
	<tr><td colspan=4><input type=submit name=sViewReport value='View Report'>	
	&nbsp; &nbsp; <input type=submit name=sExport value='Export Report'>	
	<!--<input type=submit name=sPrintReport value='Print This Report'>--></td></tr>

<tr><td class=header colspan=4>Notes:</td></tr>
<tr><td colspan=4>Report is accurate as of midnight last night after today's leads are processed.</td></tr>
<tr><td colspan=4>Only gross leads report is accurate for the offers which are not processed on a daily basis.</td></tr>
<tr><td colspan=4>Report reflects counts for current month upto midnight last night.</td></tr>
<tr><td colspan=4>Previous Month counts are of the month prior to the current month.</td></tr>
</table>

</form>

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>