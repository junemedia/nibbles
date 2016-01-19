<?php

/*********

Script to Display

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "ECPM Summary Report";

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
	
	//if (! ($sViewReport || $sExport)) {
		$iYearTo = substr( $sYesterday, 0, 4);
		$iMonthTo = substr( $sYesterday, 5, 2);
		$iDayTo = substr( $sYesterday, 8, 2);
		
		$iYearFrom = substr( $sYesterday, 0, 4);
		$iMonthFrom = substr( $sYesterday, 5, 2);
		$iDayFrom = "01";
		
	//}
	
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
		
		// specify any date of last month as just place holder
		$sPrevMonth = $iPrevMonthYear."-".$iPrevMonthMonth."-25";			
		
		$iPrevMonthNum = substr($sPrevMonth,5,2);
		$iPrevMonthNum = round($iPrevMonthNum) - 1;
		$sPrevMonthName = $aGblMonthsArray[$iPrevMonthNum];
				
		
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
				$sOffersQuery = "SELECT DISTINCT offerLeadsCountSum.offerCode, offers.mode, offers.isLive, 
										offerCompanies.creditStatus, offers.revPerLead, offerCompanies.repDesignated
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
			$sMode = $oOffersRow->mode;
			$iIsLive = $oOffersRow->isLive;
			$sCreditStatus = $oOffersRow->creditStatus;
			
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
			
			/*if ($iRow==1 && $sReportHeader == '') {
				// define report header
				$sReportHeader = "<tr><td>&nbsp;</td><td class=small>Rep.</td><td class=small>Rate/PL</td><td class=small>OfferCode</td>";
				$sExpReportHeader = "\tRep.\tRate/PL\tOfferCode\t";
			}*/
			
		//	$sReportContent .= "<tr><td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small>$iRow</td>
						//			";
			//$sExpReportContent .= "$iRow\t";
			
			
					
					// define header if iRow = 1
					if ($iRow == 1) {
			
						// get current month (last month of selected date range) details
						$sLeadsQuery = "SELECT sum($sLeadsCountCol) AS offersTaken, sum($sLeadsCountCol * offers.revPerLead) as revenue
										FROM   offerLeadsCountSum, offers
										WHERE  offerLeadsCountSum.offerCode = offers.offerCode										
										AND    dateAdded BETWEEN '$sDateFrom' AND '$sDateTo'";
						
						
						$rLeadsResult = dbQuery($sLeadsQuery);
						
						while ($oLeadsRow = dbFetchObject($rLeadsResult)) {
							$iCurrMonthOffersTakenCount = $oLeadsRow->offersTaken;
							$fCurrMonthRevenue = $oLeadsRow->revenue;
						}
						
						
						dbFreeResult($rLeadsResult);
						
						
						$fCurrMonthRevenue = sprintf("%12.2f",round($fCurrMonthRevenue, 2));
						
												
						$sDisplayQuery = "SELECT sum(opens) AS displayCount
							  		  FROM   pageDisplayStatsSum
							  		  WHERE  date_format(openDate,'%Y-%m-%d') BETWEEN '$sDateFrom' AND '$sDateTo'";
						
						
						$rDisplayResult = dbQuery($sDisplayQuery);
						while ($oDisplayRow = dbFetchObject($rDisplayResult)) {
							$iCurrMonthDisplayCount += $oDisplayRow->displayCount;
						}
						
						dbFreeResult($rDisplayResult);
						
												
						if ($iCurrMonthDisplayCount == 0) {
							$iCurrMonthDisplayCount = "&nbsp;";
						}
						
						
												
						
						// get previous month (month before the last month of selected date range) details
						$sLeadsQuery = "SELECT sum($sLeadsCountCol) AS offersTaken, sum($sLeadsCountCol * offers.revPerLead) as revenue
										FROM   offerLeadsCountSum, offers
										WHERE  offerLeadsCountSum.offerCode = offers.offerCode										
										AND    date_format(dateAdded,'%Y-%m') = date_format('$sPrevMonth','%Y-%m')";
						
						$rLeadsResult = dbQuery($sLeadsQuery);
						
						while ($oLeadsRow = dbFetchObject($rLeadsResult)) {
							$iPrevMonthOffersTakenCount = $oLeadsRow->offersTaken;
							$fPrevMonthRevenue = $oLeadsRow->revenue;
						}
						
						dbFreeResult($rLeadsResult);
						
						$fPrevMonthRevenueTotal = sprintf("%12.2f",round($fPrevMonthRevenueTotal, 2));
						
											
						
						$sDisplayQuery = "SELECT sum(opens) AS displayCount
							  		  FROM   pageDisplayStatsSum
							  		  WHERE  date_format(openDate,'%Y-%m') = date_format('$sPrevMonth','%Y-%m')";
						
						
						$rDisplayResult = dbQuery($sDisplayQuery);
						while ($oDisplayRow = dbFetchObject($rDisplayResult)) {
							$iPrevMonthDisplayCount += $oDisplayRow->displayCount;
						}
						
						dbFreeResult($rDisplayResult);
												
					}
					
					
					// get no of pages offer is on
					$iNoOfPagesOfferLiveOn = 0;
					$sOfferPagesQuery = "SELECT count(*) AS noOfPages
										 FROM   pageMap
										 WHERE  offerCode = '$sOfferCode'";
					$rOFferPagesResult = dbQuery($sOfferPagesQuery);
					while ($oOfferPagesRow = dbFetchObject($rOFferPagesResult)) {
						$iNoOfPagesOfferLiveOn = $oOfferPagesRow->noOfPages;
					} 
					
					$fOfferRevenue = 0;
					$iOfferDisplayCount = 0;					
					$fOfferEcpm = 0;
					// get lead details
					$sLeadsQuery = "SELECT sum($sLeadsCountCol) AS offersTaken, sum($sLeadsCountCol * offers.revPerLead) as revenue
									FROM   offerLeadsCountSum, offers
									WHERE  offerLeadsCountSum.offerCode = '$sOfferCode'
									AND    offerLeadsCountSum.offerCode = offers.offerCode									
									AND    dateAdded BETWEEN '$sDateFrom' AND '$sDateTo'";
					
					$rLeadsResult = dbQuery($sLeadsQuery);
					
					while ($oLeadsRow = dbFetchObject($rLeadsResult)) {
						$iOffersTakenCount += $oLeadsRow->offersTaken;
						
						$fOfferRevenue = $oLeadsRow->revenue;
					
					}
					dbFreeResult($rLeadsResult);
										
					$fOfferRevenue = sprintf("%10.2f",round($fOfferRevenue, 2));
					
			
			// get the display details and ecpm for the offer
			
			$sDisplayQuery = "SELECT sum(displayCount) AS offerDisplayCount
							  FROM   offerStatsSum
							  WHERE  offerCode = '$sOfferCode'
							  AND    displayDate BETWEEN '$sDateFrom' AND '$sDateTo' ";
			
						
			$rDisplayResult = dbQuery($sDisplayQuery);
			
			while ($oDisplayRow = dbFetchObject($rDisplayResult)) {
				
				$iOfferDisplayCount = $oDisplayRow->offerDisplayCount;
			}
			
			if ($iOfferDisplayCount) {
				$fOfferEcpm = ($fOfferRevenue * 1000 )/ $iOfferDisplayCount;
				
			} else {
				$fOfferEcpm = "0.0";
				$iOfferDisplayCount = "0";
			}
			
			$fOfferEcpm = trim(sprintf("%10.2f",round($fOfferEcpm, 2)));
			
			
			// get the Prev month (the month before the last month of selected date range )
			// display details and ecpm for the offer
			
			
			// get prev month revenue
			$fPrevMonthOfferRevenue = 0;
			$sLeadsQuery = "SELECT sum($sLeadsCountCol) AS offersTaken, sum($sLeadsCountCol * offers.revPerLead) as revenue
									FROM   offerLeadsCountSum, offers
									WHERE  offerLeadsCountSum.offerCode = '$sOfferCode'
									AND    offerLeadsCountSum.offerCode = offers.offerCode									
									AND    date_format(dateAdded, '%Y-%m') = date_format('$sPrevMonth','%Y-%m')";
			
						
			
			$rLeadsResult = dbQuery($sLeadsQuery);
			
			echo dbError();
			while ($oLeadsRow = dbFetchObject($rLeadsResult)) {
				$iPrevMonthOffersTakenCount += $oLeadsRow->offersTaken;
				$fPrevMonthOfferRevenue += $oLeadsRow->revenue;
				
			}
			
			$fPrevMonthOfferRevenue = sprintf("%10.2f",round($fPrevMonthOfferRevenue, 2));
			
			
			// get display count
			$iPrevMonthOfferDisplayCount = 0;
			$sDisplayQuery = "SELECT sum(displayCount) AS offerDisplayCount
							  FROM   offerStatsSum
							  WHERE  offerCode = '$sOfferCode'
							  AND    date_format(displayDate,'%Y-%m') = date_format('$sPrevMonth','%Y-%m') ";			
			
			
			$rDisplayResult = dbQuery($sDisplayQuery);
			
			
			echo dbError();
			while ($oDisplayRow = dbFetchObject($rDisplayResult)) {
				
				$iPrevMonthOfferDisplayCount += $oDisplayRow->offerDisplayCount;
				
			}
			
			if ($iPrevMonthOfferDisplayCount) {
				$fPrevMonthOfferEcpm = ($fPrevMonthOfferRevenue * 1000 )/ $iPrevMonthOfferDisplayCount;
				
			} else {
				$fPrevMonthOfferEcpm = "0.0";
				$iPrevMonthOfferDisplayCount = "0";
			}
			
			
			$fPrevMonthOfferEcpm = trim(sprintf("%10.2f",round($fPrevMonthOfferEcpm, 2)));
			
/*			if ($iRow==1) {
				$sReportHeader .= "<td class=small align=right>No Of Pages Offer On</td><td class=small align=right>Offer Total</td><td class=small align=right>Display Total</td>
										<td class=small align=right>Offer eCPM</td><td class=small align=right>Offer eCPM ($sPrevMonthName)</td>
										<td class=small align=right>Rate/PL</td>
										<td class=small align=right>Offer Rev</td>
										</tr>";
				
				$sExpReportHeader .= "No Of Pages Offer Live On\tOffer Total\tDisplay Total\tOffer eCPM\tOffer eCPM ($sPrevMonthName)\tRate/PL\tOffer Rev\n";
			}
			*/
			
			$fOfferRevenue = trim(sprintf("%10.2f",round($fOfferRevenue, 2)));
			
			$aReportArray['offerCode'][$iRow-1] = $sOfferCode;
			$aReportArray['offerRep'][$iRow-1] = $sOfferRep;
			$aReportArray['noOfPages'][$iRow-1] = $iNoOfPagesOfferLiveOn;
			$aReportArray['offersTakenCount'][$iRow-1] = $iOffersTakenCount;
			$aReportArray['offerDisplayCount'][$iRow-1] = $iOfferDisplayCount;
			$aReportArray['offerEcpm'][$iRow-1] = $fOfferEcpm;
			$aReportArray['prevMonthOfferEcpm'][$iRow-1] = $fPrevMonthOfferEcpm;
			$aReportArray['revPerLead'][$iRow-1] = $fRevPerLead;
			$aReportArray['offerRevenue'][$iRow-1] = $fOfferRevenue;

			if (strtoupper($sMode) == 'A' && $iIsLive && strtoupper($sCreditStatus)=='OK') {
				$aReportArray['isLive'][$iRow-1] = "Y";
			} else {
				$aReportArray['isLive'][$iRow-1] = "N";
			}

		} // end of offers while loop
		
		dbFreeResult($rOffersResult);
		
		$sReportHeader .= "<tr><td>&nbsp;</td><td class=small>Rep.</td><td class=small>Rate/PL</td><td class=small>OfferCode</td>
								<td class=small align=right>Offer eCPM</td><td class=small align=right>Offer eCPM ($sPrevMonthName)</td>
									<td class=small align=right>No Of Pages<BR> Offer On</td>
										<td class=small align=right>Offer Total</td><td class=small align=right>Display Total</td>
										<td class=small align=right>Rate/PL</td>
										<td class=small align=right>Offer Rev</td>
										<td class=small align=right>Display</td>
										</tr>";
				
		$sExpReportHeader .= "\tRep.\tRate/PL\tOfferCode\tOffer eCPM\tOffer eCPM ($sPrevMonthName)\tNo Of Pages Offer Live On\tOffer Total\tDisplay Total\tRate/PL\tOffer Rev\tDisplay\n";

		// sort array in descending order of offer ecpm only if array is not empty	
		if (count($aReportArray['offerCode']) > 0 ) {
			array_multisort($aReportArray['offerEcpm'], SORT_DESC, $aReportArray['prevMonthOfferEcpm'],  $aReportArray['offerCode'], $aReportArray['revPerLead'] ,$aReportArray['offerRep'], $aReportArray['noOfPages'], $aReportArray['offersTakenCount'], $aReportArray['offerDisplayCount'], $aReportArray['offerRevenue'], $aReportArray['isLive']);
		}
		
		for ($i = 0; $i < count($aReportArray['offerCode']); $i++) {
			
			$iRow = $i+1;
		$sReportContent .="<tr><td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small>$iRow</td><td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small>".$aReportArray['offerRep'][$i]."</td>
									<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small nowrap>\$".$aReportArray['revPerLead'][$i]."</td>
									<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small>".$aReportArray['offerCode'][$i]."</td>
									<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right nowrap>\$".$aReportArray['offerEcpm'][$i]."</td>
									<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small nowrap align=right>\$".$aReportArray['prevMonthOfferEcpm'][$i]."</td>		
									<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right>".$aReportArray['noOfPages'][$i]."</td>
									<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right>".$aReportArray['offersTakenCount'][$i]."</td>
									<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right>".$aReportArray['offerDisplayCount'][$i]."</td>
									<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right nowrap>\$".$aReportArray['revPerLead'][$i]."</td>
									<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right nowrap>\$".$aReportArray['offerRevenue'][$i]."</td>
									<td bordercolordark=#FFFFFF bordercolorlight=#000000 class=small align=right nowrap>".$aReportArray['isLive'][$i]."</td>
									</tr>";
			
			$sExpReportContent .= "$iRow\t".$aReportArray['offerRep'][$i] . "\t\$" . $aReportArray['revPerLead'][$i] . "\t" . $aReportArray['offerCode'][$i].
									"\t\$" . $aReportArray['offerEcpm'][$i] . "\t\$". $aReportArray['prevMonthOfferEcpm'][$i] . "\t" . $aReportArray['noOfPages'][$i].
									"\t". $aReportArray['offersTakenCount'][$i] . "\t" . $aReportArray['offerDisplayCount'][$i].
									"\t\$". $aReportArray['revPerLead'][$i] . "\t\$" . $aReportArray['offerRevenue'][$i] . "\t\$" . $aReportArray['isLive'][$i]. "\n";
		}
		
		if ($sReportContent != '') {
			
			$fCurrMonthRevenue = sprintf("%10.2f",round($fCurrMonthRevenue, 2));
			
			if ($iCurrMonthDisplayCount) {
				$fCurrMonthEcpm = ($fCurrMonthRevenue * 1000) / $iCurrMonthDisplayCount;
				$fCurrMonthEcpm = sprintf("%10.2f",round($fCurrMonthEcpm, 2));
				$fCurrMonthEcpm = "\$".$fCurrMonthEcpm;
			} else {
				$fCurrMonthEcpm = "&nbsp;";
			}
			
			
			$fPrevMonthRevenue = sprintf("%10.2f",round($fPrevMonthRevenue, 2));
			
			if ($iPrevMonthDisplayCount) {
				$fPrevMonthEcpm = ($fPrevMonthRevenue * 1000) / $iPrevMonthDisplayCount;
				$fPrevMonthEcpm = sprintf("%10.2f",round($fPrevMonthEcpm, 2));
				$fPrevMonthEcpm = "\$".$fPrevMonthEcpm;
			} else {
				$fPrevMonthEcpm = "&nbsp;";
			}
			
			
			$sReportContent .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td class=small nowrap>Total Rev</td>
								<td align=right nowrap class=small><b>$fCurrMonthRevenue</b></td>
								<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";	
			$sReportContent .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td class=small nowrap>$sPrevMonthName Total Rev</td>
								<td align=right nowrap class=small><b>$fPrevMonthRevenue</b></td>
								<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";	
			
			$sReportContent .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td class=small nowrap>Total Display</td>
								<td>&nbsp;</td><td align=right nowrap class=small><b>$iCurrMonthDisplayCount</b></td>
								<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";	
			$sReportContent .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td class=small nowrap>$sPrevMonthName Total Display</td>
								<td>&nbsp;</td><td align=right nowrap class=small><b>$iPrevMonthDisplayCount</b></td>
								<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";	
			
			$sReportContent .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td nowrap class=small>eCPM</td>
								<td>&nbsp;</td><td>&nbsp;</td><td align=right nowrap class=small><b>$fCurrMonthEcpm</b></td>
								<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";	
			$sReportContent .= "<tr><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td nowrap class=small>$sPrevMonthName eCPM</td>
								<td>&nbsp;</td><td>&nbsp;</td><td align=right nowrap class=small><b>$fPrevMonthEcpm</b></td>
								<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";	
			
			// export content
			$sExpReportContent .= "\n\t\t\tTotal Rev\t"."$fCurrMonthRevenue\t\t\t\t\t\t\t\n";
			$sExpReportContent .= "\t\t\t$sPrevMonthName Total Rev\t"."$fPrevMonthRevenue\t\t\t\t\t\t\t\n";
			
			$sExpReportContent .= "\t\t\tTotal Display\t\t$iCurrMonthDisplayCount\t\t\t\t\t\t\n";
			$sExpReportContent .= "\t\t\t$sPrevMonthName Total Display\t\t$iPrevMonthDisplayCount\t\t\t\t\t\t\n";
			
			$sExpReportContent .= "\t\t\teCPM\t\t\t$fCurrMonthEcpm\t\t\t\t\t\n";
			$sExpReportContent .= "\t\t\t$sPrevMonthName eCPM\t\t\t$fPrevMonthEcpm\t\t\t\t\t\n";
			
		}
		
	} // end of check date
	
	if ($sExport) {
		
		$sExpReportContent = $sExpReportHeader."\n".$sExpReportContent;
		$sExpReportContent .= "\n\nReport From $iMonthFrom-$iDayFrom-$iYearFrom To $iMonthTo-$iDayTo-$iYearTo";
		$sExpReportContent .= "\nRun Date/Time $sRunDateAndTime";
		$sExpReportContent .= "\n\nNotes:\nReport reflects counts for selected date range.\nPrevious Month counts are counts of the second last month of selected date range.";
		$sExpReportContent = ereg_replace("&nbsp;","",$sExpReportContent);
		
		header("Content-type: application/vnd.ms-excel");
		header("Content-Disposition: attachment; filename=ecpmSummary.xls");
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
<tr><td>Only gross leads report is accurate for the offers which are not processed daily on a basis.</td></tr>
<tr><td>Report reflects counts for current month upto midnight last night.</td></tr>
<tr><td>Previous Month counts are of the month prior to the current month.</td></tr>
<tr><td>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
</table>

<?php

	}
	
} else {
	echo "You are not authorized to access this page...";
}
?>