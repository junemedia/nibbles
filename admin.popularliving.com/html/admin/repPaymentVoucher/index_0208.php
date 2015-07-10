<?php

/*********

Script to Display

**********/


include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

$sPageTitle = "Prepare Payment Voucher";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	
	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	//$iCurrDay = date('d');
	
	//$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	
	if (! ($sViewReport)) {
		
		if ($iCurrMonth > 1) {
			$iMonth = $iCurrMonth - 1;
		} else {
			$iMonth = "12";
			$iYear = $iCurrYear - 1;
		}
	}
	
	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
				
		$iValue = $i+1;
		
		if ($iValue < 10) {
			$iValue = "0".$iValue;
		}
		
		if ($iValue == $iMonth) {
			$sMonthSel = "selected";
		} else {
			$sMonthSel = "";
		}
		
		
		$sMonthOptions .= "<option value='$iValue' $sMonthSel>$aGblMonthsArray[$i]";
	}
	
		
	// prepare year options
	for ($i = $iCurrYear; $i >= $iCurrYear-5; $i--) {
		
		if ($i == $iYear) {
			$sYearSel = "selected";
		} else {
			$sYearSel ="";
		}
				
		$sYearOptions .= "<option value='$i' $sYearSel>$i";		
	}	
				
	
	
// Prepare partner options for Partner Selection box
$sPartnerQuery = "SELECT id, companyName, code
				  FROM   partnerCompanies
				  ORDER BY companyName";
$rPartnerResult = dbQuery($sPartnerQuery);

while ( $oPartnerRow = dbFetchObject($rPartnerResult)) {
	
	if ($oPartnerRow->id == $iPartnerId) {
		$sSelected = "selected";
	} else {
		$sSelected ="";
	}
	$sPartnerOptions .="<option value='".$oPartnerRow->id."' $sSelected>".$oPartnerRow->companyName;
}	
	
	include("../../includes/adminHeader.php");
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";	

	//<BR><a href='JavaScript:void(window.open("selectPages.php?iMenuId=<?php echo $iMenuId;>","page","scrollbars=yes, status=yes"));'>Select Pages</a>	

?>

<form name=form1 action='voucher.php' target=_BLANK>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td>Voucher Month</td>
	<td><select name=iMonth><?php echo $sMonthOptions;?>	
		</select> &nbsp;<select name=iYear><?php echo $sYearOptions;?>
		</select>
	</td>
</tr>		
<tr><td>Select Partner</td>
	<td><select name='iPartnerId'>
	<?php echo $sPartnerOptions;?>
	</select></td>
</tr>
		
<tr><td colspan=4><input type=submit name=sPrepareVoucher value='Prepare Voucher'></td></tr>

<tr><td class=header colspan=4>Notes:</td></tr>
<tr><td colspan=4>Payment amount will change as postal verification status changes.</td></tr>
<tr><td colspan=4>Payment amount is calculated as total unique postal verified users multiplied by the rate for the campaign.</td></tr>

</table>

</form>

<?php

} else {
	echo "You are not authorized to access this page...";
}
?>