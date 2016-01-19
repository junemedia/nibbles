<?php

/*********

Script to Display

**********/

//ecpm - top 20 pages by revenue and page displays



include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "ECPM Report";

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
	
	if (! ($sViewReport || $sExport)) {
		$iYearTo = substr( $sYesterday, 0, 4);
		$iMonthTo = substr( $sYesterday, 5, 2);
		$iDayTo = substr( $sYesterday, 8, 2);
		
		$iYearFrom = substr( $sYesterday, 0, 4);
		$iMonthFrom = substr( $sYesterday, 5, 2);
		$iDayFrom = "01";
		
	}
	
	if ($iGrossLeads) {
		$sLeadsCountCol = "grossLeadsCount";
	} else {
		$sLeadsCountCol = "leadsSentCount";
	}
	
	if (($sViewReport || $sExport)&& checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {
		
		$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
		$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
		
		
		//$sPrevMonth = DateAdd("m", -1, $iYearTo."-".$iMonthTo."-".$iDayTo);
		if ($iMonthTo =='1') {
			$iPrevMonthYear = $iYearTo-1;
			$iPrevMonthMonth = "12";
		} else {
			$iPrevMonthYear = $iYearTo;
			$iPrevMonthMonth = $iMonthTo -1;
			
		}
		if ($iPrevMonthMonth < 10) {
			$iPrevMonthMonth = "0".$iPrevMonthMonth;			
		}
		
		$sPrevMonth = $iPrevMonthYear."-".$iPrevMonthMonth."-25";			
		
		$iPrevMonthNum = substr($sPrevMonth,5,2);
		$iPrevMonthNum = round($iPrevMonthNum) - 1;
		$sPrevMonthName = $aGblMonthsArray[$iPrevMonthNum];
				
		// prepare comma separated pages list
		
		if ($sShowTop10 == 'byRev') {
			$i=0;
			$sPagesSelected = '';
			$sTopPagesQuery = "SELECT pageId, sum($sLeadsCountCol) AS offersTaken, sum($sLeadsCountCol * offers.revPerLead) as revenue
							   FROM   offerLeadsCountSum, offers
							   WHERE  offerLeadsCountSum.offerCode = offers.offerCode							   									
							   AND    dateAdded BETWEEN '$sDateFrom' AND '$sDateTo'
							   GROUP BY pageId 
							   ORDER BY revenue DESC LIMIT 0,20";
			$rTopPagesResult = dbQuery($sTopPagesQuery);			
			echo dbError();
			while ($oTopPagesRow = dbFetchObject($rTopPagesResult)) {
				$aPageId[$i++] = $oTopPagesRow->pageId;
				$sPagesSelected .= "'".$oTopPagesRow->pageId."',";
			}
			
			if ($sPagesSelected !=''){
				$sPagesSelected = substr($sPagesSelected,0,strlen($sPagesSelected)-1);
			}
			
		} else if ($sShowTop10 == 'byPageDisplay') {
			
			$i=0;
			$sPagesSelected = '';
			$sTopPagesQuery = "SELECT sum(opens) AS pageDisplayCount, pageId
							  		  FROM   pageDisplayStatsSum
							  		  WHERE  date_format(openDate,'%Y-%m-%d') BETWEEN '$sDateFrom' AND '$sDateTo'
							   		  GROUP BY pageId 
							   		  ORDER BY pageDisplayCount DESC LIMIT 0,20";
			$rTopPagesResult = dbQuery($sTopPagesQuery);
			echo dbError();
			while ($oTopPagesRow = dbFetchObject($rTopPagesResult)) {
				$aPageId[$i++] = $oTopPagesRow->pageId;
				$sPagesSelected .= "'".$oTopPagesRow->pageId."',";				
			}
			
			if ($sPagesSelected !=''){
				$sPagesSelected = substr($sPagesSelected,0,strlen($sPagesSelected)-1);
			}
		} else {
		
			if ($aPageId[0] != 'all') {
				for ($i=0;$i<count($aPageId);$i++) {
					$sPagesSelected .= "'".$aPageId[$i]."',";
				}
				if ($sPagesSelected !=''){
					$sPagesSelected = substr($sPagesSelected,0,strlen($sPagesSelected)-1);
				}
			}
		}
		
		$sOffersQuery = "SELECT offers.*, offerCompanies.repDesignated
						 FROM   offers, offerCompanies
						 WHERE  offers.companyId = offerCompanies.id ";
		
		// prepare comma separated offer list
		if ($aOfferCode[0] != 'all') {
			for ($i=0; $i<count($aOfferCode); $i++) {
				
				$sOffersSelected .= "'".$aOfferCode[$i]."',";
			}
			if ($sOffersSelected !='') {
				$sOffersSelected = substr($sOffersSelected,0,strlen($sOffersSelected)-1);
				
				$sOffersQuery .= " AND offerCode IN (".$sOffersSelected.")";
			}
			
			$sOffersQuery .= " ORDER BY offerCode";
		} else {
			
			if ($aOfferCode[0] == 'all') {
				$sOffersQuery = "SELECT DISTINCT offerLeadsCountSum.offerCode, offers.revPerLead, offerCompanies.repDesignated
								 FROM   offerLeadsCountSum, offers, offerCompanies
								 WHERE  offerLeadsCountSum.offerCode = offers.offerCode	
								 AND	offers.companyId = offerCompanies.id
								 AND    dateAdded BETWEEN '$sDateFrom' AND '$sDateTo'
							 	 ORDER BY offerLeadsCountSum.offerCode";
				
			}
		}
		
		
		$rOffersResult = dbQuery($sOffersQuery);
		
		echo dbError();
		$iRow = 0;
		$aPageOffersRevenue = '';
		$aCatOffersRevenue = '';
		while ($oOffersRow = dbFetchObject($rOffersResult)) {
			
			$iRow++;
			
			$sOfferCode = $oOffersRow->offerCode;
			
			$fRevPerLead = $oOffersRow->revPerLead;
			
			$sRepDesignated = $oOffersRow->repDesignated;
			
			$fOfferRevenueRowTotal = 0;
			$iOffersTakenCount = 0;
			$fPrevMonthOfferRevenueRowTotal = 0;
			$iPrevMonthOffersTakenCount = 0;
			
			// get offer rep here
			$sOfferRep = '';
			if ($sRepDesignated != '') {
				
				$sRepQuery = "SELECT firstName, lastName
							  FROM   nbUsers
							  WHERE  id IN (".$sRepDesignated.")";
				$rRepResult = dbQuery($sRepQuery);
				echo dbError();
				while ($oRepRow = dbFetchObject($rRepResult)) {
					$sOfferRep .= substr($oRepRow->firstName,0,1).substr($oRepRow->lastName,0,1)." ";
					
				}
				
				dbFreeResult($rRepResult);
			}
			if ($sOfferRep == '') {
				$sOfferRep = "&nbsp;";
			}
			
			if ($iRow==1 && $sReportHeader == '') {
				// define report header
				$sReportHeader = "<tr><td>&nbsp;</td><td class=small>Rep.</td><td class=small>Rate/PL</td><td class=small>OfferCode</td>";
				$sExpReportHeader = "\tRep.\tRate/PL\tOfferCode\t";
			}
			
			$sReportContent .= "<tr><td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small>$iRow</td>
									<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small>$sOfferRep</td>
									<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small nowrap>\$$fRevPerLead</td>
									<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small>$sOfferCode</td>";
			$sExpReportContent .= "$iRow\t$sOfferRep\t\$$fRevPerLead\t$sOfferCode\t";
			
			if (count($aPageId) > 0) {
				
				
				$sPagesQuery = "SELECT *
								FROM   otPages
								WHERE  pageName NOT LIKE 'test%'";
				
				// prepare comma separated page list
				
				if ($sPagesSelected !=''){
					$sPagesQuery .= " AND id IN (".$sPagesSelected.")";
				}
				
				
				$sPagesQuery .= " ORDER BY pageName";
				$rPagesResult = dbQuery($sPagesQuery);
				$iPageCol = 1;
				while ($oPagesRow = dbFetchObject($rPagesResult)) {
					$iPageId = $oPagesRow->id;
					$sPageName = $oPagesRow->pageName;
					
					$fCurrMonthPageRevenue = '';
					$iCurrMonthPageDisplay = '';
					$fCurrMonthPageEcpm = '';
					
					$fPrevMonthPageRevenue = '';
					$iPrevMonthPageDisplay = '';
					$fPrevMonthPageEcpm = '';
					
					// define header if iRow = 1
					if ($iRow == 1) {
						
						$sReportHeader .= "<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small>$sPageName</td>";
						
						$sExpReportHeader .= "$sPageName\t";
						
						// get current month (last month of selected date range) details
						$sLeadsQuery = "SELECT sum($sLeadsCountCol) AS offersTaken, sum($sLeadsCountCol * offers.revPerLead) as revenue
										FROM   offerLeadsCountSum, offers
										WHERE  offerLeadsCountSum.offerCode = offers.offerCode
										AND    offerLeadsCountSum.pageId = '$iPageId'										
										AND    dateAdded BETWEEN '$sDateFrom' AND '$sDateTo'";
						
						
						$rLeadsResult = dbQuery($sLeadsQuery);
						
						while ($oLeadsRow = dbFetchObject($rLeadsResult)) {
							$iCurrMonthOffersTakenCount += $oLeadsRow->offersTaken;
							$fCurrMonthPageRevenue = $oLeadsRow->revenue;
						}
						
						
						dbFreeResult($rLeadsResult);
						
						
						$fCurrMonthPageRevenue = sprintf("%12.2f",round($fCurrMonthPageRevenue, 2));
						
						$fCurrMonthRevenueTotal += $fCurrMonthPageRevenue;
						
						
						$sDisplayQuery = "SELECT sum(opens) AS pageDisplayCount
							  		  FROM   pageDisplayStatsSum
							  		  WHERE  pageId = '$iPageId'
									  AND    date_format(openDate,'%Y-%m-%d') BETWEEN '$sDateFrom' AND '$sDateTo'";
						
						
						$rDisplayResult = dbQuery($sDisplayQuery);
						while ($oDisplayRow = dbFetchObject($rDisplayResult)) {
							$iCurrMonthPageDisplay = $oDisplayRow->pageDisplayCount;
						}
						
						dbFreeResult($rDisplayResult);
						
						$iCurrMonthDisplayTotal += $iCurrMonthPageDisplay;
						
						if ($iCurrMonthPageDisplay) {
							$fCurrMonthPageEcpm = ($fCurrMonthPageRevenue * 1000) / $iCurrMonthPageDisplay;
							$fCurrMonthPageEcpm = trim(sprintf("%10.2f",round($fCurrMonthPageEcpm, 2)));
							$fCurrMonthPageEcpm = "\$".$fCurrMonthPageEcpm;
						} else {
							$fCurrMonthPageEcpm = "&nbsp;";
						}
						
						if ($iCurrMonthPageDisplay == 0) {
							$iCurrMonthPageDisplay = "&nbsp;";
						}
						$sCurrMonthRevRow .= "<td class=small align=right nowrap>$fCurrMonthPageRevenue</td>";
						$sCurrMonthDisplayRow .= "<td class=small align=right nowrap>$iCurrMonthPageDisplay</td>";
						$sCurrMonthEcpmRow .= "<td class=small align=right nowrap>$fCurrMonthPageEcpm</td>";
						
						
						$sExpCurrMonthRevRow .= "$fCurrMonthPageRevenue\t";
						$sExpCurrMonthDisplayRow .= "$iCurrMonthPageDisplay\t";
						$sExpCurrMonthEcpmRow .= "$fCurrMonthPageEcpm\t";
						
						
						// get previous month (month before the last month of selected date range) details
						$sLeadsQuery = "SELECT sum($sLeadsCountCol) AS offersTaken, sum($sLeadsCountCol * offers.revPerLead) as revenue
										FROM   offerLeadsCountSum, offers
										WHERE  offerLeadsCountSum.offerCode = offers.offerCode
										AND    offerLeadsCountSum.pageId = '$iPageId'										
										AND    date_format(dateAdded,'%Y-%m') = date_format('$sPrevMonth','%Y-%m')";
						
						$rLeadsResult = dbQuery($sLeadsQuery);
						
						while ($oLeadsRow = dbFetchObject($rLeadsResult)) {
							$iPrevMonthOffersTakenCount = $oLeadsRow->offersTaken;
							$fPrevMonthPageRevenue = $oLeadsRow->revenue;
						}
						
						dbFreeResult($rLeadsResult);
						
						$fPrevMonthPageRevenue = sprintf("%12.2f",round($fPrevMonthPageRevenue, 2));
						
						$fPrevMonthRevenueTotal += $fPrevMonthPageRevenue;
						
						
						$sDisplayQuery = "SELECT sum(opens) AS pageDisplayCount
							  		  FROM   pageDisplayStatsSum
							  		  WHERE  pageId = '$iPageId'
									  AND    date_format(openDate,'%Y-%m') = date_format('$sPrevMonth','%Y-%m')";
						
						
						$rDisplayResult = dbQuery($sDisplayQuery);
						while ($oDisplayRow = dbFetchObject($rDisplayResult)) {
							$iPrevMonthPageDisplay = $oDisplayRow->pageDisplayCount;
						}
						
						dbFreeResult($rDisplayResult);
						
						$iPrevMonthDisplayTotal += $iPrevMonthPageDisplay;
						
						if ($iPrevMonthPageDisplay) {
							$fPrevMonthPageEcpm = ($fPrevMonthPageRevenue * 1000) / $iPrevMonthPageDisplay;
							$fPrevMonthPageEcpm = trim(sprintf("%10.2f",round($fPrevMonthPageEcpm, 2)));
							$fPrevMonthPageEcpm = "\$".$fPrevMonthPageEcpm;
						} else {
							$fCurrMonthPageEcpm = "&nbsp;";
						}
						
						$sPrevMonthRevRow .= "<td class=small align=right nowrap>$fPrevMonthPageRevenue</td>";
						$sPrevMonthDisplayRow .= "<td class=small align=right nowrap>$iPrevMonthPageDisplay</td>";
						$sPrevMonthEcpmRow .= "<td class=small align=right nowrap>$fPrevMonthPageEcpm</td>";
						
						$sExpPrevMonthRevRow .= "$fPrevMonthPageRevenue\t";
						$sExpPrevMonthDisplayRow .= "$iPrevMonthPageDisplay\t";
						$sExpPrevMonthEcpmRow .= "$fPrevMonthPageEcpm\t";
						
					}
					
					$fRevenue = 0;
					$iOfferPageDisplayCount = 0;
					//$iOffersTakenCount = 0;
					$fOfferEcpm = 0;
					// get lead details
					$sLeadsQuery = "SELECT sum($sLeadsCountCol) AS offersTaken, sum($sLeadsCountCol * offers.revPerLead) as revenue
									FROM   offerLeadsCountSum, offers
									WHERE  offerLeadsCountSum.offerCode = '$sOfferCode'
									AND    offerLeadsCountSum.offerCode = offers.offerCode
									AND    offerLeadsCountSum.pageId = '$iPageId'									
									AND    dateAdded BETWEEN '$sDateFrom' AND '$sDateTo'";
					
					$rLeadsResult = dbQuery($sLeadsQuery);
					
					while ($oLeadsRow = dbFetchObject($rLeadsResult)) {
						$iOffersTakenCount += $oLeadsRow->offersTaken;
						
						$fRevenue = $oLeadsRow->revenue;
					
					}
					dbFreeResult($rLeadsResult);
					
					/*if ($iPageId == 4) {
							echo "<BR>$sLeadsQuery ".$iPageId." rev ". $fRevenue;
					}
					*/
					//$iOffersTakenCount += $iOffersTaken;
					$fRevenue = sprintf("%10.2f",round($fRevenue, 2));
					
					$fOfferRevenueRowTotal += $fRevenue;
					
					$sDisplayQuery = "SELECT sum(displayCount) AS offerDisplayCount
							  		  FROM   offerStatsSum
							  		  WHERE  offerCode = '$sOfferCode'
									  AND    pageId = '$iPageId'
									  AND    date_format(displayDate, '%Y-%m-%d') BETWEEN '$sDateFrom' AND '$sDateTo'";
					$rDisplayResult = dbQuery($sDisplayQuery);
					
					while ($oDisplayRow = dbFetchObject($rDisplayResult)) {
						$iOfferPageDisplayCount = $oDisplayRow->offerDisplayCount;
					}
					
					dbFreeResult($rDisplayResult);
					
					if ($fRevenue == 0) {
						$fRevenue = '&nbsp;';
					}
					if ($iOfferPageDisplayCount) {
						$fOfferEcpm = ($fRevenue * 1000) / $iOfferPageDisplayCount;
						$fOfferEcpm = trim(sprintf("%10.2f",round($fOfferEcpm, 2)));
						$fOfferEcpm = "\$".$fOfferEcpm;
					} else {
						$fOfferEcpm = "&nbsp;";
					}
					
					$sReportContent .= "<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right nowrap>$fOfferEcpm</td>";
					
					$sExpReportContent .= "$fOfferEcpm\t";
					
				} // end of page while loop
				
				dbFreeResult($rPagesResult);
								
				
			} else if (count($aCategoryId) > 0) {
				// if categorywise report
				
				$sCatsQuery = "SELECT *
								FROM   categories
								WHERE  title NOT LIKE 'test%'";
				
				// prepare comma separated cat list
				if ($aCategoryId[0] != 'all') {
					for ($i=0;$i<count($aCategoryId);$i++) {
						
						$sCatsSelected = "'".$aCategoryId[$i]."',";
					}
					if ($sCatsSelected !=''){
						$sCatsSelected = substr($sCatsSelected,0,strlen($sCatsSelected)-1);
						
						$sCatsQuery .= " AND id IN (".$sCatsSelected.")";
					}
				}
				
				$sCatsQuery .= " ORDER BY title";
				$rCatsResult = dbQuery($sCatsQuery);
				$iCatCol = 1;
				while ($oCatsRow = dbFetchObject($rCatsResult)) {
					$iCatId = $oCatsRow->id;
					$sCatName = $oCatsRow->title;
												
					
					// define header if iRow = 1
					if ($iRow == 1) {
						
						$sReportHeader .= "<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small>$sCatName</td>";
						
						$sExpReportHeader .= "$sCatName\t";
						
						// get current month (last month of selected date range) details
						
						$sLeadsQuery = "SELECT sum($sLeadsCountCol) AS offersTaken, sum($sLeadsCountCol * offers.revPerLead) as revenue
									FROM   offerLeadsCountSum, offers, categoryMap
									WHERE  offerLeadsCountSum.offerCode = '$sOfferCode'
									AND    offerLeadsCountSum.offerCode = offers.offerCode
									AND    offerLeadsCountSum.offerCode = categoryMap.offerCode
									AND    categoryMap.categoryId = '$iCatId'									
									AND    dateAdded BETWEEN '$sDateFrom' AND '$sDateTo'";
					
						
						$rLeadsResult = dbQuery($sLeadsQuery);
						
						while ($oLeadsRow = dbFetchObject($rLeadsResult)) {
							$iCurrMonthOffersTakenCount += $oLeadsRow->offersTaken;
							$fCurrMonthCatRevenue = $oLeadsRow->revenue;
						}
						
						dbFreeResult($rLeadsResult);
						
						$fCurrMonthCatRevenue = sprintf("%12.2f",round($fCurrMonthCatRevenue, 2));
						
						$fCurrMonthRevenueTotal += $fCurrMonthCatRevenue;
						
						
						$sDisplayQuery = "SELECT sum(opens) AS catDisplayCount
							  		  FROM   pageDisplayStatsSum, otPages
							  		  WHERE  pageDisplayStatsSum.pageId = otPages.id
									  AND	 otPages.offersByCatMap = '$iCatId'
									  AND    date_format(openDate,'%Y-%m-%d') BETWEEN '$sDateFrom' AND '$sDateTo'";
												
						$rDisplayResult = dbQuery($sDisplayQuery);
						while ($oDisplayRow = dbFetchObject($rDisplayResult)) {
							$iCurrMonthCatDisplay = $oDisplayRow->catDisplayCount;
						}
						
						dbFreeResult($rDisplayResult);
						
						$iCurrMonthDisplayTotal += $iCurrMonthCatDisplay;
						
						if ($iCurrMonthCatDisplay) {
							$fCurrMonthCatEcpm = ($fCurrMonthCatRevenue * 1000) / $iCurrMonthCatDisplay;
							$fCurrMonthCatEcpm = trim(sprintf("%10.2f",round($fCurrMonthCatEcpm, 2)));
							$fCurrMonthCatEcpm = "\$".$fCurrMonthCatEcpm;
						} else {
							$fCurrMonthCatEcpm = "&nbsp;";
						}
						
						if ($iCurrMonthCatDisplay == 0) {
							$iCurrMonthCatDisplay = "&nbsp;";
						}
						$sCurrMonthRevRow .= "<td class=small align=right nowrap>$fCurrMonthCatRevenue</td>";
						$sCurrMonthDisplayRow .= "<td class=small align=right nowrap>$iCurrMonthCatDisplay</td>";
						$sCurrMonthEcpmRow .= "<td class=small align=right nowrap>$fCurrMonthCatEcpm</td>";
						
						
						$sExpCurrMonthRevRow .= "$fCurrMonthCatRevenue\t";
						$sExpCurrMonthDisplayRow .= "$iCurrMonthCatDisplay\t";
						$sExpCurrMonthEcpmRow .= "$fCurrMonthCatEcpm\t";
						
						// get previous month (month before the last month of selected date range) details
						$sLeadsQuery = "SELECT sum($sLeadsCountCol) AS offersTaken, sum($sLeadsCountCol * offers.revPerLead) as revenue
										FROM   offerLeadsCountSum, offers, categoryMap
										WHERE  offerLeadsCountSum.offerCode = offers.offerCode
										AND    offerLeadsCountSum.offerCode = categoryMap.offerCode
										AND    categoryMap.categoryId = '$iCatId'														
										AND    date_format(dateAdded,'%Y-%m') = date_format('$sPrevMonth','%Y-%m')";
						
						$rLeadsResult = dbQuery($sLeadsQuery);
						
						while ($oLeadsRow = dbFetchObject($rLeadsResult)) {
							$iPrevMonthOffersTakenCount += $oLeadsRow->offersTaken;
							$fPrevMonthCatRevenue = $oLeadsRow->revenue;
						}
						
						dbFreeResult($rLeadsResult);
						
						$fPrevMonthCatRevenue = sprintf("%12.2f",round($fPrevMonthCatRevenue, 2));
						
						$fPrevMonthRevenueTotal += $fPrevMonthCatRevenue;
						
						
						$sDisplayQuery = "SELECT sum(opens) AS catDisplayCount
							  		  FROM   pageDisplayStatsSum, otPages
							  		  WHERE  pageDisplayStatsSum.pageId = otPages.id
									  AND	 otPages.offersByCatMap = '$iCatId'
									  AND    date_format(openDate,'%Y-%m') = date_format('$sPrevMonth','%Y-%m')";
						
						
						$rDisplayResult = dbQuery($sDisplayQuery);
						while ($oDisplayRow = dbFetchObject($rDisplayResult)) {
							$iPrevMonthCatDisplay = $oDisplayRow->catDisplayCount;
						}
						
						dbFreeResult($rDisplayResult);
						
						$iPrevMonthDisplayTotal += $iPrevMonthCatDisplay;
						
						if ($iPrevMonthCatDisplay) {
							$fPrevMonthCatEcpm = ($fPrevMonthCatRevenue * 1000) / $iPrevMonthCatDisplay;
							$fPrevMonthCatEcpm = trim(sprintf("%10.2f",round($fPrevMonthCatEcpm, 2)));
							$fPrevMonthCatEcpm = "\$".$fPrevMonthCatEcpm;
						} else {
							$fCurrMonthCatEcpm = "&nbsp;";
						}
						
						$sPrevMonthRevRow .= "<td class=small align=right nowrap>$fPrevMonthCatRevenue</td>";
						$sPrevMonthDisplayRow .= "<td class=small align=right nowrap>$iPrevMonthCatDisplay</td>";
						$sPrevMonthEcpmRow .= "<td class=small align=right nowrap>$fPrevMonthCatEcpm</td>";
						
						$sExpPrevMonthRevRow .= "$fPrevMonthCatRevenue\t";
						$sExpPrevMonthDisplayRow .= "$iPrevMonthCatDisplay\t";
						$sExpPrevMonthEcpmRow .= "$fPrevMonthCatEcpm\t";
						
					}
					
					$fRevenue = 0;
					$iOfferCatDisplayCount = 0;
					
					// get lead details
					$sLeadsQuery = "SELECT sum($sLeadsCountCol) AS offersTaken, sum($sLeadsCountCol * offers.) as revenue
									FROM   offerLeadsCountSum, offers, categoryMap
									WHERE  offerLeadsCountSum.offerCode = '$sOfferCode'
									AND    offerLeadsCountSum.offerCode = offers.offerCode
									AND    offerLeadsCountSum.offerCode = categoryMap.offerCode
									AND    categoryMap.categoryId = '$iCatId'								
									AND    dateAdded BETWEEN '$sDateFrom' AND '$sDateTo'";
					
					$rLeadsResult = dbQuery($sLeadsQuery);
					
					while ($oLeadsRow = dbFetchObject($rLeadsResult)) {
						$iOffersTakenCount += $oLeadsRow->offersTaken;
						$fRevenue = $oLeadsRow->revenue;
					}
					dbFreeResult($rLeadsResult);
					
					
					//$iOffersTakenCount += $iOffersTaken;
					$fRevenue = sprintf("%10.2f",round($fRevenue, 2));
					
					$fOfferRevenueRowTotal += $fRevenue;
					
					$sDisplayQuery = "SELECT sum(displayCount) AS offerDisplayCount
							  		  FROM   offerStatsSum, otPages
							  		  WHERE  offerCode = '$sOfferCode'
									  AND	 offerStatsSum.pageId = otPages.id
									  AND	 otPages.offersByCatMap = '$iCatId'
									  AND    date_format(displayDate, '%Y-%m-%d') BETWEEN '$sDateFrom' AND '$sDateTo'";
					$rDisplayResult = dbQuery($sDisplayQuery);
					
					while ($oDisplayRow = dbFetchObject($rDisplayResult)) {
						$iOfferCatDisplayCount = $oDisplayRow->offerDisplayCount;
					}
					
					dbFreeResult($rDisplayResult);
					
					if ($fRevenue == 0) {
						$fRevenue = '&nbsp;';
					}
					if ($iOfferCatDisplayCount) {
						$fOfferEcpm = ($fRevenue * 1000) / $iOfferCatDisplayCount;
						$fOfferEcpm = trim(sprintf("%10.2f",round($fOfferEcpm, 2)));
						$fOfferEcpm = "\$".$fOfferEcpm;
					} else {
						$fOfferEcpm = "&nbsp;";
					}
					
					$sReportContent .= "<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right nowrap>$fOfferEcpm</td>";
					
					$sExpReportContent .= "$fOfferEcpm\t";
					
				} // end of page while loop
				
				dbFreeResult($rCatsResult);
				
				
				
				// get offer display count for all/ selected pages
				
			} //end of  else if (count(aCategoryId)>0)
			
			
			// get the display details and ecpm for the offer
			
			$sDisplayQuery = "SELECT sum(displayCount) AS offerDisplayCount
							  FROM   offerStatsSum
							  WHERE  offerCode = '$sOfferCode'
							  AND    displayDate BETWEEN '$sDateFrom' AND '$sDateTo' ";
			
			if ($sPagesSelected != '') {
				$sDisplayQuery .= " AND pageId IN (".$sPagesSelected.")";
			} else if ($sCatsSelected) {
				$sDisplayQuery = "SELECT sum(displayCount) AS offerDisplayCount
								  FROM   offerStatsSum, categoryMap
								  WHERE  offerStatsSum.offerCode = categoryMap.offerCode
								  AND    categoryMap.categoryId IN (".$sCatsSelected.")
								  AND    displayDate BETWEEN '$sDateFrom' AND '$sDateTo'";
			}
			
			$rDisplayResult = dbQuery($sDisplayQuery);
			$iOfferDisplayCountRowTotal = 0;
			while ($oDisplayRow = dbFetchObject($rDisplayResult)) {
				
				$iOfferDisplayCountRowTotal = $oDisplayRow->offerDisplayCount;
			}
			
			if ($iOfferDisplayCountRowTotal) {
				$fOfferRowEcpm = ($fOfferRevenueRowTotal * 1000 )/ $iOfferDisplayCountRowTotal;
				
			} else {
				$fOfferRowEcpm = "0.0";
				$iOfferDisplayCountRowTotal = "0";
			}
			
			$fOfferRowEcpm = trim(sprintf("%10.2f",round($fOfferRowEcpm, 2)));
			
			
			// get the Prev month (the month before the last month of selected date range )
			// display details and ecpm for the offer
			
			
			// get prev month revenue
			$fPrevMonthOfferRevenueRowTotal = 0;
			$sLeadsQuery = "SELECT sum($sLeadsCountCol) AS offersTaken, sum($sLeadsCountCol * offers.revPerLead) as revenue
									FROM   offerLeadsCountSum, offers
									WHERE  offerLeadsCountSum.offerCode = '$sOfferCode'
									AND    offerLeadsCountSum.offerCode = offers.offerCode									
									AND    date_format(dateAdded, '%Y-%m') = date_format('$sPrevMonth','%Y-%m')";
			
			if ($sPagesSelected != '') {
				$sLeadsQuery .= " AND pageId IN (".$sPagesSelected.")";
			} else if ($sCatsSelected) {
				
				$sLeadsQuery = "SELECT sum($sLeadsCountCol) AS offersTaken, sum($sLeadsCountCol * offers.revPerLead) as revenue
									FROM   offerLeadsCountSum, offers, categoryMap
									WHERE  offerLeadsCountSum.offerCode = '$sOfferCode'
									AND    offerLeadsCountSum.offerCode = offers.offerCode
									AND    offerLeadsCountSum.offerCode = categoryMap.offerCode
									AND    categoryMap.categoryId IN (".$sCatsSelected.")									
									AND    date_format(dateAdded, '%Y-%m') = date_format('$sPrevMonth','%Y-%m')";
			}
			
			
			$rLeadsResult = dbQuery($sLeadsQuery);
			
			echo dbError();
			while ($oLeadsRow = dbFetchObject($rLeadsResult)) {
				$iPrevMonthOffersTakenCount += $oLeadsRow->offersTaken;
				$fPrevMonthOfferRevenueRowTotal += $oLeadsRow->revenue;
				
			}
			
			$fPrevMonthOfferRevenueRowTotal = sprintf("%10.2f",round($fPrevMonthOfferRevenueRowTotal, 2));
			
			
			// get display count
			$iPrevMonthOfferDisplayCountRowTotal = 0;
			$sDisplayQuery = "SELECT sum(displayCount) AS offerDisplayCount
							  FROM   offerStatsSum
							  WHERE  offerCode = '$sOfferCode'
							  AND    date_format(displayDate,'%Y-%m') = date_format('$sPrevMonth','%Y-%m') ";			
			
			if ($sPagesSelected != '') {
				$sDisplayQuery .= " AND pageId IN (".$sPagesSelected.")";
			} else if ($sCatsSelected) {
				$sDisplayQuery = "SELECT sum(displayCount) AS offerDisplayCount
								  FROM   offerStatsSum, categoryMap
								  WHERE  offerStatsSum.offerCode = categoryMap.offerCode
								  AND	 offerStatsSum.offerCode = '$sOfferCode'
								  AND    categoryMap.categoryId IN (".$sCatsSelected.")
								  AND    date_format(displayDate,'%Y-%m') = date_format('$sPrevMonth', '%Y-%m') ";
			}
			
			$rDisplayResult = dbQuery($sDisplayQuery);
			
			
			echo dbError();
			while ($oDisplayRow = dbFetchObject($rDisplayResult)) {
				
				$iPrevMonthOfferDisplayCountRowTotal += $oDisplayRow->offerDisplayCount;
				
			}
			
			if ($iPrevMonthOfferDisplayCountRowTotal) {
				$fPrevMonthOfferRowEcpm = ($fPrevMonthOfferRevenueRowTotal * 1000 )/ $iPrevMonthOfferDisplayCountRowTotal;
				
			} else {
				$fPrevMonthOfferRowEcpm = "0.0";
				$iPrevMonthOfferDisplayCountRowTotal = "0";
			}
			
			
			$fPrevMonthOfferRowEcpm = trim(sprintf("%10.2f",round($fPrevMonthOfferRowEcpm, 2)));
			
			if ($iRow==1) {
				$sReportHeader .= "<td class=small>Offer Total</td><td class=small>Display Total</td>
										<td class=small>Offer eCPM</td><td class=small>Offer eCPM ($sPrevMonthName)</td>
										<td class=small>Rate/PL</td>
										<td class=small>Offer Rev</td><td>&nbsp;</td><td class=small>Rep.</td>
										</tr>";
				
				$sExpReportHeader .= "Offer Total\tDisplay Total\tOffer eCPM\tOffer eCPM ($sPrevMonthName)\tRate/PL\tOffer Rev\t&nbsp;\tRep.\n";
			}
			
			$fOfferRevenueRowTotal = trim(sprintf("%10.2f",round($fOfferRevenueRowTotal, 2)));
			
			$sReportContent .="<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right>$iOffersTakenCount</td>
									<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right>$iOfferDisplayCountRowTotal</td>
									<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right nowrap>\$$fOfferRowEcpm</td>
									<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small nowrap align=right>\$".$fPrevMonthOfferRowEcpm."</td>
									<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right nowrap>\$$fRevPerLead</td>
									<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right nowrap>\$$fOfferRevenueRowTotal</td>
									<td class=small>$iRow</td><td class=small>$sOfferRep</td></tr>";
			
			$sExpReportContent .="$iOffersTakenCount\t$iOfferDisplayCountRowTotal\t\$$fOfferRowEcpm\t\$".$fPrevMonthOfferRowEcpm;
			$sExpReportContent .= "\t\$$fRevPerLead\t\$$fOfferRevenueRowTotal\t$iRow\t$sOfferRep\t\n";
			
		} // end of offers while loop
		
		dbFreeResult($rOffersResult);
		
		if ($sReportContent != '') {
			
			$fCurrMonthRevenueTotal = sprintf("%10.2f",round($fCurrMonthRevenueTotal, 2));
			
			if ($iCurrMonthDisplayTotal) {
				$fCurrMonthEcpm = ($fCurrMonthRevenueTotal * 1000) / $iCurrMonthDisplayTotal;
				$fCurrMonthEcpm = sprintf("%10.2f",round($fCurrMonthEcpm, 2));
				$fCurrMonthEcpm = "\$".$fCurrMonthEcpm;
			} else {
				$fCurrMonthEcpm = "&nbsp;";
			}
			
			
			$fPrevMonthRevenueTotal = sprintf("%10.2f",round($fPrevMonthRevenueTotal, 2));
			
			if ($iPrevMonthDisplayTotal) {
				$fPrevMonthEcpm = ($fPrevMonthRevenueTotal * 1000) / $iPrevMonthDisplayTotal;
				$fPrevMonthEcpm = sprintf("%10.2f",round($fPrevMonthEcpm, 2));
				$fPrevMonthEcpm = "\$".$fPrevMonthEcpm;
			} else {
				$fPrevMonthEcpm = "&nbsp;";
			}
			
			
			$sReportContent .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td class=small nowrap>Page Total Rev</td>$sCurrMonthRevRow
								<td align=right nowrap class=small><b>$fCurrMonthRevenueTotal</b></td>
								<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";	
			$sReportContent .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td class=small nowrap>$sPrevMonthName Page Total Rev</td>$sPrevMonthRevRow
								<td align=right nowrap class=small><b>$fPrevMonthRevenueTotal</b></td>
								<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";	
			
			$sReportContent .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td class=small nowrap>Page Total Display</td>$sCurrMonthDisplayRow
								<td>&nbsp;</td><td align=right nowrap class=small><b>$iCurrMonthDisplayTotal</b></td>
								<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";	
			$sReportContent .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td class=small nowrap>$sPrevMonthName Page Total Display</td>$sPrevMonthDisplayRow
								<td>&nbsp;</td><td align=right nowrap class=small><b>$iPrevMonthDisplayTotal</b></td>
								<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";	
			
			$sReportContent .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td nowrap class=small>Page eCPM</td>$sCurrMonthEcpmRow
								<td>&nbsp;</td><td>&nbsp;</td><td align=right nowrap class=small><b>$fCurrMonthEcpm</b></td>
								<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";	
			$sReportContent .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td nowrap class=small>$sPrevMonthName Page eCPM</td>$sPrevMonthEcpmRow
								<td>&nbsp;</td><td>&nbsp;</td><td align=right nowrap class=small><b>$fPrevMonthEcpm</b></td>
								<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";	
			
			
			// export content
			$sExpReportContent .= "\n\t\t\tPage Total Rev\t$sExpCurrMonthRevRow"."$fCurrMonthRevenueTotal\t\t\t\t\t\t\t\n";
			$sExpReportContent .= "\t\t\t$sPrevMonthName Page Total Rev\t$sExpPrevMonthRevRow"."$fPrevMonthRevenueTotal\t\t\t\t\t\t\t\n";
			
			$sExpReportContent .= "\t\t\tPage Total Display\t$sExpCurrMonthDisplayRow\t$iCurrMonthDisplayTotal\t\t\t\t\t\t\n";
			$sExpReportContent .= "\t\t\t$sPrevMonthName Page Total Display\t$sExpPrevMonthDisplayRow\t$iPrevMonthDisplayTotal\t\t\t\t\t\t\n";
			
			$sExpReportContent .= "\t\t\tPage eCPM\t$sExpCurrMonthEcpmRow\t\t$fCurrMonthEcpm\t\t\t\t\t\n";
			$sExpReportContent .= "\t\t\t$sPrevMonthName Page eCPM\t$sExpPrevMonthEcpmRow\t\t$fPrevMonthEcpm\t\t\t\t\t\n";
			
		}
		
	} // end of check date
	
	if ($sExport) {
		
		$sExpReportContent = $sExpReportHeader."\n".$sExpReportContent;
		$sExpReportContent .= "\n\nReport From $iMonthFrom-$iDayFrom-$iYearFrom To $iMonthTo-$iDayTo-$iYearTo";
		$sExpReportContent .= "\nRun Date/Time $sRunDateAndTime";
		$sExpReportContent .= "\n\nNotes:\nReport reflects counts for selected date range.\nPrevious Month counts are counts of the second last month of selected date range.";
		$sExpReportContent = ereg_replace("&nbsp;","",$sExpReportContent);
		
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=ecpm.xls");
		header("Content-Description: Excel output");
		echo $sExpReportContent;
		// if didn't exit, all the html page content will be saved as excel file.
		exit();
		
	} else {
		
		include("../../includes/adminAddHeader.php");
		
		$iScriptEndTime = getMicroTime();
		$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);
		
		
?>

	<table cellpadding=0 cellspacing=0 bgcolor=#FFFFFF width=95% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td  class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>
	<?php echo "From $iMonthFrom-$iDayFrom-$iYearFrom to $iMonthTo-$iDayTo-$iYearTo";?><BR><BR></td></tr>
	<tr><td class=header>Run Date / Time: <?php echo $sRunDateAndTime; ?></td></tr>
	</table></td></tr></table></td></tr>
	<tr><td>
		<table cellpadding=3 cellspacing=0 bgcolor=#FFFFFF width=100% align=center border=1 bordercolorlight=#000000>
			<?php echo $sReportHeader;?>
			<?php echo $sReportContent;?>
			
		
		</table>
	</td></tr>
	</table>


<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=95% align=center>
	
<tr><td class=header>Notes:</td></tr>
<tr><td>Report is accurate as of midnight last night after today's leads are processed.</td></tr>
<tr><td>Only gross leads report is accurate for the offers which are not processed on a daily basis.</td></tr>
<tr><td>Report reflects counts for selected date range.</td></tr>
<tr><td>Previous Month counts are of the month prior to the selected date range.</td></tr>
<tr><td>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
</table>

<?php

	}
	
} else {
	echo "You are not authorized to access this page...";
}
?>