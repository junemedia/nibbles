<?php

/*********

Script to Display

**********/


include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

$sPageTitle = "ECPM Report";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	
	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');
	
		$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	
	if (! ($sViewReport)) {
		$iYearTo = substr( $sYesterday, 0, 4);
			$iMonthTo = substr( $sYesterday, 5, 2);
			$iDayTo = substr( $sYesterday, 8, 2);
		
		$iYearFrom = substr( $sYesterday, 0, 4);
			$iMonthFrom = substr( $sYesterday, 5, 2);
			$iDayFrom = "01";
			
	}
	
	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		
		$value = $i+1;
		
		if ($value < 10) {
			$value = "0".$value;
		}
		
		if ($value == $iMonthFrom) {
			$fromSel = "selected";
		} else {
			$fromSel = "";
		}
		if ($value == $iMonthTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		
		$sMonthFromOptions .= "<option value='$value' $fromSel>$aGblMonthsArray[$i]";
		$sMonthToOptions .= "<option value='$value' $toSel>$aGblMonthsArray[$i]";
	}
	
	// prepare day options for From and To date
	for ($i = 1; $i <= 31; $i++) {
		
		if ($i < 10) {
			$value = "0".$i;
		} else {
			$value = $i;
		}
		
		if ($value == $iDayFrom) {
			$fromSel = "selected";
		} else {
			$fromSel = "";
		}
		if ($value == $iDayTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		$sDayFromOptions .= "<option value='$value' $fromSel>$i";
		$sDayToOptions .= "<option value='$value' $toSel>$i";
	}
	
	// prepare year options
	for ($i = $iCurrYear; $i >= $iCurrYear-5; $i--) {
		
		if ($i == $iYearFrom) {
			$fromSel = "selected";
		} else {
			$fromSel ="";
		}
		if ($i == $iYearTo) {
			$toSel = "selected";
		} else {
			$toSel ="";
		}
		
		$sYearFromOptions .= "<option value='$i' $fromSel>$i";
		$sYearToOptions .= "<option value='$i' $toSel>$i";
	}	
		
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

	// prepare ot pages list
	
	$sPagesQuery = "SELECT *
				 FROM   otPages				 
				 WHERE pageName NOT LIKE 'test%'
				 ORDER BY pageName";	
	$rPagesResult = dbQuery($sPagesQuery);
	echo dbError();
	if ((count($aPageId) == 0 && count($aCategoryId == 0)) || $aPageId[0] == 'all') {
		$sSelected = "selected";
	}
	$sPagesOptions = "<option value='all' $sSelected>All";
	while ($oPagesRow = dbFetchObject($rPagesResult)) {
		$iTempPageId = $oPagesRow->id;
		$sTempPageName = $oPagesRow->pageName;
		
		$sSelected = '';
		for($i=0; $i<count($aPageId); $i++) {
			if ($iTempPageId == $aPageId[$i]) {
				$sSelected = "selected";
				break;
			}
		}
		$sPagesOptions .= "<option value='$iTempPageId' $sSelected>$sTempPageName";
	}
	
	// Prepare checkboxes for Categories
	$sCategoriesQuery = "SELECT *
			  	FROM   categories
				ORDER BY title";
	$rCategoriesResult = dbQuery($sCategoriesQuery);
	echo dbError();
	
	if ($aCategoryId[0] == 'all') {
		$sSelected = "selected";
	}
	$sCategoriesOptions = "<option value='all' $sSelected>All";
	
	while ($oCategoriesRow = dbFetchObject($rCategoriesResult)) {
		$iTempCategoryId = $oCategoriesRow->id;
		$sTempCategoryName = $oCategoriesRow->title;
		
		$sSelected = '';
		for($i=0; $i<count($aCategoryId); $i++) {
			if ($iTempCategoryId == $aCategoryId[$i]) {
				$sSelected = "selected";
				break;
			}
		}
		$sCategoriesOptions .= "<option value='$iTempCategoryId' $sSelected>$sTempCategoryName";
	}

	$sGrossLeadsChecked = '';
	if ($iGrossLeads) {
		$sGrossLeadsChecked = "checked";
	}
	
	$sByRevSelected = '';
	$sByPageDisplaySelected = '';
	
	if ($sShowTop10 == 'byRev') {
		$sByRevSelected = "selected";
	} else if ($sShowTop10 == 'byPageDisplay') {
		$sByPageDisplaySelected = "selected";
	}
	
	
	include("../../includes/adminHeader.php");
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";	

	//<BR><a href='JavaScript:void(window.open("selectPages.php?iMenuId=<?php echo $iMenuId;>","page","scrollbars=yes, status=yes"));'>Select Pages</a>	

?>


<form name=form1 action='report.php' target=_BLANK>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td>Date From</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td>Date To</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>		
	<tr><td>Select Pages</td>
		<td><select name='aPageId[]' multiple size=15>
		<?php echo $sPagesOptions;?>
		</select></td>
		<td>OR &nbsp; &nbsp; &nbsp; Categories </td><td>
		<select name='aCategoryId[]' multiple size=15>
		<?php echo $sCategoriesOptions;?>
		</select></td></tr>
		
	<tr><td>Select Offers</td>
		<td colspan=3><select name='aOfferCode[]' multiple size=15>
		<?php echo $sOffersOptions;?>
		</select></td></tr>
	<tr><td></td>
		<td colspan=3><input type=checkbox name=iGrossLeads value='1' <?php echo $sGrossLeadsChecked;?>> Gross Leads</td></tr>
	<tr><td>Show Only</td>
		<td colspan=3><input type=radio name=sShowTop10 value='byRev' <?php echo $sByRevSelected;?> > Top 20 Pages By Revenue	
		&nbsp; &nbsp; <input type=radio name=sShowTop10 value='byPageDisplay' <?php echo $sByPageDisplaySelected;?> > Top 20 Pages By Page Displays</td></tr>
		
	<tr><td colspan=4><input type=submit name=sViewReport value='View Report'>	
	&nbsp; &nbsp; <input type=submit name=sExport value='Export Report'>	
	<!--<input type=submit name=sPrintReport value='Print This Report'>--></td></tr>

<tr><td class=header colspan=4>Notes:</td></tr>
<tr><td colspan=4>Report is accurate as of midnight last night after today's leads are processed.</td></tr>
<tr><td colspan=4>Only gross leads report is accurate for the offers which are not processed on a daily basis.</td></tr>
<tr><td colspan=4>Report reflects counts for selected date range.</td></tr>
<tr><td colspan=4>Previous Month counts are of the month prior to the selected date range.</td></tr>
</table>

</form>

<?php

} else {
	echo "You are not authorized to access this page...";
}
?>