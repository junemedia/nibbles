<?php

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

session_start();

$iScriptStartTime = getMicroTime();
$sPageTitle = "API Report";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');
	
	$iCurrHH = date('H');
	$iCurrMM = date('i');
	$iCurrSS = date('s');
	
	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";
	
	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 20;
	}
	if (!($iPage)) {
		$iPage = 1;
	}
	
	
	if ($sFullDetails == 'Y') {
		$sColSpan = 10;
		$sExportHeader = "Offer Code\tAE\tLimited\tAvailable\tRestrictions\tNet\tOffer Name\tCategory\tHeadline\tLink\n";
	} else {
		$sColSpan = 7;
		$sExportHeader = "Offer Code\tAE\tNet\tOffer Name\tCategory\tHeadline\tLink\n";
	}
	
	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "offerCode";
		$sDateSentOrder = "SORT_ASC";
	}

	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "acctRep" :
			$sCurrOrder = $sAcctRepOrder;
			$sAcctRepOrder = ($sAcctRepOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "limited" :
			$sCurrOrder = $sLimitedOrder;
			$sLimitedOrder = ($sLimitedOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "available" :
			$sCurrOrder = $sAvailableOrder;
			$sAvailableOrder = ($sAvailableOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "restriction" :
			$sCurrOrder = $sRestrictionsOrder;
			$sRestrictionsOrder = ($sRestrictionsOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "net" :
			$sCurrOrder = $sNetOrder;
			$sNetOrder = ($sNetOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "link" :
			$sCurrOrder = $sLinkOrder;
			$sLinkOrder = ($sLinkOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "name" :
			$sCurrOrder = $sOfferNameOrder;
			$sOfferNameOrder = ($sOfferNameOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "category" :
			$sCurrOrder = $sCategoryOrder;
			$sCategoryOrder = ($sCategoryOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "headline" :
			$sCurrOrder = $sHeadLineOrder;
			$sHeadLineOrder = ($sHeadLineOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			default:
			$sCurrOrder = $sOfferCodeOrder;
			$sOfferCodeOrder = ($sOfferCodeOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
		}
	}

	if ($sCurrOrder == 'SORT_DESC') {
		$sCurrOrder = SORT_DESC;
	} else {
		$sCurrOrder = SORT_ASC;
	}

	
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sViewReport=View Report&sOfferCodeSelected=$sOfferCodeSelected&sExportExcel=$sExportExcel&sFullDetails=$sFullDetails";
	
	if ($sAllowReport == 'N') {
		$sMessage = "Server Load Is High. Please check back soon...";
	} else {
		$sTempPreviewLink = "http://www.popularliving.com/partners/preview/preview.php";

		$sGetData = "SELECT * FROM offers";
		
		if ($sOfferCodeSelected != '') {
			$sGetData .= " WHERE offerCode = '$sOfferCodeSelected'";
		}

		$rResult = dbQuery($sGetData);
			
		$aReportArray = array();
		$i = 0;
		if (dbNumRows($rResult) > 0) {
			// Effective Rate - revPerLead
			// Pay Rate - actualRevPerLead
			while ($oRow = dbFetchObject($rResult)) {
				$iNet = ($oRow->revPerLead / 2);
				
				$iNet = number_format(round($iNet,2),2);
				
				$sOfferName = $oRow->name;
				$sOfferCode = $oRow->offerCode;
				
				$sHeadline = $oRow->headline;

				$sOpenOrLimited = $oRow->openOrLimited;
				$sAvailableForApi = $oRow->isAvailableForApi;
				$sRestrictions = $oRow->restrictions;

				if ($oRow->page2Info == 'Y') {
					$sPreviewLink = "$sTempPreviewLink?sOfferCode=$sOfferCode&iPage=2&sPreview=Preview";
				} else {
					$sPreviewLink = "$sTempPreviewLink?sOfferCode=$sOfferCode&iPage=1&sPreview=Preview";
				}

				$sCheckQuery = "SELECT offerCompanies.repDesignated
								FROM   offers, offerCompanies
								WHERE  offers.companyId = offerCompanies.id
								AND    offers.offerCode = '$sOfferCode'";
				$rCheckResult = dbQuery($sCheckQuery);
				$sOfferRep = '';
				while ($oCheckRow = dbFetchObject($rCheckResult)) {
					$sRepQuery = "SELECT * FROM   nbUsers
								WHERE  id IN (".$oCheckRow->repDesignated.")";
					$rRepResult = dbQuery($sRepQuery);
					while ($oRepRow = dbFetchObject($rRepResult)) {
						$sOfferRep .= $oRepRow->firstName." ". $oRepRow->lastName.",";
					}
					if ($sOfferRep != '') {
						$sOfferRep = substr($sOfferRep,0,strlen($sOfferRep)-1);
					}
				}
				
				$sGetCategoryQuery = "SELECT * FROM categoryMap WHERE offerCode='$sOfferCode'";
				$rGetCategoryResult = dbQuery($sGetCategoryQuery);
				if (dbNumRows($rGetCategoryResult) > 0) {
					$sCategory = '';
					while ($oCategoryRow = dbFetchObject($rGetCategoryResult)) {
						$sGetCat = "SELECT * FROM categories WHERE id = '$oCategoryRow->categoryId'";
						$rGetCatResult = dbQuery($sGetCat);
						if (dbNumRows($rGetCatResult) > 0) {
							while ($oCatRow = dbFetchObject($rGetCatResult)) {
								$sCategory .= $oCatRow->title.", ";
							}
						}
					}
					if ($sCategory != '') {
						$sCategory = substr($sCategory,0,strlen($sCategory)-2);
					}
				}
					
				if ($sFullDetails == 'Y') {
					$aReportArray['sOfferCode'][$i] = $sOfferCode;
					$aReportArray['sOfferRep'][$i] = $sOfferRep;
					$aReportArray['sOpenOrLimited'][$i] = $sOpenOrLimited;
					$aReportArray['sAvailableForApi'][$i] = $sAvailableForApi;
					$aReportArray['sRestrictions'][$i] = $sRestrictions;
					$aReportArray['iNet'][$i] = $iNet;
					$aReportArray['sOfferName'][$i] = $sOfferName;
					$aReportArray['sCategory'][$i] = $sCategory;
					$aReportArray['sHeadline'][$i] = $sHeadline;
					$aReportArray['sPreviewLink'][$i] = $sPreviewLink;
				} else {
					$aReportArray['sOfferCode'][$i] = $sOfferCode;
					$aReportArray['sOfferRep'][$i] = $sOfferRep;
					$aReportArray['iNet'][$i] = $iNet;
					$aReportArray['sOfferName'][$i] = $sOfferName;
					$aReportArray['sCategory'][$i] = $sCategory;
					$aReportArray['sHeadline'][$i] = $sHeadline;
					$aReportArray['sPreviewLink'][$i] = $sPreviewLink;
				}
				$i++;
			}
		}
			
		$iNumRecords = count($aReportArray['sOfferCode']);
		$iTotalPages = ceil(($iNumRecords)/$iRecPerPage);
				
		// If current page no. is greater than total pages move to the last available page no.
		if ($iPage > $iTotalPages) {
			$iPage = $iTotalPages;
		}

		$iStartRec = ($iPage-1) * $iRecPerPage;
		$iEndRec = $iStartRec + $iRecPerPage -1;
		
		if ($iNumRecords > 0) {
			$sCurrentPage = " Page $iPage "."/ $iTotalPages";
		}
				
		if ($iTotalPages > $iPage ) {
			$iNextPage = $iPage+1;
			$sNextPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iNextPage&sCurrOrder=$sCurrOrder' class=header>Next</a>";
			$sLastPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iTotalPages&sCurrOrder=$sCurrOrder' class=header>Last</a>";
		}
		
		if ($iPage != 1) {
			$iPrevPage = $iPage-1;
			$sPrevPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iPrevPage&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>Previous</a>";
			$sFirstPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=1&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>First</a>";
		}
				
				
		if ($sFullDetails == 'Y') {
			if (count($aReportArray['sOfferCode']) > 0) {
				switch ($sOrderColumn) {
					case "acctRep" :
					array_multisort($aReportArray['sOfferRep'], $sCurrOrder, $aReportArray['sOfferCode'], $aReportArray['sOpenOrLimited'], $aReportArray['sAvailableForApi'], $aReportArray['sRestrictions'], $aReportArray['iNet'], $aReportArray['sOfferName'], $aReportArray['sCategory'], $aReportArray['sHeadline'], $aReportArray['sPreviewLink']);
					break;
					case "limited" :
					array_multisort($aReportArray['sOpenOrLimited'], $sCurrOrder, $aReportArray['sOfferRep'], $aReportArray['sOfferCode'], $aReportArray['sAvailableForApi'], $aReportArray['sRestrictions'], $aReportArray['iNet'], $aReportArray['sOfferName'], $aReportArray['sCategory'], $aReportArray['sHeadline'], $aReportArray['sPreviewLink']);
					break;
					case "available" :
					array_multisort($aReportArray['sAvailableForApi'], $sCurrOrder, $aReportArray['sOfferRep'], $aReportArray['sOpenOrLimited'], $aReportArray['sOfferCode'], $aReportArray['sRestrictions'], $aReportArray['iNet'], $aReportArray['sOfferName'], $aReportArray['sCategory'], $aReportArray['sHeadline'], $aReportArray['sPreviewLink']);
					break;
					case "restriction" :
					array_multisort($aReportArray['sRestrictions'], $sCurrOrder, $aReportArray['sOfferRep'], $aReportArray['sOpenOrLimited'], $aReportArray['sAvailableForApi'], $aReportArray['sOfferCode'], $aReportArray['iNet'], $aReportArray['sOfferName'], $aReportArray['sCategory'], $aReportArray['sHeadline'], $aReportArray['sPreviewLink']);
					break;
					case "net" :
					array_multisort($aReportArray['iNet'], $sCurrOrder, $aReportArray['sOfferRep'], $aReportArray['sOpenOrLimited'], $aReportArray['sAvailableForApi'], $aReportArray['sRestrictions'], $aReportArray['sOfferCode'], $aReportArray['sOfferName'], $aReportArray['sCategory'], $aReportArray['sHeadline'], $aReportArray['sPreviewLink']);
					break;
					case "link" :
					array_multisort($aReportArray['sPreviewLink'], $sCurrOrder, $aReportArray['sOfferRep'], $aReportArray['sOpenOrLimited'], $aReportArray['sAvailableForApi'], $aReportArray['sRestrictions'], $aReportArray['iNet'], $aReportArray['sOfferName'], $aReportArray['sCategory'], $aReportArray['sHeadline'], $aReportArray['sOfferCode']);
					break;
					case "name" :
					array_multisort($aReportArray['sOfferName'], $sCurrOrder, $aReportArray['sOfferRep'], $aReportArray['sOpenOrLimited'], $aReportArray['sAvailableForApi'], $aReportArray['sRestrictions'], $aReportArray['iNet'], $aReportArray['sOfferCode'], $aReportArray['sCategory'], $aReportArray['sHeadline'], $aReportArray['sPreviewLink']);
					break;
					case "category" :
					array_multisort($aReportArray['sCategory'], $sCurrOrder, $aReportArray['sOfferRep'], $aReportArray['sOpenOrLimited'], $aReportArray['sAvailableForApi'], $aReportArray['sRestrictions'], $aReportArray['iNet'], $aReportArray['sOfferName'], $aReportArray['sOfferCode'], $aReportArray['sHeadline'], $aReportArray['sPreviewLink']);
					break;
					case "headline" :
					array_multisort($aReportArray['sHeadline'], $sCurrOrder, $aReportArray['sOfferRep'], $aReportArray['sOpenOrLimited'], $aReportArray['sAvailableForApi'], $aReportArray['sRestrictions'], $aReportArray['iNet'], $aReportArray['sOfferName'], $aReportArray['sCategory'], $aReportArray['sOfferCode'], $aReportArray['sPreviewLink']);
					break;
					default:
					array_multisort($aReportArray['sOfferCode'], $sCurrOrder, $aReportArray['sOfferRep'], $aReportArray['sOpenOrLimited'], $aReportArray['sAvailableForApi'], $aReportArray['sRestrictions'], $aReportArray['iNet'], $aReportArray['sOfferName'], $aReportArray['sCategory'], $aReportArray['sHeadline'], $aReportArray['sPreviewLink']);
				}
			}
		} else {
				if (count($aReportArray['sOfferCode']) > 0) {
				switch ($sOrderColumn) {
					case "acctRep" :
					array_multisort($aReportArray['sOfferRep'], $sCurrOrder, $aReportArray['sOfferCode'], $aReportArray['iNet'], $aReportArray['sOfferName'], $aReportArray['sCategory'], $aReportArray['sHeadline'], $aReportArray['sPreviewLink']);
					break;
					case "net" :
					array_multisort($aReportArray['iNet'], $sCurrOrder, $aReportArray['sOfferRep'], $aReportArray['sOfferCode'], $aReportArray['sOfferName'], $aReportArray['sCategory'], $aReportArray['sHeadline'], $aReportArray['sPreviewLink']);
					break;
					case "link" :
					array_multisort($aReportArray['sPreviewLink'], $sCurrOrder, $aReportArray['sOfferRep'], $aReportArray['iNet'], $aReportArray['sOfferName'], $aReportArray['sCategory'], $aReportArray['sHeadline'], $aReportArray['sOfferCode']);
					break;
					case "name" :
					array_multisort($aReportArray['sOfferName'], $sCurrOrder, $aReportArray['sOfferRep'], $aReportArray['iNet'], $aReportArray['sOfferCode'], $aReportArray['sCategory'], $aReportArray['sHeadline'], $aReportArray['sPreviewLink']);
					break;
					case "category" :
					array_multisort($aReportArray['sCategory'], $sCurrOrder, $aReportArray['sOfferRep'], $aReportArray['iNet'], $aReportArray['sOfferName'], $aReportArray['sOfferCode'], $aReportArray['sHeadline'], $aReportArray['sPreviewLink']);
					break;
					case "headline" :
					array_multisort($aReportArray['sHeadline'], $sCurrOrder, $aReportArray['sOfferRep'], $aReportArray['iNet'], $aReportArray['sOfferName'], $aReportArray['sCategory'], $aReportArray['sOfferCode'], $aReportArray['sPreviewLink']);
					break;
					default:
					array_multisort($aReportArray['sOfferCode'], $sCurrOrder, $aReportArray['sOfferRep'], $aReportArray['iNet'], $aReportArray['sOfferName'], $aReportArray['sCategory'], $aReportArray['sHeadline'], $aReportArray['sPreviewLink']);
				}
			}
		}

		$sReportData = '';
		$sExportData = '';
		for ($ii=0;$ii<count($aReportArray['sOfferCode']);$ii++) {
			$sPageLoop++;
			if (($sPageLoop > $iStartRec) && ($sPageLoop <= ($iStartRec + $iRecPerPage))) {
				if ($sBgcolorClass == "ODD") {
					$sBgcolorClass = "EVEN_WHITE";
				} else {
					$sBgcolorClass = "ODD";
				}

				$sFullHeadline = $aReportArray['sHeadline'][$ii];
				$sHeadline = substr($aReportArray['sHeadline'][$ii],0,20);
				$sHeadline = "<p><acronym title=\"$sFullHeadline\">$sHeadline</acronym></p>";
					
					
				if ($sFullDetails == 'Y') {
					$sFullRestrictions = $aReportArray['sRestrictions'][$ii];
					$sRestrictions = substr($aReportArray['sRestrictions'][$ii],0,20);
					$sRestrictions = "<p><acronym title=\"$sFullRestrictions\">$sRestrictions</acronym></p>";
					
					$sReportData .= "<tr class=$sBgcolorClass><td>".$aReportArray['sOfferCode'][$ii]."</td>
									<td>".$aReportArray['sOfferRep'][$ii]."</td><td>".$aReportArray['sOpenOrLimited'][$ii]."</td>
									<td>".$aReportArray['sAvailableForApi'][$ii]."</td><td>".$sRestrictions."</td>
									<td>".$aReportArray['iNet'][$ii]."</td><td>".$aReportArray['sOfferName'][$ii]."</td>
									<td>".$aReportArray['sCategory'][$ii]."</td><td>".$sHeadline."</td>
									<td><a href='".$aReportArray['sPreviewLink'][$ii]."' target=_blank>Click Here</a></td></tr>";
	
					$sExportData .= $aReportArray['sOfferCode'][$ii]."\t".$aReportArray['sOfferRep'][$ii]."\t".
									$aReportArray['sOpenOrLimited'][$ii]."\t".$aReportArray['sAvailableForApi'][$ii]."\t".
									$sFullRestrictions."\t".$aReportArray['iNet'][$ii]."\t".
									$aReportArray['sOfferName'][$ii]."\t".$aReportArray['sCategory'][$ii]."\t".
									$aReportArray['sHeadline'][$ii]."\t".$aReportArray['sPreviewLink'][$ii]."\n";
				} else {
					$sReportData .= "<tr class=$sBgcolorClass><td>".$aReportArray['sOfferCode'][$ii]."</td>
							<td>".$aReportArray['sOfferRep'][$ii]."</td><td>".$aReportArray['iNet'][$ii]."</td>
							<td>".$aReportArray['sOfferName'][$ii]."</td><td>".$aReportArray['sCategory'][$ii]."</td>
							<td>".$sHeadline."</td>
							<td><a href='".$aReportArray['sPreviewLink'][$ii]."' target=_blank>Click Here</a></td></tr>";

					$sExportData .= $aReportArray['sOfferCode'][$ii]."\t".$aReportArray['sOfferRep'][$ii]."\t".
							$aReportArray['iNet'][$ii]."\t".$aReportArray['sOfferName'][$ii]."\t".
							$aReportArray['sCategory'][$ii]."\t".$aReportArray['sHeadline'][$ii]."\t".$aReportArray['sPreviewLink'][$ii]."\n";
				}
			}
		}

		// start of track users' activity in nibbles - mcs
		mysql_connect ($host, $user, $pass);
		mysql_select_db ($dbase);
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				VALUES('$sTrackingUser', '$PHP_SELF', now(), \"$sGetData\")";
		$rResult = dbQuery($sAddQuery);
		mysql_connect ($reportingHost, $reportingUser, $reportingPass);
		mysql_select_db ($reportingDbase);
		// end of track users' activity in nibbles		
	}
	
	if ($sExportExcel) {
		$sExportData = $sExportHeader.$sExportData;
		$sFileName = "apiReport_".$iCurrYear.$iCurrMonth.$iCurrDay.".xls";
		$rFpFile = fopen("$sGblWebRoot/temp/$sFileName", "w");
		if ($rFpFile) {
			fputs($rFpFile, $sExportData, strlen($sExportData));
			fclose($rFpFile);
			echo "<script language=JavaScript>
				void(window.open(\"$sGblSiteRoot/download.php?sFile=$sFileName\",\"\",\"height=150, width=300, scrollbars=yes, resizable=yes, status=yes\"));				
			  </script>";
		} else {
			$sMessage = "Error exporting data...";
		}
	}


	$sExportExcelChecked = '';
	$sFullDetailsChecked = '';
	if ($sExportExcel) { $sExportExcelChecked = "checked"; }
	if ($sFullDetails) { $sFullDetailsChecked = "checked"; }

	$sOfferCodeQuery = "SELECT offerCode FROM offers ORDER BY offerCode ASC";
	$rOfferCodeQuery = dbQuery($sOfferCodeQuery);
	while ($oReportRow = dbFetchObject($rOfferCodeQuery)) {
		if ($sOfferCodeSelected) {
			if ($oReportRow->offerCode == $sOfferCodeSelected) {
				$sSelected = "selected";
			} else {
				$sSelected = "";
			}
		} else {
			if ($oReportRow->offerCode == $sOfferCodeSelected && isset($sOfferCodeSelected)) {
				$sSelected = "selected";
			} else {
				$sSelected = "";
			}
		}
		$sOfferCodeOptions .= "<option value='$oReportRow->offerCode' $sSelected>$oReportRow->offerCode";
	}
	
	// Hidden variable to be passed with form submit
	$sHidden =  "<input type=hidden name=iMenuId value='$iMenuId'>";
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);

	include("../../includes/adminHeader.php");
	// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;
?>

<script language=JavaScript>
function funcRecPerPage(form1) {
	document.form1.submit();
}

</script>				
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>

<table width=95% align=center bgcolor=c9c9c9><tr>

	<tr><td>Offer Code</td><td><select name=sOfferCodeSelected>
	<option value='' selected>All</option><?php echo $sOfferCodeOptions;?></select>
	</td></tr>
	

<tr>
<td colspan=<?php echo $sColSpan; ?>><br><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">
&nbsp; &nbsp; <input type=checkbox name=sExportExcel value="Y" <?php echo $sExportExcelChecked;?>> Export To Excel
&nbsp; &nbsp; <input type=checkbox name=sFullDetails value="Y" <?php echo $sFullDetailsChecked;?>> Full Details
</td></tr>

<tr><td colspan=4 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>


			</table>
	
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=<?php echo $sColSpan; ?> class=bigHeader align=center><BR><?php echo $sPageTitle;?></td></tr>
	<tr><td colspan=<?php echo $sColSpan; ?> class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	
	<tr>
	
	<?php
	if ($sFullDetails) {
		?>
		
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=offerCode&sOfferCodeOrder=<?php echo $sOfferCodeOrder;?>" class=header>Offer Code</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=acctRep&sAcctRepOrder=<?php echo $sAcctRepOrder;?>" class=header>AE</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=limited&sLimitedOrder=<?php echo $sLimitedOrder;?>" class=header>Limited</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=available&sAvailableOrder=<?php echo $sAvailableOrder;?>" class=header>Available</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=restriction&sRestrictionsOrder=<?php echo $sRestrictionsOrder;?>" class=header>Restriction</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=net&sNetOrder=<?php echo $sNetOrder;?>" class=header>Net</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=name&sOfferNameOrder=<?php echo $sOfferNameOrder;?>" class=header>Offer Name</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=category&sCategoryOrder=<?php echo $sCategoryOrder;?>" class=header>Category</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=headline&sHeadLineOrder=<?php echo $sHeadLineOrder;?>" class=header>Headline</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=link&sLinkOrder=<?php echo $sLinkOrder;?>" class=header>Link</a></td>

	<?php } else {
	?>
	
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=offerCode&sOfferCodeOrder=<?php echo $sOfferCodeOrder;?>" class=header>Offer Code</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=acctRep&sAcctRepOrder=<?php echo $sAcctRepOrder;?>" class=header>AE</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=net&sNetOrder=<?php echo $sNetOrder;?>" class=header>Net</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=name&sOfferNameOrder=<?php echo $sOfferNameOrder;?>" class=header>Offer Name</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=category&sCategoryOrder=<?php echo $sCategoryOrder;?>" class=header>Category</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=headline&sHeadLineOrder=<?php echo $sHeadLineOrder;?>" class=header>Headline</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=link&sLinkOrder=<?php echo $sLinkOrder;?>" class=header>Link</a></td>
	
	<?php
	}
	?>
		
	</tr>

<?php echo $sReportData;?>

<tr><td colspan=<?php echo $sColSpan; ?> class=header><BR>Notes -</td></tr>
	<tr><td colspan=<?php echo $sColSpan; ?>>- Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=<?php echo $sColSpan; ?>>- Limited:  O = Open &nbsp;&nbsp;&nbsp;&nbsp; L = Limited</td></tr>
	<tr><td colspan=<?php echo $sColSpan; ?>>- Available:  N = Not Available For API &nbsp;&nbsp;&nbsp;&nbsp; Y = Available For API</td></tr>
	<tr><td colspan=<?php echo $sColSpan; ?>>- Net:  50% of Effective Rate.</td></tr>
	<tr><td colspan=<?php echo $sColSpan; ?>><BR><BR></td></tr>
	
</table></td></tr></table></td></tr></table></td></tr></table>
</form>			

<?php
	
include("../../includes/adminFooter.php");

} else {
	echo "You are not authorized to access this page...";
}

?>