<?php

session_start();

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$sPageTitle = "Nibbles - Reported Revenue Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

// Check user permission to access this page
if (hasAccessRight($iMenuId) || isAdmin()) {
	$iScriptStartTime = getMicroTime();
	
	if ($sDelete) {
		$sDeleteQuery = "DELETE FROM actualScrubData WHERE  id = $iId";
		$rResult = dbQuery($sDeleteQuery);
		$iId = '';
				
		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sDeleteQuery) . "\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
	}
	

	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');

	$iCurrHH = date('H');
	$iCurrMM = date('i');
	$iCurrSS = date('s');

	$iMaxDaysToReport = 90;
	$iDefaultDaysToReport = 1;
	$bDateRangeNotOk = false;
	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));

	if (!$sViewReport) {
		$iMonthTo = date('m');
		$iDayTo = date('d');
		$iYearTo = date('Y');
		$iYearFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 0, 4);
		$iMonthFrom = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 5, 2);
		$iDayFrom = 1;
		$sViewReport = 'report';
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

	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";

	if ( DateAdd("d", $iMaxDaysToReport, $sDateFrom) < $sDateTo ) {
		$bDateRangeNotOk = true;
	}
	
	
	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "dateAdded";
		$sDateOrder = "DESC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "offerCode" :
			$sCurrOrder = $sOfferCodeOrder;
			$sOfferCodeOrder = ($sOfferCodeOrder != "DESC" ? "DESC" : "ASC");			
			break;
			case "revPerLead" :
			$sCurrOrder = $sRevPerLeadOrder;
			$sRevPerLeadOrder = ($sRevPerLeadOrder != "DESC" ? "DESC" : "ASC");			
			break;
			case "noOfLeads" :
			$sCurrOrder = $sNumLeadOrder;
			$sNumLeadOrder = ($sNumLeadOrder != "DESC" ? "DESC" : "ASC");
			break;		
			case "revenue" :
			$sCurrOrder = $sRevOrder;
			$sRevOrder = ($sRevOrder != "DESC" ? "DESC" : "ASC");
			break;
			default:
			$sCurrOrder = $sDateOrder;
			$sDateOrder = ($sDateOrder != "DESC" ? "DESC" : "ASC");
		}
	}
	
	
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo&sOfferCode=$sOfferCode&iRecPerPage=$iRecPerPage&sViewReport=$sViewReport";
	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 20;
	}
	if (!($iPage)) {
		$iPage = 1;
	}
	
	
	
	if ($sViewReport != '') {
		if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo) && !$bDateRangeNotOk) {
			$sOfferFilter = '';
			$sList = '';
			
			if ($sOfferCode !='') {
				$sOfferFilter = " AND offerCode = '$sOfferCode' ";
			}

			$sSelectQuery = "SELECT * FROM actualScrubData 
							WHERE dateAdded BETWEEN '$sDateFrom' AND '$sDateTo'
							$sOfferFilter
							ORDER BY $sOrderColumn $sCurrOrder";
			$rSelectResult = dbQuery($sSelectQuery);
			$iNumRecords = dbNumRows($rSelectResult);
			
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
			$sSelectQuery .= " LIMIT $iStartRec, $iRecPerPage";
			
		
			$rSelectResult = dbQuery($sSelectQuery);
			echo dbError();
			
			if ($rSelectResult) {
				if (dbNumRows($rSelectResult) > 0) {
					// Prepare Next/Prev/First/Last links
					if ($iTotalPages > $iPage ) {
						$iNextPage = $iPage+1;
						$sNextPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iNextPage&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>Next</a>";
						$sLastPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iTotalPages&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>Last</a>";
					}
					if ($iPage != 1) {
						$iPrevPage = $iPage-1;
						$sPrevPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iPrevPage&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>Previous</a>";
						$sFirstPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=1&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>First</a>";
					}

					while ($oRow = dbFetchObject($rSelectResult)) {
						if ($sBgcolorClass=="ODD") {
							$sBgcolorClass="EVEN";
						} else {
							$sBgcolorClass="ODD";
						}
				
						$sList .= "<tr class=$sBgcolorClass><td>$oRow->offerCode</td>
										<td>$oRow->dateAdded</td>
										<td>$oRow->revPerLead</td>
										<td>$oRow->noOfLeads</td>
										<td>$oRow->revenue</td>
										<td><a href='JavaScript:void(window.open(\"add.php?iMenuId=$iMenuId&id=".$oRow->id."\", \"AddContent\", \"height=400, width=700, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
										&nbsp;&nbsp;&nbsp;<a href='JavaScript:confirmDelete(this,$oRow->id);' >Delete</a></td></tr>";
					}
				}
			}
			
			if (dbNumRows($rSelectResult) == 0) {
				$sMessage = "No Records Exist...";
			}
	
		}
	}
	
	
	
	// Get all offer code
	$rGetOfferCode = mysql_query("SELECT offerCode FROM offers ORDER BY offerCode ASC");
	$sOfferCodeOption = "<option value=''>";
	while ($oOfferRow = mysql_fetch_object($rGetOfferCode)) {
		$sOfferCodeSelected = '';
		if ($oOfferRow->offerCode == $sOfferCode) {
			$sOfferCodeSelected = "selected";
		}
		$sOfferCodeOption .= "<option value='$oOfferRow->offerCode' $sOfferCodeSelected>$oOfferRow->offerCode";
	}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

	$sAddButton ="<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"add.php?iMenuId=$iMenuId\", \"\", \"height=400, width=700, scrollbars=yes, resizable=yes, status=yes\"));'>";
	include("../../includes/adminHeader.php");
	
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);

	// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;
	

	?>
	
<script language=JavaScript>
	function confirmDelete(form1,id)
	{
		if(confirm('Are you sure to delete this record ?'))
		{							
			document.form1.elements['sDelete'].value='Delete';
			document.form1.elements['iId'].value=id;
			document.form1.submit();								
		}
	}						
</script>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<input type=hidden name=sDelete>
<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>

<table cellpadding=6 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td colspan=6 align=left><?php echo $sAddButton;?></td></tr>


<tr><td class=header>Date From</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td class=header>Date To:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>


<tr><td class=header>Offer Code: </td><td>
	<select name='sOfferCode'>
		<?php echo $sOfferCodeOption;?>
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">
	</td>
	<td class=header>
		<input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; &nbsp; 
		Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; 
		<?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; 
		<?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?>
	</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
</tr>

<tr><td colspan="6">&nbsp;</td></tr>
</table>
<table cellpadding=6 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>


<tr><td class=header><a href='<?php echo $sSortLink;?>&sOrderColumn=offerCode&sOfferCodeOrder=<?php echo $sOfferCodeOrder;?>' class=header>Offer Code</a></td>
<td class=header><a href='<?php echo $sSortLink;?>&sOrderColumn=dateAdded&sDateOrder=<?php echo $sDateOrder;?>' class=header>Date</a></td>
<td class=header><a href='<?php echo $sSortLink;?>&sOrderColumn=revPerLead&sRevPerLeadOrder=<?php echo $sRevPerLeadOrder;?>' class=header>Rev Per Lead</a></td>
<td class=header><a href='<?php echo $sSortLink;?>&sOrderColumn=noOfLeads&sNumLeadOrder=<?php echo $sNumLeadOrder;?>' class=header>No of Leads</a></td>
<td class=header><a href='<?php echo $sSortLink;?>&sOrderColumn=revenue&sRevOrder=<?php echo $sRevOrder;?>' class=header>Revenue</a></td>
</tr>

<?php echo $sList;?>


<tr><td colspan="6"><br><br>Notes:<br>
Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s).<br>
- No of Leads:  This is rounded value of No of Leads.<br>

</td>
</tr>


</table>
</form>
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>