<?php

/*********

Script to Display Ampere Mailing Statistics from the ezmlm/qmail system.

**********/

session_start();

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Address Validation Statistics";

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);


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

	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	$sToday = date('Y')."-".date('m')."-".date('d');
	$sSourceCodeOptions .= "<option value=''>All";

	if (!$sViewReport) {

		$iMonthTo = date('m');
		$iDayTo = date('d');
		$iYearTo = date('Y');

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
		$sOrderColumn = "dateChecked";
		$sDateSentOrder = "DESC";
	}

	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "successes" :
			$sCurrOrder = $sSuccessOrder;
			$sSuccessOrder = ($sSuccessOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "updates" :
			$sCurrOrder = $sUpdatesOrder;
			$sUpdatesOrder = ($sUpdatesOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "failures" :
			$sCurrOrder = $sFailuresOrder;
			$sFailuresOrder = ($sFailuresOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "errorR" :
			$sCurrOrder = $sErrorROrder;
			$sErrorROrder = ($sErrorROrder != "DESC" ? "DESC" : "ASC");
			break;
			case "errorU" :
			$sCurrOrder = $sErrorUOrder;
			$sErrorUOrder = ($sErrorUOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "errorAM" :
			$sCurrOrder = $sErrorAMOrder;
			$sErrorAMOrder = ($sErrorAMOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "errorX" :
			$sCurrOrder = $sErrorXOrder;
			$sErrorXOrder = ($sErrorXOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "errorT" :
			$sCurrOrder = $sErrorTOrder;
			$sErrorTOrder = ($sErrorTOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "errorZ" :
			$sCurrOrder = $sErrorZOrder;
			$sErrorZOrder = ($sErrorZOrder != "DESC" ? "DESC" : "ASC");
			break;
			default:
			$sCurrOrder = $sDateCheckedOrder;
			$sDateCheckedOrder = ($sDateCheckedOrder != "DESC" ? "DESC" : "ASC");
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

	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo
							&iDbMailId=$iDbMailId&iDisplayDateWise=$iDisplayDateWise&sViewReport=$sViewReport&iRecPerPage=$iRecPerPage&sDetails=$sDetails&sShowRecords=$sShowRecords&sShowFailures=$sShowFailures";

	if ($sViewReport != "") {
		if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo) && !$bDateRangeNotOk) {
			if ($sAllowReport == 'N') {
				$sMessage .= "<br>Server Load Is High. Please check back soon...";
			} else {
				if ($iDisplayDateWise) {
				} else {

					$sReportQuery = "SELECT dateChecked, successes, updates, failures, errorR, errorU, errorAM, errorX, errorT, errorZ
					FROM validateAddressAoStatsHistorySum
					WHERE dateChecked BETWEEN '$sDateFrom' AND '$sDateTo'
					ORDER BY $sOrderColumn $sCurrOrder";
				}

				// start of track users' activity in nibbles 
				$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
				mysql_connect ($host, $user, $pass); 
				mysql_select_db ($dbase); 
		
				$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: BETWEEN '$sDateFrom' AND '$sDateTo', source code: $sSourceCode\")"; 
				$rResult = dbQuery($sAddQuery); 
				echo  dbError(); 
				mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
				mysql_select_db ($reportingDbase); 
				// end of track users' activity in nibbles		
				
				$rReportResult = dbQuery($sReportQuery);
				echo dbError();


				if ($sDateTo >=	$sToday) {
					$sGetRecords = "SELECT *
						FROM  nibbles.validateAddressAoStats";
					$rGetRecordsResult = dbQuery($sGetRecords);
					$iAddSuccess=0;
					$iAddFailure=0;
					$iAddUpdate=0;
					$iErrorR = 0;
					$iErrorU = 0;
					$iErrorAM = 0;
					$iErrorX = 0;
					$iErrorT = 0;
					$iErrorZ = 0;
					while( $oRowRecord = dbFetchObject( $rGetRecordsResult ) ) {

						if( $oRowRecord->response == "Success" ) {
							$iAddSuccess ++;
						}
						if( substr( $oRowRecord->response, 0, 7 ) == "Failure" ) {
							$iAddFailure ++;
							$errorLine = explode( "|", $oRowRecord->response );
							$errorCode = $errorLine[1];
							$sErrorVar = "iError".$errorCode;
							$$sErrorVar ++;
						}
						if( substr( $oRowRecord->response, 0, 6 ) == "update" ) {
							$iAddUpdate ++;
						}
					}

					$iTotalSuccesses += $iAddSuccess;
					$iTotalUpdates += $iAddUpdate;
					$iTotalFailures += $iAddFailure;
					$iTotalErrorR += $iErrorR;
					$iTotalErrorU += $iErrorU;
					$iTotalErrorAM += $iErrorAM;
					$iTotalErrorX += $iErrorX;
					$iTotalErrorT += $iErrorT;
					$iTotalErrorZ += $iErrorZ;


					if (!$sDetails) {
						$sReportContent .= "<tr class=$sBgcolorClass>
									<td>$sToday</td>
									<td>$iAddSuccess</td>
									<td>$iAddUpdate</td>
									<td>$iAddFailure</td>
								</tr>";
					} else {
						$sReportContent .= "<tr class=$sBgcolorClass>
									<td>$sToday</td>
									<td>$iAddSuccess</td>
									<td>$iAddUpdate</td>
									<td>$iAddFailure</td>
									<td>$iErrorR</td>
									<td>$iErrorU</td>
									<td>$iErrorAM</td>
									<td>$iErrorX</td>
									<td>$iErrorT</td>
									<td>$iErrorZ</td>
								</tr>";
					}
					if (!$sDetails) {
						$sExpReportContent .= "$sToday\t$iAddSuccess\t" .
						"$iAddUpdate\t$iAddFailure\n";
					} else {
						$sExpReportContent .= "$sToday\t$iAddSuccess\t" .
						"$iAddUpdate\t$iAddFailure\t$iErrorR\t$iErrorU\t$iErrorAM\t$iErrorX\t$iErrorT\t$iErrorZ\n";
					}
				}

				while ($oReportRow = dbFetchObject($rReportResult)) {
					if ($sBgcolorClass == "ODD") {
						$sBgcolorClass = "EVEN_WHITE";
					} else {
						$sBgcolorClass = "ODD";
					}

					if (!$sDetails) {
						$sReportContent .= "<tr class=$sBgcolorClass>
									<td>$oReportRow->dateChecked</td>
									<td>$oReportRow->successes</td>
									<td>$oReportRow->updates</td>
									<td>$oReportRow->failures</td>
								</tr>";
					} else {
						$sReportContent .= "<tr class=$sBgcolorClass>
									<td>$oReportRow->dateChecked</td>
									<td>$oReportRow->successes</td>
									<td>$oReportRow->updates</td>
									<td>$oReportRow->failures</td>
									<td>$oReportRow->errorR</td>
									<td>$oReportRow->errorU</td>
									<td>$oReportRow->errorAM</td>
									<td>$oReportRow->errorX</td>
									<td>$oReportRow->errorT</td>
									<td>$oReportRow->errorZ</td>
								</tr>";
					}

					if (!$sDetails) {
						$sExpReportContent .= "$oReportRow->dateChecked\t$oReportRow->successes\t" .
						"$oReportRow->updates\t$oReportRow->failures\n";
					} else {
						$sExpReportContent .= "$oReportRow->dateChecked\t$oReportRow->successes\t" .
						"$oReportRow->updates\t$oReportRow->failures\t$oReportRow->errorR\t$oReportRow->errorU\t$oReportRow->errorAM\t$oReportRow->errorX\t$oReportRow->errorT\t$oReportRow->errorZ\n";
					}

					$iTotalSuccesses += $oReportRow->successes;
					$iTotalUpdates += $oReportRow->updates;
					$iTotalFailures += $oReportRow->failures;
					$iTotalErrorR += $oReportRow->errorR;
					$iTotalErrorU += $oReportRow->errorU;
					$iTotalErrorAM += $oReportRow->errorAM;
					$iTotalErrorX += $oReportRow->errorX;
					$iTotalErrorT += $oReportRow->errorT;
					$iTotalErrorZ += $oReportRow->errorZ;
				}


				if (!$sDetails) {
					$sReportContent .= "<tr><td colspan=6><HR color=#000000></td></tr>
							<tr><td class=header>Total</td>
								<td class=header>$iTotalSuccesses</td>
								<td class=header>$iTotalUpdates</td>
								<td class=header>$iTotalFailures</td>
							</tr>";
				} else {
					$sReportContent .= "<tr><td colspan=10><HR color=#000000></td></tr>
							<tr><td class=header>Total</td>
								<td class=header>$iTotalSuccesses</td>
								<td class=header>$iTotalUpdates</td>
								<td class=header>$iTotalFailures</td>
								<td class=header>$iTotalErrorR</td>
								<td class=header>$iTotalErrorU</td>
								<td class=header>$iTotalErrorAM</td>
								<td class=header>$iTotalErrorX</td>
								<td class=header>$iTotalErrorT</td>
								<td class=header>$iTotalErrorZ</td>
							</tr>";
				}

				if ($sShowRecords) {
					if ($sDateTo >=	$sToday) {
						$sCurrentCountQuery = "SELECT count(*) as count
											FROM validateAddressAoStats
											WHERE dateTimeCheck BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'";
						if ($sShowFailures) {
							$sCurrentCountQuery .= " AND response LIKE 'Failure|%'";
						}

						if ($sSourceCode != "") {
							$sCurrentCountQuery .= " AND sourceCode = '$sSourceCode' ";
						}


						$rCurrentCountResult = dbQuery($sCurrentCountQuery);
						echo dbError();
						$oCurrentCountRow = dbFetchObject($rCurrentCountResult);
						$iCurrentCount = $oCurrentCountRow -> count;
					}

					$sHistoryCountQuery = "SELECT count(*) as count
											FROM validateAddressAoStatsHistory
											WHERE dateTimeCheck BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'";
					if ($sShowFailures) {
						$sHistoryCountQuery .= " AND response LIKE 'Failure|%'";
					}

					if ($sSourceCode != "") {
						$sHistoryCountQuery .= " AND sourceCode = '$sSourceCode' ";
					}

					$rHistoryCountResult = dbQuery($sHistoryCountQuery);
					echo dbError();
					$oHistoryCountRow = dbFetchObject($rHistoryCountResult);
					$iHistoryCount = $oHistoryCountRow -> count;

					$iNumRecords = $iCurrentCount + $iHistoryCount;
					$iTotalPages = ceil($iNumRecords/$iRecPerPage);

					// If current page no. is greater than total pages move to the last available page no.
					if ($iPage > $iTotalPages) {
						$iPage = $iTotalPages;
					}

					$iStartRec = ($iPage-1) * $iRecPerPage;
					$iEndRec = $iStartRec + $iRecPerPage-1;

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

					$iCount = 0;
					$sReportHistoryContent = "";
					$sExpReportHistoryContent = "";

					if ($sDateTo >=	$sToday) {

						if ($sShowFailures) {
							$sFailureFilter = " AND response LIKE 'Failure|%'";
						}

						if ($sSourceCode != "") {
							$sSourceCodeFilter .= " AND sourceCode = '$sSourceCode' ";
						}

						$sReportQuery = "SELECT dateTimeCheck, address, address2, city, state, zip, response, sourceCode
											FROM validateAddressAoStats
											WHERE dateTimeCheck BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
											$sFailureFilter
											$sSourceCodeFilter
											ORDER BY dateTimeCheck";

						$rReportResult = dbQuery($sReportQuery);
						echo dbError();

						if ($sDateFrom < $sToday) {
							$iTempRecPerPage = $iRecPerPage;
							$iTempRecPerPage = $iTempRecPerPage / 2;
						} else {
							$iTempRecPerPage = $iRecPerPage;
						}

						//	use query to fetch only the rows of the page to be displayed
						$sReportQuery .= " LIMIT $iStartRec, $iTempRecPerPage";
						$rReportResult = dbQuery($sReportQuery);

						while ($oReportRow = dbFetchObject($rReportResult)) {
							$aReportArray['dateTimeCheck'][$iCount] = $oReportRow->dateTimeCheck;
							$aReportArray['sourceCode'][$iCount] = $oReportRow->sourceCode;
							$aReportArray['address'][$iCount] = $oReportRow->address;
							$aReportArray['address2'][$iCount] = $oReportRow->address2;
							$aReportArray['city'][$iCount] = $oReportRow->city;
							$aReportArray['state'][$iCount] = $oReportRow->state;
							$aReportArray['zip'][$iCount] = $oReportRow->zip;
							$aReportArray['response'][$iCount] = $oReportRow->response;
							$iCount++;
						}
					}

					if ($sShowFailures) {
						$sFailureFilter = " AND response LIKE 'Failure|%'";
					}

					if ($sSourceCode != "") {
						$sSourceCodeFilter .= " AND sourceCode = '$sSourceCode' ";
					}

					$sReportHistoryQuery = "SELECT dateTimeCheck, address, address2, city, state, zip, response, sourceCode
								FROM validateAddressAoStatsHistory
								WHERE dateTimeCheck BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
								$sFailureFilter
								$sSourceCodeFilter
								ORDER BY  dateTimeCheck";


					$rReportHistoryResult = dbQuery($sReportHistoryQuery);
					echo dbError();

					if ($sDateTo >=	$sToday) {
						$iTempRecPerPage = $iRecPerPage;
						$iTempRecPerPage = $iTempRecPerPage / 2;
						$sReportHistoryQuery .= " LIMIT $iStartRec, $iTempRecPerPage";
						$rReportHistoryResult = dbQuery($sReportHistoryQuery);
					} else {
						$sReportHistoryQuery .= " LIMIT $iStartRec, $iRecPerPage";
						$rReportHistoryResult = dbQuery($sReportHistoryQuery);
					}

					while ($oReportHistoryRow = dbFetchObject($rReportHistoryResult)) {
						$aReportArray['dateTimeCheck'][$iCount] = $oReportHistoryRow->dateTimeCheck;
						$aReportArray['sourceCode'][$iCount] = $oReportHistoryRow->sourceCode;
						$aReportArray['address'][$iCount] = $oReportHistoryRow->address;
						$aReportArray['address2'][$iCount] = $oReportHistoryRow->address2;
						$aReportArray['city'][$iCount] = $oReportHistoryRow->city;
						$aReportArray['state'][$iCount] = $oReportHistoryRow->state;
						$aReportArray['zip'][$iCount] = $oReportHistoryRow->zip;
						$aReportArray['response'][$iCount] = $oReportHistoryRow->response;
						$iCount++;
					}

					for ($i=0; $i<=$iCount; $i++) {
						if ($sBgcolorClass == "ODD") {
							$sBgcolorClass = "EVEN_WHITE";
						} else {
							$sBgcolorClass = "ODD";
						}

						$sReportHistoryContent .= "<tr class=$sBgcolorClass>
								<td>".$aReportArray['dateTimeCheck'][$i]."</td>
								<td>".$aReportArray['sourceCode'][$i]."</td>
								<td>".$aReportArray['address'][$i]."</td>
								<td>".$aReportArray['address2'][$i]."</td>
								<td>".$aReportArray['city'][$i]."</td>
								<td>".$aReportArray['state'][$i]."</td>
								<td>".$aReportArray['zip'][$i]."</td>
								<td>".$aReportArray['response'][$i]."</td>
							</tr>";

						if ($sExportExcel) {
							$sExpReportHistoryContent .= $aReportArray['dateTimeCheck'][$i]."\t";
							$sExpReportHistoryContent .= $aReportArray['sourceCode'][$i]."\t";
							$sExpReportHistoryContent .= $aReportArray['address'][$i]."\t";
							$sExpReportHistoryContent .= $aReportArray['address2'][$i]."\t";
							$sExpReportHistoryContent .= $aReportArray['city'][$i]."\t";
							$sExpReportHistoryContent .= $aReportArray['state'][$i]."\t";
							$sExpReportHistoryContent .= $aReportArray['zip'][$i]."\t";
							$sExpReportHistoryContent .= $aReportArray['response'][$i]."\n";
						}
					}
				}

				if ($sExportExcel && !$bDateRangeNotOk) {
					if (!$sDetails) {
						$sExpReportContent = "Date / Time\tSuccesses\tUpdates\tFailures\n".$sExpReportContent;
						$sExpReportContent .= "Total:\t$iTotalSuccesses\t$iTotalUpdates\t$iTotalFailures\n";
					} else {
						$sExpReportContent = "Date / Time\tSuccesses\tUpdates\tFailures\tError R: Range\tError U: Street Name\tError AM: Bad/Invalid\tError X: Undeliverable\tError T: Component Failure\tError Z: Invalid Zip"."\n".$sExpReportContent;
						$sExpReportContent .= "Total:\t$iTotalSuccesses\t$iTotalUpdates\t$iTotalFailures\t$iTotalErrorR\t$iTotalErrorU\t$iTotalErrorAM\t$iTotalErrorX\t$iTotalErrorT\t$iTotalErrorZ\n";
					}

					if ($sShowRecords) {
						$sExpReportContent .= "\n\ndate/time\tsourceCode\taddress\taddress2\tcity\tstate\tzip\tresponse\n".$sExpReportHistoryContent;
					}
					$sExpReportContent .= "\n\nReport From $iMonthFrom-$iDayFrom-$iYearFrom To $iMonthTo-$iDayTo-$iYearTo";
					$sExpReportContent .= "\nRun Date/Time $sRunDateAndTime";

					$sFileName = "dbMailsSent_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";

					$rFpFile = fopen("$sGblWebRoot/temp/$sFileName", "w");
					if ($rFpFile) {
						fputs($rFpFile, $sExpReportContent, strlen($sExpReportContent));
						fclose($rFpFile);

						echo "<script language=JavaScript>
			void(window.open(\"$sGblSiteRoot/download.php?sFile=$sFileName\",\"\",\"height=150, width=300, scrollbars=yes, resizable=yes, status=yes\"));
		  </script>";
					} else {
						$sMessage = "Error exporting data...";
					}
				}
			}
		} else {
			$sMessage .= "Date range entered is greater than maximum range ($iMaxDaysToReport days).";
		}
	}


	if ($sShowRecords) {
		$sShowRecordsChecked = "checked";
	}

	if ($sExportExcel) {
		$sExportExcelChecked = "checked";
	}

	if ($sDetails) {
		$sDetailsChecked = "checked";
	}

	if ($sShowFailures) {
		$sShowFailuresChecked = "checked";
	}


	$sSourceCodeQuery = "SELECT distinct sourceCode
	FROM validateAddressAoStatsHistory 
	WHERE dateTimeCheck BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
	ORDER BY sourceCode";
	$rSourceCodeResult = dbQuery($sSourceCodeQuery);
	echo dbError();
	$iSourceCodeCount = 0;
	while ($oSourceCodeRow = dbFetchObject($rSourceCodeResult)) {
		$aSourceCodeArray['sourceCode'][$iSourceCodeCount] = $oSourceCodeRow->sourceCode;
		$iSourceCodeCount++;
	}

	$sSourceCodeQuery = "SELECT distinct sourceCode
	FROM validateAddressAoStats 
	WHERE dateTimeCheck BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
	ORDER BY sourceCode";
	$rSourceCodeResult = dbQuery($sSourceCodeQuery);
	echo dbError();
	while ($oSourceCodeRow = dbFetchObject($rSourceCodeResult)) {
		$aSourceCodeArray['sourceCode'][$iSourceCodeCount] = $oSourceCodeRow->sourceCode;
		$iSourceCodeCount++;
	}
	sort($aSourceCodeArray['sourceCode']);
	$sPrevious = $aSourceCodeArray['sourceCode'][0];
	for ($i=0; $i<$iSourceCodeCount; $i++) {
		if( $sSourceCode == $aSourceCodeArray['sourceCode'][$i] ) {
			$sSourceCodeSelected = " selected ";
		} else {
			$sSourceCodeSelected = "";
		}
		if( $sPrevious != $aSourceCodeArray['sourceCode'][$i] ) {
			$sTempValue = $aSourceCodeArray['sourceCode'][$i];
			$sSourceCodeOptions .= "<option value='$sTempValue' $sSourceCodeSelected>$sTempValue";
		}
			$sPrevious = $aSourceCodeArray['sourceCode'][$i];
	}

	include("../../includes/adminHeader.php");

	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);

	// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;

	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";	

?>
<html>
	<head><title>
	Address Validation Stats Report
	</title></head>
<body>
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
<tr>
	<td>Source Code: </td>
	<td><select name=sSourceCode><?php echo $sSourceCodeOptions;?></select>
</tr>
	
<!--<tr><td></td>
	<td><input type=checkbox name=iDisplayDateWise value='1' <?php //echo $sDisplayDateWiseChecked;?>> Display Datewise (nonfunct)</td></tr>		-->
<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">
 &nbsp; &nbsp; <input type=checkbox name=sExportExcel value="Y" <?php echo $sExportExcelChecked;?>> Export To Excel
 &nbsp; &nbsp; <input type=checkbox name=sDetails value="Y" <?php echo $sDetailsChecked;?>> Details
 &nbsp; &nbsp; <input type=checkbox name=sShowRecords value="Y" <?php echo $sShowRecordsChecked;?>> Show Records
 &nbsp; &nbsp; <input type=checkbox name=sShowFailures value="Y" <?php echo $sShowFailuresChecked;?>> Failures Only
 
 </td>
	<td colspan=2><!--<input type=checkbox name=sShowQueries value='Y' <?php //echo $sShowQueriesChecked;?>> Show Queries--></td>
</tr>
<tr><td colspan=4 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>
</table>

<?php if (!$sDetails) {?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td colspan=5 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=5 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><?php echo $sDateSentHeader;?>
		<td valign=top class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=dateChecked&sDateCheckedOrder=<?php echo $sDateCheckedOrder;?>" class=header>Date / Time</a></td>
		<td valign=top class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=successes&sSuccessOrder=<?php echo $sSuccessOrder;?>" class=header>Successes</a></td>
		<td valign=top class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=updates&sUpdatesOrder=<?php echo $sUpdatesOrder;?>" class=header>Updates</a></td>
		<td valign=top class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=failures&sFailuresOrder=<?php echo $sFailuresOrder;?>" class=header>Failures</a></td>
	</tr>

<?php echo $sReportContent;?>

<tr><td colspan=6 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=5 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=5>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s).<br>
		Clicking on a link to sort record will not work for today's entry.<br>
		Total: This is the total for current page only, not for the entire report.<br>
		Successes: Number of successes in 'validateAddressAoStatsHistorySum' table for that date (validateAddressAoStats table for today's entry where response is "Success").<br>
		Updates: Number of update in 'validateAddressAoStatsHistorySum' table for that date (validateAddressAoStats table for today's entry where substring of response is "updates").<br>
		Failure: Number of failure in 'validateAddressAoStatsHistorySum' table for that date (validateAddressAoStats table for today's entry where substring of response is "Failure").<br>
		</td></tr>
		</table></td></tr></table></td></tr>
	</table>

</td></tr>
</table>
<?php } else { ?>
<table cellpadding=9 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center bordercolor=#000000>
		<tr><td>
		<table cellpadding=9 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=9 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td colspan=9 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=9 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><?php echo $sDateSentHeader;?>
		<td valign=top class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=dateChecked&sDateCheckedOrder=<?php echo $sDateCheckedOrder;?>" class=header>Date / Time</a></td>
		<td valign=top class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=successes&sSuccessOrder=<?php echo $sSuccessOrder;?>" class=header>Successes</a></td>
		<td valign=top class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=updates&sUpdatesOrder=<?php echo $sUpdatesOrder;?>" class=header>Updates</a></td>
		<td valign=top class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=failures&sFailuresOrder=<?php echo $sFailuresOrder;?>" class=header>Failures</a></td>
		<td valign=top class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=errorR&sErrorROrder=<?php echo $sErrorROrder;?>" class=header>Error R<br>Range</a></td>
		<td valign=top class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=errorU&sErrorUOrder=<?php echo $sErrorUOrder;?>" class=header>Error U<br>Street Name</a></td>
		<td valign=top class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=errorAM&sErrorAMOrder=<?php echo $sErrorAMOrder;?>" class=header>Error AM<br>Bad/Invalid</a></td>
		<td valign=top class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=errorX&sErrorXOrder=<?php echo $sErrorXOrder;?>" class=header>Error X<br>Undeliverable</a></td>
		<td valign=top class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=errorT&sErrorTOrder=<?php echo $sErrorTOrder;?>" class=header>Error T<br>Component Failure</a></td>
		<td valign=top class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=errorZ&sErrorZOrder=<?php echo $sErrorZOrder;?>" class=header>Error Z<br>Invalid Zip</a></td>
	</tr>

<?php echo $sReportContent;?>



<tr><td colspan=10 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=10 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=10>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s).<br>
		Clicking on a link to sort record will not work for today's entry.<br>
		Total: This is the total for current page only, not for the entire report.<br>
		Successes: Number of successes in 'validateAddressAoStatsHistorySum' table for that date (validateAddressAoStats table for today's entry where response is "Success").<br>
		Updates: Number of update in 'validateAddressAoStatsHistorySum' table for that date (validateAddressAoStats table for today's entry where substring of response is "updates").<br>
		Failure: Number of failure in 'validateAddressAoStatsHistorySum' table for that date (validateAddressAoStats table for today's entry where substring of response is "Failure").<br>
		Error R Range: Number of address range error in 'validateAddressAoStatsHistorySum' table for that date (validateAddressAoStats table for today's entry where substring of response is "Failure|R|Range Error").<br>
		Error U Street Name: Number of invalid street name in 'validateAddressAoStatsHistorySum' table for that date (validateAddressAoStats table for today's entry where substring of response is "Failure|U|Unknown Street").<br>
		Error AM Bad/Invalid: Number of invalid addresses (Ampere Media Regular Expression Validation Failed) in 'validateAddressAoStatsHistorySum' table for that date (validateAddressAoStats table for today's entry where substring of response is "Failure|AM").<br>
		Error X Undeliverable: Number of undeliverable addresses in 'validateAddressAoStatsHistorySum' table for that date (validateAddressAoStats table for today's entry where substring of response is "Failure|X|Non-Deliverable Address").<br>
		Error T Component Failure: Number of Component Failures in 'validateAddressAoStatsHistorySum' table for that date (validateAddressAoStats table for today's entry where substring of response is "Failure|T|Component Error").<br>
		Error Z Invalid Zip: Number of invalid zip code in 'validateAddressAoStatsHistorySum' table for that date (validateAddressAoStats table for today's entry where substring of response is "Failure|Z|Invalid ZIP/Postal Code").<br>
		</td></tr>
		</table></td></tr></table></td></tr>
	</table>

</td></tr>
</table>
<?php $sDetailsChecked = "checked"; } ?>


<?php if ($sShowRecords) { ?>
<table cellpadding=7 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=85% align=center bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr>
		<td class=header>Date / Time</td>
		<td class=header>Source Code</td>
		<td class=header>Address</td>
		<td class=header>Address2</td>
		<td class=header>City</td>
		<td class=header>State</td>
		<td class=header>Zip</td>
		<td class=header>Response</td>
	</tr>

<?php echo $sReportHistoryContent; ?>

<tr><td colspan=8 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=5 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=8>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s).
		<br>Clicking on a link to sort record will not work for today's entry.<br>
		If report includes data from past and today, then total pages will be incorrect.<br>
		Select all data from validateAddressAoStatsHistory table for the date range of this report (validateAddressAoStats table for today's data).<br>
		This is list of all addresses along with its response of either success, update, or failure.<br>
		
		<br>Response: -<br>
		Success: Address is valid.<br>
		Update: Address is updated and displays both old and new address.<br>
		Failure: Address is invalid and display error code and reason.<br>
		</td></tr>
		</table></td></tr></table></td></tr>
	</table>

</td></tr>
</table>
<?php } ?>

</form>
</body>
</html>
<?php

include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}

?>
