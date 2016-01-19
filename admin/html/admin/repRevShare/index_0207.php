<?php

/*********

Script to Display

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

set_time_limit(5000);
$iScriptStartTime = getMicroTime();

$sPageTitle = "Revenue Share Report";

session_start();

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
	
	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";
	
	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	$sToday = date('m')."-".date('d')."-".date('Y');
	
	$sViewReport = stripslashes($sViewReport);
	
	if ($sExportExcel && $sExportEmails) {
		$sMessage = "Please check only one export option...";
	} else {
		
		if (!($sDateFrom || $sDateTo)) {
			$sDateFrom = $sToday;
			$sDateTo = $sToday;
			if (!($sViewReport)) {
				$sViewReport = "Today's Report";
			}
		}
		
		
		if (!(isset($iAccountRep)) && $_SERVER['PHP_AUTH_USER'] != 'phil' && $_SERVER['PHP_AUTH_USER'] != 'stuart') {
			$sUserQuery = "SELECT nbUsers.*
				  FROM   nbUsers
				  WHERE  userName = '".$_SERVER['PHP_AUTH_USER']."'";
			
			$rUserResult = dbQuery($sUserQuery);
			while ($oUserRow = dbFetchObject($rUserResult)) {
				$iAccountRep = $oUserRow->id;
			}
		}
		
		
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
			
			
		} else  if ($sViewReport == "Today's Report") {
			
			//if (!($sDateFrom)) {
			$iYearFrom = date('Y');
			$iMonthFrom = date('m');
			$iDayFrom = date('d');
			
			
			/*if (!($iYearFrom)) {
			
			}*/
			$sDateFrom = "$iMonthFrom-$iDayFrom-$iYearFrom";
			
			//}
			
			//if (!($sDateTo)) {
			$iMonthTo = $iMonthFrom;
			$iDayTo = $iDayFrom;
			$iYearTo = $iYearFrom;
			
			$sDateTo = "$iMonthTo-$iDayTo-$iYearTo";
			//}
		}
		
		
		if ($sDateFrom && $sDateTo) {
			
			$sTempDateFromArray = explode("-", $sDateFrom);
			$sTempDateToArray = explode("-", $sDateTo);
			
			// temdate are the dates in mysql format
			
			$sTempDateFrom = $sTempDateFromArray[2]."-".$sTempDateFromArray[0]."-".$sTempDateFromArray[1];
			$sTempDateTo = $sTempDateToArray[2]."-".$sTempDateToArray[0]."-".$sTempDateToArray[1];
			$sTempToday = date('Y')."-".date('m')."-".date('d');
			
			// initialize all variables to 0;
			$iUniqueUsers = 0;
			$iUniqueUsersNonPv = 0;
			$iOffersTakenByPvUsers = 0;
			$fAvgOffersTakenPerUser = 0;
			$fGrossRevenue = 0;
			$fGrossPvRevenue = 0;
			
			if ($sTempDateFrom == $sTempToday && $sTempDateTo == $sTempToday) {
				$sSourceCodeQuery = "SELECT distinct campaigns.sourceCode
						 FROM   otData, campaigns
						 WHERE  otData.sourceCode = campaigns.sourceCode
						 AND	campaignTypeId = '4'
						 AND	date_format(dateTimeAdded, '%Y-%m-%d') between '$sTempDateFrom' AND '$sTempDateTo'
						 ORDER BY campaigns.sourceCode"; 
			} else {
				$sSourceCodeQuery = "SELECT distinct campaigns.sourceCode
							 FROM   otDataHistory, campaigns
							 WHERE  otDataHistory.sourceCode = campaigns.sourceCode
							 AND	campaignTypeId = '4'
							 AND	date_format(dateTimeAdded, '%Y-%m-%d') between '$sTempDateFrom' AND '$sTempDateTo'
							 ORDER BY campaigns.sourceCode"; 
				
			}
			
			//echo $sSourceCodeQuery.mysql_error();
			$rSourceCodeResult = dbQuery($sSourceCodeQuery);
			//echo mysql_num_rows($rSourceCodeResult);
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
				
			}
			
		}
		
		
		// Set Default order column
		if (!($sOrderColumn)) {
			if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
				$sOrderColumn = "dateAdded";
				$sDateAddedOrder = SORT_ASC;
			} else {
				$sOrderColumn = "sourceCode";
				$sSourceCodeOrder = SORT_ASC;
			}
		}
		
		
		// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
		if (!($sCurrOrder)) {
			switch ($sOrderColumn) {
				case "subSourceCode":
				$sCurrOrder = $sSubSourceCodeOrder;
				$sSubSourceCodeOrder = ($sSubSourceCodeOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
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
				$sAvgOffersTakenPerUserOrder = ($sAvgOffersTakenPerUserOrder != "ORT_DESC" ? "SORT_DESC" : "SORT_ASC");
				break;
				case "accountRep" :
				$sCurrOrder = $sAccountRepOrder;
				$sAccountRepOrder = ($sAccountRepOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
				break;
				case "companyName" :
				$sCurrOrder = $sCompanyNameOrder;
				$sCompanyNameOrder = ($sCompanyNameOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
				break;
				case "dateAdded" :
				$sCurrOrder = $sDateAddedOrder;
				$sDateAddedOrder = ($sDateAddedOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
				break;
				default:
				$sCurrOrder = $sSourceCodeOrder;
				$sSourceCodeOrder = ($sSourceCodeOrder != "SORT_DESC" ? "SORT_DESC" : "SORT_ASC");
			}
		}
		
		if ($sCurrOrder == 'SORT_DESC') {
			$sCurrOrder = SORT_DESC;
		} else {
			$sCurrOrder = SORT_ASC;
		}
		
		$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sSourceCode=$sSourceCode&sSubSourceCode=$sSubSourceCode&sFilter=$sFilter&sSsFilter=$sSsFilter&sPostalVerified=$sPostalVerified&
					sExcludeNonRevenue=$sExcludeNonRevenue&sDateFrom=$sDateFrom&sDateTo=$sDateTo&iRecPerPage=$iRecPerPage&iAccountRep=$iAccountRep&sViewReport=".urlencode($sViewReport);
		
		$i=0;
		
		/*
		$sSourceCodeQuery = "SELECT campaigns.*, companyName, repDesignated
							 	 FROM   campaigns, partnerCompanies
							  	 WHERE  campaigns.partnerId = partnerCompanies.id
								 AND	campaignTypeId = '4'  ";
			
			if ($sSourceCode != '') {
				if ($sFilter == 'startsWith') {
					$sSourceCodeQuery .= " AND campaigns.sourceCode LIKE '".$sSourceCode."%' ";
				} else if ($sFilter == 'exactMatch') {
					$sSourceCodeQuery .= " AND campaigns.sourceCode = '$sSourceCode' ";
				}
			} else if (isset($sAllSourceCodes)) {
				if ($sAllSourceCodes != 'All') {
					$sSourceCodeQuery .= " AND campaigns.sourceCode = '$sAllSourceCodes'";
				}
			}
			
			$sSourceCodeQuery .= " ORDER BY campaigns.sourceCode";
			
			$rSourceCodeResult = dbQuery($sSourceCodeQuery);
			
			$i=0;
			if ($rSourceCodeResult) {
			while ($oSourceCodeRow = dbFetchObject($rSourceCodeResult)) {
				
				$sTempSourceCode = $oSourceCodeRow->sourceCode;
				$sCompanyName = $oSourceCodeRow->companyName;
				$sRepDesignated = $oSourceCodeRow->repDesignated;
				
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
				
				$sTempAccountRep = "'".$iAccountRep."'";
				// prepare report only if rep designated is selected from the selection ( or all reps)
				if ($iAccountRep == '' || strstr($sRepDesignated,$sTempAccountRep)) {
										
					$aReportArray['partnerRep'][$i] = $sPartnerRep;
					$aReportArray['sourceCode'][$i] = $sTempSourceCode;
					$aReportArray['companyName'][$i] = $sCompanyName;
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
				}
				
				
			} // end of sourceCode while loop
			
			dbFreeResult($rSourceCodeResult);
			
			}
			
			
			
			// get gross e1 sub counts
				$iE1SubCounts = 0;
				$sE1CountsQuery = "SELECT sourceCode, sum(subs) AS totalSubs
								   FROM   e1TrackingSum
								   WHERE  submitDate BETWEEN '$sTempDateFrom' AND '$sTempDateTo'";
				
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
			 	
				$sE1CountsQuery .= " GROUP BY sourceCode"; 
				
				$rE1CountsResult = dbQuery($sE1CountsQuery);
				echo dbError();
				$i=0;
				while ($oE1CountsRow = dbFetchObject($rE1CountsResult)) {
					$sTempSourceCode = $oE1CountsRow->sourceCode;
					$iTempE1SubCounts = $oE1CountsRow->totalSubs;
					$sRepDesignated = '';
					$sCompanyName = '';
					$sPartnerRep = '';
					
					// get partner rep 	
						$sCompanyQuery = "SELECT companyName, repDesignated
							  FROM   campaigns, partnerCompanies
							  WHERE  campaigns.partnerId = partnerCompanies.id
							  AND	 campaigns.sourceCode = '$sTempSourceCode'";
					
					$rCompanyResult = dbQuery($sCompanyQuery);
					while ($oCompanyRow = dbFetchObject($rCompanyResult)) {
						$sCompanyName = $oCompanyRow->companyName;
						$sRepDesignated = $oCompanyRow->repDesignated;
						
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
					$aE1CountsSourceCodeArray['partnerRep'][$i] = $sPartnerRep;
					
					$aE1CountsSourceCodeArray['e1SubCounts'][$i] = $oE1CountsRow->totalSubs;
										
					$i++;
					}
				}
						
				
			$sRepDesignated = '';
			$sCompanyName = '';
			$sPartnerRep = '';
					*/
					
		if ($sViewReport != "Today's Report") {
					
			$sSourceCodeQuery = "SELECT campaigns.*, companyName, repDesignated
							 	 FROM   campaigns, partnerCompanies
							  	 WHERE  campaigns.partnerId = partnerCompanies.id
								 AND	campaignTypeId = '4'  ";
			if ($sSourceCode != '') {
					if ($sFilter == 'startsWith') {
						$sSourceCodeQuery .= " AND campaigns.sourceCode LIKE '".$sSourceCode."%' ";
					} else if ($sFilter == 'exactMatch') {
						$sSourceCodeQuery .= " AND campaigns.sourceCode = '$sSourceCode' ";
					}
				} else if (isset($sAllSourceCodes)) {
					if ($sAllSourceCodes != 'All') {
						$sSourceCodeQuery .= " AND campaigns.sourceCode = '$sAllSourceCodes'";
					}
				}
				
			$rSourceCodeResult = dbQuery($sSourceCodeQuery);
			echo dbError();
			$i=0;
			while ($oSourceCodeRow = dbFetchObject($rSourceCodeResult)) {	
					$sTempSourceCode = $oSourceCodeRow->sourceCode;	
					$fTempRate = $oSourceCodeRow->rate;
					
					// get offers taken info
					
					$sReportQuery = "SELECT otDataHistory.sourceCode, ";
					

					if ($sSuppressSubSource != 'Y' ) {
						$sReportQuery .= "otDataHistory.subSourceCode, ";
					}
					
					$sReportQuery .= " count(distinct otDataHistory.email) AS uniqueUsers,
								count(otDataHistory.email) AS totalOffersTaken, sum(1 * offers.revPerLead) as totalRevenue,
								sum(1 * offers.revPerLead * ".$fTempRate." ) as partnerNetRevenue";
					
					if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
						$sReportQuery .= ",date_format(otDataHistory.dateTimeAdded,'%Y-%m-%d') AS dateAdded ";
					}
					$sReportQuery .= " FROM   userDataHistory, otDataHistory, offers
						 WHERE  userDataHistory.email = otDataHistory.email			 			
						 AND    otDataHistory.offerCode	= offers.offerCode
						 AND    date_format(otDataHistory.dateTimeAdded,\"%Y-%m-%d\") BETWEEN '$sTempDateFrom' AND '$sTempDateTo'
						 AND	otDataHistory.sourceCode = '$sTempSourceCode' ";	
					
				
			
					if ($sSubSourceCode != '') {
						if ($sSsFilter == 'startsWith') {
							$sReportQuery .= " AND otDataHistory.subSourceCode LIKE '".$sSubSourceCode."%' ";
						} else if ($sSsFilter == 'exactMatch') {
							$sReportQuery .= " AND otDataHistory.subSourceCode = '$sSubSourceCode' ";
						}
					}
					
					if ($sPostalVerified == 'pvOnly') {
						$sReportQuery .= " AND userDataHistory.postalVerified = 'V' AND processStatus = 'P'";
					} else {
						$sReportQuery .= " AND userDataHistory.postalVerified IS NOT NULL ";
					}
					
					if ($sExcludeNonRevenue == 'Y') {
						$sReportQuery .= " AND offers.isNonRevenue != '1' ";
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
					
					while ($oReportRow = dbFetchObject($rReportResult)) {
						
						$sTempSubSourceCode = $oReportRow->subSourceCode;
						$iUniqueUsers = $oReportRow->uniqueUsers;
						$iTotalOffersTaken= $oReportRow->totalOffersTaken;
						$fTotalRevenue = $oReportRow->totalRevenue;
						$fPartnerNetRevenue = $oReportRow->partnerNetRevenue;
						
						//$fAvgOffersTakenPerUser = $iTotalOffersTaken / $iUniqueUsers; // $iTotalOffersTaken / $iUniqueUsers ;
						//$fAvgOffersTakenPerUser = sprintf("%10.2f",round($fAvgOffersTakenPerUser, 2));
						
						
						if ($sPrevSourcePrefix != substr($sTempSourceCode,0,3)) {
							$sCompanyName = '';
							$sPartnerRep = '';
							$sRepDesignated = '';
						//} else {
					
						// get partner rep 	
						$sCompanyQuery = "SELECT companyName, repDesignated
							  FROM   campaigns, partnerCompanies
							  WHERE  campaigns.partnerId = partnerCompanies.id
							  AND	 campaigns.sourceCode = '$sTempSourceCode'";
					
					$rCompanyResult = dbQuery($sCompanyQuery);
					while ($oCompanyRow = dbFetchObject($rCompanyResult)) {
						
						$sCompanyName = $oCompanyRow->companyName;
						
						$sRepDesignated = $oCompanyRow->repDesignated;
						
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
						// because myf and amp sourcecodes are not in campaigns table
						
						$aReportArray['sourceCode'][$i] = $sTempSourceCode;
						$aReportArray['subSourceCode'][$i] = $sTempSubSourceCode;						
						$aReportArray['companyName'][$i] = $sCompanyName;
						$aReportArray['partnerRep'][$i] = $sPartnerRep;
						$aReportArray['uniqueUsers'][$i] = $iUniqueUsers;
						$aReportArray['totalOffersTaken'][$i] = $iTotalOffersTaken;
						//$aReportArray['avgOffersTakenPerUser'][$i] = $fAvgOffersTakenPerUser;				
						$aReportArray['totalRevenue'][$i] = $fTotalRevenue;						
						$aReportArray['partnerNetRevenue'][$i] = $fPartnerNetRevenue;						
						$aReportArray['ourNetRevenue'][$i] = $fTotalRevenue - $fPartnerNetRevenue;
						
						if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
						
							$aReportArray['dateAdded'][$i] = $oReportRow->dateAdded;
						
						}
						
						$iClicks = '';
						$fConversion = '';
						$fEffectiveCpm = '';
						$aReportArray['clicks'][$i] = '';
						$aReportArray['conversion'][$i] = '';
						$aReportArray['effectiveCpm'][$i] = '';
						
						
						// get clicks
						$sClicksQuery = "SELECT sum(clicks) AS impressions
										  FROM	 bdRedirectsTrackingHistorySum
										  WHERE  clickDate BETWEEN '$sTempDateFrom' AND '$sTempDateTo'
										  AND	 sourceCode = '$sTempSourceCode'";			

						if ($sSubSourceCode != '') {
							if ($sSsFilter == 'startsWith') {
								$sClicksQuery .= " AND otData.subSourceCode LIKE '".$sSubSourceCode."%' ";
							} else if ($sSsFilter == 'exactMatch') {
								$sClicksQuery .= " AND otData.subSourceCode = '$sSubSourceCode' ";
							}
						}	
					
						$rClicksResult = dbQuery($sClicksQuery);
						echo dbError();
						while ($oClicksRow = dbFetchObject($rClicksResult)) {
							$iClicks = $oClicksRow->impressions;
							$aReportArray['clicks'][$i] = $iClicks;
						}
					
						if ($iClicks > 0 ) {
							$fConversion = ($iUniqueUsers / $iClicks) * 100;
							$fEffectiveCpm =  $fTotalRevenue / ($iClicks / 1000)  ;
						}
			
						$fConversion =  sprintf("%10.2f",round($fConversion, 2));
						$fEffectiveCpm = sprintf("%10.2f", round($fEffectiveCpm,2));
						
						$aReportArray['conversion'][$i] = $fConversion;
						$aReportArray['effectiveCpm'][$i] = $fEffectiveCpm;

						$sPrevSourcePrefix = substr($sTempSourceCode,0,3);
						$i++;	
					}																					
						
					} // end of offers taken while loop
					
					
			}
					// end if not today's report
					
				} else {
					
					// today's report
					
					$i=0;
					
			$sSourceCodeQuery = "SELECT campaigns.*, companyName, repDesignated
							 	 FROM   campaigns, partnerCompanies
							  	 WHERE  campaigns.partnerId = partnerCompanies.id
								 AND	campaignTypeId = '4'  ";
			if ($sSourceCode != '') {
					if ($sFilter == 'startsWith') {
						$sSourceCodeQuery .= " AND campaigns.sourceCode LIKE '".$sSourceCode."%' ";
					} else if ($sFilter == 'exactMatch') {
						$sSourceCodeQuery .= " AND campaigns.sourceCode = '$sSourceCode' ";
					}
				} else if (isset($sAllSourceCodes)) {
					if ($sAllSourceCodes != 'All') {
						$sSourceCodeQuery .= " AND campaigns.sourceCode = '$sAllSourceCodes'";
					}
				}
				
			$rSourceCodeResult = dbQuery($sSourceCodeQuery);
			while ($oSourceCodeRow = dbFetchObject($rSourceCodeResult)) {	
					$sTempSourceCode = $oSourceCodeRow->sourceCode;	
					$fTempRate = $oSourceCodeRow->rate;
					
					// get offers taken info
					
					$sReportQuery = "SELECT otData.sourceCode, ";
					

					if ($sSuppressSubSource != 'Y' ) {
						$sReportQuery .= "otData.subSourceCode, ";
					}
					
					$sReportQuery .= " count(distinct otData.email) AS uniqueUsers,
								count(otData.email) AS totalOffersTaken, sum(1 * offers.revPerLead) as totalRevenue,
								sum(1 * offers.revPerLead * ".$fTempRate." ) as partnerNetRevenue";
					
					if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
						$sReportQuery .= ",date_format(otData.dateTimeAdded,'%Y-%m-%d') AS dateAdded ";
					}
					$sReportQuery .= " FROM  userData, otData, offers
						 WHERE  userData.email = otData.email
						 AND	otData.offerCode	= offers.offerCode
						 AND    date_format(otData.dateTimeAdded,\"%Y-%m-%d\") BETWEEN '$sTempDateFrom' AND '$sTempDateTo'
						 AND	otData.sourceCode = '$sTempSourceCode' ";	
					
					if ($sIncludeTestLeads != 'Y') {
					 	$sReportQuery .= " AND 	address NOT LIKE \"3401 DUNDEE%\"" ;
					}
			 
						
			
					if ($sSubSourceCode != '') {
						if ($sSsFilter == 'startsWith') {
							$sReportQuery .= " AND otData.subSourceCode LIKE '".$sSubSourceCode."%' ";
						} else if ($sSsFilter == 'exactMatch') {
							$sReportQuery .= " AND otData.subSourceCode = '$sSubSourceCode' ";
						}
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
					
					
					while ($oReportRow = dbFetchObject($rReportResult)) {
						
						$sTempSubSourceCode = $oReportRow->subSourceCode;
						$iUniqueUsers = $oReportRow->uniqueUsers;
						$iTotalOffersTaken= $oReportRow->totalOffersTaken;
						$fTotalRevenue = $oReportRow->totalRevenue;
						$fPartnerNetRevenue = $oReportRow->partnerNetRevenue;
						
					//	$fAvgOffersTakenPerUser = $iTotalOffersTaken / $iUniqueUsers; // $iTotalOffersTaken / $iUniqueUsers ;
						//$fAvgOffersTakenPerUser = sprintf("%10.2f",round($fAvgOffersTakenPerUser, 2));
						
						
						if ($sPrevSourcePrefix != substr($sTempSourceCode,0,3)) {
							$sCompanyName = '';
							$sPartnerRep = '';
							$sRepDesignated = '';
						
					
						// get partner rep 	
						$sCompanyQuery = "SELECT companyName, repDesignated
							  FROM   campaigns, partnerCompanies
							  WHERE  campaigns.partnerId = partnerCompanies.id
							  AND	 campaigns.sourceCode = '$sTempSourceCode'";
					
					$rCompanyResult = dbQuery($sCompanyQuery);
					while ($oCompanyRow = dbFetchObject($rCompanyResult)) {
						$sCompanyName = $oCompanyRow->companyName;
						$sRepDesignated = $oCompanyRow->repDesignated;
						
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
						// because myf and amp sourcecodes are not in campaigns table
						
						$aReportArray['sourceCode'][$i] = $sTempSourceCode;
						$aReportArray['subSourceCode'][$i] = $sTempSubSourceCode;
						$aReportArray['companyName'][$i] = $sCompanyName;
						$aReportArray['partnerRep'][$i] = $sPartnerRep;
						$aReportArray['uniqueUsers'][$i] = $iUniqueUsers;
						$aReportArray['totalOffersTaken'][$i] = $iTotalOffersTaken;
						//$aReportArray['avgOffersTakenPerUser'][$i] = $fAvgOffersTakenPerUser;
						$aReportArray['totalRevenue'][$i] = $fTotalRevenue;						
						$aReportArray['partnerNetRevenue'][$i] = $fPartnerNetRevenue;			
						$aReportArray['ourNetRevenue'][$i] = $fTotalRevenue - $fPartnerNetRevenue;
						
						if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
						
							$aReportArray['dateAdded'][$i] = $oReportRow->dateAdded;

						}

						$iClicks = '';
						$fConversion = '';
						$fEffectiveCpm = '';
						$aReportArray['clicks'][$i] = '';
						$aReportArray['conversion'][$i] = '';
						$aReportArray['effectiveCpm'][$i] = '';
						
						
						// get clicks
						$sClicksQuery = "SELECT count(*) AS impressions
										  FROM	 bdRedirectsTracking
										  WHERE  clickDate BETWEEN '$sTempDateFrom' AND '$sTempDateTo'
										  AND	 sourceCode = '$sTempSourceCode'";			

						if ($sSubSourceCode != '') {
							if ($sSsFilter == 'startsWith') {
								$sClicksQuery .= " AND otData.subSourceCode LIKE '".$sSubSourceCode."%' ";
							} else if ($sSsFilter == 'exactMatch') {
								$sClicksQuery .= " AND otData.subSourceCode = '$sSubSourceCode' ";
							}
						}	
					
						$rClicksResult = dbQuery($sClicksQuery);
						echo dbError();
						while ($oClicksRow = dbFetchObject($rClicksResult)) {
							$iClicks = $oClicksRow->impressions;
							$aReportArray['clicks'][$i] = $iClicks;
						}
					
						if ($iClicks > 0 ) {
							$fConversion = ($iUniqueUsers / $iClicks) * 100;
							$fEffectiveCpm =  $fTotalRevenue / ($iClicks / 1000)  ;
						}
				
						$fConversion =  sprintf("%10.2f",round($fConversion, 2));
						$fEffectiveCpm = sprintf("%10.2f", round($fEffectiveCpm,2));
						
						$aReportArray['conversion'][$i] = $fConversion;
						$aReportArray['effectiveCpm'][$i] = $fEffectiveCpm;
														

						$sPrevSourcePrefix = substr($sTempSourceCode,0,3);
						$i++;	
					}
					
					} // end of today's offers taken while loop
					
					
				} // end of else -- (today's offers taken)
				
				}
				
			
			/***** sort arrays here for order by  *****/
			
			if (count ($aReportArray['sourceCode']) > 0 ) {
					
					if (count($aReportArray['dateAdded']) > 0)  {
						
						switch ($sOrderColumn) {
							case "subSourceCode" :
							array_multisort($aReportArray['subSourceCode'], $sCurrOrder, $aReportArray['uniqueUsers'], $aReportArray['sourceCode'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['clicks'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
							break;							
							case "uniqueUsers" :
							array_multisort($aReportArray['uniqueUsers'], $sCurrOrder, $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['clicks'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
							break;
							case "totalRevenue" :
							array_multisort($aReportArray['totalRevenue'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['totalOffersTaken'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['clicks'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
							break;
							case "totalOffersTaken" :
							array_multisort($aReportArray['totalOffersTaken'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['sourceCode'] , $aReportArray['subSourceCode'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['clicks'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
							break;
							//case "avgOffersTakenPerUser" :
							//array_multisort($aReportArray['avgOffersTakenPerUser'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['clicks'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
							//break;
							case "accountRep" :
							array_multisort($aReportArray['partnerRep'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] , $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['clicks'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
							break;
							case "companyName" :
							array_multisort($aReportArray['companyName'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] , $aReportArray['partnerRep'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['dateAdded'], $aReportArray['clicks'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
							break;
							case "clicks":
							array_multisort($aReportArray['clicks'], $sCurrOrder, $aReportArray['subSourceCode'],  $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['sourceCode'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
							break;
							case "conversion":
							array_multisort($aReportArray['conversion'], $sCurrOrder, $aReportArray['subSourceCode'],  $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['sourceCode'], $aReportArray['clicks'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
							break;
							case "effectiveCpm":
							array_multisort($aReportArray['effectiveCmp'], $sCurrOrder, $aReportArray['subSourceCode'],  $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['sourceCode'], $aReportArray['conversion'], $aReportArray['clicks'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
							break;
							case "ourNetRevenue":
							array_multisort($aReportArray['ourNetRevenue'], $sCurrOrder, $aReportArray['subSourceCode'],  $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['sourceCode'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['clicks'], $aReportArray['partnerNetRevenue']);
							break;
							case "partnerNetRevenue":
							array_multisort($aReportArray['partnerNetRevenue'], $sCurrOrder, $aReportArray['subSourceCode'],  $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded'], $aReportArray['sourceCode'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['clicks']);
							break;
							default:
							
							array_multisort($aReportArray['dateAdded'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] , $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['clicks'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
						}
					} else {
						switch ($sOrderColumn) {
					case "subSourceCode" :
					array_multisort($aReportArray['subSourceCode'], $sCurrOrder, $aReportArray['uniqueUsers'], $aReportArray['sourceCode'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['clicks'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
					break;					
					case "uniqueUsers" :
					array_multisort($aReportArray['uniqueUsers'], $sCurrOrder, $aReportArray['sourceCode'],  $aReportArray['subSourceCode'], $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['clicks'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
					break;
					case "totalRevenue" :
					array_multisort($aReportArray['totalRevenue'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['totalOffersTaken'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['clicks'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
					break;
					case "totalOffersTaken" :
					array_multisort($aReportArray['totalOffersTaken'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['clicks'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
					break;
					//case "avgOffersTakenPerUser" :
					//array_multisort($aReportArray['avgOffersTakenPerUser'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['clicks'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
					//break;
					case "accountRep" :
					array_multisort($aReportArray['partnerRep'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] , $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['companyName'], $aReportArray['clicks'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
					break;
					case "companyName" :
					array_multisort($aReportArray['companyName'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] , $aReportArray['partnerRep'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['clicks'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
					break;
					case "clicks" :
					array_multisort($aReportArray['clicks'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] , $aReportArray['partnerRep'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['companyName'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
					break;
					case "conversion" :
					array_multisort($aReportArray['conversion'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] , $aReportArray['partnerRep'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['companyName'], $aReportArray['clicks'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
					break;
					case "effectiveCpm" :
					array_multisort($aReportArray['effectiveCpm'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] , $aReportArray['partnerRep'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['companyName'], $aReportArray['conversion'], $aReportArray['clicks'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
					break;
					case "ourNetRevenue" :
					array_multisort($aReportArray['ourNetRevenue'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] , $aReportArray['partnerRep'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['companyName'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['clicks'], $aReportArray['partnerNetRevenue']);
					break;
					case "partnerNetRevenue" :
					array_multisort($aReportArray['partnerNetRevenue'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] , $aReportArray['partnerRep'], $aReportArray['sourceCode'], $aReportArray['subSourceCode'], $aReportArray['companyName'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['cllicks']);
					break;
					default:
					array_multisort($aReportArray['sourceCode'], $sCurrOrder, $aReportArray['subSourceCode'],  $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] , $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['clicks'], $aReportArray['conversion'], $aReportArray['effectiveCpm'], $aReportArray['ourNetRevenue'], $aReportArray['partnerNetRevenue']);
				}
				}
			}
			
			// now prepare report output from the aReportArray elements
			
			for ($i=0;$i< count($aReportArray['sourceCode']);$i++) {
				//echo "<BR>".$aReportArray['sourceCode'][$i];
				//echo "<BR>".$aReportArray['totalOffersTaken'][$i];
				// display sourceCode row only if it has some counts (e1 or offers taken)<BR>
//echo "<BR>".$aReportArray['sourceCode'][$i].$aReportArray['companyName'][$i];
				if ( $aReportArray['totalOffersTaken'][$i] > 0) {
					if ($sBgcolorClass == "ODD") {
						$sBgcolorClass = "EVEN_WHITE";
					} else {
						$sBgcolorClass = "ODD";
					}
					
					$sReportContent .= "<tr class=$sBgcolorClass><td>".$aReportArray['partnerRep'][$i]."</td>
									<td>".$aReportArray['sourceCode'][$i]."</td>";
				
					if ($sSuppressSubSource != 'Y' ) {
						$sReportContent .= "<td>".$aReportArray['subSourceCode'][$i]."</td>";
					}
					
					$sReportContent .= "<td>".$aReportArray['companyName'][$i]."</td>									
									<td align=right>".$aReportArray['clicks'][$i]."</td>
									<td align=right>".$aReportArray['uniqueUsers'][$i]."</td>	
									<td align=right>".$aReportArray['conversion'][$i]."</td>
									<td align=right>".$aReportArray['totalOffersTaken'][$i]."</td>
									<td align=right>".$aReportArray['effectiveCpm'][$i]."</td>								
									<td align=right>".$aReportArray['ourNetRevenue'][$i]."</td>
									<td align=right>".$aReportArray['partnerNetRevenue'][$i]."</td>
									<td align=right>".$aReportArray['totalRevenue'][$i]."</td>";

					$iGrandTotalClicks += $aReportArray['clicks'][$i];
					$iGrandTotalUniqueUsers += $aReportArray['uniqueUsers'][$i];
					$iGrandTotalOffersTaken += $aReportArray['totalOffersTaken'][$i];
					$fGrandTotalOurNetRevenue += $aReportArray['ourNetRevenue'][$i];
					$fGrandTotalPartnerNetRevenue += $aReportArray['partnerNetRevenue'][$i];
					$fGrandTotalRevenue += $aReportArray['totalRevenue'][$i];
										
						
					if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
						$sReportContent .= "<td nowrap>".$aReportArray['dateAdded'][$i]."</td>";
					}
					$sReportContent .= "</tr>";
					
					if ($sExportExcel) {
							$sExportData .= $aReportArray['partnerRep'][$i]."\t".$aReportArray['sourceCode'][$i]."\t".
											$aReportArray['subSourceCode'][$i]."\t".$aReportArray['companyName'][$i]."\t".
											$aReportArray['totalOffersTaken'][$i]."\t".$aReportArray['avgOffersTakenPerUser'][$i].
											"\t".$aReportArray['totalRevenue'][$i];
							if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
								$sExportData .= "\t".$aReportArray['dateAdded'][$i];
							}
							$sExportData .="\t\n";
							
						}
						
				}
			}
			
						
			
			//if ($iGrandTotalUniqueUsers != 0 && $iGrandTotalUniqueUsers !='') {
//				$fGrandAvgOffersTakenPerUser = $iGrandTotalOffersTaken / $iGrandTotalUniqueUsers ;
	//		}
		//	$fGrandAvgOffersTakenPerUser = sprintf("%10.2f",round($fGrandAvgOffersTakenPerUser, 2));
			
			$fGrandTotalRevenue = sprintf("%12.2f",round($fGrandTotalRevenue, 2));
			
			if ($iGrandTotalClicks > 0 ) {
				$fGrandTotalConversion = ($iGrandTotalUniqueUsers / $iGrandTotalClicks) * 100;
				$fGrandTotalEffectiveCpm =  $fGrandTotalRevenue / ($iGrandTotalClicks / 1000)  ;
			}
			
			$fGrandTotalConversion =  sprintf("%10.2f",round($fGrandTotalConversion, 2));
			$fGrandTotalEffectiveCpm = sprintf("%10.2f", round($fGrandTotalEffectiveCpm,2));
						
			
			if ($sSuppressSubSource != 'Y' ) {
				$iColspan = "4";
			} else {
				$iColspan = "3";
			}

			$sReportContent .= "<tr><td colspan=12 align=left><hr color=#000000></td></tr>
								<tr><td colspan=$iColspan><b>Summary</b></td>
								<td align=right><b>$iGrandTotalClicks</b></td>
								<td align=right><b>$iGrandTotalUniqueUsers</b></td>
								<td align=right><b>$fGrandTotalConversion</b></td>
								<td align=right><b>$iGrandTotalOffersTaken</b></td>
								<td align=right><b>$fGrandTotalEffectiveCpm</b></td>
								<td align=right><b>$fGrandTotalOurNetRevenue</b></td>
								<td align=right><b>$fGrandTotalPartnerNetRevenue</b></td>
								<td align=right><b>$fGrandTotalRevenue</b></td><BR>
								$sDatePlaceHolder
								<tr><td colspan=12 align=left><hr color=#000000></td></tr>	
							</tr>";
			if ($sExportExcel) {
				$sExportData = "Account Executive\tSource Code\tSub Source Code\tPartner Name\tUnique Users\tTotal Offers Taken\tAvg. Offers Taken Per User\tTotal Revenue\t\n" . $sExportData .
				"\n\t\t\t\t$iGrandTotalUniqueUsers\t$iGrandTotalOffersTaken\t$fGrandAvgOffersTakenPerUser\t$fGrandTotalRevenue\t\n\nReport From $sDateFrom to $sDateTo\nRun Date / Time: $sRunDateAndTime";
			}
			
			if ($sExportEmails) {
				
				// if history table
				$sExportEmailQuery = ereg_replace("count\(distinct otDataHistory.email\) AS uniqueUsers,", "distinct otDataHistory.email, otDataHistory.sourceCode ",$sReportQuery);
				$sExportEmailQuery = ereg_replace("count\(otDataHistory.email\) AS totalOffersTaken,","",$sExportEmailQuery);
				$sExportEmailQuery = ereg_replace("sum\(1 \* offers.revPerLead\) as totalRevenue","",$sExportEmailQuery);
				$sExportEmailQuery = ereg_replace("SELECT otDataHistory.sourceCode,","SELECT ",$sExportEmailQuery);
				$sExportEmailQuery = ereg_replace("otDataHistory.subSourceCode,","",$sExportEmailQuery);
				$sExportEmailQuery = ereg_replace("GROUP BY sourceCode, subSourceCode"," ",$sExportEmailQuery);
				$sExportEmailQuery = ereg_replace("GROUP BY sourceCode"," ",$sExportEmailQuery);
				
				// if today's table
				$sExportEmailQuery = ereg_replace("count\(distinct otData.email\) AS uniqueUsers,", "distinct otData.email, otData.sourceCode ",$sReportQuery);
				$sExportEmailQuery = ereg_replace("count\(otData.email\) AS totalOffersTaken,","",$sExportEmailQuery);
				$sExportEmailQuery = ereg_replace("sum\(1 \* offers.revPerLead\) as totalRevenue","",$sExportEmailQuery);
				$sExportEmailQuery = ereg_replace("SELECT otData.sourceCode,","SELECT ",$sExportEmailQuery);
				$sExportEmailQuery = ereg_replace("otData.subSourceCode,","",$sExportEmailQuery);
				$sExportEmailQuery = ereg_replace("GROUP BY sourceCode, subSourceCode"," ",$sExportEmailQuery);
				$sExportEmailQuery = ereg_replace("GROUP BY sourceCode"," ",$sExportEmailQuery);
				
				
				$rExportEmailResult = dbQuery($sExportEmailQuery);
				
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
		} else {
			$sPvAndNonPvChecked = "checked";
		}
		
		if ($sShowQueries == 'Y') {
			$sShowQueriesChecked = "checked";
		}
		
		
		
		if ($sExportExcel) {
			
			header("Content-type: application/vnd.ms-excel");
			header("Content-Disposition: attachment; filename=sourceAnalysis.xls");
			header("Content-Description: Excel output");
			echo $sExportData;
			// if didn't exit, all the html page content will be saved as excel file.
			exit();
		} else if ($sExportEmails) {
			
			header("Content-type: text/plain");
			header("Content-Disposition: attachment; filename=sourceAnalysisEmails.txt");
			header("Content-Description: Text output");
			echo $sExportEmailData;
			// if didn't exit, all the html page content will be saved as excel file.
			exit();
		}
	}
	
	
	$sRepQuery = "SELECT id, firstName
				 FROM   nbUsers
				 ORDER BY firstName";
	
	$rRepResult = dbQuery($sRepQuery);
	echo dbError();
	$sAccountRepOptions = "<option value=''>All";
	while ($oRepRow = dbFetchObject($rRepResult)) {
		if ($iAccountRep == $oRepRow->id) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		
		$sAccountRepOptions .= "<option value='$oRepRow->id' $sSelected>$oRepRow->firstName";
	}
	
	
	if ($sExportExcel) {
		$sExportExcelChecked = "checked";
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
	} else {
		$sSuppressSubSourceChecked = "checked";
	}
	
	if ($sShowQueries == 'Y') {
		
		$sQueries = "<b>Queries Used To Prepare This Report:</b>
					 <BR><BR><b>Report Query:</b><BR>".$sReportQuery;
		
		
	}
	
		
	include("../../includes/adminHeader.php");
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);
	
	
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


<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

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
	<tr><td>Report Options</td>
		<td ><input type=radio name=sPostalVerified value='pvAndNonPv' <?php echo $sPvAndNonPvChecked;?>> Gross Leads
				<input type=radio name=sPostalVerified value='pvOnly' <?php echo $sPvOnlyChecked;?>> PostalVerified
	</td></tr>
	<tr><td></td><td><input type=checkbox name=sExcludeNonRevenue value='Y' <?php echo $sExcludeNonRevenueChecked;?>> Exclude Non-Revenue Offers</td></tr>
	<tr><td></td><td><input type=checkbox name=sIncludeTestLeads value='Y' <?php echo $sIncludeTestLeadsChecked;?>> Include 3401 Test Leads</td></tr>
	<tr><td></td><td><input type=checkbox name=sSuppressSubSource value='Y' <?php echo $sSuppressSubSourceChecked;?>> Suppress Sub Source Code</td></tr>
	<tr><td colspan=2><input type=submit name=sViewReport value='History Report'>  &nbsp; &nbsp; 
	<input type=submit name=sViewReport value="Today's Report">  &nbsp; &nbsp; 
	<input type=checkbox name=sExportExcel value="Y" <?php echo $sExportExcelChecked;?>> Export To Excel
	&nbsp; &nbsp; &nbsp; <input type=checkbox name=sExportEmails value="Y"  <?php echo $sExportEmailsChecked;?>> Export Emails
	<!--<input type=submit name=sPrintReport value='Print This Report'>--></td><td colspan=2><input type=checkbox name=sShowQueries value='Y' <?php echo $sShowQueriesChecked;?>> Show Queries</td></tr>
	<!--<input type=submit name=sPrintReport value='Print This Report'></td></tr>-->
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=12 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=12 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><td><a href="<?php echo $sSortLink;?>&sOrderColumn=accountRep&sAccountRepOrder=<?php echo $sAccountRepOrder;?>" class=header>Account Executive</a></td>
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=sourceCode&sSourceCodeOrder=<?php echo $sSourceCodeOrder;?>" class=header>Source Code</a></td>
		<?php echo $sSubSourceHeader;?>
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=companyName&sCompanyNameOrder=<?php echo $sCompanyNameOrder;?>" class=header>Partner Name</a></td>
		<td align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=clicks&sClicksOrder=<?php echo $sClicksOrder;?>" class=header>Clicks</a></td>				
		<td align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=uniqueUsers&sUniqueUsersOrder=<?php echo $sUniqueUsersOrder;?>" class=header>Unique Users</a></td>				
		<td align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=conversion&sConversionOrder=<?php echo $sConversionOrder;?>" class=header>Conversion</a></td>				
		<td align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=totalOffersTaken&sTotalOffersTakenOrder=<?php echo $sTotalOffersTakenOrder;?>" class=header>Total Offers Taken</a></td>
		<td align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=effectiveCpm&sEffectiveCpmOrder=<?php echo $sEffectiveCpmOrder;?>" class=header>Effective CPM (Based on Total Rev.)</a></td>				
		<td align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=ourNetRevenue&sOurNetRevenueOrder=<?php echo $sOurNetRevenueOrder;?>" class=header>Our Net Revenue</a></td>
		<td align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=partnerNetRevenue&sPartnerNetRevenueOrder=<?php echo $sPartnerNetRevenueOrder;?>" class=header>Partner Net Revenue</a></td>
		<td align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=totalRevenue&sTotalRevenueOrder=<?php echo $sTotalRevenueOrder;?>" class=header>Total Revenue</a></td>		
	</tr>
	
<?php echo $sReportContent;?>

	<tr><td colspan=12 class=header><BR>Notes -</td></tr>
	<tr><td colspan=12>Counts will change as postal verification status changes.				
				<BR><BR>Report omits any leads having address starting with '3401 Dundee' considering those as test leads if "Include 3401 Test Leads" is not checked. 
						Test leads can be included only in today's report and deleted next day.
				<BR><BR>For history report, counts only reflects records where PV attempted. For today's report, report reflects gross counts.</td></tr>	
	<tr><td colspan=12>Gross Unique Users in Source Analysis Report may be higher than gross unique users in Campaign Analysis Report
					because in Source Analysis Report user will be unique for a source code and same user may be unique user 
					for another source code also if he came up in our site through different source codes resulting the total unique user count higher than 
					Campaign Analysis Report.					
					<BR><BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=12><BR><BR></td></tr>
	<tr><td colspan=12><?php echo $sQueries; ?></td></tr>
	
	<tr><td colspan=12><BR><BR></td></tr>
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