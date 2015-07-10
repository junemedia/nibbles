<?php
/*********
Script to Display Revenue by Page.
**********/
session_start();

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();


$sPageTitle = "Revenue Per Page";

if (hasAccessRight($iMenuId) || isAdmin()) {

	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iId value='$iId'>";	

	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');

	$iCurrHH = date('H');
	$iCurrMM = date('i');
	$iCurrSS = date('s');

	$iMaxDaysToReport = 7;
	$iDefaultDaysToReport = 1;
	$bDateRangeNotOk = false;

	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));

	if (!$sViewReport) {

		$iMonthTo = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 5, 2);
		$iDayTo = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 8, 2);
		$iYearTo = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 0, 4);

		$iYearFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 0, 4);
		$iMonthFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 5, 2);
		$iDayFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 8, 2);
	}


	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {

		$iValue = $i+1;

		if ($iValue < 10) {
			$iValue = "0".$iValue;
		}

		if ($iValue == $iMonthFrom) {
			$sFromSel = "selected";
		} else {
			$sFromSel = "";
		}
		if ($iValue == $iMonthTo) {
			$sToSel = "selected";
		} else {
			$sToSel = "";
		}

		$sMonthFromOptions .= "<option value='$iValue' $sFromSel>$aGblMonthsArray[$i]";
		$sMonthToOptions .= "<option value='$iValue' $sToSel>$aGblMonthsArray[$i]";
	}

	// prepare day options for From and To date
	for ($i = 1; $i <= 31; $i++) {

		if ($i < 10) {
			$iValue = "0".$i;
		} else {
			$iValue = $i;
		}

		if ($iValue == $iDayFrom) {
			$sFromSel = "selected";
		} else {
			$sFromSel = "";
		}
		if ($iValue == $iDayTo) {
			$sToSel = "selected";
		} else {
			$sToSel = "";
		}
		$sDayFromOptions .= "<option value='$iValue' $sFromSel>$i";
		$sDayToOptions .= "<option value='$iValue' $sToSel>$i";
	}

	// prepare year options
	for ($i = $iCurrYear; $i >= $iCurrYear-5; $i--) {

		if ($i == $iYearFrom) {
			$sFromSel = "selected";
		} else {
			$sFromSel ="";
		}
		if ($i == $iYearTo) {
			$sToSel = "selected";
		} else {
			$sToSel ="";
		}

		$sYearFromOptions .= "<option value='$i' $sFromSel>$i";
		$sYearToOptions .= "<option value='$i' $sToSel>$i";
	}

	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "pageName";
		$sSessionIdOrder = "ASC";
	}

	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "pageName" :
			$sCurrOrder = $sPageNameOrder;
			$sPageNameOrder = ($sPageNameOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "effective" :
			$sCurrOrder = $sEffectiveOrder;
			$sEffectiveOrder = ($sEffectiveOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "gross" :
			$sCurrOrder = $sGrossOrder;
			$sGrossOrder = ($sGrossOrder != "DESC" ? "DESC" : "ASC");
		}
	}

	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";

	if ( DateAdd("d", $iMaxDaysToReport, $sDateFrom) < $sDateTo ) {
		$bDateRangeNotOk = true;
	}

	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 30;
	}
	if (!($iPage)) {
		$iPage = 1;
	}

	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo&sViewReport=$sViewReport&iPage=$iPage&iRecPerPage=$iRecPerPage&sPageName=$sPageName&sSourceCode=$sSourceCode";

	if ($sViewReport != "") {
		if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo) && !$bDateRangeNotOk) {
			if ($sAllowReport == 'N') {
				$sMessage .= "<br>Server Load Is High. Please check back soon...";
			} else {
				
				
				$sTempDelete = "TRUNCATE TABLE tempRevenueByPage";
				$rTempDelete = dbQuery($sTempDelete);
				if(dbError()) echo __LINE__.":".dbError();
				
				$sWhere = "";//this should start to filter by date, etc.
				
				if ($sSourceCode != '') {
					$sSourceCodeFilter = " AND sourceCode = '$sSourceCode'";
				} else {
					$sSourceCodeFilter = '';
				}
				
				$sPagesSql = "SELECT pageName, id FROM otPages".$sWhere;
				$sPagesSqlQuery = $sPagesSql;
				$rPagesRes = dbQuery($sPagesSql);
				while($oPages = dbFetchObject($rPagesRes)){
					//then, for each of those:
					$sOfferCodes = "SELECT distinct offerCode FROM otDataHistory WHERE pageId = ".$oPages->id." AND dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'";
					$sOfferCodesQuery = $sOfferCodes;
					$rOfferCodesRes = dbQuery($sOfferCodes);
					$g = 0;
					$e = 0;
					while($oOfferCodes = dbFetchObject($rOfferCodesRes)){
						//and for each of those:
						
						$sOfferCounts = "SELECT (count(*) * offers.revPerLead) as gross, (count(*) * offers.actualRevPerLead) as effective FROM otDataHistory, offers WHERE otDataHistory.offerCode = offers.offerCode 
						AND otDataHistory.offerCode = '".$oOfferCodes->offerCode."' 
						AND otDataHistory.pageId = ".$oPages->id." 
						AND otDataHistory.processStatus = 'P'
						AND otDataHistory.sendStatus = 'S'
						AND dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'".$sSourceCodeFilter;
						$sOfferCountsQuery = $sOfferCounts;
						
						$rOfferRevResp = dbQuery($sOfferCounts);
						while($oOfferRev = dbFetchObject($rOfferRevResp)){
							$g += $oOfferRev->gross;
							$e += $oOfferRev->effective;
						}
						
					}
					
					$sql = "INSERT INTO tempRevenueByPage (pageName, pageId, gross, effective) VALUES ('$oPages->pageName', $oPages->id, $g, $e)";
					$rTempResult = dbQuery($sql);
					if(dbError()) echo __LINE__.":".dbError()."\n".$sql;
				}
				
				//echo "Starting population. Here are the queries: $sPagesSqlQuery<p>$sOfferCodesQuery<p>$sOfferCountsQuery<p>";
				
				
				
				/*							

				$sTempQuery = "INSERT INTO tempRevenueByPage (pageId,offerCode,rev,actualRev)
						SELECT pageId, otDataHistory.offerCode, (count(otDataHistory.offerCode) * offers.revPerLead), (count(otDataHistory.offerCode) * offers.actualRevPerLead)
						FROM otDataHistory, offers
   					 	WHERE otDataHistory.dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
   					 	$sSourceCodeFilter
   					 	GROUP BY pageId, otDataHistory.offerCode";
   				*/

				
				// start of track users' activity in nibbles 
				/*
				$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

				$nibblesReportWhere = "WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
   					 	$sSourceCodeFilter
   					 	$sSubSourceCodeFilter
   					 	$sGroupByFilter";
				
				$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: Where: $nibblesReportWhere\")"; 
				$rResult = dbQuery($sAddQuery); 
				// end of track users' activity in nibbles		
				
				*/
				//$rTempResult = dbQuery($sql);
				//if(dbError()) echo __LINE__.":".dbError()."\n".$sql;
			
				$sReportQuery = "SELECT * FROM tempRevenueByPage ORDER BY $sOrderColumn $sCurrOrder";
				$rReportResult = dbQuery($sReportQuery);
				echo dbError();
				$iNumRecords = dbNumRows($rReportResult);

				$iTotalPages = ceil($iNumRecords/$iRecPerPage);

				// If current page no. is greater than total pages move to the last available page no.
				if ($iPage > $iTotalPages) {
					$iPage = $iTotalPages;
				}

				$iStartRec = ($iPage-1) * $iRecPerPage;
				$iEndRec = $iStartRec + $iRecPerPage -1;

				if ($iNumRecords > 0) {
					$sCurrentPage = " Page $iPage "."/ $iTotalPages";
				}

				// use query to fetch only the rows of the page to be displayed
				$sReportQuery .= " LIMIT $iStartRec, $iRecPerPage";
				$rReportResult = dbQuery($sReportQuery);

				//echo __LINE__.":".$sReportQuery;
				if(dbError()) echo __LINE__.":".dbError().$sReportQuery;

				
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
					
					$iTotalGross = 0;
					$iTotalEffective = 0;
					while ($oReportRow = dbFetchObject($rReportResult)) {
						//echo ".";
							if ($sBgcolorClass == "ODD") {
								$sBgcolorClass = "EVEN_WHITE";
							} else {
								$sBgcolorClass = "ODD";
							}
							
							$sReportContent .= "<tr class=$sBgcolorClass>
										<td>$oReportRow->pageName</a></td>
										<td>\$$oReportRow->gross</td>
										<td>\$$oReportRow->effective</td></tr>";
											

							$iTotalGross += $oReportRow->gross;
							$iTotalEffective += $oReportRow->effective;
					}
					
					$sReportContent .= "<tr><td colspan=3><hr color=#000000></td></tr>
						<tr><td><b>Total: </b></td>
						<td><b>\$$iTotalGross</b></td>
						<td><b>\$$iTotalEffective</b></td>
						</tr></tr>";						
			
			}
		} else {
			$sMessage .= "Date range entered is greater than maximum range ($iMaxDaysToReport days).";
		}
	}

	// Get all sourceCode
	$sSourceCodeQuery = "SELECT distinct sourceCode
				FROM otDataHistory
				WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
				AND sourceCode != ''
      			ORDER BY sourceCode ASC";

	$rSourceCodeResult = dbQuery($sSourceCodeQuery);
	if(dbError()) echo __LINE__.":".dbError();

	while ($oSourceCodeRow = dbFetchObject($rSourceCodeResult)) {
		$sTempSourceCode = $oSourceCodeRow->sourceCode;
		if ($sSourceCode) {
			if ($sTempSourceCode == $sSourceCode) {
				$sSourceCodeSelected = "selected";
			} else {
				$sSourceCodeSelected = "";
			}
		} else {
			if ($sTempSourceCode == $sSourceCode && isset($sSourceCode)) {
				$sSourceCodeSelected = "selected";
			} else {
				$sSourceCodeSelected = "";
			}
		}
		$sSourceCodeOption .= "<option value='".$oSourceCodeRow->sourceCode."' $sSourceCodeSelected>$oSourceCodeRow->sourceCode";
	}
	
	include("../../includes/adminHeader.php");

	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);

	// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;

?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td>Date From</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td>Date To</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>	
	
<tr><td>Source Code:</td><td>
<select name='sSourceCode'>
<option value="">All</option>
<?php echo $sSourceCodeOption ?>
</select>
</td></tr>

	<td colspan=2></td>
<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">
</td>
	<td colspan=2></td>
</tr>
<tr><td colspan=4 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td colspan=5 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=5 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><?php echo $sDateSentHeader;?>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=pageName&sPageNameOrder=<?php echo $sPageNameOrder;?>" class=header>Page Name</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=gross&sGrossOrder=<?php echo $sGrossOrder;?>" class=header>Gross Revenue</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=effective&sEffectiveOrder=<?php echo $sEffectiveOrder;?>" class=header>Effective Revenue</a></td>
	</tr>

<?php echo $sReportContent;?>

<tr><td colspan=4 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=4 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=4>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s).<br>
		Revenue figures are based on delivered leads within the given time range.<br>
		Gross: Gross Revenue generated by leads from this page.<br>
		Effective: Actual Revenue generated by leads from this page.<br>
		</td></tr>
	<tr><td colspan=4><BR><BR></td></tr>
		</table></td></tr></table></td></tr>
	</table>
</td></tr>
</table>
</form>
<?php
include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
