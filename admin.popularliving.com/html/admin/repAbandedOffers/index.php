<?php

session_start();

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");


mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

$iScriptStartTime = getMicroTime();

$sPageTitle = "Abandoned Offers Report";

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

	$iMaxDaysToReport = 90;
	$iDefaultDaysToReport = 1;
	$bDateRangeNotOk = false;

	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 30;
	}
	if (!($iPage)) {
		$iPage = 1;
	}
	
	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));

	if (!$sViewReport) {
		$iMonthTo = substr( DateAdd( "d", -1, date('Y')."-".date('m')."-".date('d') ), 5, 2);
		$iDayTo = substr( DateAdd( "d", -1, date('Y')."-".date('m')."-".date('d') ), 8, 2);
		$iYearTo = substr( DateAdd( "d", -1, date('Y')."-".date('m')."-".date('d') ), 0, 4);
		$iYearFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 0, 4);
		$iMonthFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 5, 2);
		$iDayFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 8, 2);
		$sExcludeApiLeads = 'Y';
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
		$sOrderColumn = "offerCode";
		$sDateSentOrder = "SORT_ASC";
	}

	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "abandedCount" :
			$sCurrOrder = $sAbandedCountOrder;
			$sAbandedCountOrder = ($sAbandedCountOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "grossCount" :
			$sCurrOrder = $sGrossCountOrder;
			$sGrossCountOrder = ($sGrossCountOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "abandedPercent" :
			$sCurrOrder = $sAbandedPercentOrder;
			$sAbandedPercentOrder = ($sAbandedPercentOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "xOutCount" :
			$sCurrOrder = $sXOutCountOrder;
			$sXOutCountOrder = ($sXOutCountOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "xOutPercent" :
			$sCurrOrder = $sXOutPercentOrder;
			$sXOutPercentOrder = ($sXOutPercentOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "netCount" :
			$sCurrOrder = $sNetCountOrder;
			$sNetCountOrder = ($sNetCountOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "offerName" :
			$sCurrOrder = $sOfferNameOrder;
			$sOfferNameOrder = ($sOfferNameOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "goodPercent" :
			$sCurrOrder = $sGoodOrder;
			$sGoodOrder = ($sGoodOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			break;
			case "pageName" :
			$sCurrOrder = $sPageNameOrder;
			$spageNameOrder = ($sPageNameOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
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
		
		
	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";

	if ( DateAdd("d", $iMaxDaysToReport, $sDateFrom) < $sDateTo ) {
		$bDateRangeNotOk = true;
	}


	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sListName=$sListName&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo
			&iDbMailId=$iDbMailId&iDisplayDateWise=$iDisplayDateWise&sViewReport=$sViewReport&iRecPerPage=$iRecPerPage&sOfferCodeList=$sOfferCodeList&sSourceCode=$sSourceCode&sPageName=$sPageName
			&sOfferCodeGetEmail=$sOfferCodeGetEmail&sExcludeApiLeads=$sExcludeApiLeads&sExportExcel=$sExportExcel";

	
	if ($sViewReport != "") {
		if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo) && !$bDateRangeNotOk) {
			if ($sAllowReport == 'N') {
				$sMessage .= "<br>Server Load Is High. Please check back soon...";
			} else {
				$sTemp = '';
				$sReportAbandedPercent = 0;
				$sReportXOutPercent = 0;
				$sReportGoodPercent = 0;
				
				$sReportGrossTotal = 0;
				$sReportAbandedTotal = 0;
				$sReportXOutTotal = 0;
				$sReportNetTotal = 0;
				
				$sPageAbandedPercent = 0;
				$sPageXOutPercent = 0;

				$aReportArray = array();
				
				mysql_connect ($host, $user, $pass);
				mysql_select_db ($dbase);
				$sDeleteQuery = "TRUNCATE TABLE nibbles_temp.tempAbandedReport";
				$rDeleteResult = mysql_query($sDeleteQuery);
				mysql_connect ($reportingHost, $reportingUser, $reportingPass);
				mysql_select_db ($reportingDbase);

				$sAbandedQuery = "SELECT offerCode, count(offerCode) as abandedCount 
					FROM abandedOffersHistory
					WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'";
				if ($sOfferCodeList !='') {
					$sAbandedQuery .= " AND offerCode = '$sOfferCodeList' ";
				}
				
				if ($sSourceCode !='') {
					$sAbandedQuery .= " AND sourceCode = '$sSourceCode' ";
				}
				
				if($sPageName != ''){
					$sAbandedQuery .= " AND pageId = $sPageName";
				}
				
				$sAbandedQuery .= " GROUP BY offerCode";
				$sTemp .= "\n$sAbandedQuery";
				$rAbandedQuery = dbQuery($sAbandedQuery);
				echo dbError();
				while ($oAbandedRow = dbFetchObject($rAbandedQuery)) {
					$sOfferName = '';
					$sGetOfferNameQuery = "SELECT name FROM offers WHERE offerCode='$oAbandedRow->offerCode'";
					$rGetOfferNameResult = dbQuery($sGetOfferNameQuery);
					if (dbNumRows($rGetOfferNameResult)>0) {
						$sOfferNameRow = dbFetchObject($rGetOfferNameResult);
						$sOfferName = $sOfferNameRow->name;
					}
					mysql_connect ($host, $user, $pass);
					mysql_select_db ($dbase);
					
					$sInsertQuery = "INSERT INTO nibbles_temp.tempAbandedReport (offerCode,taken,abanded,xOutCount,offerName)
					VALUES (\"$oAbandedRow->offerCode\", \"0\", \"$oAbandedRow->abandedCount\",\"0\",\"$sOfferName\")";
					$rInsertResult = dbQuery($sInsertQuery);
					echo dbError();
					mysql_connect ($reportingHost, $reportingUser, $reportingPass);
					mysql_select_db ($reportingDbase);
				}
				
				
				
				
				$sTakenQuery = "SELECT offerCode, count(offerCode) as takenCount 
					FROM otDataHistory
					WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'";
				if ($sOfferCodeList !='') {
					$sTakenQuery .= " AND offerCode = '$sOfferCodeList' ";
				}
				
				if ($sSourceCode !='') {
					$sTakenQuery .= " AND sourceCode = '$sSourceCode' ";
				}
				
				if ($sExcludeApiLeads) {
					$sTakenQuery .= " AND pageId != '238' ";
				}
				if($sPageName != ''){
					$sTakenQuery .= " AND pageId = $sPageName";
				}
				$sTakenQuery .= " GROUP BY offerCode";
				$sTemp .= "\n$sTakenQuery";
				$rTakenQuery = dbQuery($sTakenQuery);
				echo dbError();
				while ($oTakenRow = dbFetchObject($rTakenQuery)) {
					$sOfferName = '';
					$sGetOfferNameQuery = "SELECT name FROM offers WHERE offerCode='$oTakenRow->offerCode'";
					$rGetOfferNameResult = dbQuery($sGetOfferNameQuery);
					if (dbNumRows($rGetOfferNameResult)>0) {
						$sOfferNameRow = dbFetchObject($rGetOfferNameResult);
						$sOfferName = $sOfferNameRow->name;
					}
					
					mysql_connect ($host, $user, $pass);
					mysql_select_db ($dbase);
					$sInsertQuery = "INSERT INTO nibbles_temp.tempAbandedReport (offerCode,taken,abanded,xOutCount,offerName)
					VALUES (\"$oTakenRow->offerCode\", \"$oTakenRow->takenCount\", \"0\", \"0\",\"$sOfferName\")";
					$rInsertResult = dbQuery($sInsertQuery);
					echo dbError();
					mysql_connect ($reportingHost, $reportingUser, $reportingPass);
					mysql_select_db ($reportingDbase);
				}


				$sXOutQuery = "SELECT offerCode, count(offerCode) as xOutCount 
					FROM xOutDataHistory
					WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'";
				if ($sOfferCodeList !='') {
					$sXOutQuery .= " AND offerCode = '$sOfferCodeList' ";
				}
				
				if ($sSourceCode !='') {
					$sXOutQuery .= " AND sourceCode = '$sSourceCode' ";
				}
				
				if($sPageName != ''){
					$sXOutQuery .= " AND pageId = $sPageName";
				}
				
				$sXOutQuery .= " GROUP BY offerCode";
				$sTemp .= "\n$sXOutQuery";
				$rXOutResult = dbQuery($sXOutQuery);
				echo dbError();
				while ($xOutRow = dbFetchObject($rXOutResult)) {
					$sOfferName = '';
					$sGetOfferNameQuery = "SELECT name FROM offers WHERE offerCode='$xOutRow->offerCode'";
					$rGetOfferNameResult = dbQuery($sGetOfferNameQuery);
					if (dbNumRows($rGetOfferNameResult)>0) {
						$sOfferNameRow = dbFetchObject($rGetOfferNameResult);
						$sOfferName = $sOfferNameRow->name;
					}

					mysql_connect ($host, $user, $pass);
					mysql_select_db ($dbase);
					$sInsertQuery = "INSERT INTO nibbles_temp.tempAbandedReport (offerCode,taken,abanded,xOutCount,offerName)
						VALUES (\"$xOutRow->offerCode\", \"0\", \"0\", \"$xOutRow->xOutCount\",\"$sOfferName\")";
					$rInsertResult = dbQuery($sInsertQuery);
					echo dbError();
					mysql_connect ($reportingHost, $reportingUser, $reportingPass);
					mysql_select_db ($reportingDbase);
				}
				
				$sSelectQuery = "SELECT distinct offerCode FROM nibbles_temp.tempAbandedReport";
				$rSelectResult = dbQuery($sSelectQuery);
				while ($sTempRow = dbFetchObject($rSelectResult)) {
					if (strlen($sTempRow->offerCode)==1) {
						$sDeleteQuery = "DELETE FROM nibbles_temp.tempAbandedReport WHERE offerCode='$sTempRow->offerCode'";
						$rDeleteResult = dbQuery($sDeleteQuery);
					}
				}
				

				$sGetDataQuery = "SELECT offerCode, offerName, sum(taken) as takenCount, sum(abanded) as abandedCount, 
						sum(xOutCount) as xOutCount FROM nibbles_temp.tempAbandedReport GROUP BY offerCode";
				$sTemp .= "\n$sGetDataQuery";
				$rGetDataResult = dbQuery($sGetDataQuery);
				echo dbError();
				$i = 0;
				while ($sData = dbFetchObject($rGetDataResult)) {
					$iGrossCount = $sData->xOutCount;
					$iTempXoutCount = $sData->xOutCount - $sData->takenCount - $sData->abandedCount;
					$aReportArray['offerCode'][$i] = $sData->offerCode;
					$aReportArray['offerName'][$i] = $sData->offerName;
					$aReportArray['grossCount'][$i] = $iGrossCount;
					$aReportArray['abandedCount'][$i] = $sData->abandedCount;
					
					if ($iGrossCount < 1) { $iGrossCount=0.001; }
					if ($iTempXoutCount < 1) { $iTempXoutCount=0; }
					
					$aReportArray['abandedPercent'][$i] = number_format((($sData->abandedCount/$iGrossCount)*100),1);
					$aReportArray['xOutCount'][$i] = $iTempXoutCount;
					$aReportArray['xOutPercent'][$i] = number_format((($iTempXoutCount/$iGrossCount)*100),1);
					$aReportArray['netCount'][$i] = $sData->takenCount;
					
					$iGoodPercentTemp = 100 - $aReportArray['abandedPercent'][$i] - $aReportArray['xOutPercent'][$i];
					if ($iGoodPercentTemp < 0) { $iGoodPercentTemp = 0; }
					$aReportArray['goodPercent'][$i] = $iGoodPercentTemp;
					$i++;
				}

				$iNumRecords = count($aReportArray['offerCode']);
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
				if ($iNumRecords > 0) {
					switch ($sOrderColumn) {
						case "abandedPercent" :
						array_multisort($aReportArray['abandedPercent'], $sCurrOrder, $aReportArray['offerCode'], $aReportArray['grossCount'], $aReportArray['abandedCount'], $aReportArray['xOutCount'], $aReportArray['xOutPercent'], $aReportArray['netCount'], $aReportArray['offerName'], $aReportArray['goodPercent']);
						break;
						case "grossCount" :
						array_multisort($aReportArray['grossCount'], $sCurrOrder, $aReportArray['offerCode'], $aReportArray['abandedPercent'], $aReportArray['abandedCount'], $aReportArray['xOutCount'], $aReportArray['xOutPercent'], $aReportArray['netCount'], $aReportArray['offerName'], $aReportArray['goodPercent']);
						break;
						case "abandedCount" :
						array_multisort($aReportArray['abandedCount'], $sCurrOrder, $aReportArray['offerCode'], $aReportArray['abandedPercent'], $aReportArray['grossCount'], $aReportArray['xOutCount'], $aReportArray['xOutPercent'], $aReportArray['netCount'], $aReportArray['offerName'], $aReportArray['goodPercent']);
						break;
						case "xOutPercent" :
						array_multisort($aReportArray['xOutPercent'], $sCurrOrder, $aReportArray['offerCode'], $aReportArray['abandedPercent'], $aReportArray['grossCount'], $aReportArray['xOutCount'], $aReportArray['abandedCount'], $aReportArray['netCount'], $aReportArray['offerName'], $aReportArray['goodPercent']);
						break;
						case "xOutCount" :
						array_multisort($aReportArray['xOutCount'], $sCurrOrder, $aReportArray['offerCode'], $aReportArray['abandedPercent'], $aReportArray['grossCount'], $aReportArray['abandedCount'], $aReportArray['xOutPercent'], $aReportArray['netCount'], $aReportArray['offerName'], $aReportArray['goodPercent']);
						break;
						case "netCount" :
						array_multisort($aReportArray['netCount'], $sCurrOrder, $aReportArray['offerCode'], $aReportArray['abandedPercent'], $aReportArray['grossCount'], $aReportArray['xOutCount'], $aReportArray['xOutPercent'], $aReportArray['abandedCount'], $aReportArray['offerName'], $aReportArray['goodPercent']);
						break;
						case "offerName" :
						array_multisort($aReportArray['offerName'], $sCurrOrder, $aReportArray['offerCode'], $aReportArray['abandedPercent'], $aReportArray['grossCount'], $aReportArray['xOutCount'], $aReportArray['xOutPercent'], $aReportArray['abandedCount'], $aReportArray['netCount'], $aReportArray['goodPercent']);
						break;
						case "goodPercent" :
						array_multisort($aReportArray['goodPercent'], $sCurrOrder, $aReportArray['offerCode'], $aReportArray['abandedPercent'], $aReportArray['grossCount'], $aReportArray['xOutCount'], $aReportArray['xOutPercent'], $aReportArray['abandedCount'], $aReportArray['netCount'], $aReportArray['offerName']);
						break;
						default:
						array_multisort($aReportArray['offerCode'], $sCurrOrder, $aReportArray['abandedCount'], $aReportArray['abandedPercent'], $aReportArray['grossCount'], $aReportArray['xOutCount'], $aReportArray['xOutPercent'], $aReportArray['netCount'], $aReportArray['offerName'], $aReportArray['goodPercent']);
					}
				}
				if ($sOfferCodeGetEmail) {
					$sGetEmailQuery = "SELECT email
						FROM otDataHistory
			   		 	WHERE offerCode='$sOfferCodeGetEmail'
			   		 	AND dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'";
					
					if ($sExcludeApiLeads) {
						$sGetEmailQuery .= " AND pageId != '238' ";
					}
					
					$rGetEmailResult = dbQuery($sGetEmailQuery);
					$sTempContent .= "<tr><td colspan=9><b>Email</b></td></tr>";
					while ($oTempRow = dbFetchObject($rGetEmailResult)) {
						$sTempContent .= "<tr colspan=9><td><a href='$sSortLink&sOfferCodeGetEmail=$sOfferCodeGetEmail&sTempEmail=$oTempRow->email'>$oTempRow->email</a><br></td></tr>";
					}
					
					if ($sTempEmail) {
						$sDataQuery = "SELECT *
								FROM otDataHistory
							 	WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
							 	AND offerCode='$sOfferCodeGetEmail'
							 	AND email = '$sTempEmail'";
						$rDataResult = dbQuery($sDataQuery);
						$sTempContent .= "<tr><td>Email</td><td>OfferCode</td><td>RevPerLead</td>
							<td>SourceCode</td><td>SubSourceCode</td><td>PageId</td><td>DateAdded</td>
							<td>ProcessStatus</td><td>ReasonCode</td><td>DateProcessed</td><td>SendStatus</td>
							<td>DateSent</td><td>HowSent</td><td>RealTimeResponse</td><td>UserIp</td><td>ServerIp</td>
							<td>Page2Data</td><td>LeadCounter</td><td>DailyCounter</td><td>ExcludeDataSale</td>
							<td>SessionId</td></tr>";
						while ($oTempRow = dbFetchObject($rDataResult)) {
							$sEmail = $oTempRow->email;
							if ($sBgcolorClass == "ODD") {
								$sBgcolorClass = "EVEN_WHITE";
							} else {
								$sBgcolorClass = "ODD";
							}
						$sTempContent .= "<tr class=$sBgcolorClass>
							<td>$oTempRow->email<br></td><td>$oTempRow->offerCode<br></td>
							<td>$oTempRow->revPerLead<br></td><td>$oTempRow->sourceCode<br></td>
							<td>$oTempRow->subSourceCode<br></td><td>$oTempRow->pageId<br></td>
							<td>$oTempRow->dateTimeAdded<br></td><td>$oTempRow->processStatus<br></td>
							<td>$oTempRow->reasonCode<br></td><td>$oTempRow->dateTimeProcessed<br></td>
							<td>$oTempRow->sendStatus<br></td><td>$oTempRow->dateTimeSent<br></td>
							<td>$oTempRow->howSent<br></td><td>$oTempRow->realTimeResponse<br></td>
							<td>$oTempRow->remoteIp<br></td><td>$oTempRow->serverIp<br></td>
							<td>$oTempRow->page2Data<br></td><td>$oTempRow->leadCounter<br></td>
							<td>$oTempRow->dailyCounter<br></td><td>$oTempRow->excludeDataSale<br></td>
							<td>$oTempRow->sessionId<br></td></tr>";
						}
											
																	
						$sDataQuery = "SELECT *
								FROM userDataHistory
							 	WHERE email='$sEmail'";
						$rDataResult = dbQuery($sDataQuery);
						$sTempContent .= "<tr><td>Email</td><td>Salutation</td><td>First</td>
							<td>Last</td><td>Address</td><td>Address2</td><td>City</td>
							<td>State</td><td>Zip</td><td>Phone</td><td>DateTimeAdded</td>
							<td>PostalVerified</td><td>SessionId</td></tr>";
						while ($oTempRow = dbFetchObject($rDataResult)) {
							if ($sBgcolorClass == "ODD") {
								$sBgcolorClass = "EVEN_WHITE";
							} else {
								$sBgcolorClass = "ODD";
							}
							
							$sTempContent .= "<tr class=$sBgcolorClass>
								<td>$oTempRow->email<br></td><td>$oTempRow->salutation<br></td>
								<td>$oTempRow->first<br></td><td>$oTempRow->last<br></td>
								<td>$oTempRow->address<br></td><td>$oTempRow->address2<br></td>
								<td>$oTempRow->city<br></td><td>$oTempRow->state<br></td>
								<td>$oTempRow->zip<br></td><td>$oTempRow->phoneNo<br></td>
								<td>$oTempRow->dateTimeAdded<br></td><td>$oTempRow->postalVerified<br></td>
								<td>$oTempRow->sessionId<br></td></tr>";
						}
					}
				}

				$iCount = 0;
				$sPageLoop = 0;
				$sPageGrossTotal = 0;
				$sAbandedTotal = 0;
				$sXOutTotal = 0;
				$sNetTotal = 0;
				
				$sExportReportContent = "Offer Code\tOffer Name\tGross Count\tAbandoned Count\tAbandoned Percent\tX Out Count\tX Out Percent\tNet Count\tGood Percent\n";
				for( $iLoop=0; $iLoop<$iNumRecords; $iLoop++ ) {
					$sPageLoop++;
					if (($sPageLoop > $iStartRec) && ($sPageLoop <= ($iStartRec + $iRecPerPage))) {
						if ($sBgcolorClass == "ODD") {
							$sBgcolorClass = "EVEN_WHITE";
						} else {
							$sBgcolorClass = "ODD";
						}
						$sOfferCodeLink = $aReportArray['offerCode'][$iLoop];
						
						
						if ($sOfferCodeLink == $sOfferCodeGetEmail) {
							$sReportContent .= "<tr class=$sBgcolorClass>
								<td><a href='$sSortLink&sOfferCodeGetEmail=$sOfferCodeLink'>$sOfferCodeLink</a></td>
								<td>".$aReportArray['offerName'][$iLoop]."</td>
								<td>".$aReportArray['grossCount'][$iLoop]."</td><td>".$aReportArray['abandedCount'][$iLoop]."</td>
								<td>".$aReportArray['abandedPercent'][$iLoop]."</td><td>".$aReportArray['xOutCount'][$iLoop]."</td>
								<td>".$aReportArray['xOutPercent'][$iLoop]."</td><td>".$aReportArray['netCount'][$iLoop]."</td>
								<td>".$aReportArray['goodPercent'][$iLoop]."</td>
								</tr>$sTempContent";
						} else {
							$sReportContent .= "<tr class=$sBgcolorClass>
								<td><a href='$sSortLink&sOfferCodeGetEmail=$sOfferCodeLink'>$sOfferCodeLink</a></td>
								<td>".$aReportArray['offerName'][$iLoop]."</td>
								<td>".$aReportArray['grossCount'][$iLoop]."</td><td>".$aReportArray['abandedCount'][$iLoop]."</td>
								<td>".$aReportArray['abandedPercent'][$iLoop]."</td><td>".$aReportArray['xOutCount'][$iLoop]."</td>
								<td>".$aReportArray['xOutPercent'][$iLoop]."</td><td>".$aReportArray['netCount'][$iLoop]."</td>
								<td>".$aReportArray['goodPercent'][$iLoop]."</td>
								</tr>";					
						}
						
						$sExportReportContent .= "$sOfferCodeLink\t".$aReportArray['offerName'][$iLoop]."\t".
								$aReportArray['grossCount'][$iLoop]."\t".$aReportArray['abandedCount'][$iLoop]."\t".
								$aReportArray['abandedPercent'][$iLoop]."\t".$aReportArray['xOutCount'][$iLoop]."\t".
								$aReportArray['xOutPercent'][$iLoop]."\t".$aReportArray['netCount'][$iLoop]."\t".
								$aReportArray['goodPercent'][$iLoop]."\n";

						$sPageGrossTotal += $aReportArray['grossCount'][$iLoop];
						$sAbandedTotal += $aReportArray['abandedCount'][$iLoop];
						$sXOutTotal += $aReportArray['xOutCount'][$iLoop];
						$sNetTotal += $aReportArray['netCount'][$iLoop];
						$iCount++;
					}
				}
				if ($iNumRecords > 0) {
					$sReportGrossTotal = array_sum($aReportArray['grossCount']);
					$sReportAbandedTotal = array_sum($aReportArray['abandedCount']);
					$sReportXOutTotal = array_sum($aReportArray['xOutCount']);
					$sReportNetTotal = array_sum($aReportArray['netCount']);
					
					$sPageAbandedPercent = ($sPageGrossTotal===0 ? number_format((($sAbandedTotal / $sPageGrossTotal)*100),1) : '0.0');
					$sReportAbandedPercent = ($sReportGrossTotal===0 ? number_format((($sReportAbandedTotal / $sReportGrossTotal)*100),1) : '0.0');
					$sPageXOutPercent = ($sPageGrossTotal===0 ? number_format((($sXOutTotal / $sPageGrossTotal)*100),1) : '0.0');
					$sReportXOutPercent = ($sReportGrossTotal===0 ? number_format((($sReportXOutTotal / $sReportGrossTotal)*100),1) : '0.0');
				}
				
				$sPageGoodPercent = (100 - $sPageAbandedPercent - $sPageXOutPercent);
				$sReportContent .= "<tr><td colspan=9><hr color=#000000></td></tr>
					<tr><td><b>Page Total: </b></td><td>&nbsp;</td><td>$sPageGrossTotal</td><td>$sAbandedTotal</td>
					<td>$sPageAbandedPercent</td><td>$sXOutTotal</td><td>$sPageXOutPercent</td><td>$sNetTotal</td><td>$sPageGoodPercent</td></tr>";
				
				$sReportGoodPercent = (100 - $sReportAbandedPercent - $sReportXOutPercent);
				$sReportContent .= "<tr>
					<tr><td><b>Grand Total: </b></td><td>&nbsp;</td><td>$sReportGrossTotal</td><td>$sReportAbandedTotal</td>
					<td>$sReportAbandedPercent</td><td>$sReportXOutTotal</td><td>$sReportXOutPercent</td><td>$sReportNetTotal</td><td>$sReportGoodPercent</td></tr>";

				
				$sExportReportContent .= "\n\nPage Total:\t\t$sPageGrossTotal\t$sAbandedTotal\t$sPageAbandedPercent\t$sXOutTotal\t$sPageXOutPercent\t$sNetTotal\t$sPageGoodPercent\n";
				$sExportReportContent .= "Gross Total:\t\t$sReportGrossTotal\t$sReportAbandedTotal\t$sReportAbandedPercent\t$sReportXOutTotal\t$sReportXOutPercent\t$sReportNetTotal\t$sReportGoodPercent";
				$sExportReportContent .= "\n\n\n\nReport From $sDateFrom to $sDateTo\nRun Date / Time: $sRunDateAndTime";
				
				
				
				// start of track users' activity in nibbles
				mysql_connect ($host, $user, $pass);
				mysql_select_db ($dbase);
				$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sTemp\")";
				$rResult = dbQuery($sAddQuery);
				mysql_connect ($reportingHost, $reportingUser, $reportingPass);
				mysql_select_db ($reportingDbase);
				// end of track users' activity in nibbles
			}
		} else {
			$sMessage .= "Date range entered is greater than maximum range ($iMaxDaysToReport days).";
		}
	}

	if ($sExcludeApiLeads == 'Y') {
		$sExcludeApiChecked = "checked";
	}

	if ($sExportExcel == 'Y') {
		$sExportExcelChecked = "checked";
	}
	
	
	
	
	if ($sExportExcel) {
		$sFileName = "abandedOffers_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";
		$rFpFile = fopen("$sGblWebRoot/temp/$sFileName", "w");
		if ($rFpFile) {
			fputs($rFpFile, $sExportReportContent, strlen($sExportReportContent));
			fclose($rFpFile);
			echo "<script language=JavaScript>
			void(window.open(\"$sGblSiteRoot/download.php?sFile=$sFileName\",\"\",\"height=150, width=300, scrollbars=yes, resizable=yes, status=yes\"));
		  </script>";
		} else {
			$sMessage = "Error exporting data...";
		}
	}
	

	$sOfferCodeQuery = "SELECT distinct offerCode FROM abandedOffersHistory
		WHERE dateTimeAdded between '$sDateFrom 00:00:00' and '$sDateTo 23:59:59'
		ORDER BY offerCode";
	$rOfferCodeQuery = dbQuery($sOfferCodeQuery);
	echo dbError();
	while ($oReportRow = dbFetchObject($rOfferCodeQuery)) {
		$sTempOfferCode = $oReportRow->offerCode;
		if ($sOfferCodeList) {
			if ($sTempOfferCode == $sOfferCodeList) {
				$sOfferCodeSelected = "selected";
			} else {
				$sOfferCodeSelected = "";
			}
		} else {
			if ($sTempOfferCode == $sOfferCodeList && isset($sOfferCodeList)) {
				$sOfferCodeSelected = "selected";
			} else {
				$sOfferCodeSelected = "";
			}
		}
		$sOfferCodeOptions .= "<option value='$oReportRow->offerCode' $sOfferCodeSelected>$oReportRow->offerCode";
	}
	
	
	$sSourceCodeQuery = "SELECT sourceCode FROM links order by sourceCode";
	$rSourceCodeResult = mysql_query($sSourceCodeQuery);
	$sSourceCodeOption = "<option value=''>";
	while ($oSourceCodeRow = mysql_fetch_object($rSourceCodeResult)) {
		if ($oSourceCodeRow->sourceCode == $sSourceCode) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		$sSourceCodeOption .= "<option value='$oSourceCodeRow->sourceCode' $sSelected>$oSourceCodeRow->sourceCode";
	}
	
	//for the page filter
	$sPageFilterQuery = "SELECT pageName, id from otPages order by pageName DESC";
	$rPageFilterResult = mysql_query($sPageFilterQuery);
	$sPageFilterOptions = "<option value=''>";
	while($oPageNameRow = mysql_fetch_object($rPageFilterResult)) {
		$sPageFilterOptions .= "<option value='$oPageNameRow->id' ".
			($oPageNameRow->id == $sPageName ? 'selected' : '').">$oPageNameRow->pageName";
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
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?></select></td></tr>	
<tr><td>Offer Code</td><td><select name=sOfferCodeList>
	<option value='' selected>All</option><?php echo $sOfferCodeOptions;?></select></td></tr>
	
	<tr><td>Source Code</td>
		<td><select name='sSourceCode'>
		<?php echo $sSourceCodeOption;?>
		</select>
		</td>
	</tr>
	
	<tr><td>Page Name</td>
		<td><select name='sPageName'>
		<?php echo $sPageFilterOptions;?>
		</select>
		</td>
	</tr>
	
<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">
 &nbsp; &nbsp; &nbsp; &nbsp; <input type=checkbox name=sExcludeApiLeads value="Y" <?php echo $sExcludeApiChecked;?>> Exclude API
 &nbsp; &nbsp; &nbsp; &nbsp; <input type=checkbox name="sExportExcel" value="Y" <?php echo $sExportExcelChecked;?>> Export To Excel
 </td><td colspan=2></td></tr>
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
	<tr><td colspan=9 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=9 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=offerCode&sOfferCodeOrder=<?php echo $sOfferCodeOrder;?>" class=header>Offer Code</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=offerName&sOfferNameOrder=<?php echo $sOfferNameOrder;?>" class=header>Offer Name</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=grossCount&sGrossCountOrder=<?php echo $sGrossCountOrder;?>" class=header>Gross Count</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=abandedCount&sAbandedCountOrder=<?php echo $sAbandedCountOrder;?>" class=header>Don't Want This<br>Offer Checked<br>Count</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=abandedPercent&sAbandedPercentOrder=<?php echo $sAbandedPercentOrder;?>" class=header>Abandoned Percent</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=xOutCount&sXOutCountOrder=<?php echo $sXOutCountOrder;?>" class=header>Didn't Complete<br>X Out Count</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=xOutPercent&sXOutPercentOrder=<?php echo $sXOutPercentOrder;?>" class=header>X Out Percent</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=netCount&sNetCountOrder=<?php echo $sNetCountOrder;?>" class=header>Net Count</a></td>
		<td valign="top" class=header><a href="<?php echo $sSortLink;?>&sListName=<?php echo $sListName;?>&sOrderColumn=goodPercent&sGoodOrder=<?php echo $sGoodOrder;?>" class=header>% Good</a></td>
	</tr>
<?php echo $sReportContent;?>
<tr><td colspan=9 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=9 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=9>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s).<br>
		 Today's data is not included on this report.<br><br>
		 Page Total: This is the total for current page only, not for the entire report.<br><br>
		 Grand Total:  This is the total for entire report.<br><br>
		 OfferCode: This is offerCode.  Clicking on actual offerCode will display all email addresses associated with this offerCode and date.  
		 	Click on email address to get detailed information from userDataHistory and otDataHistory tables.<br><br>
		 Gross Count: Number of times the offer was checked on the first page.<br><br>
		 Abandoned Count: Number of offers abandoned (I Don't Want This Offer Is Checked) for this offerCode within this date range.<br><br>
		 Abandoned Percent: The ratio of Abandoned Count versus Gross Count.<br><br>
		 X Out Count: Number of leads selected on the first page, but the page was closed before the page 2 information submitted. 
		 			Result of Gross Count minus Abandoned Count minus Net Count.<br><br>
		 X Out Percent: The ratio of X Out Count versus Gross Count.<br><br>
		 Net Count:  Number of leads collected.  Count of leads collected on otDataHistory table.<br>
		 Good Percent: 100 Percent minus Abandoned Percent minus X Out Percent.<br>
		 <br><br>
		 <b>Gross Count Query: </b><?php echo $sXOutQuery; ?><br><br>
		 <b>Abandoned Count Query: </b><?php echo $sAbandedQuery; ?><br><br>
		 <b>Net Count Query: </b><?php echo $sTakenQuery; ?><br><br>
		 <b>X Out Count: </b>Result of Gross Count minus Abandoned Count minus Net Count.<br><br>
		 
		</td></tr>
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