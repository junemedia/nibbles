<?php

/*********

Script to Display

**********/


include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Partner Pixel Displays";

session_start();

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
	
if ($sViewReport) {
	
	if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iMonthTo,$iDayTo,$iYearTo)) >= 0 || $iYearTo=='') {
			$iYearTo = $iCurrYear;
			$iMonthTo = $iCurrMonth;
			$iDayTo = $iCurrDay;;
		}
		
		if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iMonthFrom,$iDayFrom,$iYearFrom)) >= 0 || $iYearFrom=='') {
			$iYearFrom = $iCurrYear;
			$iMonthFrom = $iCurrMonth;
			$iDayFrom = "01";
		}
		
} else  {
	$iYearFrom = $iCurrYear;
	$iMonthFrom = $iCurrMonth;
	$iDayFrom = "01";
		
	$iMonthTo = $iMonthFrom;
	$iDayTo = $iCurrDay;
	$iYearTo = $iYearFrom;
	
}

	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {

		$value = $i+1;

		if ($value < 10) {
			$value = "0".$value;
		}
		
		if ($value == $iMonthFrom) {
			$fromSel = "selected";
		} else {
			$fromSel = "";
		}
		if ($value == $iMonthTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		
		$sMonthFromOptions .= "<option value='$value' $fromSel>$aGblMonthsArray[$i]";
		$sMonthToOptions .= "<option value='$value' $toSel>$aGblMonthsArray[$i]";
	}
	
	// prepare day options for From and To date
	for ($i = 1; $i <= 31; $i++) {
		
		if ($i < 10) {
			$value = "0".$i;
		} else {
			$value = $i;
		}
		
		if ($value == $iDayFrom) {
			$fromSel = "selected";
		} else {
			$fromSel = "";
		}
		if ($value == $iDayTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		$sDayFromOptions .= "<option value='$value' $fromSel>$i";
		$sDayToOptions .= "<option value='$value' $toSel>$i";
	}
	
	// prepare year options
	for ($i = $iCurrYear; $i >= $iCurrYear-5; $i--) {
		
		if ($i == $iYearFrom) {
			$fromSel = "selected";
		} else {
			$fromSel ="";
		}
		if ($i == $iYearTo) {
			$toSel = "selected";
		} else {
			$toSel ="";
		}
		
		$sYearFromOptions .= "<option value='$i' $fromSel>$i";
		$sYearToOptions .= "<option value='$i' $toSel>$i";
	}
	
	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "sourceCode";
		$sSourceCodeOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			/*case "openDate" :
			$sCurrOrder = $sOpenDateOrder;
			$sOpenDateOrder = ($sOpenDateOrder != "DESC" ? "DESC" : "ASC");			
			break;	*/
			case "displayCount" :
			$sCurrOrder = $sDisplayCountOrder;
			$sDisplayCountOrder = ($sDisplayCountOrder != "DESC" ? "DESC" : "ASC");			
			break;			
			default:
			$sCurrOrder = $sSourceCodeOrder;
			$sSourceCodeOrder = ($sSourceCodeOrder != "DESC" ? "DESC" : "ASC");
		}
	}

	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";	
		
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sDateFrom=$sDateFrom&sDateTo=$sDateTo&sViewReport=$sViewReport";

	if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {

	if ($sAllowReport == 'N') {
		$sMessage = "Server Load Is High. Please check back soon...";
	} else {
	
		$sPixelQuery = "SELECT sourceCode, sum(opens) displayCount
						FROM   partnerPixelTracking
						WHERE  openDate BETWEEN '$sDateFrom' AND '$sDateTo'
						GROUP BY sourceCode
						ORDER BY $sOrderColumn $sCurrOrder";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
		mysql_connect ($host, $user, $pass); 
		mysql_select_db ($dbase); 

		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sPixelQuery\")"; 
		$rResult = dbQuery($sAddQuery); 
		echo  dbError(); 
		mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
		mysql_select_db ($reportingDbase); 
		// end of track users' activity in nibbles		



		
		$rPixelResult = dbQuery( $sPixelQuery );
		echo dbError();
		
		while ( $oPixelRow = dbFetchObject($rPixelResult)) {
			
			if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN_WHITE";
			} else {
				$sBgcolorClass = "ODD";
			}
		
			$sReportContent .= "<tr class=$sBgcolorClass><td>$oPixelRow->sourceCode</td>
								<td align=right>$oPixelRow->displayCount</td>
							</tr>";
			
			$sExpReportContent .= "$oPixelRow->sourceCode\t$oPixelRow->displayCount\n";
		}
	}
	}

	if ($sShowQueries == 'Y') {

		$sQueries .= "<tr><td colspan=2><b>Queries Used To Prepare This Report:</b>";
		$sQueries .= "<BR><BR><b>Report Query To Get Report Data:</b><BR>".$sPixelQuery;
		$sQueries .= "</td></tr><tr><td colspan=2><BR><BR></td></tr>";
		
		$sShowQueriesChecked = "checked";
	}
		
	if ($sExportExcel) {
		
		$sExpReportContent = "Source Code\tOpens"."\n".$sExpReportContent;
		$sExpReportContent .= "\n\nReport From $iMonthFrom-$iDayFrom-$iYearFrom To $iMonthTo-$iDayTo-$iYearTo";
		$sExpReportContent .= "\nRun Date/Time $sRunDateAndTime";
		
		$sFileName = "partnerPixels_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";

		$rFpFile = fopen("$sGblWebRoot/temp/$sFileName", "w");
		if ($rFpFile) {
			fputs($rFpFile, $sExpReportContent, strlen($sExpReportContent));
			fclose($rFpFile);

			echo "<script language=JavaScript>
				void(window.open(\"$sGblSiteRoot/download.php?sFile=$sFileName\",\"\",\"height=150, width=300, scrollbars=yes, resizable=yes, status=yes\"));
			  </script>";
		} else {
			$sMessage = "Error exporting data...";
		}
	
	}
	
	
	if ($sExportExcel) {
		$sExportExcelChecked = "checked";
	}
	
	include("../../includes/adminHeader.php");
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);

	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";	

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
	
<tr><td colspan=4><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">
	&nbsp; &nbsp; <input type=checkbox name=sExportExcel value="Y" <?php echo $sExportExcelChecked;?>> Export To Excel
	&nbsp; &nbsp; &nbsp; &nbsp; <input type=checkbox name=sShowQueries value='Y' <?php echo $sShowQueriesChecked;?>> Show Queries</td>
</tr>
</table>


<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td colspan=2 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR>From <?php echo "$sDateFrom to $sDateTo";?><BR><BR><BR></td></tr>
	<tr><td colspan=2 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	<tr><td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=sourceCode&sSourceCodeOrder=<?php echo $sSourceCodeOrder;?>" class=header>Source Code</a></td>
		<td class=header align=right><a href="<?php echo $sSortLink;?>&sOrderColumn=displayCount&sDisplayCountOrder=<?php echo $sDisplayCountOrder;?>" class=header>Opens</a></td>		
	</tr>

<?php echo $sReportContent;?>

<tr><td colspan=2 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=2 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=2><BR>To place a pixel on a partner's page use the following tag:
					<BR><b><?php echo htmlentities('<img src="http://www.popularliving.com/pixel.php?src=SOURCE_CODE" width="1" height="1">');?></b>
					<BR><BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
	<tr><td colspan=2><BR><BR></td></tr>
		<?php echo $sQueries;?>
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