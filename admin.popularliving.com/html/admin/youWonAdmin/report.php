<?php

/***********

Script to display You Won Report

************/

include("../../includes/paths.php");

$sPageTitle = "You Won Reporting";
session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	
	
	//$monthArray = array('Jan','Feb','Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
	
	$iCurrYear = date(Y);
	$iCurrMonth = date(m); //01 to 12
	$iCurrDay = date(d); // 01 to 31
	
	// set curr date values to be selected by default
	if (!($sGetReport)) {
		$iMonthFrom = $iCurrMonth;
		$iMonthTo = $iCurrMonth;
		$iDayFrom = "01";
		$iDayTo = $iCurrDay;
		$iYearFrom = $iCurrYear;
		$iYearTo = $iCurrYear;
	}
	
	
	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		$iValue = $i+1;	
		if ($i < 10) {
			$iValue ="0".$i+1;
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
	
	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";		
	
	if (checkDate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo,$iYearTo)) {		
		
		if (!($sOrderColumn)) {
			$sOrderColumn = "dateWon";
			$sDateWonOrder = "DESC";
		}
		switch ($sOrderColumn) {
			
			case "totalWon" :
			$sCurrOrder = $sTotalWonOrder;
			$sTotalWonOrder = ($sTotalWonOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "clicks" :
			$sCurrOrder = $totalRespondedOrder;
			$sTotalRespondedOrder = ($sTotalRespondedOrder != "DESC" ? "DESC" : "ASC");
			break;
			default:
			$sCurrOrder = $sDateWonOrder;
			$sDateWonOrder = ($sDateWonOrder != "DESC" ? "DESC" : "ASC");
		}
		
		$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearFrom=$iYearFrom";
		$sSortLink .="&iMonthTo=$iMonthTo&iDayTo=$iDayTo&iYearTo=$iYearTo&sGetReport=GetReport";
		
		// Specify Page no. settings
		$iRecPerPage = 10;
		if (!($iPage)) {
			$iPage = 1;
		}
		$iStartRec = ($iPage-1) * $iRecPerPage;
		$iEndRec = $iStartRec + $iRecPerPage -1;		
		
		// Prepare report data to display
		$sSelectQuery = "SELECT dateWon, count(email) AS totalWon
						FROM youWonTest
						WHERE dateWon >= '$sDateFrom'
						AND dateWon <= '$sDateTo'";		
		
		$sSelectQuery .= " GROUP BY dateWon	";
		$sSelectQuery .= " ORDER BY ".$sOrderColumn." $sCurrOrder";
		
		// Get the total no of records and count total no of pages
		$rResult = dbQuery($sSelectQuery);
		echo dbError();
		$iNumRecords = dbNumRows($rResult);
		$iGrandTotalWon = 0;
		$iTotalPages = ceil($iNumRecords/$iRecPerPage);
		if ($iNumRecords > 0)
		$sCurrentPage = " Page $iPage "."/ $iTotalPages";
		while($oTempRow = dbFetchObject($rResult)) {
			$iGrandTotalWon += $oTempRow->totalWon;
		}
		
		// get grandTotalResponses
		$sResponseQuery = "SELECT count(responded) grandTotalResponded
						  FROM   youWonTest
						  WHERE  dateWon >= '$sDateFrom'
						  AND    dateWon <= '$sDateTo'
						  AND    responded = 'Y'";
		$rResponseResult = dbQuery($sResponseQuery);
		while($oResponseRow =dbFetchObject($rResponseResult)) {
			$iGrandTotalResponses = $oResponseRow->grandTotalResponded;
		}		
		
		$iPageTotalWon = 0;
		$sSelectQuery .= " LIMIT $iStartRec, $iRecPerPage";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Report: $sSelectQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sSelectQuery);
		
		if ($rResult) {
			
			$iNumRecords = dbNumRows($rResult);
			if ($iNumRecords > 0) {
				
				$iTotalClicks=0;
				while ($oRow = dbFetchObject($rResult)) {
					
					if ($sBgcolorClass=="ODD") {
						$sBgcolorClass="EVEN";
					} else {
						$sBgcolorClass="ODD";
					}
					
					$iPageTotalWon += $oRow->totalWon;
					// Prepare Next/Prev/First/Last links
					if ($iTotalPages > $iPage) {
						$iNextPage = $iPage+1;
						$sNextPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iNextPage&sCurrOrder=$sCurrOrder' class=header>Next</a>";
						$sLastPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iTotalPages&sCurrOrder=$sCurrOrder' class=header>Last</a>";
					}
					if ($iPage!=1) {
						$iPrevPage = $iPage-1;
						$sPrevPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iPrevPage&sCurrOrder=$sCurrOrder' class=header>Previous</a>";
						$sFirstPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=1&sCurrOrder=$sCurrOrder' class=header>First</a>";
					}
					// count responses
					$iTotalResponses = 0;
					$sResponseQuery = "SELECT count(responded) totalResponses
									  FROM   youWonTest
									  WHERE  dateWon = '".$oRow->dateWon."'
									  AND    responded ='Y'";
					$rResponseResult = dbQuery($sResponseQuery);
					while($oResponseRow= dbFetchObject($rResponseResult)) {
						$iTotalResponses = $oResponseRow->totalResponses;
					}
					$iPageTotalResponses += $iTotalResponses;
					$fPercentage = round((100 * $iTotalResponses)/($oRow->totalWon),2);
					
					$sReportData .="<tr class=$sBgcolorClass><td>$oRow->dateWon</td>
								 <td>$oRow->totalWon</td><td>$iTotalResponses</td>
								<td>$fPercentage</td></tr>";										
				}
			} else {
				$sMessage = "No records exist...";
			}
			if ($sBgcolorClass=="ODD") {
				$sBgcolorClass="EVEN";
			} else {
				$sBgcolorClass="ODD";
			}
			if ($iPageTotalWon != 0) {
				$fPercentage = round((100 * $iPageTotalResponses)/($iPageTotalWon), 2);
			} else {
				$fPercentage = 0;
			}
			$sReportData .="<tr class=$bgcolorClass><td><b>Page Total</b></td><td><b>$iPageTotalWon</b></td><td><b>$iPageTotalResponses</b></td><td><b>$fPercentage</b></td></tr>";
			
			if ($sBgcolorClass=="ODD") {
				$sBgcolorClass="EVEN";
			} else {
				$sBgcolorClass="ODD";
			}
			if ($iGrandTotalWon != 0) {
				$fPercentage = round((100 * $iGrandTotalResponses)/($iGrandTotalWon), 2);
			} else {
				$fPercentage = 0;
			}
			$sReportData .="<tr class=$sBgcolorClass><td><b>Grand Total</b></td><td><b>$iGrandTotalWon</b></td><td><b>$iGrandTotalResponses</b></td><td><b>$fPercentage</b></td></tr>";
			
			dbFreeResult($rResult);
			
		} else {
			echo dbError();
		}
	} else {
		$sMessage = "Please select valid dates...";
	}	
	
	
	$sHidden =  "<input type=hidden name=iMenuId value='$iMenuId'>";
	
	$sYouWonLink = "<a href='index.php?iMenuId=$iMenuId'>Back To You Won Admin Menu</a>";
	
	
include("../../includes/adminHeader.php");	

?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<table width=95% align=center bgcolor=c9c9c9>
<tr><td><?php echo $sYouWonLink;?></td></tr>
	<td>Date from</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td>Date to</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>
	<tr>
<td><input type=submit name=sGetReport value='Get Report'></td></tr>

			</table>
			
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=3 align=right class=header><?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp;
		 <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>	
<tr><TD align=left class=header>Date Won</TD>
<TD align=left class=header>Total eMail Addresses Entered</TD>	
<TD align=left class=header>Total Links Clicked</TD>
<TD align=left class=header>%</TD>
</tr>
<?php echo $sReportData;?>
<tr><td colspan=3 align=right class=header><?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp;
		 <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>
</table>
</form>		
	
<?php 
	include("../../includes/adminFooter.php");
	
} else {
	echo "You are not authoresed to access this page...";
}				

?>