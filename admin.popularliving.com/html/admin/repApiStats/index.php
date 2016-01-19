<?php

//total leads posted, total accepted, total rejected, by source code and date - need report
// Report will be API Statistics

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

session_start();

$iScriptStartTime = getMicroTime();
$sPageTitle = "API Statistics Reporting";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');
	
	$iCurrHH = date('H');
	$iCurrMM = date('i');
	$iCurrSS = date('s');
	
	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

	// set curr date values to be selected by default

	if (!($sViewReport)) {
		$iYearFrom = date('Y');
		$iMonthFrom = date('m');
		$iDayFrom = date('d');
		$iMonthTo = $iMonthFrom;
		$iDayTo = $iDayFrom;
		$iYearTo = $iYearFrom;
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
	
	if (checkDate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo,$iYearTo)) {
		 if ($sAllowReport == 'N') {
			$sMessage = "Server Load Is High. Please check back soon...";
		 } else {
			// specify default order column
			if ($sReportBy == 'offerCode') {
				if (!($sOrderColumn)) {
					$sOrderColumn = "offerCode";
					$sOfferCodeOrder = "ASC";
				}
			} else {
				if (!($sOrderColumn)) {
					$sOrderColumn = "sourceCode";
					$sSourceCodeOrder = "ASC";
				}
			}
			
			// specify current order (ASC or DESC) and reverse the order in link of that column's header
			if (!($sCurrOrder)) {
				switch ($sOrderColumn) {
					case "leadsPosted" :
					$sCurrOrder = $sLeadsPostedOrder;
					$sLeadsPostedOrder = ($sLeadsPostedOrder != "DESC" ? "DESC" : "ASC");
					break;
					case "leadsAccepted" :
					$sCurrOrder = $sLeadsAcceptedOrder;
					$sLeadsAcceptedOrder = ($sLeadsAcceptedOrder != "DESC" ? "DESC" : "ASC");
					break;
					case "leadsRejected" :
					$sCurrOrder = $sLeadsRejectedOrder;
					$sLeadsRejectedOrder = ($sLeadsRejectedOrder != "DESC" ? "DESC" : "ASC");
					break;
					case "datePosted" :
					$sCurrOrder = $sDatePostedOrder;
					$sDatePostedOrder = ($sDatePostedOrder != "DESC" ? "DESC" : "ASC");
					break;
					case "leadData" :
					$sCurrOrder = $sLeadDataOrder;
					$sLeadDataOrder = ($sLeadDataOrder != "DESC" ? "DESC" : "ASC");
					break;
					case "links.offerCode" :
					$sCurrOrder = $sCampaignsOfferCodeOrder;
					$sCampaignsOfferCodeOrder = ($sCampaignsOfferCodeOrder != "DESC" ? "DESC" : "ASC");
					break;
					case "offerCode" :
					$sCurrOrder = $sOfferCodeOrder;
					$sOfferCodeOrder = ($sOfferCodeOrder != "DESC" ? "DESC" : "ASC");
					break;
					case "reason" :
					$sCurrOrder = $sReasonOrder;
					$sReasonOrder = ($sReasonOrder != "DESC" ? "DESC" : "ASC");
					break;
					default:
					$sCurrOrder = $sSourceCodeOrder;
					$sSourceCodeOrder = ($sSourceCodeOrder != "DESC" ? "DESC" : "ASC");
				}
			}

			$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iMonthFrom=$iMonthFrom&iDayFrom=$iDayFrom&iYearFrom=$iYearFrom";
			$sSortLink .= "&iMonthTo=$iMonthTo&iDayTo=$iDayTo&iYearTo=$iYearTo&sSourceCode=$sSourceCode";
			$sSortLink .= "&sFilter=$sFilter&sViewReport=View Report&sReportBy=$sReportBy&sOfferCode=$sOfferCode&sShowErrors=$sShowErrors";

			if ($sReportBy == 'offerCode') {
				if ($sOfferCode != '' && $sShowErrors == 'Y') {
					$sSelectQuery = "SELECT *
							 FROM   apiRejectionLog
							 WHERE  datePosted BETWEEN '$sDateFrom' AND '$sDateTo'
							 AND	offerCode = '$sOfferCode'
					 		 ORDER BY $sOrderColumn $sCurrOrder";
				} else {
					$sSelectQuery = "SELECT offerCode, sum(leadsPosted) AS leadsPosted, 
										sum(leadsAccepted) AS leadsAccepted, 
										sum(leadsRejected) AS leadsRejected
								 FROM   apiStatsByOffer
								 WHERE  datePosted BETWEEN '$sDateFrom' AND '$sDateTo'";
					if ($sOfferCode != '') {
						$sSelectQuery .= " AND offerCode = '$sOfferCode'";
					}
					$sSelectQuery .= " GROUP BY offerCode";
					$sSelectQuery .= " ORDER BY $sOrderColumn $sCurrOrder";
				}

				$sTempReportHeader = "<tr><th align=left><a href='$sSortLink&sOrderColumn=offerCode&sOfferCodeOrder=$sOfferCodeOrder'>Offer Code</a></th>";
				$sTempExportHeader = "Offer Code";
				$sNotes = "<br>- Report By Offer Code Not Available (Inaccurate) Before March 25, 2006.";
			} else {
				if ($sSourceCode != '' && $sFilter == 'exactMatchWithError') {
					$sSelectQuery = "SELECT *
							 FROM   apiRejectionLog
							 WHERE  datePosted BETWEEN '$sDateFrom' AND '$sDateTo'
							 AND	sourceCode = '$sSourceCode'
					 		 ORDER BY $sOrderColumn $sCurrOrder";
				} else {
					$sSelectQuery = "SELECT apiStats.sourceCode, links.offerCode, sum(leadsPosted) AS leadsPosted, 
										sum(leadsAccepted) AS leadsAccepted, 
										sum(leadsRejected) AS leadsRejected
								 FROM   apiStats LEFT JOIN links ON apiStats.sourceCode = links.sourceCode
								 WHERE  datePosted BETWEEN '$sDateFrom' AND '$sDateTo'";
					//echo $sSelectQuery;
					if ($sSourceCode != '') {
						if ($sFilter == 'exactMatch') {
							$sSelectQuery .= " AND apiStats.sourceCode = '$sSourceCode'";
						} else if ($sFilter == 'startsWith') {
							$sSelectQuery .= " AND apiStats.sourceCode LIKE '$sSourceCode%'";
						}
					}
	
					$sSelectQuery .= " GROUP BY apiStats.sourceCode";
					$sSelectQuery .= " ORDER BY $sOrderColumn $sCurrOrder";
	
					if (!($sSourceCode != '' && $sFilter == 'exactMatch')) {
						$sSelectQuery .= ", datePosted";
					}
				}
				$sTempReportHeader = "<tr><th align=left><a href='$sSortLink&sOrderColumn=sourceCode&sSourceCodeOrder=$sSourceCodeOrder'>Source Code</a></th><th align=left><a href='$sSortLink&sOrderColumn=links.offerCode&sCampaignsOfferCodeOrder=$sCampaignsOfferCodeOrder'>Offer Code</a></th>";
				$sTempExportHeader = "Source Code\tOfferCode";
			}
			
			
			
			// start of track users' activity in nibbles - mcs
			mysql_connect ($host, $user, $pass);
			mysql_select_db ($dbase);
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
					VALUES('$sTrackingUser', '$PHP_SELF', now(), \"$sSelectQuery\")";
			$rResult = dbQuery($sAddQuery);
			mysql_connect ($reportingHost, $reportingUser, $reportingPass);
			mysql_select_db ($reportingDbase);
			// end of track users' activity in nibbles
			
			$rResult = dbQuery($sSelectQuery);
			echo  dbError();
			$iTotalLeadsPosted = 0;
			$iTotalLeadsAccepted = 0;
			$iTotalLeadsRejected = 0;
			
			if ($rResult) {
				if (dbNumRows($rResult) > 0) {
					while ($oRow = dbFetchObject($rResult)) {
						if ($sReportBy == 'offerCode') {
							$sTempRow = $oRow->offerCode;
						} else {
							$sTempRow = $oRow->sourceCode."</td><td>".$oRow->offerCode;
						}
						
						if ($sBgcolorClass == "ODD") {
							$sBgcolorClass = "EVEN_WHITE";
						} else {
							$sBgcolorClass = "ODD";
						}
						
						$iTotalLeadsPosted += $oRow->leadsPosted;
						$iTotalLeadsAccepted += $oRow->leadsAccepted;
						$iTotalLeadsRejected += $oRow->leadsRejected;
						
						if (($sSourceCode != '' && $sFilter == 'exactMatchWithError') || ($sOfferCode != '' && $sShowErrors == 'Y')) {
							$sReportData .= "<tr class=$sBgcolorClass><td nowrap>$sTempRow</td>
											  <td align=right nowrap>$oRow->datePosted</td>
											  <td >".nl2br($oRow->leadData)."</td>
											  <td >".nl2br($oRow->reason)."</td></tr>";
							$sExportData .= $sTempRow."\t$oRow->datePosted\t".nl2br($oRow->leadData)."\t";
							$sExportData .= nl2br($oRow->reason)."\n";
						} else {
							$sReportData .= "<tr class=$sBgcolorClass><td>$sTempRow</td>
											  <td align=right>$oRow->leadsPosted</td>
											  <td align=right>$oRow->leadsAccepted</td>
											  <td align=right>$oRow->leadsRejected</td></tr>";
							$sExportData .= "$sTempRow\t$oRow->leadsPosted\t$oRow->leadsAccepted\t$oRow->leadsRejected\n";
						}
					}

					if (($sSourceCode != '' && $sFilter == 'exactMatchWithError') || ($sOfferCode != '' && $sShowErrors == 'Y')) {
						$sReportHeader = "$sTempReportHeader
											<th align=left><a href='$sSortLink&sOrderColumn=datePosted&sDatePostedOrder=$sDatePostedOrder'>Date Posted</a></th>
											<th align=left><a href='$sSortLink&sOrderColumn=leadData&sLeadDataOrder=$sLeadDataOrder'>Lead Data</a></th>
											<th align=left><a href='$sSortLink&sOrderColumn=reason&sReasonOrder=$sReasonOrder'>Reason</a></th>
										</tr>";
						$sReportData = $sReportHeader . $sReportData;
						$sExportData = "$sTempExportHeader\tDate Posted\tLead Data\tReason\n\n".$sExportData;
					} else {
						$sReportHeader = "$sTempReportHeader
											<th align=right><a href='$sSortLink&sOrderColumn=leadsPosted&sLeadsPostedOrder=$sLeadsPostedOrder'>Leads Posted</a></th>
											<th align=right><a href='$sSortLink&sOrderColumn=leadsAccepted&sLeadsAcceptedOrder=$sLeadsAcceptedOrder'>Leads Accepted</a></th>
											<th align=right><a href='$sSortLink&sOrderColumn=leadsRejected&sLeadsRejectedOrder=$sLeadsRejectedOrder'>Leads Rejected</a></th>
										</tr>";
						$sReportData .= "<tr><td colspan=5 align=left><hr color=#000000></td></tr>	
											<tr><td class=header>Total Counts</td>".
											($sReportBy == 'sourceCode' ? '<td></td>' : '').
												  "<td align=right class=header>$iTotalLeadsPosted</td>
												  <td align=right class=header>$iTotalLeadsAccepted</td>
												  <td align=right class=header>$iTotalLeadsRejected</td></tr>
											<tr><td colspan=5 align=left><hr color=#000000></td></tr>";
						$sReportData = $sReportHeader . $sReportData;
						$sExportData = "$sTempExportHeader\tLeads Posted\tLeads Accepted\tLeads Rejected\n\n".$sExportData;
						$sExportData .= "\nTotal Counts\t$iTotalLeadsPosted\t$iTotalLeadsAccepted\t$iTotalLeadsRejected";
					}
				} else {
					$sMessage = "No Records Exist...";
				}
			}
		}
	}
	
	if ($sExportExcel) {
		$sFileName = "apiStats_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";
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

	if ($sFilter == 'startsWith') {
		$sStartsWithChecked = "checked";
	} else if ($sFilter == 'exactMatch') {
		$sExactMatchChecked = "checked";
	} else if ($sFilter == 'exactMatchWithError') {
		$sExactMatchWithErrorChecked = "checked";
	}

	if ($sExportExcel) { $sExportExcelChecked = "checked"; }
	if ($sShowErrors) { $sShowErrorsChecked = "checked"; }
	
	$sReportByOfferChecked = '';
	$sReportBySrcChecked = '';
	$sSrcDisable = '';
	$sOfferDisable = '';
	if ($sReportBy == 'offerCode') {
		$sReportByOfferChecked = ' selected ';
		$sSrcDisable = ' disabled ';
	} else {
		$sReportBySrcChecked = ' selected ';
		$sOfferDisable = ' disabled ';
	}
	

	$sOfferCodeQuery = "SELECT offerCode FROM offers ORDER BY offerCode ASC";
	$rOfferCodeQuery = dbQuery($sOfferCodeQuery);
	while ($oReportRow = dbFetchObject($rOfferCodeQuery)) {
		if ($sOfferCode) {
			if ($oReportRow->offerCode == $sOfferCode) {
				$sOfferCodeSelected = "selected";
			} else {
				$sOfferCodeSelected = "";
			}
		} else {
			if ($oReportRow->offerCode == $sOfferCode && isset($sOfferCode)) {
				$sOfferCodeSelected = "selected";
			} else {
				$sOfferCodeSelected = "";
			}
		}
		$sOfferCodeOptions .= "<option value='$oReportRow->offerCode' $sOfferCodeSelected>$oReportRow->offerCode";
	}
	
	
	

	// Hidden variable to be passed with form submit
	$sHidden =  "<input type=hidden name=iMenuId value='$iMenuId'>";
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);

	include("../../includes/adminHeader.php");
	// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;
?>

<script language=JavaScript>
function funcRecPerPage(form1) {
	document.form1.submit();
}

function enableDisable(val) {
	if (document.form1.sReportBy.value=='sourceCode') {
		document.form1.sOfferCode.disabled=true;
		document.form1.sShowErrors.disabled=true;
		document.form1.sSourceCode.disabled=false;
		document.form1.sFilter[0].disabled=false;
		document.form1.sFilter[1].disabled=false;
		document.form1.sFilter[2].disabled=false;
	} else {
		document.form1.sOfferCode.disabled=false;
		document.form1.sShowErrors.disabled=false;
		document.form1.sSourceCode.disabled=true;
		document.form1.sFilter[0].disabled=true;
		document.form1.sFilter[1].disabled=true;
		document.form1.sFilter[2].disabled=true;
	}
}

</script>				
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>

<table width=95% align=center bgcolor=c9c9c9><tr>
<tr><td><?php echo $sCampaignsLink;?></td></tr>
	<td>Date from</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td>Date to</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>	
	<tr><td>Source Code</td><td colspan=3><input type=text name=sSourceCode value='<?php echo $sSourceCode;?>' <?php echo $sSrcDisable; ?>>
	<input type='radio' name='sFilter' value='startsWith' <?php echo $sStartsWithChecked;?> <?php echo $sSrcDisable; ?>> Starts With
		&nbsp; <input type='radio' name='sFilter' value='exactMatch' <?php echo $sExactMatchChecked;?> <?php echo $sSrcDisable; ?>> Exact Match
		&nbsp; <input type='radio' name='sFilter' value='exactMatchWithError' <?php echo $sExactMatchWithErrorChecked;?> <?php echo $sSrcDisable; ?>> Exact Match With Error List
		</td>
		</tr>
		
	<tr><td>Report By:</td>
		<td>
			<select name="sReportBy" onclick="enableDisable(this);">
			<option value="sourceCode" <?php echo $sReportBySrcChecked; ?>>Source Code</option>
			<option value="offerCode" <?php echo $sReportByOfferChecked; ?>>Offer Code</option>
			</select>
		</td>
	</tr>
	
	
	<tr><td>Offer Code</td><td><select name=sOfferCode <?php echo $sOfferDisable; ?>>
	<option value='' selected>All</option><?php echo $sOfferCodeOptions;?></select>
	&nbsp; &nbsp; <input type=checkbox name=sShowErrors value="Y" <?php echo $sShowErrorsChecked;?> <?php echo $sOfferDisable; ?>> Show Errors
	</td></tr>
	

<tr>
<td colspan=2><br><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">
&nbsp; &nbsp; <input type=checkbox name=sExportExcel value="Y" <?php echo $sExportExcelChecked;?>> Export To Excel
</td></tr>
			</table>
	
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=5 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=5 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	
<?php echo $sReportData;?>

<tr><td colspan=5 class=header><BR>Notes -</td></tr>
	<tr><td colspan=5>- Updated in real time.
		<?php echo $sNotes; ?>
		<BR>- Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=5><BR><BR></td></tr>
	
</table></td></tr></table></td></tr></table></td></tr></table>
</form>			

<?php
	
include("../../includes/adminFooter.php");

} else {
	echo "You are not authorized to access this page...";
}

?>
