<?php

/***********

Script to display Report of Microsoft Complaints by Email

************/

include("../../includes/paths.php");

$sPageTitle = "Microsoft Complaints by Email";

$sParentMenuFolder = "scomp";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
session_start();


if (hasAccessRight($iParentMenuId) || isAdmin()) {
		
	// Set Current Date values	
	$iCurrYear = date(Y);
	$iCurrMonth = date(m); //01 to 12
	$iCurrDay = date(d); // 01 to 31
	
	// set curr date values to be selected by default
	// to display report of current month
	if (!($sSubmit)) {
		$iMonthFrom = $iCurrMonth;
		$iMonthTo = $iCurrMonth;
		$iDayFrom = $iCurrDay;
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
	
	// prepare year options for From and To date
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
	
	// Set the sortLink to use for all the links on this page
	
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearFrom=$iYearFrom&iMonthTo=$iMonthTo&iDayTo=$iDayTo&iYearTo=$iYearTo";
	$sSortLink .= "&sFilter=$sFilter&sExactMatch=$sExactMatch&sSubmit=ViewReport";
	
	// Check if the selected date is a valid date
	if (checkDate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo,$iYearTo)) {
		
		// Set Order column as Current Order and set sorting order of it.
		// Don't change the order if Prev/Next/Last/First clicked, i.e. currOrder will be there
		if (!($sCurrOrder)) {
			if (!($sOrderColumn)) {
				$sOrderColumn = "counts";
				$sCountsOrder = "DESC";
			}
			switch ($sOrderColumn) {
				
				case "unsubDate" :
				$sCurrOrder = $sUnsubDateOrder;
				$sUnsubDateOrder = ($sUnsubDateOrder != "DESC" ? "DESC" : "ASC");
				break;
				case "email":
				$sCurrOrder = $sEmailOrder;
				$sEmailOrder = ($sEmailOrder != "DESC" ? "DESC" : "ASC");
				break;
				default :
				$sCurrOrder = $sCountsOrder;
				$sCountsOrder = ($sCountsOrder != "DESC" ? "DESC" : "ASC");
			}
		}
		
		// Prepare filter part of the query if filter specified...
		if ($sFilter != '') {
			if ($sExactMatch == 'Y') {
				$sFilterPart = " AND email='$sFilter' ";
			} else {
				$sFilterPart = " AND email like '%$sFilter%'";
			}
		}
		
		// Specify Page no. settings
		$iRecPerPage = 50;
		if (!($iPage)) {
			$iPage = 1;
		}
		$iStartRec = ($iPage-1) * $iRecPerPage;
		$iEndRec = $iStartRec + $iRecPerPage -1;
		
		if ($sSupressDate == 'Y') {
			// count records and GrandTotalCounts for group by email, if date is supressed
			$sCountQuery = "SELECT count(*) numRecords, sum(counts) counts
						   FROM scompEmailStats
						   WHERE unsubDate BETWEEN '$sDateFrom' AND '$sDateTo'
						   AND sender='microsoft'
						   $sFilterPart GROUP BY email";
			$rCountResult = dbQuery($sCountQuery);
			$iNumRecords = dbNumRows($rCountResult);
			while ($oCountRow = dbFetchObject($rCountResult)) {
				$iGrandTotalCounts += $oCountRow->counts;
			}
		} else {
			// count records and GrandTotalCounts, if date should be displayed
			$sSelectQuery = "SELECT  count(*) numRecords, sum(counts) counts
							FROM   scompEmailStats
							WHERE  unsubDate BETWEEN '$sDateFrom' AND '$sDateTo'
							AND sender='microsoft'";
			$sSelectQuery .= $sFilterPart;
			
			$rResult = dbQuery($sSelectQuery);
			
			if ($rResult) {
				while ($oTempRow = dbFetchObject($rResult)) {
					$iNumRecords = $oTempRow->numRecords;
					$iGrandTotalCounts = $oTempRow->counts;
				}
			}
		}
		
		$iTotalCounts = 0;
		$iEmailCounts = 0;
		$iTotalPages = ceil($iNumRecords/$iRecPerPage);
		if ($iNumRecords > 0)
		$sCurrentPage = "Page $iPage "."/ $iTotalPages";
		
		
		// Prepare query to fetch the records
		if ($sSupressDate == 'Y') {
			$sSelectQuery = "SELECT email, sum(counts) counts
						FROM scompEmailStats
						WHERE  unsubDate BETWEEN '$sDateFrom' AND '$sDateTo'
						AND sender='microsoft'
						$sFilterPart ";
			$sSelectQuery .= " GROUP BY email";
		} else {
			$sSelectQuery = "SELECT email, unsubDate, counts
							FROM scompEmailStats
							WHERE  unsubDate BETWEEN '$sDateFrom' AND '$sDateTo'
							AND sender='microsoft'
							$sFilterPart ";
		}
		
		if ($sSortPage != 'Y') {
			if ($sOrderColumn != '')
			$sSelectQuery .= " ORDER BY $sOrderColumn $sCurrOrder";
		}
				
		$sSelectQuery .=" LIMIT $iStartRec, $iRecPerPage";

		// start of track users' activity in nibbles
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sSelectQuery\")";
		$rResult = dbQuery($sAddQuery);
		// end of track users' activity in nibbles
		
		$rResult = dbQuery($sSelectQuery);
		
		// Prepare Next/Prev/First/Last links
		if ($iTotalPages > $iPage ) {
			$iNextPage = $iPage+1;
			$sNextPageLink = "<a href='".$sSortLink."&sSortPage=$sSortPage&sSupressDate=$sSupressDate&sOrderColumn=$sOrderColumn&iPage=$iNextPage&sCurrOrder=$sCurrOrder' class=header>Next</a>";
			$sLastPageLink = "<a href='".$sSortLink."&sSortPage=$sSortPage&sSupressDate=$sSupressDate&sOrderColumn=$sOrderColumn&iPage=$iTotalPages&sCurrOrder=$sCurrOrder' class=header>Last</a>";
		}
		if ($iPage != 1) {
			$iPrevPage = $iPage-1;
			$sPrevPageLink = "<a href='".$sSortLink."&sSortPage=$sSortPage&sSupressDate=$sSupressDate&sOrderColumn=$sOrderColumn&iPage=$iPrevPage&sCurrOrder=$sCurrOrder' class=header>Previous</a>";
			$sFirstPageLink = "<a href='".$sSortLink."&sSortPage=$sSortPage&sSupressDate=$sSupressDate&sOrderColumn=$sOrderColumn&iPage=1&sCurrOrder=$sCurrOrder' class=header>First</a>";
		}
		
		if ($iNumRecords > 0) {
			// If to sort all the records of result
			if ($sSortPage != 'Y' )	{
				while ($oRow = dbFetchObject($rResult)) {
					if ($sBgcolorClass == "ODD") {
						$sBgcolorClass = "EVEN";
					} else {
						$sBgcolorClass = "ODD";
					}
					
					$iTotalCounts += $oRow->counts;
					
					$sReportData .= "<tr class=$sBgcolorClass>
									<td>$oRow->email</td>";
					if($sSupressDate != 'Y') {
						$sReportData .= "<td>$oRow->unsubDate</td>";
					}
					$sReportData .= "<td>$oRow->counts</td></tr>";
				}
			} else {
				// Sort records which are displayed in current page only
				$i = 0;
				
				while ($oReportRow = dbFetchObject($rResult)) {
					//	Put the data in Multidimensional array
					$aReportArray['email'][$i] = $oReportRow->email;
					$aReportArray['unsubDate'][$i] = $oReportRow->unsubDate;
					$aReportArray['counts'][$i] = $oReportRow->counts;
					// If no. of records per page reached, break from fetching the rows
					if ( $i >= $iRecPerPage)
					break;
					$i++;
				}
				
				// Sort the Multidimensional array according to order by column
				switch ($sOrderColumn) {
					case "unsubDate":
					array_multisort( $aReportArray["unsubDate"],$aReportArray["email"], $aReportArray["counts"]);
					break;
					case "counts":
					array_multisort($aReportArray["counts"], $aReportArray["email"], $aReportArray["unsubDate"] );
					break;
					default:
					array_multisort( $aReportArray["email"],  $aReportArray["unsubDate"], $aReportArray["counts"] );
				}
				
				// Prepare Report data to display in rows, from Multidimensional Array
				for ($i = 0; $i < $iRecPerPage;	$i++) {
					
					if ($sBgcolorClass == "ODD") {
						$sBgcolorClass = "EVEN";
					} else {
						$sBgcolorClass = "ODD";
					}
					if ($iNumRecords < $i) {
						break;
					}
					$sReportData .= "<tr class=$sBgcolorClass>
								<td>".$aReportArray['email'][$i]."</td>";
					if ($sSupressDate != 'Y') {
						$sReportData .= "<td>".$aReportArray['unsubDate'][$i]."</td>";
					}
					$sReportData .= "<td>".$aReportArray['counts'][$i]."</td></tr>";
					$iTotalCounts += $aReportArray["counts"][$i];
				}
			}
		} else {
			$sMessage = "No Records Exist...";
		}
		// Display Page total
		if ($sBgcolorClass == "ODD") {
			$sBgcolorClass = "EVEN";
		} else {
			$sBgcolorClass = "ODD";
		}
		
		$sReportData .= "<tr class=$sBgcolorClass>";
		if (!($sSupressDate))
		$sReportData .= "<td></td>";
		$sReportData .= "<td><b>Page Total Counts</b></td><td><b>$iTotalCounts</b></td></tr>";
		
		// Display Grand total
		if ($sBgcolorClass == "ODD") {
			$sBgcolorClass = "EVEN";
		} else {
			$sBgcolorClass = "ODD";
		}
		
		$sReportData .= "<tr class=$sBgcolorClass>";
		if (!($sSupressDate))
		$sReportData .= "<td></td>";
		$sReportData .= "<td><b>Grand Total Counts</b></td><td><b>$iGrandTotalCounts</b></td></tr>";
		
		dbFreeResult($rResult);
		
	} else {
		$sMessage = "Please select valid dates...";
	}
	
	// If exactMatch checked
	if ($sExactMatch == 'Y') {
		$sExactMatchChecked = "checked";
	} else {
		$sExactMatchChecked = '';
	}
	
	
	$sHidden =  "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iParentMenuId value='$iParentMenuId'>";
	
	$sAOLComplaintsLink = "<a href='index.php?iMenuId=$iParentMenuId'>Back To AOL Complaints Menu</a>";
	
	// Supress Date/Display Date  link
	if ($sSupressDate == '') {
		$sSupressDateLink = "<a href='$sSortLink&sSortPage=$sSortPage&sSupressDate=Y'>Supress Date</a>";
		$sDateHeader = "<th align=left><a href='$sSortLink&sSortPage=$sSortPage&sOrderColumn=unsubDate&sUnsubDateOrder=$sUnsubDateOrder'>Unsubscribe Date</a></th>";
	} else {
		$sSupressDateLink = "<a href='$sSortLink&sSortPage=$sSortPage'>Display Date</a>";
		$sDateHeader='';
	}
	
	// Sorting in a page link
	if ($sSortPage) {
		$sSortPageLink = "<a href='$sSortLink&iPage=$iPage&sSupressDate=$sSupressDate'>Sort all the records</a>";
	} else {
		$sSortPageLink = "<a href='$sSortLink&iPage=$iPage&sSupressDate=$sSupressDate&sSortPage=Y'>Sort records in a page only</a>";
	}
	
	$sSortLink .= "&sSortPage=$sSortPage&sSupressDate=$sSupressDate";

	include("../../includes/adminHeader.php");			
	
	?>
	
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<table width=95% align=center bgcolor=c9c9c9><tr>
<tr><td><?php echo $sAOLComplaintsLink;?></td></tr>
	<td>Date from</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td>Date to</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>
	<tr><td>Filter By eMail</td><td><input type=text name=sFilter value='<?php echo $sFilter;?>'> &nbsp; 
			<input type=checkbox name=sExactMatch value='Y' <?php echo $sExactMatchChecked;?>> Exact Match</td></tr>
	<tr>
<td><input type=submit name=sSubmit value='View Report'></td><td colspan=2><?php echo $sSupressDateLink;?> &nbsp; <?php echo $sSortPageLink;?></td></tr>
			</table>
			
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=3 align=right class=header><?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>
<tr><th align=left><a href='<?php echo $sSortLink;?>&sOrderColumn=email&sEmailOrder=<?php echo $sEmailOrder;?>'>eMail</a></th>	
<?php echo $sDateHeader;?>
	<th align=left><a href='<?php echo $sSortLink;?>&sOrderColumn=counts&sCountsOrder=<?php echo $sCountsOrder;?>'>Counts</a></th></tr>			
<?php echo $sReportData;?>
<tr><td colspan=3 align=right class=header><?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>
<tr><td><?php echo $sAOLComplaintsLink;?></td></tr>
</table>
</form>			

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>