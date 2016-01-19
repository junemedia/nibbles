<?php

/*********

Script to Display

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "OT Source Analysis Report";
//echo "Sddf". $iAccountRep;
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
			
			//if (!($sDateFrom)) {
			$sDateFrom = "$iMonthFrom-$iDayFrom-$iYearFrom";
			//}
			//if (!($sDateTo)) {
			$sDateTo = "$iMonthTo-$iDayTo-$iYearTo";
			//}
			
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
				$sSourceCodeQuery = "SELECT distinct sourceCode
						 FROM   otData
						 WHERE  date_format(dateTimeAdded, '%Y-%m-%d') between '$sTempDateFrom' AND '$sTempDateTo'
						 ORDER BY sourceCode"; 
			} else {
				$sSourceCodeQuery = "SELECT distinct sourceCode
							 FROM   otDataHistory
							 WHERE  date_format(dateTimeAdded, '%Y-%m-%d') between '$sTempDateFrom' AND '$sTempDateTo'
							 ORDER BY sourceCode"; 
				
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
		//if ($sGetReport || $sExport) {
		
		
		// Set Default order column
		if (!($sOrderColumn)) {
			$sOrderColumn = "sourceCode";
			$sSourceCodeOrder = SORT_ASC;
		}
		
		
		// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
		if (!($sCurrOrder)) {
			switch ($sOrderColumn) {
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
		
		
		$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sSourceCode=$sSourceCode&sFilter=$sFilter&sPostalVerified=$sPostalVerified&
					sExcludeNonRevenue=$sExcludeNonRevenue&sDateFrom=$sDateFrom&sDateTo=$sDateTo&iRecPerPage=$iRecPerPage&iAccountRep=$iAccountRep&sViewReport=$sViewReport";
		
		
		$i=0;
		if ($sViewReport != "Today's Report") {
			
			$sReportQuery = "SELECT otDataHistory.sourceCode, count(distinct otDataHistory.email) AS uniqueUsers,
								count(otDataHistory.email) AS totalOffersTaken, sum(1 * revPerLead) as totalRevenue";
			
			if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
				$sReportQuery .= ",date_format(otDataHistory.dateTimeAdded,'%Y-%m-%d') AS dateAdded ";
			}
			$sReportQuery .= " FROM   userDataHistory, otDataHistory, offers
						 WHERE  userDataHistory.email = otDataHistory.email			 			
						 AND    otDataHistory.offerCode	= offers.offerCode
						 AND    date_format(otDataHistory.dateTimeAdded,\"%Y-%m-%d\") BETWEEN '$sTempDateFrom' AND '$sTempDateTo'";	
			
			if ($sIncludeTestLeads !='Y') {
			 	$sReportQuery .= " AND 	address NOT LIKE \"3401 DUNDEE%\" ";
			}
			
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
			
			if ($sPostalVerified == 'pvOnly') {
				$sReportQuery .= " AND userDataHistory.postalVerified = 'V' AND verified != 'I'";
			} else {
				$sReportQuery .= " AND userDataHistory.postalVerified IS NOT NULL ";
			}
			
			if ($sExcludeNonRevenue == 'Y') {
				$sReportQuery .= " AND offers.isNonRevenue != '1' ";
			}
			
			$sReportQuery .=  "GROUP BY sourceCode";
			if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
				$sReportQuery .= ", dateAdded ";
			}
			$sReportQuery .="  ORDER BY sourceCode";
			
			if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
				$sReportQuery .= ", dateAdded";
			}
			
			//$sReportQuery .= ", $sOrderColumn $sCurrOrder";			
			
			$rReportResult = dbQuery($sReportQuery);
		//	echo $sReportQuery.dbError();
			echo  dbError();
			while ($oReportRow = dbFetchObject($rReportResult)) {
				$sTempSourceCode = $oReportRow->sourceCode;
				$iUniqueUsers = $oReportRow->uniqueUsers;
				$iTotalOffersTaken= $oReportRow->totalOffersTaken;
				$fTotalRevenue = $oReportRow->totalRevenue;
				
				$fAvgOffersTakenPerUser = $iTotalOffersTaken / $iUniqueUsers; // $iTotalOffersTaken / $iUniqueUsers ;
				$fAvgOffersTakenPerUser = sprintf("%10.2f",round($fAvgOffersTakenPerUser, 2));
				
				// get company name
				
				$sCompanyName = '';
				$sPartnerRep = '';
				$sRepDesignated = '';
				if ($sTempSourcePrefix == substr($sTempSourceCode,0,3)) {
					$sCompanyName = $sPrevCompanyName;
					$sPartnerRep = $sPrevPartnerRep;
					$sRepDesignated = $sPrevRepDesignated;
				} else {
					$sCompanyQuery = "SELECT companyName, repDesignated
							  FROM   campaigns, partnerCompanies
							  WHERE  campaigns.partnerId = partnerCompanies.id
							  AND	 campaigns.sourceCode = '".substr($sTempSourceCode,0,14)."'";
					
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
				}
				
				if (strtoupper(substr($sTempSourceCode,0,3))  == 'MYF') {
					$sCompanyName = "MyFree";
					$sPartnerRep = "phil";
				}
				
				$sTempAccountRep = "'".$iAccountRep."'";
				
				if ($iAccountRep == '' || strstr($sRepDesignated,$sTempAccountRep)) {
					
					$aReportArray['partnerRep'][$i] = $sPartnerRep;
					$aReportArray['sourceCode'][$i] = $sTempSourceCode;
					$aReportArray['companyName'][$i] = $sCompanyName;
					$aReportArray['uniqueUsers'][$i] = $iUniqueUsers;
					$aReportArray['totalOffersTaken'][$i] = $iTotalOffersTaken;
					$aReportArray['avgOffersTakenPerUser'][$i] = $fAvgOffersTakenPerUser;
					$aReportArray['totalRevenue'][$i] = $fTotalRevenue;
					
					
					/*
					$sReportContent .= "<tr><td>$sPartnerRep</td>
					<td>$sTempSourceCode</td>
					<td>$sCompanyName</td>
					<td align=right>$iUniqueUsers</td>
					<td align=right>$iTotalOffersTaken</td>
					<td align=right>$fAvgOffersTakenPerUser</td>
					<td align=right>$fTotalRevenue</td>";
					*/
					
					if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
						//	$sReportContent .= "<td nowrap>$oReportRow->dateAdded</td>";
						$aReportArray['dateAdded'][$i] = $oReportRow->dateAdded;
						//$sTempClickDate = $oClicksRow->clickDate;
					}
					
					$i++;
					
					//$sReportContent .= "</tr>";
					
					if ($sExportExcel) {
						$sExportData .= "$sPartnerRep\t$sTempSourceCode\t$sCompanyName\t$iUniqueUsers\t$iTotalOffersTaken\t$fAvgOffersTakenPerUser\t$fTotalRevenue";
						if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
							$sExportData .= "\t$oReportRow->dateAdded";
						}
						$sExportData .="\t\n";
						
					}
					
					$iGrandTotalUniqueUsers += $iUniqueUsers;
					$iGrandTotalOffersTaken += $iTotalOffersTaken;
					$fGrandTotalRevenue += $fTotalRevenue;
					
					$sPrevCompanyName = $sCompanyName;
					$sPrevPartnerRep = $sPartnerRep;
					$sPrevRepDesignated = $sRepDesignated;
					
					$sTempSourcePrefix = substr($sTempSourceCode,0,3);
				}
				
			}
			
			if (count ($aReportArray['sourceCode']) > 0 ) {
				
				if (count($aReportArray['dateAdded']) > 0)  {
					switch ($sOrderColumn) {
					case "uniqueUsers" :
					array_multisort($aReportArray['uniqueUsers'], $sCurrOrder, $aReportArray['sourceCode'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded']);
					break;
					case "totalRevenue" :
					array_multisort($aReportArray['totalRevenue'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['sourceCode'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded']);
					break;
					case "totalOffersTaken" :
					array_multisort($aReportArray['totalOffersTaken'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['sourceCode'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded']);
					break;
					case "avgOffersTakenPerUser" :
					array_multisort($aReportArray['avgOffersTakenPerUser'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['sourceCode'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded']);
					break;
					case "accountRep" :
					array_multisort($aReportArray['partnerRep'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['sourceCode'], $aReportArray['companyName'], $aReportArray['dateAdded']);					
					break;
					case "companyName" :
					array_multisort($aReportArray['companyName'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['sourceCode'], $aReportArray['dateAdded']);
					break;
					case "sourceCode":
					array_multisort($aReportArray['sourceCode'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['dateAdded']);
					default:
					array_multisort($aReportArray['dateAdded'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName'], $aReportArray['sourceCode']);
				}
				} else {
				switch ($sOrderColumn) {
					case "uniqueUsers" :
					array_multisort($aReportArray['uniqueUsers'], $sCurrOrder, $aReportArray['sourceCode'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName']);
					break;
					case "totalRevenue" :
					array_multisort($aReportArray['totalRevenue'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['sourceCode'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName']);
					break;
					case "totalOffersTaken" :
					array_multisort($aReportArray['totalOffersTaken'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['sourceCode'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName']);
					break;
					case "avgOffersTakenPerUser" :
					array_multisort($aReportArray['avgOffersTakenPerUser'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['sourceCode'], $aReportArray['partnerRep'], $aReportArray['companyName']);
					break;
					case "accountRep" :
					array_multisort($aReportArray['partnerRep'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['sourceCode'], $aReportArray['companyName']);					
					break;
					case "companyName" :
					array_multisort($aReportArray['companyName'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['sourceCode']);
					break;
					default:
					array_multisort($aReportArray['sourceCode'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName']);
				}
				}
				
			}
			
			
			for ($i=0; $i < count($aReportArray['partnerRep']); $i++) {
				$sReportContent .= "<tr><td>".$aReportArray['partnerRep'][$i]."</td>
								<td>".$aReportArray['sourceCode'][$i]."</td>
								<td>".$aReportArray['companyName'][$i]."</td>
								<td align=right>".$aReportArray['uniqueUsers'][$i]."</td>								
								<td align=right>".$aReportArray['totalOffersTaken'][$i]."</td>
								<td align=right>".$aReportArray['avgOffersTakenPerUser'][$i]."</td>
								<td align=right>".$aReportArray['totalRevenue'][$i]."</td>";
				
				if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
					$sReportContent .= "<td nowrap>".$aReportArray['dateAdded'][$i]."</td>";
				}
			}
			
			
			
			if ($iGrandTotalUniqueUsers != 0 && $iGrandTotalUniqueUsers !='') {
				$fGrandAvgOffersTakenPerUser = $iGrandTotalOffersTaken / $iGrandTotalUniqueUsers ;
			}
			$fGrandAvgOffersTakenPerUser = sprintf("%10.2f",round($fGrandAvgOffersTakenPerUser, 2));
			
			$fGrandTotalRevenue = sprintf("%12.2f",round($fGrandTotalRevenue, 2));
			
			$sReportContent .= "<tr><td colspan=7 align=left><hr color=#000000></td></tr>
								<tr><td colspan=3><b>Summary</b></td>
								<td align=right><b>$iGrandTotalUniqueUsers</b></td>								
								<td align=right><b>$iGrandTotalOffersTaken</b></td>
								<td align=right><b>$fGrandAvgOffersTakenPerUser</b></td>
								<td align=right><b>$fGrandTotalRevenue</b></td>
							</tr>";
			if ($sExportExcel) {
				$sExportData = "Account Executive\tSource Code\tPartner Name\tUnique Users\tTotal Offers Taken\tAvg. Offers Taken Per User\tTotal Revenue\t\n" . $sExportData .
				"\n\t\t\t$iGrandTotalUniqueUsers\t$iGrandTotalOffersTaken\t$fGrandAvgOffersTakenPerUser\t$fGrandTotalRevenue\t\n\nReport From $sDateFrom to $sDateTo\nRun Date / Time: $sRunDateAndTime";
			}
			
			if ($sExportEmails) {
				
				$sExportEmailQuery = ereg_replace("count\(distinct otDataHistory.email\) AS uniqueUsers,", "distinct otDataHistory.email, otDataHistory.sourceCode ",$sReportQuery);
				$sExportEmailQuery = ereg_replace("count\(otDataHistory.email\) AS totalOffersTaken,","",$sExportEmailQuery);
				$sExportEmailQuery = ereg_replace("sum\(1 \* revPerLead\) as totalRevenue","",$sExportEmailQuery);
				$sExportEmailQuery = ereg_replace("SELECT otDataHistory.sourceCode,","SELECT ",$sExportEmailQuery);
				$sExportEmailQuery = ereg_replace("GROUP BY sourceCode"," ",$sExportEmailQuery);
				
				
				
				$rExportEmailResult = dbQuery($sExportEmailQuery);
				
				while ($oExportEmailRow = dbFetchObject($rExportEmailResult)) {
					$sExportEmailData .= "\"$oExportEmailRow->email\",\"$oExportEmailRow->sourceCode\"\r\n";
				}
				
			}
			
			
			//}
			
		} else {
			
			
			$sReportQuery = "SELECT otData.sourceCode, count(distinct otData.email) AS uniqueUsers,
								count(otData.email) AS totalOffersTaken, sum(1 * revPerLead) as totalRevenue
						 FROM   userData, otData, offers
						 WHERE  userData.email = otData.email			 			
						 AND    offers.offerCode = otData.offerCode		
						 AND    date_format(otData.dateTimeAdded,\"%Y-%m-%d\") BETWEEN '$sTempDateFrom' AND '$sTempDateTo'";				
			
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

			if ($sExcludeNonRevenue == 'Y') {
				$sReportQuery .= " AND offers.isNonRevenue != '1' ";
			}
			
			$sReportQuery .=  " GROUP BY sourceCode
						   ORDER BY sourceCode, offers.offerCode";
			
			$rReportResult = dbQuery($sReportQuery);
			
			echo dbError();
			
			$rReportResult = dbQuery($sReportQuery);
			//echo $sReportQuery.mysql_error()."<BR>".mysql_num_rows($rReportResult);
			$fTotalRevenue = 0;
			while ($oReportRow = dbFetchObject($rReportResult)) {
				$sTempSourceCode = $oReportRow->sourceCode;
				$fTotalRevenue = $oReportRow->totalRevenue;
				$iUniqueUsers = $oReportRow->uniqueUsers;
				$iTotalOffersTaken = $oReportRow->totalOffersTaken;
				
				$fAvgOffersTakenPerUser = $iTotalOffersTaken / $iUniqueUsers; // $iTotalOffersTaken / $iUniqueUsers ;
				$fAvgOffersTakenPerUser = sprintf("%10.2f",round($fAvgOffersTakenPerUser, 2));
				
				
				$sCompanyName = '';
				$sPartnerRep = '';
				$sRepDesignated = '';
				if ($sTempSourcePrefix == substr($sTempSourceCode,0,3)) {
					$sCompanyName = $sPrevCompanyName;
					$sPartnerRep = $sPrevPartnerRep;
					$sRepDesignated = $sPrevRepDesignated;
				} else {
					$sCompanyQuery = "SELECT companyName, repDesignated
							  FROM   campaigns, partnerCompanies
							  WHERE  campaigns.partnerId = partnerCompanies.id
							  AND	 campaigns.sourceCode = '".substr($sTempSourceCode,0,14)."'";
					
					$rCompanyResult = dbQuery($sCompanyQuery);
					echo dbError();
					while ($oCompanyRow = dbFetchObject($rCompanyResult)) {
						$sCompanyName = $oCompanyRow->companyName;
						$sRepDesignated = $oCompanyRow->repDesignated;
					}
					
					// get rep
					
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
				}
				
				
				
				
				$sTempAccountRep = "'".$iAccountRep."'";
				
				if (strtoupper(substr($sTempSourceCode,0,3))  == 'MYF') {
					$sCompanyName = "MyFree";
					$sPartnerRep = "phil";
				}
				
				if ($iAccountRep == '' || strstr($sRepDesignated,$sTempAccountRep)) {
					/*
					$sReportContent .= "<tr><td>$sPartnerRep</td>
					<td>$sTempSourceCode</td>
					<Td>$sCompanyName</td>
					<td align=right>$iUniqueUsers</td>
					<td align=right>$iTotalOffersTaken</td>
					<td align=right>$fAvgOffersTakenPerUser</td>
					<td align=right>$fTotalRevenue</td>
					</tr>";
					
					*/
					$aReportArray['partnerRep'][$i] = $sPartnerRep;
					$aReportArray['sourceCode'][$i] = $sTempSourceCode;
					$aReportArray['companyName'][$i] = $sCompanyName;
					$aReportArray['uniqueUsers'][$i] = $iUniqueUsers;
					$aReportArray['totalOffersTaken'][$i] = $iTotalOffersTaken;
					$aReportArray['avgOffersTakenPerUser'][$i] = $fAvgOffersTakenPerUser;
					$aReportArray['totalRevenue'][$i] = $fTotalRevenue;
					
					
					$i++;
					
					if ($sExportExcel) {
						$sExportData .= "$sPartnerRep\t$sTempSourceCode\t$sCompanyName\t$iUniqueUsers\t$iTotalOffersTaken\t$fAvgOffersTakenPerUser\t$fTotalRevenue\t\n";
					}
					
					$iGrandTotalUniqueUsers += $iUniqueUsers;
					$iGrandTotalOffersTaken += $iTotalOffersTaken;
					$fGrandTotalRevenue += $fTotalRevenue;
					
					$sPrevCompanyName = $sCompanyName;
					$sPrevRepDesignated = $sRepDesignated;
					$sPrevPartnerRep = $sPartnerRep;
					
					$sTempSourcePrefix = substr($sTempSourceCode,0,3);
					
				} // if rep condition
			}
			
			if (count ($aReportArray['sourceCode']) > 0 ) {
				switch ($sOrderColumn) {
					case "uniqueUsers" :
					array_multisort($aReportArray['uniqueUsers'], $sCurrOrder, $aReportArray['sourceCode'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName']);
					break;
					case "totalRevenue" :
					array_multisort($aReportArray['totalRevenue'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['sourceCode'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName']);
					break;
					case "totalOffersTaken" :
					array_multisort($aReportArray['totalOffersTaken'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['sourceCode'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName']);
					break;
					case "avgOffersTakenPerUser" :
					array_multisort($aReportArray['avgOffersTakenPerUser'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['sourceCode'], $aReportArray['partnerRep'], $aReportArray['companyName']);
					break;
					case "accountRep" :
					array_multisort($aReportArray['partnerRep'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['sourceCode'], $aReportArray['companyName']);
					break;
					case "companyName" :
					array_multisort($aReportArray['companyName'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['sourceCode']);
					break;
					default:
					array_multisort($aReportArray['sourceCode'], $sCurrOrder, $aReportArray['uniqueUsers'],  $aReportArray['totalRevenue'], $aReportArray['totalOffersTaken'] ,$aReportArray['avgOffersTakenPerUser'], $aReportArray['partnerRep'], $aReportArray['companyName']);
				}
			}
			
			for ($j=0; $j < count($aReportArray['partnerRep']); $j++) {
				
				$sReportContent .= "<tr><td>".$aReportArray['partnerRep'][$j]."</td>
								<td>".$aReportArray['sourceCode'][$j]."</td>
								<td>".$aReportArray['companyName'][$j]."</td>
								<td align=right>".$aReportArray['uniqueUsers'][$j]."</td>								
								<td align=right>".$aReportArray['totalOffersTaken'][$j]."</td>
								<td align=right>".$aReportArray['avgOffersTakenPerUser'][$j]."</td>
								<td align=right>".$aReportArray['totalRevenue'][$j]."</td>";
				
				if (($sSourceCode != '' || $sSubSourceCode != '') && $sFilter == 'exactMatch') {
					$sReportContent .= "<td nowrap>".$aReportArray['dateAdded'][$j]."</td>";
				}
				
				$sReportContent .= "</tr>";
			}
			
			
			if ($iGrandTotalUniqueUsers !='' && $iGrandTotalUniqueUsers !=0) {
				$fGrandAvgOffersTakenPerUser = $iGrandTotalOffersTaken / $iGrandTotalUniqueUsers ;
			}
			$fGrandAvgOffersTakenPerUser = sprintf("%10.2f",round($fGrandAvgOffersTakenPerUser, 2));
			
			$fGrandTotalRevenue = sprintf("%12.2f",round($fGrandTotalRevenue, 2));
			
			
			$sReportContent .= "<tr><td colspan=7 align=left><hr color=#000000></td></tr>
								<tr><td colspan=3><b>Summary</b></td>
								<td align=right><b>$iGrandTotalUniqueUsers</b></td>								
								<td align=right><b>$iGrandTotalOffersTaken</b></td>
								<td align=right><b>$fGrandAvgOffersTakenPerUser</b></td>
								<td align=right><b>$fGrandTotalRevenue</b></td>
							</tr>";
			if ($sExportExcel) {
				$sExportData = "Account Executive\tSource Code\tPartner Name\tUnique Users\tTotal Offers Taken\tAvg. Offers Taken Per User\tTotal Revenue\t\n" . $sExportData .
				"\nSummary\t\t\t$iGrandTotalUniqueUsers\t$iGrandTotalOffersTaken\t$fGrandAvgOffersTakenPerUser\t$fGrandTotalRevenue\t\n\nReport From $sDateFrom to $sDateTo\nRun Date / Time: $sRunDateAndTime";
			}
			
			if ($sExportEmails) {
				$sExportEmailQuery = ereg_replace("count\(distinct otData.email\) AS uniqueUsers,", "distinct otData.email, otData.sourceCode ",$sReportQuery);
				$sExportEmailQuery = ereg_replace("count\(otData.email\) AS totalOffersTaken,","",$sExportEmailQuery);
				$sExportEmailQuery = ereg_replace("sum\(1 \* revPerLead\) as totalRevenue","",$sExportEmailQuery);
				$sExportEmailQuery = ereg_replace("SELECT otData.sourceCode,","SELECT ",$sExportEmailQuery);
				$sExportEmailQuery = ereg_replace("GROUP BY sourceCode"," ",$sExportEmailQuery);
				
				$rExportEmailResult = dbQuery($sExportEmailQuery);
				//echo "Ddf". $sExportEmailQuery.mysql_error();
				while ($oExportEmailRow = dbFetchObject($rExportEmailResult)) {
					$sExportEmailData .= "$oExportEmailRow->email\r\n";
				}
			}
			
		}
		
		$rDeleteMemoryTableResult = dbQuery($sDeleteMemoryTableQuery);
		
		if ($sFilter == 'startsWith') {
			$sStartsWithChecked = "CHECKED";
		} else if ($sFilter == 'exactMatch') {
			$sExactMatchChecked = "CHECKED";
		}
		
		if ($sPostalVerified == 'pvOnly') {
			$sPvOnlyChecked = "checked";
		} else {
			$sPvAndNonPvChecked = "checked";
		}
		
		if ($sShowQueries == 'Y') {
			$sShowQueriesChecked = "checked";
		}
		
		
		//$exportData = "<table>";
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
	
	if ($sShowQueries == 'Y') {
		
		$sQueries = "<b>Queries Used To Prepare This Report:</b>
					 <BR><BR><b>Report Query:</b><BR>".$sReportQuery;
		
	}
	
	
	
	/*if ($sShowQueries) {
	
	$sReportContent .= "<tr><td colspan=5><b>Queries Used To Prepare This Report:</b><BR><BR>$sQueries</td></tr>
	<tr><td colspan=2><BR><BR></td></tr>";
	}*/
	
	
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
	<tr><Td>Account Executive</td><td><select name=iAccountRep><?php echo $sAccountRepOptions;?></select> </td></tr>
	<tr><td>Report Options</td>
		<td ><input type=radio name=sPostalVerified value='pvAndNonPv' <?php echo $sPvAndNonPvChecked;?>> Gross Leads
				<input type=radio name=sPostalVerified value='pvOnly' <?php echo $sPvOnlyChecked;?>> PostalVerified
	</td></tr>
	<tr><td></td><td><input type=checkbox name=sExcludeNonRevenue value='Y' <?php echo $sExcludeNonRevenueChecked;?>> Exclude Non-Revenue Offers</td></tr>
	<tr><td></td><td><input type=checkbox name=sIncludeTestLeads value='Y' <?php echo $sIncludeTestLeadsChecked;?>> Include 3401 Test Leads</td></tr>
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
	<tr><td colspan=7 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=7 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><td><a href="<?php echo $sSortLink;?>&sOrderColumn=accountRep&sAccountRepOrder=<?php echo $sAccountRepOrder;?>" class=header>Account Executive</a></td>
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=sourceCode&sSourceCodeOrder=<?php echo $sSourceCodeOrder;?>" class=header>Source Code</a></td>
		<td><a href="<?php echo $sSortLink;?>&sOrderColumn=companyName&sCompanyNameOrder=<?php echo $sCompanyNameOrder;?>" class=header>Partner Name</a></td>
		<td align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=uniqueUsers&sUniqueUsersOrder=<?php echo $sUniqueUsersOrder;?>" class=header>Unique Users</a></td>				
		<td align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=totalOffersTaken&sTotalOffersTakenOrder=<?php echo $sTotalOffersTakenOrder;?>" class=header>Total Offers Taken</a></td>
		<td align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=avgOffersTakenPerUser&sAvgOffersTakenPerUserOrder=<?php echo $sAvgOffersTakenPerUserOrder;?>" class=header>Avg. Offers Taken Per User</a></td>
		<td align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=totalRevenue&sTotalRevenueOrder=<?php echo $sTotalRevenueOrder;?>" class=header>Total Revenue</a></td>		
	</tr>
	
<?php echo $sReportContent;?>

	<tr><td colspan=7 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=7 class=header><BR>Notes -</td></tr>
	<tr><td colspan=7>Counts will change as postal verification status changes.
				<BR><BR>Report omits any leads having address starting with '3401 Dundee' considering those as test leads if "Include 3401 Test Leads" is not checked. 
				<BR><BR>For history report, counts only reflects records where PV attempted. For today's report, report reflects gross counts.</td></tr>	
	<tr><td colspan=7>Gross Unique Users in Source Analysis Report may be higher than gross unique users in Campaign Analysis Report
					because in Source Analysis Report user will be unique for a source code and same user may be unique user 
					for another source code also if he came up in our site through different source codes resulting the total unique user count higher than 
					Campaign Analysis Report.					
					<BR><BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=7><BR><BR></td></tr>
	<tr><td colspan=7><?php echo $sQueries; ?></td></tr>
	
	<tr><td colspan=7><BR><BR></td></tr>
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