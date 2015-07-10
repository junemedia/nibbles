<?php

/*********

Script to Display 

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Onetime Offer Exceptions Report";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
		
$iCurrYear = date('Y');
$iCurrMonth = date('m');
$iCurrDay = date('d');

$iCurrHH = date('H');
$iCurrMM = date('i');
$iCurrSS = date('s');


$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";


// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";	

	
	$sPageOffersQuery = "SELECT otPages.pageName, pageMap.*
				   		 FROM	offers, otPages, pageMap, offerCompanies
						 WHERE  pageMap.offerCode = offers.offerCode
						 AND	offers.companyId = offerCompanies.id
						 AND    offers.mode = 'A'
						 AND    offers.isLive = '1'
						 AND    offerCompanies.creditStatus = 'ok'
						 AND	otPages.id = pageMap.pageId
						 AND    otPages.offersByCatMap = 0 ";
	
	if ($sOfferCode1 != '' && $sOfferCode2 != '') {
		$sPageOffersQuery .= " AND (pageMap.offerCode = '$sOfferCode1' || pageMap.offerCode = '$sOfferCode2') ";		
	} else {
		$sOfferCode1 = '';
		$sOfferCode2 = '';
	}
	
	$sPageOffersQuery .= " ORDER BY pageId, sortOrder";
	$rPageOffersResult = dbQuery($sPageOffersQuery);
	//echo $sPageOffersQuery;
	echo dbError();
	
	$sPrevOfferCode = '';
	$iPrevPageId = '';
	
	while ($oPageOffersRow = dbFetchObject($rPageOffersResult)) {
		$iPageId = $oPageOffersRow->pageId;
		$sPageName = $oPageOffersRow->pageName;
		$iSortOrder = $oPageOffersRow->sortOrder;
		$sOfferCode = $oPageOffersRow->offerCode;
		
		// check if prev offer and this offer is from the same category
		
		if ($iPageId == $iPrevPageId) {
			
			$sCatOfferQuery = "SELECT *
							   FROM   categoryMap A, categoryMap B
							   WHERE  A.offerCode = '$sOfferCode'
							   AND    B.offerCode = '$sPrevOfferCode'
							   AND	  A.categoryId = B.categoryId";
			$rCatOfferResult = dbQuery($sCatOfferQuery);
			
			echo dbError();
			
			if (dbNumRows($rCatOfferResult) > 0) {
				if ($sBgcolorClass == "ODD") {
					$sBgcolorClass = "EVEN_WHITE";
				} else {
					$sBgcolorClass = "ODD";
				}
			
				$sReportContent .= "<tr class=$sBgcolorClass><td>$sPageName</td><td>$sOfferCode</td><td>$sPrevOfferCode</td></tr>";
			}
			
		}
		
		$sPrevOfferCode = $sOfferCode;		
		$iPrevPageId = $iPageId;
	}
	
	
	$sOffersQuery = "SELECT offerCode
					 FROM   offers
					 ORDER BY offerCode";
	$rOffersResult = dbQuery($sOffersQuery);
	
	$sOfferCode1Options = "<option value=''>All";
	$sOfferCode2Options = "<option value=''>All";
	
	while ($oOffersRow = dbFetchObject($rOffersResult)) {
		$sTempOfferCode = $oOffersRow->offerCode;
		if ($sTempOfferCode == $sOfferCode1) {
			$sSelected = "Selected";
		} else {
			$sSelected = "";
		}
		
		$sOfferCode1Options .= "<option value='$sTempOfferCode' $sSelected>$sTempOfferCode";
		
		if ($sTempOfferCode == $sOfferCode2) {
			$sSelected = "Selected";
		} else {
			$sSelected = "";
		}
				
		$sOfferCode2Options .= "<option value='$sTempOfferCode' $sSelected>$sTempOfferCode";
	} 
	
	include("../../includes/adminHeader.php");	
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);
		
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>Check Offers</td>
	<td>Offer Code 1&nbsp; &nbsp; &nbsp;<select name=sOfferCode1><?php echo $sOfferCode1Options;?></select>
		&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
		Offer Code 2&nbsp; &nbsp; &nbsp;<select name=sOfferCode2><?php echo $sOfferCode2Options;?></select>
	</td></tr>
<tr><td colspan=2><input type=submit name=sViewReport value='View Report'>	
	<!--<input type=submit name=sPrintReport value='Print This Report'>--></td>
		<td><input type=checkbox name=sShowQueries value='Y' <?php echo $sShowQueriesChecked;?>> Show Queries</td></tr>
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=3 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR><BR><BR></td></tr>
	<tr><td colspan=3 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr>
		<td class=header>Page Name</td>		
		<td class=header>Offer Code 1</td>
		<td class=header>Offer Code 2</td>		
	</tr>
	
			<?php echo $sReportContent;?>
			<tr><td colspan=3 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=3 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=3>Report displays list of offers from same category in a row on any page.
		<BR>Report considers offers only which are live and collecting leads.
		<BR>If offercodes are selected, report checks if any of those two offers are in a row on any page.
		<BR>Report omits checking on a page on which offers are displayed by category mapping.
		<BR><BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=3><BR><BR></td></tr>
		<?php echo $sQueries;?>
		</td></tr></table></td></tr></table></td></tr>
	</table>

</td></tr>
</table>
</form>

<?php

} else {
	echo "You are not authorized to access this page...";
}
?>