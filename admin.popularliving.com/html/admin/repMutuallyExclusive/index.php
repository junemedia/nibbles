<?php

include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblIncludePath/reportInclude.php");

$iScriptStartTime = getMicroTime();
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

$sPageTitle = "Mutually Exclusive Offers Report";

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
// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
		<input type=hidden name=iId value='$iId'>";	
if ($sAllowReport == 'N') {
	$sMessage = "Server Load Is High. Please check back soon...";
} else {
	$sExpReportContent = '';
	$sReportContent = '';
	
	$sReportQuery = "SELECT * FROM offersMutExclusive";
	if ($sOfferCode != '') {
		$sReportQuery .= " WHERE offerCode1 = '$sOfferCode'
						OR offerCode2 = '$sOfferCode'";
	}

	$sReportQuery .= " ORDER BY offerCode1, offerCode2";
	$rResult = dbQuery($sReportQuery);
	echo dbError();
	while ($oRow = dbFetchObject($rResult)) {
		if ($sBgcolorClass == "ODD") {
			$sBgcolorClass = "EVEN_WHITE";
		} else {
			$sBgcolorClass = "ODD";
		}

		$sReportContent .= "<tr class=$sBgcolorClass><td>$oRow->offerCode1</td><td>$oRow->offerCode2</td></tr>";
		$sExpReportContent .= "$oRow->offerCode1\t$oRow->offerCode2\n";
	}

	// start of track users' activity in nibbles
	mysql_connect ($host, $user, $pass);
	mysql_select_db ($dbase);
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  		VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sReportQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	mysql_connect ($reportingHost, $reportingUser, $reportingPass);
	mysql_select_db ($reportingDbase);
	// end of track users' activity in nibbles
}


if ($sExportExcel) {
	$sExpReportContent = "Offer Code\tMutual Exclusive Offers"."\n".$sExpReportContent;
	$sExpReportContent .= "\nRun Date/Time $sRunDateAndTime";
	$sFileName = "mutExcOffers_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".xls";
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


$sQuery = "SELECT offerCode FROM offers ORDER BY offerCode";
$rOffersResult = dbQuery($sQuery);
$sOfferCodeOptions = "<option value=''>All";
while ($oOffersRow = dbFetchObject($rOffersResult)) {
	$sTempOfferCode = $oOffersRow->offerCode;
	if ($sTempOfferCode == $sOfferCode) {
		$sSelected = "Selected";
	} else {
		$sSelected = "";
	}
	$sOfferCodeOptions .= "<option value='$sTempOfferCode' $sSelected>$sTempOfferCode";
}

include("../../includes/adminHeader.php");

$iScriptEndTime = getMicroTime();
$iScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime);

// display javascript from reportInclude.php which defined funcReportClicked() function
echo $sReportJavaScript;	
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>

<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td>&nbsp;</td>
	<td>Offer Code: &nbsp; &nbsp; &nbsp;
	<select name=sOfferCode><?php echo $sOfferCodeOptions;?></select>
	</td></tr>
<tr><td colspan=2><input type=button name=sSubmit value='View Report' onClick="funcReportClicked('report');">	
&nbsp; &nbsp;
<input type=checkbox name=sExportExcel value="Y" <?php if ($sExportExcel) { echo 'checked'; }?>> Export To Excel</td>
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
	<tr>
		<td class=header>Offer Code</td>		
		<td class=header>Mutually Exclusive Offers</td>
	</tr>
		<?php echo $sReportContent;?>
		<tr><td colspan=2 align=left><hr color=#000000></td></tr>	
	<tr><td colspan=2 class=header><BR>Notes -
	</td></tr>
	<tr><td colspan=2>
	<b>Mutually Exclusive:- </b>If offer ABC is mutually exclusive to offer XYZ, then neither offer will show up on the same page as the other. 
	For example: If the stim for ABC is displayed, the stim for XYZ will not be displayed on the same page. 
	If XYZ is displayed, ABC will not be displayed on the same page.<br><br>
	Approximate time to run this report - <?php echo $iScriptExecutionTime;?> second(s)
		<br><br><b>Query: </b><?php echo $sReportQuery;?>
	</td></tr>
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