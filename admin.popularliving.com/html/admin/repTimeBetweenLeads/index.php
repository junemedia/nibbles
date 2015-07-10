<?php

/*********

Script to Display 

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Time Between Leads Received";

session_start();

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);


if (hasAccessRight($iMenuId) || isAdmin()) {

// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";	

		
$iCurrYear = date('Y');
$iCurrMonth = date('m');
$iCurrDay = date('d');

$iCurrHH = date('H');
$iCurrMM = date('i');
$iCurrSS = date('s');

$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";

if (!($sGetReport)) {
	$iYearFrom = $iCurrYear;
	$iMonthFrom = $iCurrMonth;
	$iDayFrom = $iCurrDay;
	
	$iYearTo = $iYearFrom;
	$iMonthTo = $iMonthFrom;
	$iDayTo = $iDayFrom;
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
		
	
if (checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo)) {
	
	if ($sAllowReport == 'N') {
			$sMessage = "Server Load Is High. Please check back soon...";
	} else {
		
	$sDateFrom = "$iYearFrom-$iMonthFrom-$iDayFrom";
	$sDateTo = "$iYearTo-$iMonthTo-$iDayTo";
		
	$sDateTimeFrom = $sDateFrom." 00:00:00";
	$sDateTimeTo = $sDateTo." 23:59:59";
	
	for ($i = 0; $i <= 25; $i++) {
		$aTimeBetweenLeadsArray[$i]=0;
	}
		
					
	$sDateTimeQuery = "SELECT id, dateTimeAdded
					   FROM otData
					   WHERE dateTimeAdded BETWEEN '$sDateTimeFrom'
      				   AND '$sDateTimeTo'
					   ORDER BY dateTimeAdded DESC";
		
	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	mysql_connect ($host, $user, $pass); 
	mysql_select_db ($dbase); 

	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sDateTimeQuery\")"; 
	$rResult = dbQuery($sAddQuery); 
	echo  dbError(); 
	mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
	mysql_select_db ($reportingDbase); 
	// end of track users' activity in nibbles		
	
	$rDateTimeResult = dbQuery($sDateTimeQuery);
	echo dbError();
	
		
	while ($oDateTimeRow = dbFetchObject($rDateTimeResult)) {
		
		$iId = $oDateTimeRow->id - 1;
		
		$sDateTimeAdded = $oDateTimeRow->dateTimeAdded;
		
		$sTempDateTimeQuery = "SELECT id, dateTimeAdded
							   FROM   otData
							   WHERE  dateTimeAdded BETWEEN '$sDateTimeFrom'
      				   				  AND '$sDateTimeTo'
							   AND    id = '$iId'";
				
		$rTempDateTimeResult = dbQuery($sTempDateTimeQuery);
		echo dbError();
		if ($rDateTimeResult) {
		while ($oTempDateTimeRow = dbFetchObject($rTempDateTimeResult)) {
			$iTempId = $oTempDateTimeRow->id;
			$sTempDateTimeAdded = $oTempDateTimeRow->dateTimeAdded;
		}
		
		if ($iId && $iTempId) {
			$sDateTimeAddedSeconds = mktime(substr($sDateTimeAdded,11,2),substr($sDateTimeAdded,14,2),substr($sDateTimeAdded,17,2),substr($sDateTimeAdded,5,2), substr($sDateTimeAdded,8,2),substr($sDateTimeAdded,0,4));
			$sTempDateTimeAddedSeconds = mktime(substr($sTempDateTimeAdded,11,2),substr($sTempDateTimeAdded,14,2),substr($sTempDateTimeAdded,17,2),substr($sTempDateTimeAdded,5,2), substr($sTempDateTimeAdded,8,2),substr($sTempDateTimeAdded,0,4));
			$iTimeDiff =  round(($sDateTimeAddedSeconds - $sTempDateTimeAddedSeconds) / 60);
			
			//echo "<BR>$sDateTimeAdded $sTempDateTimeAdded ".$iTimeDiff;	
			
			switch ($iTimeDiff) {
				case "0":
				$aTimeBetweenLeadsArray[0]++;
				break;
				case "1":
				$aTimeBetweenLeadsArray[1]++;
				break;
				case "2":
				$aTimeBetweenLeadsArray[2]++;
				break;
				case "3":
				$aTimeBetweenLeadsArray[3]++;
				break;
				case "4":
				$aTimeBetweenLeadsArray[4]++;
				break;
				case "5":
				$aTimeBetweenLeadsArray[5]++;
				break;
				case "6":
				$aTimeBetweenLeadsArray[6]++;
				break;
				case "7":
				$aTimeBetweenLeadsArray[7]++;
				break;
				case "8":
				$aTimeBetweenLeadsArray[8]++;
				break;
				case "9":
				$aTimeBetweenLeadsArray[9]++;
				break;
				case "10":
				$aTimeBetweenLeadsArray[10]++;
				break;
				case "11":
				$aTimeBetweenLeadsArray[11]++;
				break;
				case "12":
				$aTimeBetweenLeadsArray[12]++;
				break;
				case "13":
				$aTimeBetweenLeadsArray[13]++;
				break;
				case "14":
				$aTimeBetweenLeadsArray[14]++;
				break;
				case "15":
				$aTimeBetweenLeadsArray[15]++;
				break;
				case "16":
				$aTimeBetweenLeadsArray[16]++;
				break;
				case "17":
				$aTimeBetweenLeadsArray[17]++;
				break;
				case "18":
				$aTimeBetweenLeadsArray[18]++;
				break;
				case "19":
				$aTimeBetweenLeadsArray[19]++;
				break;
				case "20":
				$aTimeBetweenLeadsArray[20]++;
				break;
				case "21":
				$aTimeBetweenLeadsArray[21]++;
				break;
				case "22":
				$aTimeBetweenLeadsArray[22]++;
				break;
				case "23":
				$aTimeBetweenLeadsArray[23]++;
				break;
				case "24":
				$aTimeBetweenLeadsArray[24]++;
				break;
				case "25":
				$aTimeBetweenLeadsArray[25]++;
				break;
				default:
				$aTimeBetweenLeadsArray[26]++;
			}			
		}
	
		}
		
		}
	
		
		// get count from history table if date range falls into history
		
		$sDateTimeQuery = "SELECT id, dateTimeAdded
					   FROM otDataHistory
					   WHERE dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
					   ORDER BY dateTimeAdded DESC";
		
	$rDateTimeResult = dbQuery($sDateTimeQuery);
	echo dbError();
	
	if ($rDateTimeResult) {	
	while ($oDateTimeRow = dbFetchObject($rDateTimeResult)) {
		
		$iId = $oDateTimeRow->id - 1;
		
		$sDateTimeAdded = $oDateTimeRow->dateTimeAdded;
		
		$sTempDateTimeQuery = "SELECT id, dateTimeAdded
							   FROM   otDataHistory
							   WHERE  dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
							   AND    id = '$iId'";
				
		$rTempDateTimeResult = dbQuery($sTempDateTimeQuery);
		echo dbError();
		while ($oTempDateTimeRow = dbFetchObject($rTempDateTimeResult)) {
			$iTempId = $oTempDateTimeRow->id;
			$sTempDateTimeAdded = $oTempDateTimeRow->dateTimeAdded;
		}
		
		if ($iId && $iTempId) {
			$sDateTimeAddedSeconds = mktime(substr($sDateTimeAdded,11,2),substr($sDateTimeAdded,14,2),substr($sDateTimeAdded,17,2),substr($sDateTimeAdded,5,2), substr($sDateTimeAdded,8,2),substr($sDateTimeAdded,0,4));
			$sTempDateTimeAddedSeconds = mktime(substr($sTempDateTimeAdded,11,2),substr($sTempDateTimeAdded,14,2),substr($sTempDateTimeAdded,17,2),substr($sTempDateTimeAdded,5,2), substr($sTempDateTimeAdded,8,2),substr($sTempDateTimeAdded,0,4));
			$iTimeDiff =  round(($sDateTimeAddedSeconds - $sTempDateTimeAddedSeconds) / 60);
			
			//echo "<BR>$sDateTimeAdded $sTempDateTimeAdded ".$iTimeDiff;	
			
			switch ($iTimeDiff) {
				case "0":
				$aTimeBetweenLeadsArray[0]++;
				break;
				case "1":
				$aTimeBetweenLeadsArray[1]++;
				break;
				case "2":
				$aTimeBetweenLeadsArray[2]++;
				break;
				case "3":
				$aTimeBetweenLeadsArray[3]++;
				break;
				case "4":
				$aTimeBetweenLeadsArray[4]++;
				break;
				case "5":
				$aTimeBetweenLeadsArray[5]++;
				break;
				case "6":
				$aTimeBetweenLeadsArray[6]++;
				break;
				case "7":
				$aTimeBetweenLeadsArray[7]++;
				break;
				case "8":
				$aTimeBetweenLeadsArray[8]++;
				break;
				case "9":
				$aTimeBetweenLeadsArray[9]++;
				break;
				case "10":
				$aTimeBetweenLeadsArray[10]++;
				break;
				case "11":
				$aTimeBetweenLeadsArray[11]++;
				break;
				case "12":
				$aTimeBetweenLeadsArray[12]++;
				break;
				case "13":
				$aTimeBetweenLeadsArray[13]++;
				break;
				case "14":
				$aTimeBetweenLeadsArray[14]++;
				break;
				case "15":
				$aTimeBetweenLeadsArray[15]++;
				break;
				case "16":
				$aTimeBetweenLeadsArray[16]++;
				break;
				case "17":
				$aTimeBetweenLeadsArray[17]++;
				break;
				case "18":
				$aTimeBetweenLeadsArray[18]++;
				break;
				case "19":
				$aTimeBetweenLeadsArray[19]++;
				break;
				case "20":
				$aTimeBetweenLeadsArray[20]++;
				break;
				case "21":
				$aTimeBetweenLeadsArray[21]++;
				break;
				case "22":
				$aTimeBetweenLeadsArray[22]++;
				break;
				case "23":
				$aTimeBetweenLeadsArray[23]++;
				break;
				case "24":
				$aTimeBetweenLeadsArray[24]++;
				break;
				case "25":
				$aTimeBetweenLeadsArray[25]++;
				break;
				default:
				$aTimeBetweenLeadsArray[26]++;
			}			
		}
	
		}	
		
		}
	
		
		// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "interval";
		$sIntervalOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
		switch ($sOrderColumn) {
			case "count" :
			$sCurrOrder = $sCountOrder;
			$sCountOrder = ($sCountOrder != "DESC" ? "DESC" : "ASC");
			break;			
			default:
			$sCurrOrder = $sIntervalOrder;
			$sIntervalOrder = ($sIntervalOrder != "DESC" ? "DESC" : "ASC");
		}
		
	}
			
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sSourceCode=$sSourceCode&sFilter=$sFilter&sPostalVerified=$sPostalVerified&
					sExcludeNonRevenue=$sExcludeNonRevenue&sDateFrom=$sDateFrom&sDateTo=$sDateTo&iRecPerPage=$iRecPerPage";

		$iTotalCount = 0;
	
		for ($i = 0; $i < count($aTimeBetweenLeadsArray); $i++) {
			if ($i == 26) {
				$sReportContent	.= "<tr><td>More Than 25</td><td>".$aTimeBetweenLeadsArray[$i]."</td></tr>";
				$sExpReportContent .= "More Than 25\t".$aTimeBetweenLeadsArray[$i]."\n";
			} else {
				$sReportContent	.= "<tr><td>$i</td><td>".$aTimeBetweenLeadsArray[$i]."</td></tr>";
				$sExpReportContent .= "$i\t".$aTimeBetweenLeadsArray[$i]."\n";
			}
			$iTotalCount += $aTimeBetweenLeadsArray[$i];
		}
		$sReportContent .= "<tr><td class=header>Total Lead Count</td><td class=header>$iTotalCount</td></tr>";
		$sExpReportContent .= "Total Lead Count\t$iTotalCount\n";
			
} // end of allow report condition
} // end of check date	
	

if ($sExportExcel) {
		
		$sExpReportContent = "Interval Between Leads (In Minutes)\tCount"."\n".$sExpReportContent;
		$sExpReportContent .= "\n\nReport From $iMonthFrom-$iDayFrom-$iYearFrom To $iMonthTo-$iDayTo-$iYearTo";
		$sExpReportContent .= "\nRun Date/Time $sRunDateAndTime";
		
		$sFileName = "timeBetweenLeads_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";

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
	
// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;
?>
<script language=JavaScript>
function funcRecPerPage(form1) {
					document.form1.elements['sAdd'].value='';
					document.form1.submit();
				}					
</script>
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>


<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center >

<tr><td>Date From</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td><td>Date To</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?>
	</select></td></tr>		
	<tr><td></td><td colspan=3><input type=button name=sSubmit value='Get Report' onClick="funcReportClicked('report');">
&nbsp; &nbsp; <input type=checkbox name=sExportExcel value="Y" <?php echo $sExportExcelChecked;?>> Export To Excel</td></tr>
</table>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>
<table cellpadding=0 cellspacing=0 bgcolor=c9c9c9 width=80% align=center border=1 bordercolor=#000000>
		<tr><td>
		<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=100% align=center>
	<tr><td>
	<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=80% align=center>
	<tr><td><!--<a href='<php echo $sSortLink;?>&sOrderColumn=interval&sIntervalOrder=<php echo $sIntervalOrder;?>' class=header>-->Interval Between Leads<BR>(In Minutes)<!--</a>--></td>
		<td><!--<a href='<php echo $sSortLink;?>&sOrderColumn=count&sCountOrder=<php echo $sCountOrder;?>' class=header>-->Count<!--</a>--></td>
	</tr>
	<?php echo $sReportContent;?>
			
</table></td></tr></table></td></tr>
	</table>


</td></tr>
</table>

<table cellpadding=5 cellspacing=0 bgcolor=#FFFFFF width=95% align=center>
	
<tr><td class=header>Notes:</td></tr>
<tr><td>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
</table>


</form>

<?php

	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>