<?php

// Script to Display source analysis report
session_start();

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);

set_time_limit(500000000);
$iScriptStartTime = getMicroTime();

$sPageTitle = "OT Source Analysis Report";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

	$sLinksLink = "<a href='$sGblAdminSiteRoot/linksMgmnt/index.php?iMenuId=$iMenuId'>Links Management</a>";

	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');
	$iCurrHH = date('H');
	$iCurrMM = date('i');
	$iCurrSS = date('s');
	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

	// Show expired links also by default
	if (!($sViewReport)) {
		$sShowExpired = 'Y';
		$sExcludeNonRevenue = 'Y';
		$sTruncateSubSource = 'Y';
	}

	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	$sToday = date('m')."-".date('d')."-".date('Y');
	$sViewReport = stripslashes($sViewReport);

	if ($sExportExcel && $sExportEmails) {
		$sMessage .= "Please check only one export option...";
	} else if ($sAllowReport == 'N') {
		$sMessage .= "Server Load Is High. Please check back soon...";
	} else {

		/***** display today's report by default  ********/
		if (!($sDateFrom || $sDateTo)) {
			$sDateFrom = $sToday;
			$sDateTo = $sToday;
			if (!($sViewReport)) {
				$sViewReport = "Today's Report";
				$sSuppressSubSource='Y';
			}
		}
		/***********/

		/*******  Display all AE's links if stuart or phil accessing the report  ******/
		if (!(isset($iAccountRep)) && $sTrackingUser != 'phil' && $sTrackingUser != 'stuart' && $sTrackingUser != 'spatel') {
			$sUserQuery = "SELECT nbUsers.*
				  FROM   nbUsers
				  WHERE  userName = '$sTrackingUser'";
			$rUserResult = dbQuery($sUserQuery);
			while ($oUserRow = dbFetchObject($rUserResult)) {
				$iAccountRep = $oUserRow->id;
			}
		}
		/*******************/

		if ($sViewReport == "History Report") {
			$iMonthFrom = substr($sDateFrom,0,2);
			$iDayFrom = substr($sDateFrom,3,2);
			$iYearFrom = substr($sDateFrom, 6,4);
			$iMonthTo = substr($sDateTo,0,2);
			$iDayTo = substr($sDateTo,3,2);
			$iYearTo = substr($sDateTo, 6,4);

			if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iMonthTo,$iDayTo,$iYearTo)) >= 0 || $iYearTo=='') {
				$iYearTo = substr( $sYesterday, 0, 4);
				$iMonthTo = substr( $sYesterday, 5, 2);
				$iDayTo = substr( $sYesterday, 8, 2);
			}

			if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iMonthFrom,$iDayFrom,$iYearFrom)) >= 0 || $iYearFrom=='') {
				$iYearFrom = substr( $sYesterday, 0, 4);
				$iMonthFrom = substr( $sYesterday, 5, 2);
				$iDayFrom = "01";
			}

			$sDateFrom = "$iMonthFrom-$iDayFrom-$iYearFrom";
			$sDateTo = "$iMonthTo-$iDayTo-$iYearTo";
		} else if ($sViewReport == "Today's Report") {
			$iYearFrom = date('Y');
			$iMonthFrom = date('m');
			$iDayFrom = date('d');
			$sDateFrom = "$iMonthFrom-$iDayFrom-$iYearFrom";
			$iMonthTo = $iMonthFrom;
			$iDayTo = $iDayFrom;
			$iYearTo = $iYearFrom;
			$sDateTo = "$iMonthTo-$iDayTo-$iYearTo";
		}


		if ($sDateFrom && $sDateTo) {
			$sTempDateFromArray = explode("-", $sDateFrom);
			$sTempDateToArray = explode("-", $sDateTo);
			// tempdates are the dates in mysql format
			$sTempDateFrom = $sTempDateFromArray[2]."-".$sTempDateFromArray[0]."-".$sTempDateFromArray[1];
			$sTempDateTo = $sTempDateToArray[2]."-".$sTempDateToArray[0]."-".$sTempDateToArray[1];
			$sTempDateTimeFrom = $sTempDateFromArray[2]."-".$sTempDateFromArray[0]."-".$sTempDateFromArray[1]." 00:00:00";
			$sTempDateTimeTo = $sTempDateToArray[2]."-".$sTempDateToArray[0]."-".$sTempDateToArray[1]." 23:59:59";
			$sTempToday = date('Y')."-".date('m')."-".date('d');

			// initialize all variables to 0;
			$iUniqueUsers = 0;
			$iUniqueUsersNonPv = 0;
			$iOffersTakenByPvUsers = 0;
			$fAvgOffersTakenPerUser = 0;
			$fGrossRevenue = 0;
			$fGrossPvRevenue = 0;

			// start of track users' activity in nibbles 
			mysql_connect ($host, $user, $pass); 
			mysql_select_db ($dbase); 
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sViewReport, BETWEEN '$sTempDateTimeFrom' AND '$sTempDateTimeTo', selected source code: $sAllSourceCodes, source code: $sSourceCode, subsource code: $sSubSourceCode, filter: $sFilter, account rep: $iAccountRep\")"; 
			$rResult = dbQuery($sAddQuery); 
			mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
			mysql_select_db ($reportingDbase); 
			// end of track users' activity in nibbles		

			/**********  Get source codes for the selected date range to display in selection box *********/
			if ($sTempDateFrom == $sTempToday && $sTempDateTo == $sTempToday) {
				$sSourceCodeQuery = "SELECT distinct sourceCode
						 FROM   otData
						 WHERE  dateTimeAdded between '$sTempDateTimeFrom' AND '$sTempDateTimeTo'
						 ORDER BY sourceCode"; 
			} else {
				$sSourceCodeQuery = "SELECT distinct sourceCode
							 FROM   otDataHistory
							 WHERE  dateTimeAdded between '$sTempDateTimeFrom' AND '$sTempDateTimeTo'
							 ORDER BY sourceCode"; 
			}
			
			$rSourceCodeResult = dbQuery($sSourceCodeQuery);

			$sGetSrcForCampaignType = '';
			while ($oSourceCodeRow = dbFetchObject($rSourceCodeResult)) {
				$sTempSourceCode = $oSourceCodeRow->sourceCode;
				if ($sSourceCode) {
					if ($sTempSourceCode == $sSourceCode) {
						$sSrcSelected = "selected";
					} else {
						$sSrcSelected = "";
					}
				} else {
					if ($sTempSourceCode == $sAllSourceCodes && isset($sAllSourceCodes)) {
						$sSrcSelected = "selected";
					} else {
						$sSrcSelected = "";
					}
				}
				$sSourceCodeOptions .= "<option value='$sTempSourceCode' $sSrcSelected>$sTempSourceCode";
				$sGetSrcForCampaignType .= "'$sTempSourceCode',";
			}
			/*********  End of getting source codes for the selected date range  ********/
			$sGetSrcForCampaignType = substr($sGetSrcForCampaignType,0,strlen($sGetSrcForCampaignType)-1);
			// Start getting campaign types
			$sCampTypeQuery = "SELECT distinct campaignTypeId FROM links
							WHERE sourceCode IN ($sGetSrcForCampaignType)";
			$rCampTypeResult = mysql_query($sCampTypeQuery);
			$sGetCampaignTypeId = '';
			while ($oCampTypeRow = dbFetchObject($rCampTypeResult)) {
				$sGetCampaignTypeId .= "'$oCampTypeRow->campaignTypeId',";
			}
			if ($sGetCampaignTypeId !='') {
				$sGetCampaignTypeId = substr($sGetCampaignTypeId,0,strlen($sGetCampaignTypeId)-1);
				$sGetCampaignTypes = "SELECT * FROM campaignTypes WHERE id IN ($sGetCampaignTypeId) ORDER by campaignType ASC";
				$rGetCampaignTypes = mysql_query($sGetCampaignTypes);
				while ($oCampTypeRow2 = dbFetchObject($rGetCampaignTypes)) {
					if ($oCampTypeRow2->id == $iCampaignType) {
						$sSelected = 'selected';
					} else {
						$sSelected = '';
					}
					$sCampaignTypeOptions .= "<option value='$oCampTypeRow2->id' $sSelected>$oCampTypeRow2->campaignType";
				}
			}
			// End getting campaign types
		}


		// Set Default order column
		if (!($sOrderColumn)) {
			if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
				$sOrderColumn = "dateAdded";
				$sDateAddedOrder = SORT_ASC;
			} else {
				if ($sPartnersSummaryChecked=='checked') {
					$sOrderColumn = "companyName";
					$sCurrOrder = SORT_ASC;
				} else {
					$sOrderColumn = "sourceCode";
					$sCurrOrder = SORT_ASC;
				}
			}
		}

		// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
		if (!($sCurrOrder)) {
			switch ($sOrderColumn) {
				case "subSourceCode":
				$sCurrOrder = $sSubSourceCodeOrder;
				$sSubSourceCodeOrder = ($sSubSourceCodeOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
				break;
				case "e1SubCounts" :
				$sCurrOrder = $sE1SubCountsOrder;
				$sE1SubCountsOrder = ($sE1SubCountsOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
				break;
				case "uniqueUsers" :
				$sCurrOrder = $sUniqueUsersOrder;
				$sUniqueUsersOrder = ($sUniqueUsersOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
				break;
				case "totalRevenue" :
				$sCurrOrder = $sTotalRevenueOrder;
				$sTotalRevenueOrder = ($sTotalRevenueOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
				break;
				case "totalOffersTaken" :
				$sCurrOrder = $sTotalOffersTakenOrder;
				$sTotalOffersTakenOrder = ($sTotalOffersTakenOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
				break;
				case "avgOffersTakenPerUser" :
				$sCurrOrder = $sAvgOffersTakenPerUserOrder;
				$sAvgOffersTakenPerUserOrder = ($sAvgOffersTakenPerUserOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
				break;
				case "accountRep" :
				$sCurrOrder = $sAccountRepOrder;
				$sAccountRepOrder = ($sAccountRepOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
				break;
				case "companyName" :
				$sCurrOrder = $sCompanyNameOrder;
				$sCompanyNameOrder = ($sCompanyNameOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
				break;
				case "iCampaignTypeId" :
				$sCurrOrder = $sCampaignTypeOrder;
				$sCampaignTypeOrder = ($sCampaignTypeOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
				break;
				case "dateAdded" :
				$sCurrOrder = $sDateAddedOrder;
				$sDateAddedOrder = ($sDateAddedOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
				break;
				default:
				$sCurrOrder = $sSourceCodeOrder;
				$sSourceCodeOrder = ($sSourceCodeOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			}
		} else {
			$sCurrOrder = $sCompanyNameOrder;
			$sCompanyNameOrder = ($sCompanyNameOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
		}
		
		if ($sCurrOrder == 'SORT_DESC') {
			$sCurrOrder = SORT_DESC;
		} else {
			$sCurrOrder = SORT_ASC;
		}

		$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sSourceCode=$sSourceCode&iCampaignTypeId=$iCampaignTypeId&sSubSourceCode=$sSubSourceCode&sShowSeparateTotal=$sShowSeparateTotal&sFilter=$sFilter&sSsFilter=$sSsFilter&sPostalVerified=$sPostalVerified&sPartnersSummary=$sPartnersSummary&sSuppressSubSource=$sSuppressSubSource&sShowExpired=$sShowExpired&sTruncateSubSource=$sTruncateSubSource&
					sExcludeNonRevenue=$sExcludeNonRevenue&sDateFrom=$sDateFrom&sDateTo=$sDateTo&iCampaignType=$iCampaignType&iRecPerPage=$iRecPerPage&iAccountRep=$iAccountRep&sViewReport=".urlencode($sViewReport);

		if ($sViewReport) {
			$i=0;

			if (isset($sAllSourceCodes)) {
				if ($sAllSourceCodes != 'All') {
					$iCampaignType = '';
					$iCampaignRateType = '';
				}
			}
			if ($sSourceCode != '') {
				$iCampaignType = '';
				$iCampaignRateType = '';
			}
			/**********  Get the links sourceCodes and put in array with other values initialized  *********/
			// ( There are other sourceCodes also which are not in links table. Like, starting with myf, amp)

			$sSourceCodeQuery = "SELECT links.*, companyName, repDesignated
							 	 FROM   links, partnerCompanies
							  	 WHERE  links.partnerId = partnerCompanies.id";

			if ($sShowExpired != 'Y') {
				$sSourceCodeQuery .= " AND   (expirationDate = '' ||  expirationDate >= CURRENT_DATE) ";
			}
			
			if ($iCampaignRateType !='') {
				$sSourceCodeQuery .= " AND   links.campaignRateTypeId = '$iCampaignRateType' ";
			}
			
			if ($iCampaignType !='') {
				$sSourceCodeQuery .= " AND   links.campaignTypeId = '$iCampaignType' ";
			}

			if ($sSourceCode != '') {
				if ($sFilter == 'startsWith') {
					$sSourceCodeQuery .= " AND links.sourceCode LIKE '".$sSourceCode."%' ";
				} else if ($sFilter == 'exactMatch') {
					$sSourceCodeQuery .= " AND links.sourceCode = '$sSourceCode' ";
				}
			} else if (isset($sAllSourceCodes)) {
				if ($sAllSourceCodes != 'All') {
					$sSourceCodeQuery .= " AND links.sourceCode = '$sAllSourceCodes'";
				}
			}

			$sSourceCodeQuery .= " ORDER BY links.sourceCode";
			//echo $sSourceCodeQuery;
			$rSourceCodeResult = dbQuery($sSourceCodeQuery);

			$i=0;
			if ($rSourceCodeResult) {
				$sSourceCodeIn = '';
				$iSourceCodeInCount = 0;
				while ($oSourceCodeRow = dbFetchObject($rSourceCodeResult)) {
					$sTempSourceCode = $oSourceCodeRow->sourceCode;
					$sCompanyName = $oSourceCodeRow->companyName;
					$sRepDesignated = $oSourceCodeRow->repDesignated;
					$iCampaignTypeId = $oSourceCodeRow->campaignTypeId;
					
					$sCampTypeQuery1 = "SELECT * FROM campaignTypes WHERE id='$iCampaignTypeId'";
					$rCampTypeResult1 = mysql_query($sCampTypeQuery1);
					while ($oCampTypeRow1 = mysql_fetch_object($rCampTypeResult1)) {
						$iCampaignTypeId = $oCampTypeRow1->campaignType;
					}
					

					$sPartnerRep = '';
					if ($sRepDesignated != '') {
						$sRepQuery = "SELECT userName
						  FROM   nbUsers
						  WHERE  id IN (".$sRepDesignated.")";
						$rRepResult = dbQuery($sRepQuery);
						echo dbError();
						while ($oRepRow = dbFetchObject($rRepResult)) {
							$sPartnerRep .= $oRepRow->userName.",";
						}

						if ($sPartnerRep != '') {
							$sPartnerRep = substr($sPartnerRep,0,strlen($sPartnerRep)-1);
						}
					}


					if (strtoupper(substr($sTempSourceCode,0,3))  == 'MYF') {
						$sCompanyName = "MyFree";
						$sPartnerRep = "phil";
					}
		
					// prepare report only if rep designated is selected from the selection ( or all reps)
					$aReportArray['partnerRep'][$i] = $sPartnerRep;
					$aReportArray['sourceCode'][$i] = $sTempSourceCode;
					$aReportArray['companyName'][$i] = $sCompanyName;
					if ($iCampaignTypeId === 0 || $iCampaignTypeId === '0') { $iCampaignTypeId = ''; }
					$aReportArray['iCampaignTypeId'][$i] = $iCampaignTypeId;
					

					// initialize other array elements of aReportArray to keep array counts same
					$aReportArray['e1SubCounts'][$i] = '';
					$aReportArray['subSourceCode'][$i] = '';
					$aReportArray['uniqueUsers'][$i] = '';
					$aReportArray['totalOffersTaken'][$i] = '';
					$aReportArray['avgOffersTakenPerUser'][$i] = '';
					$aReportArray['totalRevenue'][$i] = '';

					if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
						$aReportArray['dateAdded'][$i] = '';
					}
					$i++;
					
					
					$sSourceCodeIn .= "'$sTempSourceCode',";
					$iSourceCodeInCount++;
					
				} // end of sourceCode while loop
				dbFreeResult($rSourceCodeResult);
				if ($sSourceCodeIn !='') {
					$sSourceCodeIn = substr($sSourceCodeIn,0,strlen($sSourceCodeIn)-1);
				}
				if ($iSourceCodeInCount == 0) {
					$sSourceCodeIn = '';
				}
			}
			/**********  End of getting all the links source codes  **********/

			/********  get gross e1 sub counts and put into report array for matching sourceCode
			If matching sourceCode not found, add new array entry for the missing sourceCode and put e1 counts
			and initialize other arry values  ********/
			$iE1SubCounts = 0;
			$sE1CountsQuery = "SELECT sourceCode, sum(subs) AS totalSubs
								   FROM   eTrackingSum
								   WHERE  submitDate BETWEEN '$sTempDateFrom' AND '$sTempDateTo'";
			
			if ($sFilter == 'exactMatch') {
				$sE1CountsQuery = "SELECT sourceCode, subs AS totalSubs
								   FROM   eTrackingSum
								   WHERE  submitDate BETWEEN '$sTempDateFrom' AND '$sTempDateTo'";
			}
			
			if ($sSourceCode != '') {
				if ($sFilter == 'startsWith') {
					$sE1CountsQuery .= " AND sourceCode LIKE '".$sSourceCode."%' ";
				} else if ($sFilter == 'exactMatch') {
					$sE1CountsQuery .= " AND sourceCode = '$sSourceCode' ";
				}
			} else if (isset($sAllSourceCodes)) {
				if ($sAllSourceCodes != 'All') {
					$sE1CountsQuery .= " AND sourceCode = '$sAllSourceCodes'";
				}
			}
			
			if (($iCampaignRateType !='' || $iCampaignType !='') && $sSourceCodeIn !='') {
				$sE1CountsQuery .= " AND   sourceCode IN ($sSourceCodeIn) ";
			}

			if ($sFilter == 'exactMatch') {
				$sE1CountsQuery .= " GROUP BY submitDate";
			} else {
				$sE1CountsQuery .= " GROUP BY sourceCode";
			}

			$rE1CountsResult = dbQuery($sE1CountsQuery);
			echo dbError();
			$i=0;
			while ($oE1CountsRow = dbFetchObject($rE1CountsResult)) {
				$sTempSourceCode = $oE1CountsRow->sourceCode;
				$iTempE1SubCounts = $oE1CountsRow->totalSubs;
				$sRepDesignated = '';
				$sCompanyName = '';
				$iCampaignTypeId = '';
				$sPartnerRep = '';
				//  check if campaign is expired
				if ($sShowExpired != 'Y') {
					$sTempCheckQuery = "SELECT *
							  FROM   links
							  WHERE  sourceCode = '$sTempSourceCode'
							  AND   (expirationDate = '' ||  expirationDate >= CURRENT_DATE)";

					$rTempCheckResult = dbQuery($sTempCheckQuery);
					echo dbError();
					if ( dbNumRows($rTempCheckResult) == 0 ) {
						continue;
					}
				}

				// get partner rep
				$sCompanyQuery = "SELECT companyName, repDesignated, campaignTypeId
							  FROM   links, partnerCompanies
							  WHERE  links.partnerId = partnerCompanies.id
							  AND	 links.sourceCode = '$sTempSourceCode'";
				$rCompanyResult = dbQuery($sCompanyQuery);
				echo dbError();

				while ($oCompanyRow = dbFetchObject($rCompanyResult)) {
					$sCompanyName = $oCompanyRow->companyName;
					$sRepDesignated = $oCompanyRow->repDesignated;
					$iCampaignTypeId = $oCompanyRow->campaignTypeId;

					$sCampTypeQuery1 = "SELECT * FROM campaignTypes WHERE id='$iCampaignTypeId'";
					$rCampTypeResult1 = mysql_query($sCampTypeQuery1);
					while ($oCampTypeRow1 = mysql_fetch_object($rCampTypeResult1)) {
						$iCampaignTypeId = $oCampTypeRow1->campaignType;
					}
				}


				if ($sRepDesignated != '') {
					$sRepQuery = "SELECT userName
						  FROM   nbUsers
						  WHERE  id IN (".$sRepDesignated.")";
					$rRepResult = dbQuery($sRepQuery);
					echo dbError();
					while ($oRepRow = dbFetchObject($rRepResult)) {
						$sPartnerRep .= $oRepRow->userName.",";
					}

					if ($sPartnerRep != '') {
						$sPartnerRep = substr($sPartnerRep,0,strlen($sPartnerRep)-1);
					}
				}

				if (strtoupper(substr($sTempSourceCode,0,3))  == 'MYF') {
					$sCompanyName = "MyFree";
					$sPartnerRep = "phil";
				}

				$sTempAccountRep = "'".$iAccountRep."'";
				// prepare report only if rep designated is selected from the selection ( or all reps)
				if ($iAccountRep == '' || strstr($sRepDesignated,$sTempAccountRep)) {
					$aE1CountsSourceCodeArray['sourceCode'][$i] = $sTempSourceCode;
					$aE1CountsSourceCodeArray['companyName'][$i] = $sCompanyName;
					if ($iCampaignTypeId === 0 || $iCampaignTypeId === '0') { $iCampaignTypeId = ''; }
					$aE1CountsSourceCodeArray['iCampaignTypeId'][$i] = $iCampaignTypeId;
					$aE1CountsSourceCodeArray['partnerRep'][$i] = $sPartnerRep;
					$aE1CountsSourceCodeArray['e1SubCounts'][$i] = $oE1CountsRow->totalSubs;
					$i++;
				}
			}
			/*********  End of getting e1 counts  *********/
			$sRepDesignated = '';
			$sCompanyName = '';
			$iCampaignTypeId = '';
			$sPartnerRep = '';

			if ($sViewReport != "Today's Report") {
				// get offers taken info and put values into offersTakenSourceCodeArray
				$sReportQuery = "SELECT otDataHistory.sourceCode, ";

				if ($sSuppressSubSource != 'Y' ) {
					$sReportQuery .= "otDataHistory.subSourceCode, ";
				}

				$sReportQuery .= " count(distinct otDataHistory.email) AS uniqueUsers,
								count(otDataHistory.email) AS totalOffersTaken, sum(otDataHistory.revPerLead) as totalRevenue";

				if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
					$sReportQuery .= ",date_format(otDataHistory.dateTimeAdded,'%Y-%m-%d') AS dateAdded ";
				}
				$sReportQuery .= " FROM   otDataHistory
						 WHERE  otDataHistory.dateTimeAdded BETWEEN '$sTempDateTimeFrom' AND '$sTempDateTimeTo'";	

				if ($sSourceCode != '') {
					if ($sFilter == 'startsWith') {
						$sReportQuery .= " AND otDataHistory.sourceCode LIKE '".$sSourceCode."%' ";
					} else if ($sFilter == 'exactMatch') {
						$sReportQuery .= " AND otDataHistory.sourceCode = '$sSourceCode' ";
					}
				} else if (isset($sAllSourceCodes)) {
					if ($sAllSourceCodes != 'All') {
						$sReportQuery .= " AND otDataHistory.sourceCode = '$sAllSourceCodes'";
					}
				}
				
				if (($iCampaignRateType !='' || $iCampaignType !='') && $sSourceCodeIn !='') {
					$sReportQuery .= " AND   sourceCode IN ($sSourceCodeIn) ";
				}

				if ($sSubSourceCode != '') {
					if ($sSsFilter == 'startsWith') {
						$sReportQuery .= " AND otDataHistory.subSourceCode LIKE '".$sSubSourceCode."%' ";
					} else if ($sSsFilter == 'exactMatch') {
						$sReportQuery .= " AND otDataHistory.subSourceCode = '$sSubSourceCode' ";
					}
				}

				if ($sPostalVerified == 'pvOnly') {
					$sReportQuery .= " AND postalVerified = 'V' AND processStatus = 'P'";
				} else {
					$sReportQuery .= " AND postalVerified IS NOT NULL ";
				}

				if ($sExcludeNonRevenue == 'Y') {
					$sReportQuery .= " AND otDataHistory.revPerLead != 0 ";
				}

				$sReportQuery .=  " GROUP BY sourceCode";

				if ($sSuppressSubSource != 'Y' ) {
					$sReportQuery .= ", subSourceCode ";
				}

				if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
					$sReportQuery .= ", dateAdded ";
				}
				$sReportQuery .="  ORDER BY sourceCode, subSourceCode";

				if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
					$sReportQuery .= ", dateAdded";
				}

				$rReportResult = dbQuery($sReportQuery);
				echo  dbError();
				$i=0;
				while ($oReportRow = dbFetchObject($rReportResult)) {
					$sTempSourceCode = $oReportRow->sourceCode;
					$sTempSubSourceCode = $oReportRow->subSourceCode;
					$iUniqueUsers = $oReportRow->uniqueUsers;
					$iTotalOffersTaken= $oReportRow->totalOffersTaken;
					$fTotalRevenue = $oReportRow->totalRevenue;

					$fAvgOffersTakenPerUser = $iTotalOffersTaken / $iUniqueUsers; // $iTotalOffersTaken / $iUniqueUsers ;
					$fAvgOffersTakenPerUser = sprintf("%10.2f",round($fAvgOffersTakenPerUser, 2));
					$sDateAdded = $oReportRow->dateAdded;

					/*********  Get account rep. Id with quotes around it if it's not the same as previous loop *******/
					// samir patel 9/25/06 9:15am.  commented out below if condition because it was showing same campaign type for
					// the whole company even the source code says different campaign type
					//if ($sPrevSourcePrefix != substr($sTempSourceCode,0,3) || $sCompanyName=="") {
						$sCompanyName = '';
						$sPartnerRep = '';
						$sRepDesignated = '';

						// get partner rep
						$sCompanyQuery = "SELECT companyName, repDesignated, campaignTypeId
							  FROM   links, partnerCompanies
							  WHERE  links.partnerId = partnerCompanies.id
							  AND	 links.sourceCode = '$sTempSourceCode'";

						$rCompanyResult = dbQuery($sCompanyQuery);
						echo dbError();
						if ( dbNumRows($rCompanyResult) > 0) {
							if ($sShowExpired != 'Y') {
								$sTempCheckQuery = "SELECT *
							  FROM   links
							  WHERE  sourceCode = '$sTempSourceCode'
							  AND   (expirationDate = '' ||  expirationDate >= CURRENT_DATE)";
								$rTempCheckResult = dbQuery($sTempCheckQuery);
								echo dbError();
								if ( dbNumRows($rTempCheckResult) == 0 ) {
									continue;
								}
							}
						}
						
						
						$iCampaignTypeId = '';
						while ($oCompanyRow = dbFetchObject($rCompanyResult)) {
							$sCompanyName = $oCompanyRow->companyName;
							$sRepDesignated = $oCompanyRow->repDesignated;
							$iCampaignTypeId = $oCompanyRow->campaignTypeId;
							
							$sCampTypeQuery1 = "SELECT * FROM campaignTypes WHERE id='$iCampaignTypeId'";
							$rCampTypeResult1 = mysql_query($sCampTypeQuery1);
							while ($oCampTypeRow1 = mysql_fetch_object($rCampTypeResult1)) {
								$iCampaignTypeId = $oCampTypeRow1->campaignType;
							}
						}
						
					//	if ($sTempSourceCode == 'bfntb062105060') {
					//		echo $iCampaignTypeId.": $i<br>";
					//	}

						if ($sRepDesignated != '') {
							$sRepQuery = "SELECT userName
						  FROM   nbUsers
						  WHERE  id IN (".$sRepDesignated.")";
							$rRepResult = dbQuery($sRepQuery);
							echo dbError();
							while ($oRepRow = dbFetchObject($rRepResult)) {
								$sPartnerRep .= $oRepRow->userName.",";
							}

							if ($sPartnerRep != '') {
								$sPartnerRep = substr($sPartnerRep,0,strlen($sPartnerRep)-1);
							}
						}

						if (strtoupper(substr($sTempSourceCode,0,3))  == 'MYF') {
							$sCompanyName = "MyFree";
							$sPartnerRep = "phil";
						}
					//}
					
				//	if ($sTempSourceCode == 'bfntb062105060') {
				//		echo $iCampaignTypeId."::: $i<br>";
				//	}
					$sTempAccountRep = "'".$iAccountRep."'";
					/*******  End getting account rep id.  ********/

					/*********  Add data into report array only if rep designated is selected from the selection ( or all reps)  *********/
					if ($iAccountRep == '' || strstr($sRepDesignated,$sTempAccountRep)) {
						// store sourceCode in an array with numeric key to merge with sourceCode array
						// this will be merged to sourceCode array to get myf and amp sourceCodes
						// because myf and amp sourcecodes are not in links table
						$aOffersTakenSourceCodeArray['sourceCode'][$i] = $sTempSourceCode;
						$aOffersTakenSourceCodeArray['subSourceCode'][$i] = $sTempSubSourceCode;
						$aOffersTakenSourceCodeArray['companyName'][$i] = $sCompanyName;
						
						if ($iCampaignTypeId === 0 || $iCampaignTypeId === '0') { $iCampaignTypeId = ''; }
						$aOffersTakenSourceCodeArray['iCampaignTypeId'][$i] = $iCampaignTypeId;
						$aOffersTakenSourceCodeArray['partnerRep'][$i] = $sPartnerRep;
						$aOffersTakenSourceCodeArray['uniqueUsers'][$i] = $iUniqueUsers;
						$aOffersTakenSourceCodeArray['totalOffersTaken'][$i] = $iTotalOffersTaken;
						$aOffersTakenSourceCodeArray['avgOffersTakenPerUser'][$i] = $fAvgOffersTakenPerUser;
						$aOffersTakenSourceCodeArray['totalRevenue'][$i] = $fTotalRevenue;

						if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
							$aOffersTakenSourceCodeArray['dateAdded'][$i] = $oReportRow->dateAdded;
						}

						$sPrevSourcePrefix = substr($sTempSourceCode,0,3);

						$iUniqueUsersDup = 0;
						$iUniqueUsersNpv = 0;
						$iUniqueUsersNcv = 0;
						/**********  Get other counts to display if 'full details' is checked  *********/

						if ($sPostalVerified == 'fullDetails') {
							/**********  Get dup counts  **********/
							$sDupsQuery = "SELECT distinct otDataHistory.email AS uniqueUser ";

							if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
								$sDupsQuery .= ",date_format(otDataHistory.dateTimeAdded,'%Y-%m-%d') AS dateAdded ";
							}

							$sDupsQuery .= " FROM   otDataHistory
									     WHERE  otDataHistory.sourceCode = '$sTempSourceCode'";
							if ($sSuppressSubSource != 'Y' ) {
								$sDupsQuery .= " AND	  otDataHistory.subSourceCode = '$sTempSubSourceCode'";
							}

							if ($sExcludeNonRevenue == 'Y') {
								$sDupsQuery .= " AND otDataHistory.revPerLead != 0 ";
							}

							if ($sDateAdded != '') {
								$sTempDateTimeFromAdded = $sDateAdded." 00:00:00";
								$sTempDateTimeToAdded = $sDateAdded." 23:59:59";
								$sDupsQuery .= " AND dateTimeAdded BETWEEN  '$sTempDateTimeFromAdded' AND '$sTempDateTimeToAdded' ";
							} else {
								$sDupsQuery .= " AND otDataHistory.dateTimeAdded BETWEEN '$sTempDateTimeFrom' AND '$sTempDateTimeTo' ";
							}

							$sDupsQuery .= " AND processStatus = 'R' and reasonCode = 'dup' ";
							
							if (($iCampaignRateType !='' || $iCampaignType !='') && $sSourceCodeIn !='') {
								$sDupsQuery .= " AND   sourceCode IN ($sSourceCodeIn) ";
							}

							$rDupsResult = dbQuery($sDupsQuery);
							echo dbError();
							while ($oDupsRow = dbFetchObject($rDupsResult)) {
								$sUniqueUser = $oDupsRow->uniqueUser;

								/**********  IMPORTANT  ***********/
								/*  User is counted into dups counts only if user has not taken any valid offers  ********/
								$sCheckQuery = eregi_replace(" AND processStatus = 'R' and reasonCode = 'dup'", " AND processStatus = 'P'", $sDupsQuery);
								$sCheckQuery .= " AND email = '$sUniqueUser'";
								$rCheckResult = dbQuery($sCheckQuery);

								if ( dbNumRows($rCheckResult) == 0 ) {
									$iUniqueUsersDup++;
								}
							}
							/***********  End of getting dup counts  ***********/

							/***********  Get Not postal verified counts  **********/

							$sNpvQuery = "SELECT count(distinct otDataHistory.email) AS uniqueUsersNpv ";
							if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
								$sNpvQuery .= ",date_format(otDataHistory.dateTimeAdded,'%Y-%m-%d') AS dateAdded ";
							}

							$sNpvQuery .= " FROM   otDataHistory
									     WHERE  otDataHistory.sourceCode = '$sTempSourceCode'";

							if ($sSuppressSubSource != 'Y' ) {
								$sNpvQuery .= " AND	  otDataHistory.subSourceCode = '$sTempSubSourceCode'";
							}
							
							if (($iCampaignRateType !='' || $iCampaignType !='') && $sSourceCodeIn !='') {
								$sNpvQuery .= " AND   sourceCode IN ($sSourceCodeIn) ";
							}

							if ($sExcludeNonRevenue == 'Y') {
								$sNpvQuery .= " AND otDataHistory.revPerLead != 0 ";
							}

							if ($sDateAdded != '') {
								$sTempDateTimeFromAdded = $sDateAdded." 00:00:00";
								$sTempDateTimeToAdded = $sDateAdded." 23:59:59";
								$sNpvQuery .= " AND dateTimeAdded BETWEEN  '$sTempDateTimeFromAdded' AND '$sTempDateTimeToAdded' ";
							} else {
								$sNpvQuery .= " AND otDataHistory.dateTimeAdded BETWEEN '$sTempDateTimeFrom' AND '$sTempDateTimeTo' ";
							}

							$sNpvQuery .= " AND processStatus = 'R' and reasonCode = 'npv' ";
							if ($sDateAdded != '') {
								$sNpvQuery .= " GROUP BY dateAdded ";
							}
							$rNpvResult = dbQuery($sNpvQuery);
							echo dbError();
							while ($oNpvRow = dbFetchObject($rNpvResult)) {
								$iUniqueUsersNpv = $oNpvRow->uniqueUsersNpv;
							}
							/***********  End of getting Not postal verified counts  *************/


							/************  Get not custom verified counts  ************/

							$sNcvQuery = eregi_replace(" AND processStatus = 'R' and reasonCode = 'dup'", " AND processStatus = 'R' and reasonCode = 'ncv'", $sDupsQuery);
							$rNcvResult = dbQuery($sNcvQuery);
							echo dbError();
							while ($oNcvRow = dbFetchObject($rNcvResult)) {
								$sUniqueUser = $oDupsRow->uniqueUser;

								/**********  IMPORTANT  ***********/
								/*  User is counted into ncv counts only if user has not taken any valid offers  ********/
								$sCheckQuery = eregi_replace(" AND processStatus = 'R' and reasonCode = 'ncv'", " AND processStatus = 'P'", $sDupsQuery);
								$sCheckQuery .= " AND email = '$sUniqueUser'";
								$rCheckResult = dbQuery($sCheckQuery);
								if ( dbNumRows($rCheckResult) == 0 ) {
									$iUniqueUsersNcv++;
								}
							}
							/***********  End of getting not custom verified counts  ***********/


							/***********  Get net unique users counts  ***********/
							$sNetQuery = "SELECT count(distinct otDataHistory.email) AS uniqueUsersNet,
											 count(otDataHistory.email) AS totalOffersTaken, sum(otDataHistory.revPerLead) as totalRevenue ";

							if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
								$sNetQuery .= ",date_format(otDataHistory.dateTimeAdded,'%Y-%m-%d') AS dateAdded ";
							}

							$sNetQuery .= " FROM   otDataHistory
									     WHERE  otDataHistory.sourceCode = '$sTempSourceCode'";
							
							if (($iCampaignRateType !='' || $iCampaignType !='') && $sSourceCodeIn !='') {
								$sNetQuery .= " AND   sourceCode IN ($sSourceCodeIn) ";
							}

							if ($sSuppressSubSource != 'Y' ) {
								$sNetQuery .= " AND	  otDataHistory.subSourceCode = '$sTempSubSourceCode'";
							}

							if ($sExcludeNonRevenue == 'Y') {
								$sNetQuery .= " AND otDataHistory.revPerLead != 0 ";
							}

							if ($sDateAdded != '') {
								$sTempDateTimeFromAdded = $sDateAdded." 00:00:00";
								$sTempDateTimeToAdded = $sDateAdded." 23:59:59";
								$sNetQuery .= " AND dateTimeAdded BETWEEN  '$sTempDateTimeFromAdded' AND '$sTempDateTimeToAdded' ";
							} else {
								$sDupsQuery .= " AND otDataHistory.dateTimeAdded BETWEEN '$sTempDateTimeFrom' AND '$sTempDateTimeTo' ";
							}

							$sNetQuery .= " AND postalVerified = 'V' AND processStatus = 'P' ";
							if ($sDateAdded != '') {
								$sNetQuery .= " GROUP BY dateAdded ";
							}
							$rNetResult = dbQuery($sNetQuery);
							while ($oNetRow = dbFetchObject($rNetResult)) {
								$iUniqueUsersNet = $oNetRow->uniqueUsersNet;

								/***************** IMPORTANT ******************/
								// Validated Offers taken and Net revenue should be displayed if "Gross details" is selected
								// This can't be done in main report query because GROSS unique users count
								// is required there
								// replace offers taken and revenue with validated counts/values here.

								$iTotalOffersTaken= $oNetRow->totalOffersTaken;
								$fTotalRevenue = $oNetRow->totalRevenue;
								$fAvgOffersTakenPerUser = $iTotalOffersTaken / $iUniqueUsers; // $iTotalOffersTaken / $iUniqueUsers ;
								$fAvgOffersTakenPerUser = sprintf("%10.2f",round($fAvgOffersTakenPerUser, 2));
							}
							/**************  End of getting net users count  ***********/

							// put the count values into offersTakenSourceCodearray
							$aOffersTakenSourceCodeArray['uniqueUsersDup'][$i] = $iUniqueUsersDup;
							$aOffersTakenSourceCodeArray['uniqueUsersNpv'][$i] = $iUniqueUsersNpv;
							$aOffersTakenSourceCodeArray['uniqueUsersNcv'][$i] = $iUniqueUsersNcv;
							$aOffersTakenSourceCodeArray['uniqueUsersNet'][$i] = $iUniqueUsersNet;
							$aOffersTakenSourceCodeArray['totalOffersTaken'][$i] = $iTotalOffersTaken;
							$aOffersTakenSourceCodeArray['avgOffersTakenPerUser'][$i] = $fAvgOffersTakenPerUser;
							$aOffersTakenSourceCodeArray['totalRevenue'][$i] = $fTotalRevenue;
						} // end of full details queries
						/**********  End of getting other counts to display if 'full details' is checked  *********/
						$i++;
					}
					/*********  End of adding data into report array only if rep designated is selected from the selection ( or all reps)  *********/
				} // end of offers taken while loop
			//	echo $aOffersTakenSourceCodeArray['iCampaignTypeId'][98];
				// end if not today's report
			}  else {
				// today's report
				// get offers taken info
				$sReportQuery = "SELECT otData.sourceCode, ";

				if ($sSuppressSubSource != 'Y' ) {
					$sReportQuery .= "otData.subSourceCode, ";
				}

				$sReportQuery .= " count(distinct otData.email) AS uniqueUsers,
								count(otData.email) AS totalOffersTaken, sum(otData.revPerLead) as totalRevenue";

				if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
					$sReportQuery .= ",date_format(otData.dateTimeAdded,'%Y-%m-%d') AS dateAdded ";
				}
				$sReportQuery .= " FROM  userData, otData, offers
						 WHERE  userData.email = otData.email
						 AND	otData.offerCode	= offers.offerCode
						 AND    otData.dateTimeAdded BETWEEN '$sTempDateTimeFrom' AND '$sTempDateTimeTo'";	
				if ($sIncludeTestLeads != 'Y') {
					$sReportQuery .= " AND 	address NOT LIKE \"3401 DUNDEE%\"" ;
				}

				if ($sSourceCode != '') {
					if ($sFilter == 'startsWith') {
						$sReportQuery .= " AND otData.sourceCode LIKE '".$sSourceCode."%' ";
					} else if ($sFilter == 'exactMatch') {
						$sReportQuery .= " AND otData.sourceCode = '$sSourceCode' ";
					}
				} else if (isset($sAllSourceCodes)) {
					if ($sAllSourceCodes != 'All') {
						$sReportQuery .= " AND otData.sourceCode = '$sAllSourceCodes'";
					}
				}

				if ($sSubSourceCode != '') {
					if ($sSsFilter == 'startsWith') {
						$sReportQuery .= " AND otData.subSourceCode LIKE '".$sSubSourceCode."%' ";
					} else if ($sSsFilter == 'exactMatch') {
						$sReportQuery .= " AND otData.subSourceCode = '$sSubSourceCode' ";
					}
				}
				
				if (($iCampaignRateType !='' || $iCampaignType !='') && $sSourceCodeIn !='') {
					$sReportQuery .= " AND   sourceCode IN ($sSourceCodeIn) ";
				}

				if ($sExcludeNonRevenue == 'Y') {
					$sReportQuery .= " AND offers.isNonRevenue != '1' ";
				}

				$sReportQuery .=  "GROUP BY sourceCode";

				if ($sSuppressSubSource != 'Y' ) {
					$sReportQuery .= ", subSourceCode ";
				}

				if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
					$sReportQuery .= ", dateAdded ";
				}

				$sReportQuery .="  ORDER BY sourceCode, subSourceCode";

				if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
					$sReportQuery .= ", dateAdded";
				}

				$rReportResult = dbQuery($sReportQuery);
				echo  dbError();
				$i=0;
				while ($oReportRow = dbFetchObject($rReportResult)) {
					$sTempSourceCode = $oReportRow->sourceCode;
					$sTempSubSourceCode = $oReportRow->subSourceCode;
					$iUniqueUsers = $oReportRow->uniqueUsers;
					$iTotalOffersTaken= $oReportRow->totalOffersTaken;
					$fTotalRevenue = $oReportRow->totalRevenue;
					$fAvgOffersTakenPerUser = $iTotalOffersTaken / $iUniqueUsers; // $iTotalOffersTaken / $iUniqueUsers ;
					$fAvgOffersTakenPerUser = sprintf("%10.2f",round($fAvgOffersTakenPerUser, 2));

					if ($sPrevSourcePrefix != substr($sTempSourceCode,0,3)  || $sCompanyName=="") {
						$sCompanyName = '';
						$iCampaignTypeId = '';
						$sPartnerRep = '';
						$sRepDesignated = '';

						// get partner rep
						$sCompanyQuery = "SELECT companyName, repDesignated, campaignTypeId
							  FROM   links, partnerCompanies
							  WHERE  links.partnerId = partnerCompanies.id
							  AND	 links.sourceCode = '$sTempSourceCode'";
						$rCompanyResult = dbQuery($sCompanyQuery);

						if ( dbNumRows($rCompanyResult) > 0) {
							if ($sShowExpired != 'Y') {
								$sTempCheckQuery = "SELECT *
							  FROM   links
							  WHERE  sourceCode = '$sTempSourceCode'
							  AND   (expirationDate = '' ||  expirationDate >= CURRENT_DATE)";

								$rTempCheckResult = dbQuery($sTempCheckQuery);
								echo dbError();
								if ( dbNumRows($rTempCheckResult) == 0 ) {
									continue;
								}
							}
						}
						while ($oCompanyRow = dbFetchObject($rCompanyResult)) {
							$sCompanyName = $oCompanyRow->companyName;
							$sRepDesignated = $oCompanyRow->repDesignated;
							$iCampaignTypeId = $oCompanyRow->campaignTypeId;
							
							$sCampTypeQuery1 = "SELECT * FROM campaignTypes WHERE id='$iCampaignTypeId'";
							$rCampTypeResult1 = mysql_query($sCampTypeQuery1);
							while ($oCampTypeRow1 = mysql_fetch_object($rCampTypeResult1)) {
								$iCampaignTypeId = $oCampTypeRow1->campaignType;
							}
							
						}
						
						if ($sRepDesignated != '') {
							$sRepQuery = "SELECT userName
						  FROM   nbUsers
						  WHERE  id IN (".$sRepDesignated.")";
							$rRepResult = dbQuery($sRepQuery);
							echo dbError();
							while ($oRepRow = dbFetchObject($rRepResult)) {
								$sPartnerRep .= $oRepRow->userName.",";
							}

							if ($sPartnerRep != '') {
								$sPartnerRep = substr($sPartnerRep,0,strlen($sPartnerRep)-1);
							}
						}

						if (strtoupper(substr($sTempSourceCode,0,3))  == 'MYF') {
							$sCompanyName = "MyFree";
							$sPartnerRep = "phil";
						}
					}
					$sTempAccountRep = "'".$iAccountRep."'";
					// prepare report only if rep designated is selected from the selection ( or all reps)
					if ($iAccountRep == '' || strstr($sRepDesignated,$sTempAccountRep)) {

						// store sourceCode in an array with numeric key to merge with sourceCode array
						// this will be merged to sourceCode array to get myf and amp sourceCodes
						// because myf and amp sourcecodes are not in links table
						$aOffersTakenSourceCodeArray['sourceCode'][$i] = $sTempSourceCode;
						$aOffersTakenSourceCodeArray['subSourceCode'][$i] = $sTempSubSourceCode;
						$aOffersTakenSourceCodeArray['companyName'][$i] = $sCompanyName;
						
						if ($iCampaignTypeId === 0 || $iCampaignTypeId === '0') { $iCampaignTypeId = ''; }
						$aOffersTakenSourceCodeArray['iCampaignTypeId'][$i] = $iCampaignTypeId;
						
						$aOffersTakenSourceCodeArray['partnerRep'][$i] = $sPartnerRep;
						$aOffersTakenSourceCodeArray['uniqueUsers'][$i] = $iUniqueUsers;
						$aOffersTakenSourceCodeArray['totalOffersTaken'][$i] = $iTotalOffersTaken;
						$aOffersTakenSourceCodeArray['avgOffersTakenPerUser'][$i] = $fAvgOffersTakenPerUser;
						$aOffersTakenSourceCodeArray['totalRevenue'][$i] = $fTotalRevenue;

						if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
							$aOffersTakenSourceCodeArray['dateAdded'][$i] = $oReportRow->dateAdded;
						}

						$sPrevSourcePrefix = substr($sTempSourceCode,0,3);

						$i++;
					}
				} // end of today's offers taken while loop
			} // end of else -- (today's offers taken)

			// if searched for src and subSourceCode not in links table, make empty array components
			// to push values into from offers taken array, otherwise array_search and array_push will give error
			$aReportArray['sourceCode'] = array();
			$aReportArray['subSourceCode'] = array();
			$aReportArray['companyName'] = array();
			$aReportArray['iCampaignTypeId'] = array();
			$aReportArray['partnerRep'] = array();
			$aReportArray['e1SubCounts'] = array();
			$aReportArray['uniqueUsers'] = array();
			$aReportArray['totalOffersTaken'] = array();
			$aReportArray['avgOffersTakenPerUser'] = array();
			$aReportArray['totalRevenue'] = array();
			$aReportArray['dateAdded'] = array();
			$aReportArray['uniqueUsersDup'] = array();
			$aReportArray['uniqueUsersNpv'] = array();
			$aReportArray['uniqueUsersNcv'] = array();
			$aReportArray['uniqueUsersNet'] = array();
			
			//print_r($aE1CountsSourceCodeArray['e1SubCounts']);
			
			/**********  Loop through e1 counts array and make and entry in array if sourceCode doesn't exist  ********/
			// update the e1 counts in the report array if matching sourceCode exists
			for ($i=0;$i< count($aE1CountsSourceCodeArray['sourceCode']);$i++) {
				$iTempKey = '';
				if (count($aReportArray['sourceCode']) >0 ) {
					$iTempKey = array_search($aE1CountsSourceCodeArray['sourceCode'][$i], $aReportArray['sourceCode']);
				}

				if (!(is_numeric($iTempKey) && $aReportArray['subSourceCode'][$iTempKey] == '')) {
					array_push($aReportArray['sourceCode'], $aE1CountsSourceCodeArray['sourceCode'][$i]);
					array_push($aReportArray['subSourceCode'], '');
					array_push($aReportArray['companyName'], $aE1CountsSourceCodeArray['companyName'][$i]);
					array_push($aReportArray['iCampaignTypeId'], $aE1CountsSourceCodeArray['iCampaignTypeId'][$i]);
					array_push($aReportArray['partnerRep'], $aE1CountsSourceCodeArray['partnerRep'][$i]);
					array_push($aReportArray['e1SubCounts'], $aE1CountsSourceCodeArray['e1SubCounts'][$i]);
					array_push($aReportArray['uniqueUsers'], '');
					array_push($aReportArray['totalOffersTaken'], '');
					array_push($aReportArray['avgOffersTakenPerUser'], '');
					array_push($aReportArray['totalRevenue'], '');

					if ($sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
						array_push($aReportArray['uniqueUsersDup'],'');
						array_push($aReportArray['uniqueUsersNpv'], '');
						array_push($aReportArray['uniqueUsersNcv'], '');
						array_push($aReportArray['uniqueUsersNet'], '');
					}

					if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
						array_push($aReportArray['dateAdded'],'');
					}
				} else {
					$aReportArray['e1SubCounts'][$iTempKey] = $aE1CountsSourceCodeArray['e1SubCounts'][$i];
				}
			}
				$sTemp = count($aOffersTakenSourceCodeArray['sourceCode']);



			// below if condition added because on the report, the e1Captures wan't matching with it's date - it was off by a day
			// that's because e1Capture count was more than $aOffersTakenSourceCodeArray count
			if (count($aE1CountsSourceCodeArray['e1SubCounts']) > count($aOffersTakenSourceCodeArray['sourceCode'])) {
				$iExtra = count($aE1CountsSourceCodeArray['e1SubCounts']) - count($aOffersTakenSourceCodeArray['sourceCode']);
				for ($ii = 0; $ii < $iExtra; $ii++) {
					unset($aE1CountsSourceCodeArray['e1SubCounts'][$ii]);
				}
				$temp_array = array_values($aE1CountsSourceCodeArray['e1SubCounts']);
				$aE1CountsSourceCodeArray['e1SubCounts'] = $temp_array;
				//mail('spatel@amperemedia.com',__FILE__,__FILE__.': '.__LINE__);
			}
			/***********  End of looping through e1 counts array  ***********/
			/**********  Loop through offers taken array and add elements into report array if matching sourceCode doesn't exist  *********/
			// update the offers taken counts in the report array if matching sourceCode exists
			for ($i=0;$i< count($aOffersTakenSourceCodeArray['sourceCode']);$i++) {
				$iTempKey = '';
				if (count($aReportArray['sourceCode']) >0 ) {
					$iTempKey = array_search($aOffersTakenSourceCodeArray['sourceCode'][$i], $aReportArray['sourceCode']);
				}

				if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
					$bDateWise = true;
				}

				if (!(is_numeric($iTempKey) && $aReportArray['subSourceCode'][$iTempKey] == $aOffersTakenSourceCodeArray['subSourceCode'][$i]) || $bDateWise) {
					array_push($aReportArray['sourceCode'], $aOffersTakenSourceCodeArray['sourceCode'][$i]);
					array_push($aReportArray['subSourceCode'], $aOffersTakenSourceCodeArray['subSourceCode'][$i]);
					array_push($aReportArray['companyName']	,$aOffersTakenSourceCodeArray['companyName'][$i]);
					array_push($aReportArray['iCampaignTypeId']	,$aOffersTakenSourceCodeArray['iCampaignTypeId'][$i]);
					array_push($aReportArray['partnerRep'],$aOffersTakenSourceCodeArray['partnerRep'][$i]);
					array_push($aReportArray['uniqueUsers'],$aOffersTakenSourceCodeArray['uniqueUsers'][$i]);
					array_push($aReportArray['totalOffersTaken'],$aOffersTakenSourceCodeArray['totalOffersTaken'][$i]);
					array_push($aReportArray['avgOffersTakenPerUser'],$aOffersTakenSourceCodeArray['avgOffersTakenPerUser'][$i]);
					array_push($aReportArray['totalRevenue'],$aOffersTakenSourceCodeArray['totalRevenue'][$i]);

					if ($sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
						array_push($aReportArray['uniqueUsersDup'], $aOffersTakenSourceCodeArray['uniqueUsersDup'][$i]);
						array_push($aReportArray['uniqueUsersNpv'], $aOffersTakenSourceCodeArray['uniqueUsersNpv'][$i]);
						array_push($aReportArray['uniqueUsersNcv'], $aOffersTakenSourceCodeArray['uniqueUsersNcv'][$i]);
						array_push($aReportArray['uniqueUsersNet'], $aOffersTakenSourceCodeArray['uniqueUsersNet'][$i]);
					}

					if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
						array_push($aReportArray['dateAdded'], $aOffersTakenSourceCodeArray['dateAdded'][$i]);
					}

					if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
						// put placeholder for e1 counts also to keep arry sizes consistent
						array_push($aReportArray['e1SubCounts'],$aE1CountsSourceCodeArray['e1SubCounts'][$i]);
					} else {
						array_push($aReportArray['e1SubCounts'],'');
					}
				} else {
					/// store other values in the same row
					$aReportArray['uniqueUsers'][$iTempKey] = $aOffersTakenSourceCodeArray['uniqueUsers'][$i];
					$aReportArray['totalOffersTaken'][$iTempKey] = $aOffersTakenSourceCodeArray['totalOffersTaken'][$i];
					$aReportArray['avgOffersTakenPerUser'][$iTempKey] = $aOffersTakenSourceCodeArray['avgOffersTakenPerUser'][$i];
					$aReportArray['totalRevenue'][$iTempKey] = $aOffersTakenSourceCodeArray['totalRevenue'][$i];

					if ($sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
						$aReportArray['uniqueUsersDup'][$iTempKey] = $aOffersTakenSourceCodeArray['uniqueUsersDup'][$i];
						$aReportArray['uniqueUsersNpv'][$iTempKey] = $aOffersTakenSourceCodeArray['uniqueUsersNpv'][$i];
						$aReportArray['uniqueUsersNcv'][$iTempKey] = $aOffersTakenSourceCodeArray['uniqueUsersNcv'][$i];
						$aReportArray['uniqueUsersNet'][$iTempKey] = $aOffersTakenSourceCodeArray['uniqueUsersNet'][$i];
					}

					if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
						$aReportArray['dateAdded'][$iTempKey] = $aOffersTakenSourceCodeArray['dateAdded'][$i];
					}
				}
			}

			/************* sort arrays here for order by  *************/

			if (count ($aReportArray['sourceCode']) > 0 ) {
				if (count($aReportArray['dateAdded']) > 0 && $sPostalVerified != 'fullDetails')  {
					switch ($sOrderColumn) {
						case "subSourceCode" :
						array_multisort($aReportArray['subSourceCode'], $sCurrOrder, $aReportArray['uniqueUsers'], $aReportArray['sourceCode'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['e1SubCounts'], $aReportArray['iCampaignTypeId']);
						break;
						case "e1SubCounts" :
						array_multisort($aReportArray['e1SubCounts'], $sCurrOrder, $aReportArray['uniqueUsers'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['iCampaignTypeId']);
						break;
						case "uniqueUsers" :
						array_multisort($aReportArray['uniqueUsers'], $sCurrOrder, $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['e1SubCounts'], $aReportArray['iCampaignTypeId']);
						break;
						case "totalRevenue" :
						array_multisort($aReportArray['totalRevenue'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['e1SubCounts'], $aReportArray['iCampaignTypeId']);
						break;
						case "totalOffersTaken" :
						array_multisort($aReportArray['totalOffersTaken'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['sourceCode'] , $aReportArray['subSourceCode'], $aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['e1SubCounts'], $aReportArray['iCampaignTypeId']);
						break;
						case "avgOffersTakenPerUser" :
						array_multisort($aReportArray['avgOffersTakenPerUser'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['e1SubCounts'], $aReportArray['iCampaignTypeId']);
						break;
						case "accountRep" :
						array_multisort($aReportArray['partnerRep'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['e1SubCounts'], $aReportArray['iCampaignTypeId']);
						break;
						case "dateAdded" :
						array_multisort($aReportArray['dateAdded'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['e1SubCounts'], $aReportArray['iCampaignTypeId']);
						break;
						case "companyName":					
						array_multisort($aReportArray['companyName'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['dateAdded'], $aReportArray['e1SubCounts'], $aReportArray['iCampaignTypeId']);	
						break;
						case "iCampaignTypeId":
						array_multisort($aReportArray['iCampaignTypeId'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['dateAdded'], $aReportArray['e1SubCounts'], $aReportArray['companyName']);	
						break;
						default:
						array_multisort($aReportArray['sourceCode'], $aReportArray['subSourceCode'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['e1SubCounts'], $aReportArray['iCampaignTypeId']);
					}
				} else if (count($aReportArray['dateAdded']) > 0 && $sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
					switch ($sOrderColumn) {
						case "subSourceCode" :
						array_multisort($aReportArray['subSourceCode'], $sCurrOrder, $aReportArray['uniqueUsers'], $aReportArray['sourceCode'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['e1SubCounts'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['iCampaignTypeId']);
						break;
						case "e1SubCounts" :
						array_multisort($aReportArray['e1SubCounts'], $sCurrOrder, $aReportArray['uniqueUsers'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['iCampaignTypeId']);
						break;
						case "uniqueUsers" :
						array_multisort($aReportArray['uniqueUsers'], $sCurrOrder, $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['e1SubCounts'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['iCampaignTypeId']);
						break;
						case "totalRevenue" :
						array_multisort($aReportArray['totalRevenue'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['e1SubCounts'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['iCampaignTypeId']);
						break;
						case "totalOffersTaken" :
						array_multisort($aReportArray['totalOffersTaken'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['sourceCode'] , $aReportArray['subSourceCode'], $aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['e1SubCounts'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['iCampaignTypeId']);
						break;
						case "avgOffersTakenPerUser" :
						array_multisort($aReportArray['avgOffersTakenPerUser'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['e1SubCounts'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['iCampaignTypeId']);
						break;
						case "accountRep" :
						array_multisort($aReportArray['partnerRep'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['e1SubCounts'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['iCampaignTypeId']);
						break;
						case "dateAdded" :
						array_multisort($aReportArray['dateAdded'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['e1SubCounts'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'], $aReportArray['iCampaignTypeId']);
						break;
						case "companyName":
						array_multisort($aReportArray['companyName'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['dateAdded'], $aReportArray['e1SubCounts'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['iCampaignTypeId']);
						break;
						case "iCampaignTypeId":
						array_multisort($aReportArray['iCampaignTypeId'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['dateAdded'], $aReportArray['e1SubCounts'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['companyName']);
						break;
						default:
						array_multisort($aReportArray['sourceCode'], $sCurrOrder, $aReportArray['subSourceCode'], $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['e1SubCounts'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['iCampaignTypeId']);
					}

				} else if ( $sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
					switch ($sOrderColumn) {
						case "subSourceCode" :
						array_multisort($aReportArray['subSourceCode'], $sCurrOrder, $aReportArray['uniqueUsers'], $aReportArray['sourceCode'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['e1SubCounts'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['iCampaignTypeId']);
						break;
						case "e1SubCounts" :
						array_multisort($aReportArray['e1SubCounts'], $sCurrOrder, $aReportArray['uniqueUsers'], $aReportArray['subSourceCode'], $aReportArray['sourceCode'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['iCampaignTypeId']);
						break;
						case "uniqueUsers" :
						array_multisort($aReportArray['uniqueUsers'], $sCurrOrder, $aReportArray['sourceCode'],  $aReportArray['subSourceCode'], $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['e1SubCounts'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['iCampaignTypeId']);
						break;
						case "totalRevenue" :
						array_multisort($aReportArray['totalRevenue'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['e1SubCounts'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['iCampaignTypeId']);
						break;
						case "totalOffersTaken" :
						array_multisort($aReportArray['totalOffersTaken'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['e1SubCounts'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['iCampaignTypeId']);
						break;
						case "avgOffersTakenPerUser" :
						array_multisort($aReportArray['avgOffersTakenPerUser'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['e1SubCounts'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['iCampaignTypeId']);
						break;
						case "accountRep" :
						array_multisort($aReportArray['partnerRep'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['companyName'], $aReportArray['e1SubCounts'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['iCampaignTypeId']);
						break;
						case "companyName" :
						array_multisort($aReportArray['companyName'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['e1SubCounts'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['iCampaignTypeId']);
						break;
						case "iCampaignTypeId" :
						array_multisort($aReportArray['iCampaignTypeId'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['e1SubCounts'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['companyName']);
						break;
						default:
						array_multisort($aReportArray['sourceCode'], $sCurrOrder, $aReportArray['subSourceCode'], $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['e1SubCounts'], $aReportArray['uniqueUsersDup'],$aReportArray['uniqueUsersNpv'],$aReportArray['uniqueUsersNcv'],$aReportArray['uniqueUsersNet'], $aReportArray['iCampaignTypeId']);
					}
				} else {
					switch ($sOrderColumn) {
						case "subSourceCode" :
						array_multisort($aReportArray['subSourceCode'], $sCurrOrder, $aReportArray['uniqueUsers'], $aReportArray['sourceCode'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['e1SubCounts'], $aReportArray['iCampaignTypeId']);
						break;
						case "e1SubCounts" :
						array_multisort($aReportArray['e1SubCounts'], $sCurrOrder, $aReportArray['uniqueUsers'], $aReportArray['subSourceCode'], $aReportArray['sourceCode'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['iCampaignTypeId']);
						break;
						case "uniqueUsers" :
						array_multisort($aReportArray['uniqueUsers'], $sCurrOrder, $aReportArray['sourceCode'],  $aReportArray['subSourceCode'], $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['e1SubCounts'], $aReportArray['iCampaignTypeId']);
						break;
						case "totalRevenue" :
						array_multisort($aReportArray['totalRevenue'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['e1SubCounts'], $aReportArray['iCampaignTypeId']);
						break;
						case "totalOffersTaken" :
						array_multisort($aReportArray['totalOffersTaken'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['e1SubCounts'], $aReportArray['iCampaignTypeId']);
						break;
						case "avgOffersTakenPerUser" :
						array_multisort($aReportArray['avgOffersTakenPerUser'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['e1SubCounts'], $aReportArray['iCampaignTypeId']);
						break;
						case "accountRep" :
						array_multisort($aReportArray['partnerRep'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['companyName'], $aReportArray['e1SubCounts'], $aReportArray['iCampaignTypeId']);
						break;
						case "companyName" :
						array_multisort($aReportArray['companyName'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['e1SubCounts'], $aReportArray['iCampaignTypeId']);
						break;
						case "iCampaignTypeId" :
						array_multisort($aReportArray['iCampaignTypeId'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['e1SubCounts'], $aReportArray['companyName']);
						break;
						default:
						array_multisort($aReportArray['sourceCode'], $sCurrOrder, $aReportArray['subSourceCode'], $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['e1SubCounts'], $aReportArray['iCampaignTypeId']);
					}
				}
			}
			/***********  End of sorting arrays  *********/
			$iAPIE1Total = 0;
			$iAPIUniqueTotal = 0;
			$iAPIOfferTakenTotal = 0;
			$iAPIAvgTakenTotal = 0;
			$iAPIRevTotal = 0;
					
			$iEmailE1Total = 0;
			$iEmailUniqueTotal = 0;
			$iEmailOfferTakenTotal = 0;
			$iEmailAvgTakenTotal = 0;
			$iEmailRevTotal = 0;
					
			$iHeaderE1Total = 0;
			$iHeaderUniqueTotal = 0;
			$iHeaderOfferTakenTotal = 0;
			$iHeaderAvgTakenTotal = 0;
			$iHeaderRevTotal = 0;
			
			$iBDE1Total = 0;
			$iBDUniqueTotal = 0;
			$iBDOfferTakenTotal = 0;
			$iBDAvgTakenTotal = 0;
			$iBDRevTotal = 0;
					
			$iBannerE1Total = 0;
			$iBannerUniqueTotal = 0;
			$iBannerOfferTakenTotal = 0;
			$iBannerAvgTakenTotal = 0;
			$iBannerRevTotal = 0;
			
			$iTextE1Total = 0;
			$iTextUniqueTotal = 0;
			$iTextOfferTakenTotal = 0;
			$iTextAvgTakenTotal = 0;
			$iTextRevTotal = 0;
			
			$iPopE1Total = 0;
			$iPopUniqueTotal = 0;
			$iPopOfferTakenTotal = 0;
			$iPopAvgTakenTotal = 0;
			$iPopRevTotal = 0;
			
			$iOtherE1Total = 0;
			$iOtherUniqueTotal = 0;
			$iOtherOfferTakenTotal = 0;
			$iOtherAvgTakenTotal = 0;
			$iOtherRevTotal = 0;
			
			
			$iAPIUniqueUsersDup = 0;
			$iAPIUniqueUsersNpv = 0;
			$iAPIUniqueUsersNcv = 0;
			$iAPIUniqueUsersNet = 0;
			
			$iEmailUniqueUsersDup = 0;
			$iEmailUniqueUsersNpv = 0;
			$iEmailUniqueUsersNcv = 0;
			$iEmailUniqueUsersNet = 0;
					
			$iHeaderUniqueUsersDup = 0;
			$iHeaderUniqueUsersNpv = 0;
			$iHeaderUniqueUsersNcv = 0;
			$iHeaderUniqueUsersNet = 0;
					
			$iBdUniqueUsersDup = 0;
			$iBdUniqueUsersNpv = 0;
			$iBdUniqueUsersNcv = 0;
			$iBdUniqueUsersNet = 0;
			
			$iBannerUniqueUsersDup = 0;
			$iBannerUniqueUsersNpv = 0;
			$iBannerUniqueUsersNcv = 0;
			$iBannerUniqueUsersNet = 0;
			
			$iTextUniqueUsersDup = 0;
			$iTextUniqueUsersNpv = 0;
			$iTextUniqueUsersNcv = 0;
			$iTextUniqueUsersNet = 0;
					
			$iPopUniqueUsersDup = 0;
			$iPopUniqueUsersNpv = 0;
			$iPopUniqueUsersNcv = 0;
			$iPopUniqueUsersNet = 0;
			
			$iOtherUniqueUsersDup = 0;
			$iOtherpUniqueUsersNpv = 0;
			$iOtherUniqueUsersNcv = 0;
			$iOtherUniqueUsersNet = 0;

			/***********  End of preparing report from aReportArray elements  **********/
			for ($i=0;$i< count($aReportArray['sourceCode']);$i++) {
				// display sourceCode row only if it has some counts (e1 or offers taken)<BR>
				if ($aReportArray['e1SubCounts'][$i] > 0 || $aReportArray['totalOffersTaken'][$i] > 0) {
					if ( $aReportArray['totalOffersTaken'][$i] > 0) {
						if ($sBgcolorClass == "ODD") {
							$sBgcolorClass = "EVEN_WHITE";
						} else {
							$sBgcolorClass = "ODD";
						}
	
						$sReportContent .= "<tr class=$sBgcolorClass><td>".$aReportArray['partnerRep'][$i]."</td>
										<td>".$aReportArray['sourceCode'][$i]."</td>";
	
						if ($sSuppressSubSource != 'Y' ) {
							if ($sTruncateSubSource == 'Y') {
								$sReportContent .= "<td>".substr($aReportArray['subSourceCode'][$i],0,10)."</td>";
							} else {
								$sReportContent .= "<td>".$aReportArray['subSourceCode'][$i]."</td>";
							}
						}
	
						$sReportContent .= "<td>".$aReportArray['companyName'][$i]."</td>
										<td>".$aReportArray['iCampaignTypeId'][$i]."</td>
										<td align=right>".$aReportArray['e1SubCounts'][$i]."</td>
										<td align=right>".$aReportArray['uniqueUsers'][$i]."</td>";
	
						if ($sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
							$sReportContent .= "<td align=right>".$aReportArray['uniqueUsersDup'][$i]."</td>
												<td align=right>".$aReportArray['uniqueUsersNpv'][$i]."</td>
												<td align=right>".$aReportArray['uniqueUsersNcv'][$i]."</td>
												<td align=right>".$aReportArray['uniqueUsersNet'][$i]."</td>";
						}
	
						$sReportContent .= "<td align=right>".$aReportArray['totalOffersTaken'][$i]."</td>
										<td align=right>".$aReportArray['avgOffersTakenPerUser'][$i]."</td>
										<td align=right>".$aReportArray['totalRevenue'][$i]."</td>";
						
						
						
						
						if ($iCampaignType == '' && $sShowSeparateTotal == 'Y') {
							if ($aReportArray['iCampaignTypeId'][$i] == 'Other') {
								$iOtherE1Total += $aReportArray['e1SubCounts'][$i];
								$iOtherUniqueTotal += $aReportArray['uniqueUsers'][$i];
								$iOtherOfferTakenTotal += $aReportArray['totalOffersTaken'][$i];
								$iOtherAvgTakenTotal += $aReportArray['avgOffersTakenPerUser'][$i];
								$iOtherRevTotal += $aReportArray['totalRevenue'][$i];
								
								if ($sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
									$iOtherUniqueUsersDup += $aReportArray['uniqueUsersDup'][$i];
									$iOtherpUniqueUsersNpv += $aReportArray['uniqueUsersNpv'][$i];
									$iOtherUniqueUsersNcv += $aReportArray['uniqueUsersNcv'][$i];
									$iOtherUniqueUsersNet += $aReportArray['uniqueUsersNet'][$i];
								}
							}
									
							if ($aReportArray['iCampaignTypeId'][$i] == 'Popup') {
								$iPopE1Total += $aReportArray['e1SubCounts'][$i];
								$iPopUniqueTotal += $aReportArray['uniqueUsers'][$i];
								$iPopOfferTakenTotal += $aReportArray['totalOffersTaken'][$i];
								$iPopAvgTakenTotal += $aReportArray['avgOffersTakenPerUser'][$i];
								$iPopRevTotal += $aReportArray['totalRevenue'][$i];
								
								if ($sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
									$iPopUniqueUsersDup += $aReportArray['uniqueUsersDup'][$i];
									$iPopUniqueUsersNpv += $aReportArray['uniqueUsersNpv'][$i];
									$iPopUniqueUsersNcv += $aReportArray['uniqueUsersNcv'][$i];
									$iPopUniqueUsersNet += $aReportArray['uniqueUsersNet'][$i];
								}
							}
								
							if ($aReportArray['iCampaignTypeId'][$i] == 'Text link') {
								$iTextE1Total += $aReportArray['e1SubCounts'][$i];
								$iTextUniqueTotal += $aReportArray['uniqueUsers'][$i];
								$iTextOfferTakenTotal += $aReportArray['totalOffersTaken'][$i];
								$iTextAvgTakenTotal += $aReportArray['avgOffersTakenPerUser'][$i];
								$iTextRevTotal += $aReportArray['totalRevenue'][$i];
								
								if ($sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
									$iTextUniqueUsersDup += $aReportArray['uniqueUsersDup'][$i];
									$iTextUniqueUsersNpv += $aReportArray['uniqueUsersNpv'][$i];
									$iTextUniqueUsersNcv += $aReportArray['uniqueUsersNcv'][$i];
									$iTextUniqueUsersNet += $aReportArray['uniqueUsersNet'][$i];
								}
							}
									
							if ($aReportArray['iCampaignTypeId'][$i] == 'Banner') {
								$iBannerE1Total += $aReportArray['e1SubCounts'][$i];
								$iBannerUniqueTotal += $aReportArray['uniqueUsers'][$i];
								$iBannerOfferTakenTotal += $aReportArray['totalOffersTaken'][$i];
								$iBannerAvgTakenTotal += $aReportArray['avgOffersTakenPerUser'][$i];
								$iBannerRevTotal += $aReportArray['totalRevenue'][$i];
								
								if ($sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
									$iBannerUniqueUsersDup += $aReportArray['uniqueUsersDup'][$i];
									$iBannerUniqueUsersNpv += $aReportArray['uniqueUsersNpv'][$i];
									$iBannerUniqueUsersNcv += $aReportArray['uniqueUsersNcv'][$i];
									$iBannerUniqueUsersNet += $aReportArray['uniqueUsersNet'][$i];
								}
							}
									
							if ($aReportArray['iCampaignTypeId'][$i] == 'API') {
								$iAPIE1Total += $aReportArray['e1SubCounts'][$i];
								$iAPIUniqueTotal += $aReportArray['uniqueUsers'][$i];
								$iAPIOfferTakenTotal += $aReportArray['totalOffersTaken'][$i];
								$iAPIAvgTakenTotal += $aReportArray['avgOffersTakenPerUser'][$i];
								$iAPIRevTotal += $aReportArray['totalRevenue'][$i];
								
								if ($sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
									$iAPIUniqueUsersDup += $aReportArray['uniqueUsersDup'][$i];
									$iAPIUniqueUsersNpv += $aReportArray['uniqueUsersNpv'][$i];
									$iAPIUniqueUsersNcv += $aReportArray['uniqueUsersNcv'][$i];
									$iAPIUniqueUsersNet += $aReportArray['uniqueUsersNet'][$i];
								}
							}
													
							if ($aReportArray['iCampaignTypeId'][$i] == 'Email') {
								$iEmailE1Total += $aReportArray['e1SubCounts'][$i];
								$iEmailUniqueTotal += $aReportArray['uniqueUsers'][$i];
								$iEmailOfferTakenTotal += $aReportArray['totalOffersTaken'][$i];
								$iEmailAvgTakenTotal += $aReportArray['avgOffersTakenPerUser'][$i];
								$iEmailRevTotal += $aReportArray['totalRevenue'][$i];
								
								if ($sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
									$iEmailUniqueUsersDup += $aReportArray['uniqueUsersDup'][$i];
									$iEmailUniqueUsersNpv += $aReportArray['uniqueUsersNpv'][$i];
									$iEmailUniqueUsersNcv += $aReportArray['uniqueUsersNcv'][$i];
									$iEmailUniqueUsersNet += $aReportArray['uniqueUsersNet'][$i];
								}
							}
													
							if ($aReportArray['iCampaignTypeId'][$i] == 'Header') {
								$iHeaderE1Total += $aReportArray['e1SubCounts'][$i];
								$iHeaderUniqueTotal += $aReportArray['uniqueUsers'][$i];
								$iHeaderOfferTakenTotal += $aReportArray['totalOffersTaken'][$i];
								$iHeaderAvgTakenTotal += $aReportArray['avgOffersTakenPerUser'][$i];
								$iHeaderRevTotal += $aReportArray['totalRevenue'][$i];
											
								if ($sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
									$iHeaderUniqueUsersDup += $aReportArray['uniqueUsersDup'][$i];
									$iHeaderUniqueUsersNpv += $aReportArray['uniqueUsersNpv'][$i];
									$iHeaderUniqueUsersNcv += $aReportArray['uniqueUsersNcv'][$i];
									$iHeaderUniqueUsersNet += $aReportArray['uniqueUsersNet'][$i];
								}
							}
													
							if ($aReportArray['iCampaignTypeId'][$i] == 'BD Flow/Cobrand') {
								$iBDE1Total += $aReportArray['e1SubCounts'][$i];
								$iBDUniqueTotal += $aReportArray['uniqueUsers'][$i];
								$iBDOfferTakenTotal += $aReportArray['totalOffersTaken'][$i];
								$iBDAvgTakenTotal += $aReportArray['avgOffersTakenPerUser'][$i];
								$iBDRevTotal += $aReportArray['totalRevenue'][$i];
									
								if ($sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
									$iBdUniqueUsersDup += $aReportArray['uniqueUsersDup'][$i];
									$iBdUniqueUsersNpv += $aReportArray['uniqueUsersNpv'][$i];
									$iBdUniqueUsersNcv += $aReportArray['uniqueUsersNcv'][$i];
									$iBdUniqueUsersNet += $aReportArray['uniqueUsersNet'][$i];
								}
							}
						}

						
						$iGrandTotalE1SubCounts += $aReportArray['e1SubCounts'][$i];
						$iGrandTotalUniqueUsers += $aReportArray['uniqueUsers'][$i];
						if ($sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
							$iGrandTotalUniqueUsersDup += $aReportArray['uniqueUsersDup'][$i];
							$iGrandTotalUniqueUsersNpv += $aReportArray['uniqueUsersNpv'][$i];
							$iGrandTotalUniqueUsersNcv += $aReportArray['uniqueUsersNcv'][$i];
							$iGrandTotalUniqueUsersNet += $aReportArray['uniqueUsersNet'][$i];
						}
						$iGrandTotalOffersTaken += $aReportArray['totalOffersTaken'][$i];
						$fGrandTotalRevenue += $aReportArray['totalRevenue'][$i];
	
						if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
							$sReportContent .= "<td nowrap>".$aReportArray['dateAdded'][$i]."</td>";
						}
						
						$sReportContent .= "</tr>";	
						if ($sExportExcel) {
							$sExportData .= $aReportArray['partnerRep'][$i]."\t".$aReportArray['sourceCode'][$i]."\t".
							$aReportArray['subSourceCode'][$i]."\t".$aReportArray['companyName'][$i]."\t".$aReportArray['iCampaignTypeId'][$i]."\t".
							$aReportArray['e1SubCounts'][$i]."\t".$aReportArray['uniqueUsers'][$i]."\t";
	
							if ($sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
								$sExportData .= $aReportArray['uniqueUsersDup'][$i]."\t".$aReportArray['uniqueUsersNpv'][$i]."\t".
								$aReportArray['uniqueUsersNcv'][$i]."\t".$aReportArray['uniqueUsersNet'][$i]."\t";
							}
	
							$sExportData .= $aReportArray['totalOffersTaken'][$i]."\t".$aReportArray['avgOffersTakenPerUser'][$i].
							"\t".$aReportArray['totalRevenue'][$i];
							if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
								$sExportData .= "\t".$aReportArray['dateAdded'][$i];
							}
							$sExportData .="\t\n";
						}
					}
				}
			}

			/***********  End of preparing report from array elements  **********/
			$sAPIFull = '';
			$sEmailFull = '';
			$sHeaderFull = '';
			$sBdFull = '';
			$sBannerFull = '';
			$sTextFull = '';
			$sPopFull = '';
			$sOtherFull = '';
			
			
			if ($sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
				$sPopFull = "<td align=right><b>$iPopUniqueUsersDup</b></td>
							<td align=right><b>$iPopUniqueUsersNpv</b></td>
							<td align=right><b>$iPopUniqueUsersNcv</b></td>
							<td align=right><b>$iPopUniqueUsersNet</b></td>";
	
				$sOtherFull = "<td align=right><b>$iOtherUniqueUsersDup</b></td>
							<td align=right><b>$iOtherpUniqueUsersNpv</b></td>
							<td align=right><b>$iOtherUniqueUsersNcv</b></td>
							<td align=right><b>$iOtherUniqueUsersNet</b></td>";
	
				$sAPIFull = "<td align=right><b>$iAPIUniqueUsersDup</b></td>
							<td align=right><b>$iAPIUniqueUsersNpv</b></td>
							<td align=right><b>$iAPIUniqueUsersNcv</b></td>
							<td align=right><b>$iAPIUniqueUsersNet</b></td>";
	
				$sEmailFull = "<td align=right><b>$iEmailUniqueUsersDup</b></td>
							<td align=right><b>$iEmailUniqueUsersNpv</b></td>
							<td align=right><b>$iEmailUniqueUsersNcv</b></td>
							<td align=right><b>$iEmailUniqueUsersNet</b></td>";
	
				$sHeaderFull = "<td align=right><b>$iHeaderUniqueUsersDup</b></td>
							<td align=right><b>$iHeaderUniqueUsersNpv</b></td>
							<td align=right><b>$iHeaderUniqueUsersNcv</b></td>
							<td align=right><b>$iHeaderUniqueUsersNet</b></td>";
	
				$sBdFull = "<td align=right><b>$iBdUniqueUsersDup</b></td>
							<td align=right><b>$iBdUniqueUsersNpv</b></td>
							<td align=right><b>$iBdUniqueUsersNcv</b></td>
							<td align=right><b>$iBdUniqueUsersNet</b></td>";
	
				$sBannerFull = "<td align=right><b>$iBannerUniqueUsersDup</b></td>
							<td align=right><b>$iBannerUniqueUsersNpv</b></td>
							<td align=right><b>$iBannerUniqueUsersNcv</b></td>
							<td align=right><b>$iBannerUniqueUsersNet</b></td>";
	
				$sTextFull = "<td align=right><b>$iTextUniqueUsersDup</b></td>
							<td align=right><b>$iTextUniqueUsersNpv</b></td>
							<td align=right><b>$iTextUniqueUsersNcv</b></td>
							<td align=right><b>$iTextUniqueUsersNet</b></td>";
			}

			if ($iGrandTotalUniqueUsers != 0 && $iGrandTotalUniqueUsers !='') {
				$fGrandAvgOffersTakenPerUser = $iGrandTotalOffersTaken / $iGrandTotalUniqueUsers ;
			}
			$fGrandAvgOffersTakenPerUser = sprintf("%10.2f",round($fGrandAvgOffersTakenPerUser, 2));

			$fGrandTotalRevenue = sprintf("%12.2f",round($fGrandTotalRevenue, 2));

			if ($sSuppressSubSource != 'Y' ) {
				$iColspan = "4";
			} else {
				$iColspan = "3";
			}

			$sReportContent .= "<tr><td colspan=13 align=left><hr color=#000000></td></tr>
								<tr><td colspan=$iColspan><b>Summary</b></td>
								<td>&nbsp;</td>
								<td align=right><b>$iGrandTotalE1SubCounts</b></td>								
								<td align=right><b>$iGrandTotalUniqueUsers</b></td>";

			if ($sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
				$sReportContent .= "<td align=right><b>$iGrandTotalUniqueUsersDup</b></td>
									<td align=right><b>$iGrandTotalUniqueUsersNpv</b></td>
									<td align=right><b>$iGrandTotalUniqueUsersNcv</b></td>
									<td align=right><b>$iGrandTotalUniqueUsersNet</b></td>";
			}

			$sReportContent .= "<td align=right><b>$iGrandTotalOffersTaken</b></td>
								<td align=right><b>$fGrandAvgOffersTakenPerUser</b></td>
								<td align=right><b>$fGrandTotalRevenue</b></td><BR>
								</tr>";
			
			
			if ($iCampaignType == '' && $sShowSeparateTotal == 'Y') {
				if ($iAPIUniqueTotal > 0) {
					$iAPIAvgTakenTotal = number_format(($iAPIOfferTakenTotal / $iAPIUniqueTotal), 2, '.', "");
				} else {
					$iAPIAvgTakenTotal = 0;
				}
				$iAPIRevTotal = number_format($iAPIRevTotal, 2, '.', "");
				$sReportContent .= "<tr><td colspan=$iColspan><b>API Total</b></td>
									<td>&nbsp;</td>
									<td align=right><b>$iAPIE1Total</b></td>								
									<td align=right><b>$iAPIUniqueTotal</b></td>
									$sAPIFull
									<td align=right><b>$iAPIOfferTakenTotal</b></td>
									<td align=right><b>$iAPIAvgTakenTotal</b></td>
									<td align=right><b>$iAPIRevTotal</b></td><BR>
									<tr>";
				
				if ($iEmailUniqueTotal > 0) {
					$iEmailAvgTakenTotal = number_format(($iEmailOfferTakenTotal / $iEmailUniqueTotal), 2, '.', "");
				} else {
					$iEmailAvgTakenTotal = 0;
				}
				$iEmailRevTotal = number_format($iEmailRevTotal, 2, '.', "");
				$sReportContent .= "<tr><td colspan=$iColspan><b>Email Total</b></td>
									<td>&nbsp;</td>
									<td align=right><b>$iEmailE1Total</b></td>								
									<td align=right><b>$iEmailUniqueTotal</b></td>
									$sEmailFull
									<td align=right><b>$iEmailOfferTakenTotal</b></td>
									<td align=right><b>$iEmailAvgTakenTotal</b></td>
									<td align=right><b>$iEmailRevTotal</b></td><BR>
									<tr>";

				if ($iHeaderUniqueTotal > 0) {
					$iHeaderAvgTakenTotal = number_format(($iHeaderOfferTakenTotal / $iHeaderUniqueTotal), 2, '.', "");
				} else {
					$iHeaderAvgTakenTotal = 0;
				}
				$iHeaderRevTotal = number_format($iHeaderRevTotal, 2, '.', "");
				$sReportContent .= "<tr><td colspan=$iColspan><b>Header Total</b></td>
									<td>&nbsp;</td>
									<td align=right><b>$iHeaderE1Total</b></td>								
									<td align=right><b>$iHeaderUniqueTotal</b></td>
									$sHeaderFull
									<td align=right><b>$iHeaderOfferTakenTotal</b></td>
									<td align=right><b>$iHeaderAvgTakenTotal</b></td>
									<td align=right><b>$iHeaderRevTotal</b></td><BR>
									<tr>";
				
				if ($iBDUniqueTotal > 0) {
					$iBDAvgTakenTotal = number_format(($iBDOfferTakenTotal / $iBDUniqueTotal), 2, '.', "");
				} else {
					$iBDAvgTakenTotal = 0;
				}
				$iBDRevTotal = number_format($iBDRevTotal, 2, '.', "");
				$sReportContent .= "<tr><td colspan=$iColspan><b>BD Flow/Cobrand Total</b></td>
									<td>&nbsp;</td>
									<td align=right><b>$iBDE1Total</b></td>								
									<td align=right><b>$iBDUniqueTotal</b></td>
									$sBdFull
									<td align=right><b>$iBDOfferTakenTotal</b></td>
									<td align=right><b>$iBDAvgTakenTotal</b></td>
									<td align=right><b>$iBDRevTotal</b></td><BR>
									<tr>";
				
				if ($iBannerUniqueTotal > 0) {
					$iBannerAvgTakenTotal = number_format(($iBannerOfferTakenTotal / $iBannerUniqueTotal), 2, '.', "");
				} else {
					$iBannerAvgTakenTotal = 0;
				}
				$iBannerRevTotal = number_format($iBannerRevTotal, 2, '.', "");
				$sReportContent .= "<tr><td colspan=$iColspan><b>Banner Total</b></td>
									<td>&nbsp;</td>
									<td align=right><b>$iBannerE1Total</b></td>								
									<td align=right><b>$iBannerUniqueTotal</b></td>
									$sBannerFull
									<td align=right><b>$iBannerOfferTakenTotal</b></td>
									<td align=right><b>$iBannerAvgTakenTotal</b></td>
									<td align=right><b>$iBannerRevTotal</b></td><BR>
									<tr>";
				
				if ($iTextUniqueTotal > 0) {
					$iTextAvgTakenTotal = number_format(($iTextOfferTakenTotal / $iTextUniqueTotal), 2, '.', "");
				} else {
					$iTextAvgTakenTotal = 0;
				}
				$iTextRevTotal = number_format($iTextRevTotal, 2, '.', "");
				$sReportContent .= "<tr><td colspan=$iColspan><b>Text Line Total</b></td>
									<td>&nbsp;</td>
									<td align=right><b>$iTextE1Total</b></td>								
									<td align=right><b>$iTextUniqueTotal</b></td>
									$sTextFull
									<td align=right><b>$iTextOfferTakenTotal</b></td>
									<td align=right><b>$iTextAvgTakenTotal</b></td>
									<td align=right><b>$iTextRevTotal</b></td><BR>
									<tr>";
				
				if ($iPopUniqueTotal > 0) {
					$iPopAvgTakenTotal = number_format(($iPopOfferTakenTotal / $iPopUniqueTotal), 2, '.', "");
				} else {
					$iPopAvgTakenTotal = 0;
				}
				$iPopRevTotal = number_format($iPopRevTotal, 2, '.', "");
				$sReportContent .= "<tr><td colspan=$iColspan><b>Pop Total</b></td>
									<td>&nbsp;</td>
									<td align=right><b>$iPopE1Total</b></td>								
									<td align=right><b>$iPopUniqueTotal</b></td>
									$sPopFull
									<td align=right><b>$iPopOfferTakenTotal</b></td>
									<td align=right><b>$iPopAvgTakenTotal</b></td>
									<td align=right><b>$iPopRevTotal</b></td><BR>
									<tr>";
				
				if ($iOtherUniqueTotal > 0) {
					$iOtherAvgTakenTotal = number_format(($iOtherOfferTakenTotal / $iOtherUniqueTotal), 2, '.', "");
				} else {
					$iOtherAvgTakenTotal = 0;
				}
				$iOtherRevTotal = number_format($iOtherRevTotal, 2, '.', "");
				$sReportContent .= "<tr><td colspan=$iColspan><b>Other Total</b></td>
									<td>&nbsp;</td>
									<td align=right><b>$iOtherE1Total</b></td>								
									<td align=right><b>$iOtherUniqueTotal</b></td>
									$sOtherFull
									<td align=right><b>$iOtherOfferTakenTotal</b></td>
									<td align=right><b>$iOtherAvgTakenTotal</b></td>
									<td align=right><b>$iOtherRevTotal</b></td><BR>
									<tr>";
			}
		
			$sReportContent .= "<tr><td colspan=13 align=left><hr color=#000000></td></tr>	";
			
			if ($sExportExcel) {
				$sExportHeader = "Account Executive\tSource Code\tSub Source Code\tPartner Name\tRate Structure\tGross E1 Counts\tUnique Users\t";
				if ($sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
					$sExportHeader .= "Dup UniqueUsers\tNpv Unique Users\tNcv Unique Users\tNet Unique Users\t";
				}
				$sExportHeader .= "Total Offers Taken\tAvg. Offers Taken Per User\tTotal Revenue\t\n" ;

				$sExportData = $sExportHeader . $sExportData;
				$sExportData .= "\n\t\t\t\t\t$iGrandTotalE1SubCounts\t$iGrandTotalUniqueUsers\t";
				if ($sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
					$sExportData .= "$iGrandTotalUniqueUsersDup\t$iGrandTotalUniqueUsersNpv
									 \t$iGrandTotalUniqueUsersNcv\t$iGrandTotalUniqueUsersNet\t";
				}

				$sExportData .= "$iGrandTotalOffersTaken\t$fGrandAvgOffersTakenPerUser\t$fGrandTotalRevenue\t\n\nReport From $sDateFrom to $sDateTo\nRun Date / Time: $sRunDateAndTime";
			}

			if ($sExportEmails) {
				// if history table
				$sExportEmailQuery = eregi_replace("count\(distinct otDataHistory.email\) AS uniqueUsers,", "distinct otDataHistory.email, otDataHistory.sourceCode ",$sReportQuery);
				$sExportEmailQuery = eregi_replace("count\(otDataHistory.email\) AS totalOffersTaken,","",$sExportEmailQuery);
				$sExportEmailQuery = eregi_replace("sum\(otDataHistory.revPerLead\) as totalRevenue","",$sExportEmailQuery);
				$sExportEmailQuery = eregi_replace("SELECT otDataHistory.sourceCode,","SELECT ",$sExportEmailQuery);
				$sExportEmailQuery = eregi_replace("otDataHistory.subSourceCode,","",$sExportEmailQuery);
				$sExportEmailQuery = eregi_replace("GROUP BY sourceCode, subSourceCode , dateAdded"," ",$sExportEmailQuery);
				$sExportEmailQuery = eregi_replace("GROUP BY sourceCode, subSourceCode"," ",$sExportEmailQuery);
				$sExportEmailQuery = eregi_replace("GROUP BY sourceCode"," ",$sExportEmailQuery);

				// if today's table
				if ($sViewReport == "Today's Report") {
					$sExportEmailQuery = eregi_replace("count\(distinct otData.email\) AS uniqueUsers,", "distinct otData.email, otData.sourceCode ",$sReportQuery);
					$sExportEmailQuery = eregi_replace("count\(otData.email\) AS totalOffersTaken,","",$sExportEmailQuery);
					$sExportEmailQuery = eregi_replace("sum\(otData.revPerLead\) as totalRevenue","",$sExportEmailQuery);
					$sExportEmailQuery = eregi_replace("SELECT otData.sourceCode,","SELECT ",$sExportEmailQuery);
					$sExportEmailQuery = eregi_replace("otData.subSourceCode,","",$sExportEmailQuery);
					$sExportEmailQuery = eregi_replace("GROUP BY sourceCode, subSourceCode , dateAdded"," ",$sExportEmailQuery);
					$sExportEmailQuery = eregi_replace("GROUP BY sourceCode, subSourceCode"," ",$sExportEmailQuery);
					$sExportEmailQuery = eregi_replace("GROUP BY sourceCode"," ",$sExportEmailQuery);
				}

				$rExportEmailResult = dbQuery($sExportEmailQuery);
				echo dbError();
				while ($oExportEmailRow = dbFetchObject($rExportEmailResult)) {
					$sExportEmailData .= "\"$oExportEmailRow->email\",\"$oExportEmailRow->sourceCode\"\r\n";
				}
			}

			if ($sFilter == 'startsWith') {
				$sStartsWithChecked = "CHECKED";
			} else if ($sFilter == 'exactMatch') {
				$sExactMatchChecked = "CHECKED";
			}

			if ($sSsFilter == 'startsWith') {
				$sSsStartsWithChecked = "CHECKED";
			} else if ($sSsFilter == 'exactMatch') {
				$sSsExactMatchChecked = "CHECKED";
			}

			if ($sPostalVerified == 'pvOnly') {
				$sPvOnlyChecked = "checked";
			} else if ($sPostalVerified == 'pvAndNonPv') {
				$sPvAndNonPvChecked = "checked";
			} else if ($sPostalVerified == 'fullDetails') {
				$sFullDetailsChecked = "checked";
			}

			if ($sShowQueries == 'Y') {
				$sShowQueriesChecked = "checked";
			}

			if ($sPartnersSummary != 'Y' ) {
				$sPartnersSummaryChecked == "";
			} else {
				$sPartnersSummaryChecked = "checked";
			}

			if ($sExportExcel && $sPartnersSummaryChecked!='checked') {
				$sFileName = "sourceAnalysis_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";
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
			} else if ($sExportEmails) {
				$sFileName = "sourceAnalysisEmails_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";
				$rFpFile = fopen("$sGblWebRoot/temp/$sFileName", "w");
				if ($rFpFile) {
					fputs($rFpFile, $sExportEmailData, strlen($sExportEmailData));
					fclose($rFpFile);
					echo "<script language=JavaScript>
					void(window.open(\"$sGblSiteRoot/download.php?sFile=$sFileName\",\"\",\"height=150, width=300, scrollbars=yes, resizable=yes, status=yes\"));
			  	</script>";
				} else {
					$sMessage = "Error exporting data...";
				}
			}
		}
	}

	$sRepQuery = "SELECT id, firstName, userName
				 FROM   nbUsers
				 ORDER BY userName";
	$rRepResult = dbQuery($sRepQuery);
	echo dbError();
	$sAccountRepOptions = "<option value=''>All";
	while ($oRepRow = dbFetchObject($rRepResult)) {
		if ($iAccountRep == $oRepRow->id) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		$sAccountRepOptions .= "<option value='$oRepRow->id' $sSelected>$oRepRow->userName";
	}
	
	
	$sCampRateTypeQuery = "SELECT * FROM campaignRateStructure ORDER BY rateType";
	$rCampRateTypeResult = mysql_query($sCampRateTypeQuery);
	while ($oCampRateTypeRow = mysql_fetch_object($rCampRateTypeResult)) {
		if ($oCampRateTypeRow->id == $iCampaignRateType) {
			$sSelected = 'selected';
		} else {
			$sSelected = '';
		}
		$sCampaignRateTypeOptions .= "<option value='$oCampRateTypeRow->id' $sSelected>$oCampRateTypeRow->rateType";
	}


	if ($sExportExcel) {
		$sExportExcelChecked = "checked";
	}
	
	$sShowSeparateTotalChecked = '';
	if ($sShowSeparateTotal) {
		$sShowSeparateTotalChecked = 'checked';
	}

	if ($sExportEmails) {
		$sExportEmailsChecked = "checked";
	}

	if ($sExcludeNonRevenue == 'Y') {
		$sExcludeNonRevenueChecked = "checked";
	}

	if ($sIncludeTestLeads == 'Y') {
		$sIncludeTestLeadsChecked = "checked";
	}

	if ($sSuppressSubSource != 'Y' ) {
		$sSubSourceHeader = "<td><a href='$sSortLink&sOrderColumn=subSourceCode&sSubSourceCodeOrder=$sSubSourceCodeOrder' class=header>SubSource Code</a></td>";
		$sSuppressSubSourceChecked = "";
	} else {
		$sSuppressSubSourceChecked = "checked";
	}

	if ($sShowExpired == 'Y') {
		$sShowExpiredChecked = "checked";
	}
	
	if ($sTruncateSubSource == 'Y') {
		$sTruncateSubSourceChecked = "checked";
	}

	if ($sShowQueries == 'Y') {
		$sQueries = "<b>Queries Used To Prepare This Report:</b>
					 <BR><BR><b>Report Query:</b><BR>".$sReportQuery.
		"<BR><BR><b>E1 Gross Counts Query:</b><BR>".$sE1CountsQuery;
	}

	
	if ($sPartnersSummaryChecked=='checked') {
		array_multisort($aReportArray['companyName'], SORT_ASC, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['e1SubCounts'], $aReportArray['iCampaignTypeId']);

		$iCountEntries = count($aReportArray['sourceCode']);
		$sPrevious = $aReportArray['companyName'][0];
		$sReportContentNew = "";
		$sExportData = "Account Executive\tPartner Name\tRate Structure\te1 Captures\tGross Unique Users\tTotal Offers Taken\tAvg. Offers Taken Per User\tTotal Revenue\n";
		$iTempE1SubCounts = 0;
		$iUniqueUsers = 0;
		$iTotalOffersTaken = 0;
		$fTotalRevenue = 0;
		$fAvgOffersTakenPerUser = 0;
		$GTempE1SubCounts = 0;
		$GUniqueUsers = 0;
		$GTotalOffersTaken = 0;
		$GAvgOffersTakenPerUser = 0;
		$GTotalRevenue = 0;
		
		for( $iLoop=0; $iLoop<$iCountEntries; $iLoop++ ) {
			if( $sPrevious == $aReportArray['companyName'][$iLoop] ) {
				$iTempE1SubCounts += $aReportArray['e1SubCounts'][$iLoop];
				$iUniqueUsers += $aReportArray['uniqueUsers'][$iLoop];
				$iTotalOffersTaken += $aReportArray['totalOffersTaken'][$iLoop];
				$fTotalRevenue += $aReportArray['totalRevenue'][$iLoop];
				
				if ($iUniqueUsers!=0) {
					$fAvgOffersTakenPerUser = $iTotalOffersTaken / $iUniqueUsers;
					$fAvgOffersTakenPerUser = sprintf("%10.2f",round($fAvgOffersTakenPerUser, 2));
				} else {
					$fAvgOffersTakenPerUser = 0;
				}
			} else {
				$aReportArrayPartnersSummary['partnerRep'][$iLoop]=$aReportArray['partnerRep'][$iLoop-1];
				$aReportArrayPartnersSummary['companyName'][$iLoop]=$aReportArray['companyName'][$iLoop-1];
				$aReportArrayPartnersSummary['iCampaignTypeId'][$iLoop]=$aReportArray['iCampaignTypeId'][$iLoop-1];
				$aReportArrayPartnersSummary['e1SubCounts'][$iLoop]=$iTempE1SubCounts;
				$aReportArrayPartnersSummary['uniqueUsers'][$iLoop]=$iUniqueUsers;
				$aReportArrayPartnersSummary['totalOffersTaken'][$iLoop]=$iTotalOffersTaken;
				$aReportArrayPartnersSummary['avgOffersTakenPerUser'][$iLoop]=$fAvgOffersTakenPerUser;
				$aReportArrayPartnersSummary['totalRevenue'][$iLoop]=$fTotalRevenue;

				if ($fAvgOffersTakenPerUser != 0 && $fTotalRevenue != 0) {
					$sExportData .= $aReportArray['partnerRep'][$iLoop-1]."\t".$aReportArray['companyName'][$iLoop-1]."\t".$aReportArray['iCampaignTypeId'][$iLoop-1]."\t$iTempE1SubCounts\t$iUniqueUsers\t$iTotalOffersTaken\t$fAvgOffersTakenPerUser\t$fTotalRevenue\t\n";
				}
				
				$GTempE1SubCounts += $iTempE1SubCounts;
				$GUniqueUsers += $iUniqueUsers;
				$GTotalOffersTaken += $iTotalOffersTaken;
				$GAvgOffersTakenPerUser += $fAvgOffersTakenPerUser;
				$GTotalRevenue += $fTotalRevenue;

				$iTempE1SubCounts = 0+$aReportArray['e1SubCounts'][$iLoop];
				$iUniqueUsers = $aReportArray['uniqueUsers'][$iLoop];
				$iTotalOffersTaken = $aReportArray['totalOffersTaken'][$iLoop];
				$fTotalRevenue = $aReportArray['totalRevenue'][$iLoop];
				
				if ($iUniqueUsers!=0) {
				$fAvgOffersTakenPerUser = $iTotalOffersTaken / $iUniqueUsers;
				$fAvgOffersTakenPerUser = sprintf("%10.2f",round($fAvgOffersTakenPerUser, 2));
				}
			}

			$sPrevious = $aReportArray['companyName'][$iLoop];
		}

		$aReportArrayPartnersSummary['partnerRep'][$iCountEntries]=$aReportArray['partnerRep'][$iCountEntries-1];
		$aReportArrayPartnersSummary['companyName'][$iCountEntries]=$aReportArray['companyName'][$iCountEntries-1];
		$aReportArrayPartnersSummary['iCampaignTypeId'][$iCountEntries]=$aReportArray['iCampaignTypeId'][$iCountEntries-1];
		$aReportArrayPartnersSummary['e1SubCounts'][$iCountEntries]=$iTempE1SubCounts;
		$aReportArrayPartnersSummary['uniqueUsers'][$iCountEntries]=$iUniqueUsers;
		$aReportArrayPartnersSummary['totalOffersTaken'][$iCountEntries]=$iTotalOffersTaken;
		$aReportArrayPartnersSummary['avgOffersTakenPerUser'][$iCountEntries]=$fAvgOffersTakenPerUser;
		$aReportArrayPartnersSummary['totalRevenue'][$iCountEntries]=$fTotalRevenue;

		$GTempE1SubCounts += $iTempE1SubCounts;
		$GUniqueUsers += $iUniqueUsers;
		$GTotalOffersTaken += $iTotalOffersTaken;
		
		if ($GUniqueUsers!=0) {
			$GAvgOffersTakenPerUser = $GTotalOffersTaken / $GUniqueUsers;
			$GAvgOffersTakenPerUser = sprintf("%10.2f",round($GAvgOffersTakenPerUser, 2));
		} else {
			$GAvgOffersTakenPerUser = 0;
		}
		
		$GTotalRevenue += $fTotalRevenue;
		$GrandTotal = "\nTotal:\t\t\t$GTempE1SubCounts\t$GUniqueUsers\t$GTotalOffersTaken\t$GAvgOffersTakenPerUser\t$GTotalRevenue\n";		
		
		if ($fAvgOffersTakenPerUser != 0 && $fTotalRevenue != 0) {
		$sExportData .= $aReportArray['partnerRep'][$iCountEntries-1]."\t".$aReportArray['companyName'][$iCountEntries-1]."\t".$aReportArray['iCampaignTypeId'][$iCountEntries-1]."\t$iTempE1SubCounts\t$iUniqueUsers\t$iTotalOffersTaken\t$fAvgOffersTakenPerUser\t$fTotalRevenue\t";
		}
		$sExportData .= "\n$GrandTotal\nReport From $sDateFrom to $sDateTo\nRun Date / Time: $sRunDateAndTime";
		
		switch ($sOrderColumn) {
			case "e1SubCounts" :
			array_multisort($aReportArrayPartnersSummary['e1SubCounts'], $sCurrOrder, $aReportArrayPartnersSummary['uniqueUsers'], $aReportArrayPartnersSummary['totalRevenue'], $aReportArrayPartnersSummary['totalOffersTaken'] ,$aReportArrayPartnersSummary['avgOffersTakenPerUser'], $aReportArrayPartnersSummary['partnerRep'], $aReportArrayPartnersSummary['companyName'], $aReportArrayPartnersSummary['iCampaignTypeId']);
			break;
			case "uniqueUsers" :
			array_multisort($aReportArrayPartnersSummary['uniqueUsers'], $sCurrOrder, $aReportArrayPartnersSummary['totalRevenue'], $aReportArrayPartnersSummary['totalOffersTaken'] ,$aReportArrayPartnersSummary['avgOffersTakenPerUser'], $aReportArrayPartnersSummary['partnerRep'], $aReportArrayPartnersSummary['companyName'], $aReportArrayPartnersSummary['e1SubCounts'], $aReportArrayPartnersSummary['iCampaignTypeId']);
			break;
			case "totalRevenue" :
			array_multisort($aReportArrayPartnersSummary['totalRevenue'], $sCurrOrder, $aReportArrayPartnersSummary['uniqueUsers'],  $aReportArrayPartnersSummary['totalOffersTaken'] ,$aReportArrayPartnersSummary['avgOffersTakenPerUser'], $aReportArrayPartnersSummary['partnerRep'], $aReportArrayPartnersSummary['companyName'], $aReportArrayPartnersSummary['e1SubCounts'], $aReportArrayPartnersSummary['iCampaignTypeId']);
			break;
			case "totalOffersTaken" :
			array_multisort($aReportArrayPartnersSummary['totalOffersTaken'], $sCurrOrder, $aReportArrayPartnersSummary['uniqueUsers'],  $aReportArrayPartnersSummary['totalRevenue'], $aReportArrayPartnersSummary['avgOffersTakenPerUser'], $aReportArrayPartnersSummary['partnerRep'], $aReportArrayPartnersSummary['companyName'], $aReportArrayPartnersSummary['e1SubCounts'], $aReportArrayPartnersSummary['iCampaignTypeId']);
			break;
			case "avgOffersTakenPerUser" :
			array_multisort($aReportArrayPartnersSummary['avgOffersTakenPerUser'], $sCurrOrder, $aReportArrayPartnersSummary['uniqueUsers'],  $aReportArrayPartnersSummary['totalRevenue'], $aReportArrayPartnersSummary['totalOffersTaken'] , $aReportArrayPartnersSummary['partnerRep'], $aReportArrayPartnersSummary['companyName'], $aReportArrayPartnersSummary['e1SubCounts'], $aReportArrayPartnersSummary['iCampaignTypeId']);
			break;
			case "partnerRep" :
			array_multisort($aReportArrayPartnersSummary['partnerRep'], $sCurrOrder, $aReportArrayPartnersSummary['uniqueUsers'],  $aReportArrayPartnersSummary['totalRevenue'], $aReportArrayPartnersSummary['totalOffersTaken'] ,$aReportArrayPartnersSummary['avgOffersTakenPerUser'], $aReportArrayPartnersSummary['companyName'], $aReportArrayPartnersSummary['e1SubCounts'], $aReportArrayPartnersSummary['iCampaignTypeId']);
			break;
			case "iCampaignTypeId" :
			array_multisort($aReportArrayPartnersSummary['iCampaignTypeId'], $sCurrOrder, $aReportArrayPartnersSummary['uniqueUsers'],  $aReportArrayPartnersSummary['totalRevenue'], $aReportArrayPartnersSummary['totalOffersTaken'] ,$aReportArrayPartnersSummary['avgOffersTakenPerUser'], $aReportArrayPartnersSummary['companyName'], $aReportArrayPartnersSummary['e1SubCounts'], $aReportArrayPartnersSummary['partnerRep']);
			break;
			default:
			array_multisort($aReportArrayPartnersSummary['companyName'], $sCurrOrder, $aReportArrayPartnersSummary['uniqueUsers'],  $aReportArrayPartnersSummary['totalRevenue'], $aReportArrayPartnersSummary['totalOffersTaken'] ,$aReportArrayPartnersSummary['avgOffersTakenPerUser'], $aReportArrayPartnersSummary['partnerRep'], $aReportArrayPartnersSummary['e1SubCounts'], $aReportArrayPartnersSummary['iCampaignTypeId']);
		}

		$iCountEntries = count($aReportArrayPartnersSummary['companyName']);
		$sReportContentNew = "";
		$iTempE1SubCounts = 0;
		$iUniqueUsers = 0;
		$iTotalOffersTaken = 0;
		$fTotalRevenue = 0;
		$fAvgOffersTakenPerUser = 0;
		
		$iAPIE1Total = 0;
		$iAPIUniqueTotal = 0;
		$iAPIOfferTakenTotal = 0;
		$iAPIAvgTakenTotal = 0;
		$iAPIRevTotal = 0;
				
		$iEmailE1Total = 0;
		$iEmailUniqueTotal = 0;
		$iEmailOfferTakenTotal = 0;
		$iEmailAvgTakenTotal = 0;
		$iEmailRevTotal = 0;
				
		$iHeaderE1Total = 0;
		$iHeaderUniqueTotal = 0;
		$iHeaderOfferTakenTotal = 0;
		$iHeaderAvgTakenTotal = 0;
		$iHeaderRevTotal = 0;
		
		$iBDE1Total = 0;
		$iBDUniqueTotal = 0;
		$iBDOfferTakenTotal = 0;
		$iBDAvgTakenTotal = 0;
		$iBDRevTotal = 0;
				
		$iBannerE1Total = 0;
		$iBannerUniqueTotal = 0;
		$iBannerOfferTakenTotal = 0;
		$iBannerAvgTakenTotal = 0;
		$iBannerRevTotal = 0;
		
		$iTextE1Total = 0;
		$iTextUniqueTotal = 0;
		$iTextOfferTakenTotal = 0;
		$iTextAvgTakenTotal = 0;
		$iTextRevTotal = 0;
		
		$iPopE1Total = 0;
		$iPopUniqueTotal = 0;
		$iPopOfferTakenTotal = 0;
		$iPopAvgTakenTotal = 0;
		$iPopRevTotal = 0;
		
		$iOtherE1Total = 0;
		$iOtherUniqueTotal = 0;
		$iOtherOfferTakenTotal = 0;
		$iOtherAvgTakenTotal = 0;
		$iOtherRevTotal = 0;

		for( $iLoop=0; $iLoop<$iCountEntries; $iLoop++ ) {
			if ($aReportArrayPartnersSummary['uniqueUsers'][$iLoop] == "") {
				$aReportArrayPartnersSummary['uniqueUsers'][$iLoop] = 0;
			}
			
			if ($aReportArrayPartnersSummary['totalOffersTaken'][$iLoop] == "") {
				$aReportArrayPartnersSummary['totalOffersTaken'][$iLoop] = 0;
			}
		
			$aReportArrayPartnersSummary['avgOffersTakenPerUser'][$iLoop] = number_format($aReportArrayPartnersSummary['avgOffersTakenPerUser'][$iLoop], 2, '.', "");
			$aReportArrayPartnersSummary['totalRevenue'][$iLoop] = number_format($aReportArrayPartnersSummary['totalRevenue'][$iLoop], 2, '.', "");
			
			if ($aReportArrayPartnersSummary['avgOffersTakenPerUser'][$iLoop] != 0 && $aReportArrayPartnersSummary['totalRevenue'][$iLoop] != 0) {
				if ($sBgcolorClass == "ODD") {
					$sBgcolorClass = "EVEN_WHITE";
				} else {
					$sBgcolorClass = "ODD";
				}
				$sReportContentNew .="<tr class=$sBgcolorClass><td>".$aReportArrayPartnersSummary['partnerRep'][$iLoop]."</td>
						<td>".$aReportArrayPartnersSummary['companyName'][$iLoop]."</td>
						<td>".$aReportArrayPartnersSummary['iCampaignTypeId'][$iLoop]."</td>
						<td>".$aReportArrayPartnersSummary['e1SubCounts'][$iLoop]."</td>
						<td>".$aReportArrayPartnersSummary['uniqueUsers'][$iLoop]."</td>
						<td>".$aReportArrayPartnersSummary['totalOffersTaken'][$iLoop]."</td>
						<td>".$aReportArrayPartnersSummary['avgOffersTakenPerUser'][$iLoop]."</td>
						<td align=right>".$aReportArrayPartnersSummary['totalRevenue'][$iLoop]."</td></tr>";
				
				if ($iCampaignType == '') {
					if ($aReportArrayPartnersSummary['iCampaignTypeId'][$iLoop] == 'Other') {
						$iOtherE1Total += $aReportArrayPartnersSummary['e1SubCounts'][$iLoop];
						$iOtherUniqueTotal += $aReportArrayPartnersSummary['uniqueUsers'][$iLoop];
						$iOtherOfferTakenTotal += $aReportArrayPartnersSummary['totalOffersTaken'][$iLoop];
						$iOtherAvgTakenTotal += $aReportArrayPartnersSummary['avgOffersTakenPerUser'][$iLoop];
						$iOtherRevTotal += $aReportArrayPartnersSummary['totalRevenue'][$iLoop];
					}
					
					if ($aReportArrayPartnersSummary['iCampaignTypeId'][$iLoop] == 'Popup') {
						$iPopE1Total += $aReportArrayPartnersSummary['e1SubCounts'][$iLoop];
						$iPopUniqueTotal += $aReportArrayPartnersSummary['uniqueUsers'][$iLoop];
						$iPopOfferTakenTotal += $aReportArrayPartnersSummary['totalOffersTaken'][$iLoop];
						$iPopAvgTakenTotal += $aReportArrayPartnersSummary['avgOffersTakenPerUser'][$iLoop];
						$iPopRevTotal += $aReportArrayPartnersSummary['totalRevenue'][$iLoop];
					}
				
					if ($aReportArrayPartnersSummary['iCampaignTypeId'][$iLoop] == 'Text link') {
						$iTextE1Total += $aReportArrayPartnersSummary['e1SubCounts'][$iLoop];
						$iTextUniqueTotal += $aReportArrayPartnersSummary['uniqueUsers'][$iLoop];
						$iTextOfferTakenTotal += $aReportArrayPartnersSummary['totalOffersTaken'][$iLoop];
						$iTextAvgTakenTotal += $aReportArrayPartnersSummary['avgOffersTakenPerUser'][$iLoop];
						$iTextRevTotal += $aReportArrayPartnersSummary['totalRevenue'][$iLoop];
					}
					
					if ($aReportArrayPartnersSummary['iCampaignTypeId'][$iLoop] == 'Banner') {
						$iBannerE1Total += $aReportArrayPartnersSummary['e1SubCounts'][$iLoop];
						$iBannerUniqueTotal += $aReportArrayPartnersSummary['uniqueUsers'][$iLoop];
						$iBannerOfferTakenTotal += $aReportArrayPartnersSummary['totalOffersTaken'][$iLoop];
						$iBannerAvgTakenTotal += $aReportArrayPartnersSummary['avgOffersTakenPerUser'][$iLoop];
						$iBannerRevTotal += $aReportArrayPartnersSummary['totalRevenue'][$iLoop];
					}
					
					if ($aReportArrayPartnersSummary['iCampaignTypeId'][$iLoop] == 'API') {
						$iAPIE1Total += $aReportArrayPartnersSummary['e1SubCounts'][$iLoop];
						$iAPIUniqueTotal += $aReportArrayPartnersSummary['uniqueUsers'][$iLoop];
						$iAPIOfferTakenTotal += $aReportArrayPartnersSummary['totalOffersTaken'][$iLoop];
						$iAPIAvgTakenTotal += $aReportArrayPartnersSummary['avgOffersTakenPerUser'][$iLoop];
						$iAPIRevTotal += $aReportArrayPartnersSummary['totalRevenue'][$iLoop];
					}
									
					if ($aReportArrayPartnersSummary['iCampaignTypeId'][$iLoop] == 'Email') {
						$iEmailE1Total += $aReportArrayPartnersSummary['e1SubCounts'][$iLoop];
						$iEmailUniqueTotal += $aReportArrayPartnersSummary['uniqueUsers'][$iLoop];
						$iEmailOfferTakenTotal += $aReportArrayPartnersSummary['totalOffersTaken'][$iLoop];
						$iEmailAvgTakenTotal += $aReportArrayPartnersSummary['avgOffersTakenPerUser'][$iLoop];
						$iEmailRevTotal += $aReportArrayPartnersSummary['totalRevenue'][$iLoop];
					}
									
					if ($aReportArrayPartnersSummary['iCampaignTypeId'][$iLoop] == 'Header') {
						$iHeaderE1Total += $aReportArrayPartnersSummary['e1SubCounts'][$iLoop];
						$iHeaderUniqueTotal += $aReportArrayPartnersSummary['uniqueUsers'][$iLoop];
						$iHeaderOfferTakenTotal += $aReportArrayPartnersSummary['totalOffersTaken'][$iLoop];
						$iHeaderAvgTakenTotal += $aReportArrayPartnersSummary['avgOffersTakenPerUser'][$iLoop];
						$iHeaderRevTotal += $aReportArrayPartnersSummary['totalRevenue'][$iLoop];
					}

					if ($aReportArrayPartnersSummary['iCampaignTypeId'][$iLoop] == 'BD Flow/Cobrand') {
						$iBDE1Total += $aReportArrayPartnersSummary['e1SubCounts'][$iLoop];
						$iBDUniqueTotal += $aReportArrayPartnersSummary['uniqueUsers'][$iLoop];
						$iBDOfferTakenTotal += $aReportArrayPartnersSummary['totalOffersTaken'][$iLoop];
						$iBDAvgTakenTotal += $aReportArrayPartnersSummary['avgOffersTakenPerUser'][$iLoop];
						$iBDRevTotal += $aReportArrayPartnersSummary['totalRevenue'][$iLoop];
					}
				}
			}
		}
		
		$GTotalRevenue = number_format($GTotalRevenue, 2, '.', "");
		$sReportContentNew .= "<tr><td colspan=8><hr color=#000000></td></tr>
					<tr><td><b>Summary: </b></td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td><b>$GTempE1SubCounts</b></td>
					<td><b>$GUniqueUsers</b></td>
					<td><b>$GTotalOffersTaken</b></td>
					<td><b>$GAvgOffersTakenPerUser</b></td>
					<td align=right><b>$GTotalRevenue</b></td></tr></tr>";
		
		
		if ($iCampaignType == '' && $sShowSeparateTotal == 'Y') {
			if ($iAPIUniqueTotal > 0) {
				$iAPIAvgTakenTotal = number_format(($iAPIOfferTakenTotal / $iAPIUniqueTotal), 2, '.', "");
			} else {
				$iAPIAvgTakenTotal = 0;
			}
		
			$sReportContentNew .= "<tr><td colspan=8></td></tr>
				<tr><td><b>API: </b></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><b>$iAPIE1Total</b></td>
				<td><b>$iAPIUniqueTotal</b></td>
				<td><b>$iAPIOfferTakenTotal</b></td>
				<td><b>$iAPIAvgTakenTotal</b></td>
				<td align=right><b>$iAPIRevTotal</b></td></tr></tr>";
			
			
			if ($iPopUniqueTotal > 0) {
				$iPopAvgTakenTotal = number_format(($iPopOfferTakenTotal / $iPopUniqueTotal), 2, '.', "");
			} else {
				$iPopAvgTakenTotal = 0;
			}
			$sReportContentNew .= "<tr><td colspan=8></td></tr>
				<tr><td><b>Pop: </b></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><b>$iPopE1Total</b></td>
				<td><b>$iPopUniqueTotal</b></td>
				<td><b>$iPopOfferTakenTotal</b></td>
				<td><b>$iPopAvgTakenTotal</b></td>
				<td align=right><b>$iPopRevTotal</b></td></tr></tr>";

			if ($iTextUniqueTotal > 0) {
				$iTextAvgTakenTotal = number_format(($iTextOfferTakenTotal / $iTextUniqueTotal), 2, '.', "");
			} else {
				$iTextAvgTakenTotal = 0;
			}
			$sReportContentNew .= "<tr><td colspan=8></td></tr>
				<tr><td><b>Text Link: </b></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><b>$iTextE1Total</b></td>
				<td><b>$iTextUniqueTotal</b></td>
				<td><b>$iTextOfferTakenTotal</b></td>
				<td><b>$iTextAvgTakenTotal</b></td>
				<td align=right><b>$iTextRevTotal</b></td></tr></tr>";
			
			if ($iBannerUniqueTotal > 0) {
				$iBannerAvgTakenTotal = number_format(($iBannerOfferTakenTotal / $iBannerUniqueTotal), 2, '.', "");
			} else {
				$iBannerAvgTakenTotal = 0;
			}
			$sReportContentNew .= "<tr><td colspan=8></td></tr>
				<tr><td><b>Banner: </b></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><b>$iBannerE1Total</b></td>
				<td><b>$iBannerUniqueTotal</b></td>
				<td><b>$iBannerOfferTakenTotal</b></td>
				<td><b>$iBannerAvgTakenTotal</b></td>
				<td align=right><b>$iBannerRevTotal</b></td></tr></tr>";
			
			if ($iBDUniqueTotal > 0) {
				$iBDAvgTakenTotal = number_format(($iBDOfferTakenTotal / $iBDUniqueTotal), 2, '.', "");
			} else {
				$iBDAvgTakenTotal = 0;
			}
			$sReportContentNew .= "<tr><td colspan=8></td></tr>
				<tr><td><b>BD Flow/Cobrand: </b></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><b>$iBDE1Total</b></td>
				<td><b>$iBDUniqueTotal</b></td>
				<td><b>$iBDOfferTakenTotal</b></td>
				<td><b>$iBDAvgTakenTotal</b></td>
				<td align=right><b>$iBDRevTotal</b></td></tr></tr>";
			
			
			if ($iHeaderUniqueTotal > 0) {
				$iHeaderAvgTakenTotal = number_format(($iHeaderOfferTakenTotal / $iHeaderUniqueTotal), 2, '.', "");
			} else {
				$iHeaderAvgTakenTotal = 0;
			}
			$sReportContentNew .= "<tr><td colspan=8></td></tr>
				<tr><td><b>Header: </b></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><b>$iHeaderE1Total</b></td>
				<td><b>$iHeaderUniqueTotal</b></td>
				<td><b>$iHeaderOfferTakenTotal</b></td>
				<td><b>$iHeaderAvgTakenTotal</b></td>
				<td align=right><b>$iHeaderRevTotal</b></td></tr></tr>";
			
			
			if ($iEmailUniqueTotal > 0) {
				$iEmailAvgTakenTotal = number_format(($iEmailOfferTakenTotal / $iEmailUniqueTotal), 2, '.', "");
			} else {
				$iEmailAvgTakenTotal = 0;
			}
			$sReportContentNew .= "<tr><td colspan=8></td></tr>
				<tr><td><b>Email: </b></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><b>$iEmailE1Total</b></td>
				<td><b>$iEmailUniqueTotal</b></td>
				<td><b>$iEmailOfferTakenTotal</b></td>
				<td><b>$iEmailAvgTakenTotal</b></td>
				<td align=right><b>$iEmailRevTotal</b></td></tr></tr>";
			
			
			if ($iOtherUniqueTotal > 0) {
				$iOtherAvgTakenTotal = number_format(($iOtherOfferTakenTotal / $iOtherUniqueTotal), 2, '.', "");
			} else {
				$iOtherAvgTakenTotal = 0;
			}
				
			$sReportContentNew .= "<tr><td colspan=8></td></tr>
				<tr><td><b>Other: </b></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><b>$iOtherE1Total</b></td>
				<td><b>$iOtherUniqueTotal</b></td>
				<td><b>$iOtherOfferTakenTotal</b></td>
				<td><b>$iOtherAvgTakenTotal</b></td>
				<td align=right><b>$iOtherRevTotal</b></td></tr></tr>";
		}
		
		$sReportContentNew .= "<tr><td colspan=8><hr color=#000000></td></tr>";

		if ($sExportExcel && $sPartnersSummaryChecked=='checked') {
			$sFileName = "sourceAnalysis_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";
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
	}
	
	include("../../includes/adminHeader.php");

	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);

	// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;

?>
<script language=JavaScript>

function funcAllSource() {
	if (document.form1.sAllSourceCodes.checked) {
		document.form1.sSourceCode.value = '';
		document.form1.sSourceCode.disabled = true;
		document.form1.sPartnerCode.value = '';
		document.form1.sPartnerCode.disabled = true;
	} else {
		document.form1.sSourceCode.disabled = false;
	}
}

function funcAllPartner() {
	if (document.form1.sAllPartnerCodes.checked) {
		document.form1.sPartnerCode.value = '';
		document.form1.sPartnerCode.disabled = true;
		document.form1.sSourceCode.value = '';
		document.form1.sSourceCode.disabled = true;
	} else {
		document.form1.sPartnerCode.disabled = false;
	}
	
}

</script>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><td><?php echo $sLinksLink;?></td></tr>
	<tr><td>Date From</td><td><input type=textbox name=sDateFrom Value='<?php echo $sDateFrom;?>' onChange='document.form1.submit();'></td></tr>
	<tr><td>Date To</td><td><input type=textbox name=sDateTo Value='<?php echo $sDateTo;?>' onChange='document.form1.submit();'></td></tr>	
	<tr><td>Source Code</td><td><select name=sAllSourceCodes>
			<option value='All' selected>All
			<?php echo $sSourceCodeOptions;?>
			</select></td></tr>
	<tr><td><b>OR</b>  &nbsp; Source Code</td><td><input type=text name=sSourceCode value='<?php echo $sSourceCode;?>'>
			<input type='radio' name='sFilter' value='startsWith' <?php echo $sStartsWithChecked;?>> Starts With
		&nbsp; <input type='radio' name='sFilter' value='exactMatch' <?php echo $sExactMatchChecked;?>> Exact Match
	</td></tr>
	<tr><td>Sub Source Code</td><td><input type=text name=sSubSourceCode value='<?php echo $sSubSourceCode;?>'>
			<input type='radio' name='sSsFilter' value='startsWith' <?php echo $sSsStartsWithChecked;?>> Starts With
		&nbsp; <input type='radio' name='sSsFilter' value='exactMatch' <?php echo $sSsExactMatchChecked;?>> Exact Match
	</td></tr>
	<tr><Td>Account Executive</td><td><select name=iAccountRep><?php echo $sAccountRepOptions;?></select> </td></tr>
	
	
	<tr><td>Campaign Types</td><td>
		<select name=iCampaignType>
		<option value='' selected>All</option>
		<?php echo $sCampaignTypeOptions;?></select>&nbsp;&nbsp;
		<input type=checkbox name=sShowSeparateTotal value='Y' <?php echo $sShowSeparateTotalChecked;?>> Show Separate Total (Campaign Types: All)
	</td></tr>
	
	
	<tr><td>Rate Structure</td><td>
		<select name=iCampaignRateType>
		<option value='' selected>All</option>
		<?php echo $sCampaignRateTypeOptions;?></select>
	</td></tr>
	
	

	<tr><td>Report Options</td>
		<td ><input type=radio name=sPostalVerified value='pvAndNonPv' <?php echo $sPvAndNonPvChecked;?>> Gross Leads
				<input type=radio name=sPostalVerified value='pvOnly' <?php echo $sPvOnlyChecked;?>> PostalVerified
				<input type=radio name=sPostalVerified value='fullDetails' <?php echo $sFullDetailsChecked;?>> Full Details
	</td></tr>
	<tr><td></td><td><input type=checkbox name=sExcludeNonRevenue value='Y' <?php echo $sExcludeNonRevenueChecked;?>> Exclude Non-Revenue Offers</td></tr>
	<tr><td></td><td><input type=checkbox name=sIncludeTestLeads value='Y' <?php echo $sIncludeTestLeadsChecked;?>> Include 3401 Test Leads</td></tr>
	<tr><td></td><td><input type=checkbox name=sSuppressSubSource value='Y' <?php echo $sSuppressSubSourceChecked;?>> Suppress Sub Source Code&nbsp;&nbsp;&nbsp;&nbsp;
	<input type=checkbox name=sTruncateSubSource value='Y' <?php echo $sTruncateSubSourceChecked;?>> Truncate Sub Source Code</td></tr>
	<tr><td></td><td><input type=checkbox name=sPartnersSummary value='Y' <?php echo $sPartnersSummaryChecked;?>> Partners Summary</td></tr>
	<tr><td></td><td><input type=checkbox name=sShowExpired value='Y' <?php echo $sShowExpiredChecked;?>> Show Expired Campaigns</td></tr>
	<tr><td colspan=2><input type=button name=sSubmit value='History Report' onClick="funcReportClicked('history');">  &nbsp; &nbsp; 
	<input type=button name=sSubmit value="Today's Report" onClick="funcReportClicked('today');">  &nbsp; &nbsp; 
	<input type=checkbox name=sExportExcel value="Y" <?php echo $sExportExcelChecked;?>> Export To Excel
	&nbsp; &nbsp; &nbsp; <input type=checkbox name=sExportEmails value="Y"  <?php echo $sExportEmailsChecked;?>> Export Emails
	&nbsp; &nbsp; &nbsp; <input type=checkbox name=sShowQueries value='Y' <?php echo $sShowQueriesChecked;?>> Show Queries</td></tr>
	<!--<input type=submit name=sPrintReport value='Print This Report'></td></tr>-->
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center border=0>
	<tr><td colspan=12 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=12 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr>
	
	<?php
		if ($sPartnersSummaryChecked!='checked') { ?>
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=accountRep&sAccountRepOrder=<?php echo $sAccountRepOrder;?>" class=header>Account Executive</a></td>
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=sourceCode&sSourceCodeOrder=<?php echo $sSourceCodeOrder;?>" class=header>Source Code</a></td>
		<?php echo $sSubSourceHeader;?>
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=companyName&sCompanyNameOrder=<?php echo $sCompanyNameOrder;?>" class=header>Partner Name</a></td>
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=iCampaignTypeId&sCampaignTypeOrder=<?php echo $sCampaignTypeOrder;?>" class=header>Campaign Type</a></td>
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=e1SubCounts&companyName&sE1SubCountsOrder=<?php echo $sE1SubCountsOrder;?>" class=header>e1 Captures</a></td>
		<td align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=uniqueUsers&sUniqueUsersOrder=<?php echo $sUniqueUsersOrder;?>" class=header>Gross Unique Users</a></td>
		<?php 

		if ($sViewReport == 'History Report' && $sPostalVerified == 'fullDetails') {
			echo "<td align=right><a href=\"$sSortLink&sOrderColumn=uniqueUsers&sUniqueUsersOrder=$sUniqueUsersOrder\" class=header>Dup Unique Users</a></td>
					<td align=right><a href=\"$sSortLink&sOrderColumn=uniqueUsers&sUniqueUsersOrder=$sUniqueUsersOrder\" class=header>Npv Unique Users</a></td>				
					<td align=right><a href=\"$sSortLink&sOrderColumn=uniqueUsers&sUniqueUsersOrder=$sUniqueUsersOrder\" class=header>Ncv Unique Users</a></td>
					<td align=right><a href=\"$sSortLink&sOrderColumn=uniqueUsers&sUniqueUsersOrder=$sUniqueUsersOrder\" class=header>Net Unique Users</a></td>";
		}

		if ($sViewReport == 'History Report' && ($sPostalVerified == 'fullDetails' || $sPostalVerified == 'pvOnly')) {
			echo "<td align=right><a href=\"$sSortLink&sOrderColumn=totalOffersTaken&sTotalOffersTakenOrder=$sTotalOffersTakenOrder\" class=header>Validated Offers Taken</a></td>";
		} else {
			echo "<td align=right><a href=\"$sSortLink&sOrderColumn=totalOffersTaken&sTotalOffersTakenOrder=$sTotalOffersTakenOrder\" class=header>Total Offers Taken</a></td>";
		}


		?>
		<td align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=avgOffersTakenPerUser&sAvgOffersTakenPerUserOrder=<?php echo $sAvgOffersTakenPerUserOrder;?>" class=header>Avg. Offers Taken Per User</a></td>
		<td align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=totalRevenue&sTotalRevenueOrder=<?php echo $sTotalRevenueOrder;?>" class=header>Total Revenue</a></td>
		
		
		<?php if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') { ?>
		<td align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=dateAdded&sDateAddedOrder=<?php echo $sDateAddedOrder;?>" class=header>Date</a></td>
		<?php } ?>
		
		</tr>
	<?php } else { ?>
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=partnerRep&sAccountRepOrder=<?php echo $sAccountRepOrder;?>" class=header>Account Executive</a></td>
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=companyName&sCompanyNameOrder=<?php echo $sCompanyNameOrder;?>" class=header>Partner Name</a></td>
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=iCampaignTypeId&sCampaignTypeOrder=<?php echo $sCampaignTypeOrder;?>" class=header>Campaign Type</a></td>
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=e1SubCounts&companyName&sE1SubCountsOrder=<?php echo $sE1SubCountsOrder;?>" class=header>e1 Captures</a></td>
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=uniqueUsers&sUniqueUsersOrder=<?php echo $sUniqueUsersOrder;?>" class=header>Gross Unique Users</a></td>
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=totalOffersTaken&sTotalOffersTakenOrder=<?php echo $sTotalOffersTakenOrder;?>" class=header>Total Offers Taken</a></td>
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=avgOffersTakenPerUser&sAvgOffersTakenPerUserOrder=<?php echo $sAvgOffersTakenPerUserOrder;?>" class=header>Avg. Offers Taken Per User</a></td>
		<td align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=totalRevenue&sTotalRevenueOrder=<?php echo $sTotalRevenueOrder;?>" class=header>Total Revenue</a></td>		
	</tr>
	<?php } ?>
	
	<?php
	if ($sPartnersSummaryChecked!='checked') {
		echo $sReportContent;
	} else {
		echo $sReportContentNew;
	}
	?>

	<tr><td colspan=12 class=header><BR>Notes -</td></tr>
	<tr><td colspan=12>Counts will change as postal verification status changes.
				<BR><BR>If you change the date range, you need to click the button once again to view the report.
				<BR><BR>e1 Captures are the gross number of emails submitted through an e1 form that pass front end bounds checks.
				<BR><BR>Gross Unique Users: This is the count of distinct email from otData/otDataHistory group by sourceCode.
				<BR><BR>Total Offers Taken: This is the count of email from otData/otDataHistory group by sourceCode.
				<BR><BR>Report omits any leads having address starting with '3401 Dundee' considering those as test leads if "Include 3401 Test Leads" is not checked. 
						Test leads can be included only in today's report and deleted next day.
				<BR><BR>For history report, counts only reflects records where PV attempted. For today's report, report reflects gross counts.</td></tr>	
	<tr><td colspan=10>Gross Unique Users in Source Analysis Report may be higher than gross unique users in Campaign Analysis Report
					because in Source Analysis Report user will be unique for a source code and same user may be unique user 
					for another source code also if he came up in our site through different source codes resulting the total unique user count higher than 
					Campaign Analysis Report.
					
					<br><br>Campaign Types / Rate Structure filter works only if report is for all source codes.  There is no
					point of filtering by campaign type or rate structure if you are running report for specific source code.
					
					<br><br>Report will show separate total only if 'Show Separate Total' is checked and All is selected for 'Campaign Types'.

					<BR><BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=12><BR><BR></td></tr>
	<tr><td colspan=12><?php echo wordwrap($sQueries); ?></td></tr>
	
	<tr><td colspan=12><BR><BR></td></tr>
		</td></tr></table></td></tr></table></td></tr>
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
