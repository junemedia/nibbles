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


$sPageTitle = "Leads Per Email Report";

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

	$iMaxDaysToReport = 7;
	$iDefaultDaysToReport = 1;
	$bDateRangeNotOk = false;

	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));

	if (!$sViewReport) {

		$iMonthTo = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 5, 2);
		$iDayTo = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 8, 2);
		$iYearTo = substr( DateAdd( "d", -$iDefaultDaysToReport, date('Y')."-".date('m')."-".date('d') ), 0, 4);

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
		$sOrderColumn = "email";
		$sSessionIdOrder = "ASC";
	}

	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "sourceCode" :
			$sCurrOrder = $sSourceCodeOrder;
			$sSourceCodeOrder = ($sSourceCodeOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "count" :
			$sCurrOrder = $sCountOrder;
			$sCountOrder = ($sCountOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "email" :
			$sCurrOrder = $sEmailOrder;
			$sEmailOrder = ($sEmailOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "subSourceCode" :
			$sCurrOrder = $sSubSourceCodeOrder;
			$sSubSourceCodeOrder = ($sSubSourceCodeOrder != "DESC" ? "DESC" : "ASC");
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
	if (!($iLeadsPerEmail)) {
		$iLeadsPerEmail = 3;	
	}

	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iYearFrom=$iYearFrom&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearTo=$iYearTo&iMonthTo=$iMonthTo&iDayTo=$iDayTo
							&iDbMailId=$iDbMailId&sViewReport=$sViewReport&iRecPerPage=$iRecPerPage&sUserName=$sUserName&sPageName=$sPageName&iLeadsPerEmail=$iLeadsPerEmail&sSourceCode=$sSourceCode
							&sSubSourceCode=$sSubSourceCode&sShowSubSourceCode=$sShowSubSourceCode";

	if ($sViewReport != "") {
		if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo) && !$bDateRangeNotOk) {
			if ($sAllowReport == 'N') {
				$sMessage .= "<br>Server Load Is High. Please check back soon...";
			} else {
				$sTempDelete = "TRUNCATE TABLE tempEmailStats";
				$rTempDelete = dbQuery($sTempDelete);
				echo dbError();
				
				if ($sSourceCode != '') {
					$sSourceCodeFilter = " AND sourceCode = '$sSourceCode'";
				}
				
				if ($sSubSourceCode != '') {
					$sSubSourceCodeFilter = " AND subSourceCode = '$sSubSourceCode'";
				}
				
				if ($sShowSubSourceCode) {
					$sGroupByFilter = "GROUP BY email, sourceCode, subSourceCode";
				} else {
					$sGroupByFilter = "GROUP BY email";
				}

				$sTempQuery = "INSERT INTO tempEmailStats (email,count,sourceCode,subSourceCode)
						SELECT email, count(email) as count, sourceCode, subSourceCode
						FROM otDataHistory
   					 	WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
   					 	$sSourceCodeFilter
   					 	$sSubSourceCodeFilter
   					 	$sGroupByFilter";

				
				// start of track users' activity in nibbles 
				$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

				$nibblesReportWhere = "WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
   					 	$sSourceCodeFilter
   					 	$sSubSourceCodeFilter
   					 	$sGroupByFilter";
				
				$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: Where: $nibblesReportWhere\")"; 
				$rResult = dbQuery($sAddQuery); 
				// end of track users' activity in nibbles		
				
				
				$rTempResult = dbQuery($sTempQuery);
				echo dbError();

				$sDelete = "DELETE FROM tempEmailStats WHERE count < $iLeadsPerEmail";
				$rDeleteResult = dbQuery($sDelete);
				echo dbError();
				
				
				$sReportQuery = "SELECT * FROM tempEmailStats ORDER BY $sOrderColumn $sCurrOrder";
				$rReportResult = dbQuery($sReportQuery);
				echo dbError();
				$iNumRecords = dbNumRows($rReportResult);

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
				$sReportQuery .= " LIMIT $iStartRec, $iRecPerPage";
				$rReportResult = dbQuery($sReportQuery);

				
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
					
					
					if ($sTempEmail) {
						$sDataQuery = "SELECT *
							FROM otDataHistory
	   					 	WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
	   					 	AND email = '$sTempEmail'";
						$rDataResult = dbQuery($sDataQuery);
						$sTempContent = "<tr><td>OfferCode</td><td>RevPerLead</td>
						<td>SourceCode</td><td>SubSourceCode</td><td>PageId</td><td>DateAdded</td>
						<td>ProcessStatus</td><td>ReasonCode</td><td>DateProcessed</td><td>SendStatus</td>
						<td>DateSent</td><td>HowSent</td><td>RealTimeResponse</td><td>UserIp</td><td>ServerIp</td>
						<td>Page2Data</td><td>LeadCounter</td><td>DailyCounter</td><td>ExcludeDataSale</td><td>SessionId</td>";
						while ($oTempRow = dbFetchObject($rDataResult)) {
							$sEmail = $oTempRow->email;
							if ($sBgcolorClass == "ODD") {
								$sBgcolorClass = "EVEN_WHITE";
							} else {
								$sBgcolorClass = "ODD";
							}
							$sTempContent .= "<tr class=$sBgcolorClass>
									<td>$oTempRow->offerCode<br></td>
									<td>$oTempRow->revPerLead<br></td>
									<td>$oTempRow->sourceCode<br></td>
									<td>$oTempRow->subSourceCode<br></td>
									<td>$oTempRow->pageId<br></td>
									<td>$oTempRow->dateTimeAdded<br></td>
									<td>$oTempRow->processStatus<br></td>
									<td>$oTempRow->reasonCode<br></td>
									<td>$oTempRow->dateTimeProcessed<br></td>
									<td>$oTempRow->sendStatus<br></td>
									<td>$oTempRow->dateTimeSent<br></td>
									<td>$oTempRow->howSent<br></td>
									<td>$oTempRow->realTimeResponse<br></td>
									<td>$oTempRow->remoteIp<br></td>
									<td>$oTempRow->serverIp<br></td>
									<td>$oTempRow->page2Data<br></td>
									<td>$oTempRow->leadCounter<br></td>
									<td>$oTempRow->dailyCounter<br></td>
									<td>$oTempRow->excludeDataSale<br></td>
									<td>$oTempRow->sessionId<br></td>
									</tr>";
						}
						
												
						$sDataQuery = "SELECT *
							FROM userDataHistory
	   					 	WHERE email='$sTempEmail'";
						$rDataResult = dbQuery($sDataQuery);
						$sTempContent .= "<tr><td>Salutation</td><td>First</td>
						<td>Last</td><td>Address</td><td>Address2</td><td>City</td>
						<td>State</td><td>Zip</td><td>Phone</td><td>DateTimeAdded</td>
						<td>PostalVerified</td><td>SessionId</td>";
						while ($oTempRow = dbFetchObject($rDataResult)) {
							if ($sBgcolorClass == "ODD") {
								$sBgcolorClass = "EVEN_WHITE";
							} else {
								$sBgcolorClass = "ODD";
							}
							$sTempContent .= "<tr class=$sBgcolorClass>
									<td>$oTempRow->salutation<br></td>
									<td>$oTempRow->first<br></td>
									<td>$oTempRow->last<br></td>
									<td>$oTempRow->address<br></td>
									<td>$oTempRow->address2<br></td>
									<td>$oTempRow->city<br></td>
									<td>$oTempRow->state<br></td>
									<td>$oTempRow->zip<br></td>
									<td>$oTempRow->phoneNo<br></td>
									<td>$oTempRow->dateTimeAdded<br></td>
									<td>$oTempRow->postalVerified<br></td>
									<td>$oTempRow->sessionId<br></td>
									</tr>";
						}
					}

					while ($oReportRow = dbFetchObject($rReportResult)) {
							if ($sBgcolorClass == "ODD") {
								$sBgcolorClass = "EVEN_WHITE";
							} else {
								$sBgcolorClass = "ODD";
							}
							
							if ($sShowSubSourceCode) {
								if ($oReportRow->email == $sTempEmail) {
									$sReportContent .= "<tr class=$sBgcolorClass>
										<td><a href='$sSortLink&sTempEmail=$oReportRow->email'>$oReportRow->email</a></td>
										<td>$oReportRow->count</td>
										<td>$oReportRow->sourceCode</td>
										<td>$oReportRow->subSourceCode</td></tr>$sTempContent";
								} else {
									$sReportContent .= "<tr class=$sBgcolorClass>
										<td><a href='$sSortLink&sTempEmail=$oReportRow->email'>$oReportRow->email</a></td>
										<td>$oReportRow->count</td>
										<td>$oReportRow->sourceCode</td>
										<td>$oReportRow->subSourceCode</td></tr>";
								}
							} else {
								if ($oReportRow->email == $sTempEmail) {
									$sReportContent .= "<tr class=$sBgcolorClass>
										<td><a href='$sSortLink&sTempEmail=$oReportRow->email'>$oReportRow->email</a></td>
										<td>$oReportRow->count</td>
										<td>$oReportRow->sourceCode</td></tr>$sTempContent";
								} else {
									$sReportContent .= "<tr class=$sBgcolorClass>
										<td><a href='$sSortLink&sTempEmail=$oReportRow->email'>$oReportRow->email</a></td>
										<td>$oReportRow->count</td>
										<td>$oReportRow->sourceCode</td></tr>";
								}
							}

							$iTotalCount += $oReportRow->count;
					}
					
					if ($sShowSubSourceCode) {
						$sReportContent .= "<tr><td colspan=4><hr color=#000000></td></tr>
						<tr><td><b>Total: </b></td>
						<td><b>$iTotalCount</b></td>
						<td><b></b></td>
						</tr></tr>";
					} else {
						$sReportContent .= "<tr><td colspan=3><hr color=#000000></td></tr>
						<tr><td><b>Total: </b></td>
						<td><b>$iTotalCount</b></td>
						</tr></tr>";						
					}
			}
		} else {
			$sMessage .= "Date range entered is greater than maximum range ($iMaxDaysToReport days).";
		}
	}

	// Get all sourceCode
	$sSourceCodeQuery = "SELECT distinct sourceCode
				FROM otDataHistory
				WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
				AND sourceCode != ''
      			ORDER BY sourceCode ASC";

	$rSourceCodeResult = dbQuery($sSourceCodeQuery);
	echo dbError();

	while ($oSourceCodeRow = dbFetchObject($rSourceCodeResult)) {
		$sTempSourceCode = $oSourceCodeRow->sourceCode;
		if ($sSourceCode) {
			if ($sTempSourceCode == $sSourceCode) {
				$sSourceCodeSelected = "selected";
			} else {
				$sSourceCodeSelected = "";
			}
		} else {
			if ($sTempSourceCode == $sSourceCode && isset($sSourceCode)) {
				$sSourceCodeSelected = "selected";
			} else {
				$sSourceCodeSelected = "";
			}
		}
		$sSourceCodeOption .= "<option value='".$oSourceCodeRow->sourceCode."' $sSourceCodeSelected>$oSourceCodeRow->sourceCode";
	}
	

	// Get all subSourceCode
	$sSubSourceCodeQuery = "SELECT distinct subSourceCode
				FROM otDataHistory
				WHERE dateTimeAdded BETWEEN '$sDateFrom 00:00:00' AND '$sDateTo 23:59:59'
				AND subSourceCode != ''
      			ORDER BY subSourceCode ASC";

	$rSubSourceCodeResult = dbQuery($sSubSourceCodeQuery);
	echo dbError();

	while ($oSubSourceCodeRow = dbFetchObject($rSubSourceCodeResult)) {
		$sTempSubSourceCode = $oSubSourceCodeRow->subSourceCode;
		if ($sSubSourceCode) {
			if ($sTempSubSourceCode == $sSubSourceCode) {
				$sSubSourceCodeSelected = "selected";
			} else {
				$sSubSourceCodeSelected = "";
			}
		} else {
			if ($sTempSubSourceCode == $sSubSourceCode && isset($sSubSourceCode)) {
				$sSubSourceCodeSelected = "selected";
			} else {
				$sSubSourceCodeSelected = "";
			}
		}
		$sSubSourceCodeOption .= "<option value='".$oSubSourceCodeRow->subSourceCode."' $sSubSourceCodeSelected>$oSubSourceCodeRow->subSourceCode";
	}
	
	if ($sShowSubSourceCode) {
		$sShowSubSourceCodeChecked = "checked";
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
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>	
	
	
<tr><td>Minimum Leads Per Email Address:</td><td>
<input type=text name=iLeadsPerEmail value='<?php echo $iLeadsPerEmail;?>' size=2>
</td></tr>

<tr><td>Source Code:</td><td>
<select name='sSourceCode'>
<option value="">All</option>
<?php echo $sSourceCodeOption ?>
</select>
</td></tr>

<tr><td>Sub Source Code:</td><td>
<select name='sSubSourceCode'>
<option value="">All</option>
<?php echo $sSubSourceCodeOption ?>
</select>
</td></tr>



	<td colspan=2></td>
<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">
&nbsp;&nbsp;<input type="checkbox" value="Y" name="sShowSubSourceCode" <?php echo $sShowSubSourceCodeChecked;?>>&nbsp;Show SubSourceCode
</td>
	<td colspan=2></td>
</tr>
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
	<tr><td colspan=5 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=5 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><?php echo $sDateSentHeader;?>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=email&sEmailOrder=<?php echo $sEmailOrder;?>" class=header>Email</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=count&sCountOrder=<?php echo $sCountOrder;?>" class=header>Count</a></td>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=sourceCode&sSourceCodeOrder=<?php echo $sSourceCodeOrder;?>" class=header>Source Code</a></td>
		<?php if ($sShowSubSourceCode) { ?>
		<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=subSourceCode&sSubSourceCodeOrder=<?php echo $sSubSourceCodeOrder;?>" class=header>Sub Source Code</a></td>
		<?php } ?>
	</tr>

<?php echo $sReportContent;?>

<tr><td colspan=4 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=4 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=4>
		<BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s).<br>
		Clicking on actual email address link will display related otData/userData.<br>
		Today's data not available; only have data from prior day and earlier.<br>
		No more than 7 day range allowed.<br>
		Count:  Number of leads collected with this email address.<br>
		Total: This is the total for current page only, not for the entire report.<br>
		</td></tr>
	<tr><td colspan=4><BR><BR></td></tr>
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
