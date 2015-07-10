<?php
/*********

Script to Display offers approaching to inactive date

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();

$sPageTitle = "Offers Approaching Inactive Date";

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


if (!($iInactiveDays)) {
	$iInactiveDays = "7";
}

// Set Default order column
		if (!($sOrderColumn)) {
		
			$sOrderColumn = "offerCode";
			$sOfferCodeOrder = "ASC";
		
		}
		
if (!($sCurrOrder)) {
	switch ($sOrderColumn) {
		case "inactiveDate":
			$sCurrOrder = $sInactiveDateOrder;
			$sInactiveDateOrder = ($sInactiveDateOrder != "DESC" ? "DESC" : "ASC");
			break;
		default:		
			$sCurrOrder = $sOfferCodeOrder;
			$sOfferCodeOrder = ($sOfferCodeOrder != "DESC" ? "DESC" : "ASC");
			break;
				
		}
}

$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iInactiveDays=$iInactiveDays&sViewReport=$sViewReport";
	
// get the offers having inactive date withing a week
$sOffersQuery = "SELECT offerCode, date_format(inactiveDateTime, '%Y-%m-%d %h:%i') as inactiveDate
				 FROM   offers
				 WHERE  date_format(inactiveDateTime,'%Y-%m-%d') BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL $iInactiveDays DAY)
			 	 AND    mode = 'A' 
				 AND	isLive = '1' 
				 ORDER BY $sOrderColumn $sCurrOrder";


// start of track users' activity in nibbles 
$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
mysql_connect ($host, $user, $pass); 
mysql_select_db ($dbase); 

$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sOffersQuery\")"; 
$rResult = dbQuery($sAddQuery); 
echo  dbError(); 
mysql_connect ($reportingHost, $reportingUser, $reportingPass); 
mysql_select_db ($reportingDbase); 
// end of track users' activity in nibbles	

$rOffersResult = dbQuery($sOffersQuery);
echo dbError();
//echo dbNumRows($rOffersResult);
if ( dbNumRows($rOffersResult) > 0) {
	while ($oOffersRow = dbFetchObject($rOffersResult)) {
		$sOfferCode = $oOffersRow->offerCode;
		$sInactiveDateTime = $oOffersRow->inactiveDate;
		
		//$sEmailContent .= "OfferCode: $sOfferCode   -   Inactive Date:$sInactiveDateTime\r\n";
		$sReportContent .= "<tr><td>$sOfferCode</td><td>$sInactiveDateTime</td></tr>";
		$sExpReportContent .= "$sOfferCode\t$sInactiveDateTime\n";
		
	}
	
}

if ($sExportExcel) {
		$sExpReportContent = "Offer Code\tInactive Date"."\n".$sExpReportContent;
		$sExpReportContent .= "\n\nReport For Offers Approaching To Inactive Date In Next $iInactiveDays Days";
		
		$sExpReportContent .= "\nRun Date/Time $sRunDateAndTime";
		
		$sFileName = "offersApprchInactive_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";
		
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
	
	
	$iScriptEndTime = getMicroTime();
	$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);
	
		// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iId value='$iId'>";	
	
	include("../../includes/adminHeader.php");	

// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;	
?>


<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td width=15%>Inactive Date Within Days</td>
	<td><input type=text name=iInactiveDays value='<?php echo $iInactiveDays;?>'></td>
</tr>
<tr><td></td><td><input type=submit name='sSubmit' value='View Report' onClick="funcReportClicked('report');">
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
	<tr><td colspan=2 class=bigHeader align=center><BR><?php echo $sPageTitle;?><BR><BR><BR></td></tr>
	
	<tr><td colspan=2 class=header>Run Date / Time: <?php echo $sRunDateAndTime;?></td></tr>
	
	<tr><td class=header><a href='<?php echo $sSortLink;?>&sOrderColumn=offerCode&sOfferCodeOrder=<?php echo $sOfferCodeOrder;?>' class=header>Offers</a></td>
		<td class=header><a href='<?php echo $sSortLink;?>&sOrderColumn=inactiveDate&sInactiveDateOrder=<?php echo $sInactiveDateOrder;?>' class=header>Inactive Date</a></td>		
	</tr>
	
			<?php echo $sReportContent;?>
			<tr><td colspan=5 align=center><hr color=#000000></td></tr>	
	
	<tr><td colspan=2><BR><BR></td></tr>
	
	<tr><td colspan=2 class=header><BR>Notes -</td></tr>
	
	<tr><td colspan=2>Report only reflects active and live offers.</td></tr>	
	<tr><td colspan=2><BR>Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)</td></tr>
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