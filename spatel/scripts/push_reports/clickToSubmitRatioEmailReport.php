<?php

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

// Pulling yesterday's leads from DB
$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));

//$sYesterday = '2006-10-14';
$sTruncateTempTableQuery = "TRUNCATE TABLE tempRepClickToSubmitRatio";
$rTruncateTempTableResult = dbQuery($sTruncateTempTableQuery);

// Read script configuration from database
$configSql = "SELECT alertEmailName, fraudTriggerGroupSize, alertTriggerPercent, enabledStatus 
		FROM alertEmails 
		WHERE alertEmailName='clickToSubmitRatio'";
$rConfigResult = dbQuery( $configSql );
while ($oConfigRow = dbFetchObject($rConfigResult)) {
	if( strtolower($oConfigRow->enabledStatus) == 'd' ) {
		cssLogFinish( $iScriptId );
		die();
	}
	$sTriggerGroupSize = $oConfigRow->fraudTriggerGroupSize;
	$sTriggerPercent = $oConfigRow->alertTriggerPercent;
}



$sGetBdRedirectsQuery = "SELECT * FROM    bdRedirectsTrackingHistorySum
			  WHERE   clickDate BETWEEN '$sYesterday'  AND 	'$sYesterday'";
$rGetBdRedirectsResult = dbQuery($sGetBdRedirectsQuery);
while ($oRedirectRow = dbFetchObject($rGetBdRedirectsResult)) {
	$sInsertRedirectQuery = "INSERT INTO tempRepClickToSubmitRatio (sourceCode,redirectCount,submitCount)
			VALUES (\"$oRedirectRow->sourceCode\",\"$oRedirectRow->clicks\",'0')";
	$rInsertRedirectResult = dbQuery($sInsertRedirectQuery);
}
		
	
$sClickedSubmitQuery = "SELECT sourceCode, count(sourceCode) as counts FROM xOutDataHistory
   				  WHERE   dateTimeAdded BETWEEN '$sYesterday 00:00:00' AND '$sYesterday 23:59:59' 
   				  GROUP BY sourceCode";
$rClickedSubmitResult = dbQuery($sClickedSubmitQuery);
while ($oSubmitRow = dbFetchObject($rClickedSubmitResult)) {
	$sInsertRedirectQuery = "INSERT INTO tempRepClickToSubmitRatio (sourceCode,redirectCount,submitCount)
		VALUES (\"$oSubmitRow->sourceCode\",\"0\",\"$oSubmitRow->counts\")";
	$rInsertRedirectResult = dbQuery($sInsertRedirectQuery);
}
		

$sGetReportDataQuery = "SELECT sourceCode, sum(redirectCount) as redirects, sum(submitCount) as submits
	 		FROM tempRepClickToSubmitRatio GROUP BY sourceCode";
$rGetReportDataResult = dbQuery($sGetReportDataQuery);


$sInternalReport = "<table border=1><tr>
			<td><b>&nbsp;&nbsp;Source Code&nbsp;&nbsp;</b></td>
			<td><b>&nbsp;&nbsp;Redirect Total&nbsp;&nbsp;</b></td>
			<td><b>&nbsp;&nbsp;Submit Total&nbsp;&nbsp;</b></td>
			<td><b>&nbsp;&nbsp;Conversion Rate&nbsp;&nbsp;</b></td></tr>";
$iTempCount = 0;
while ($oReportRow = dbFetchObject($rGetReportDataResult)) {
	$sConversionRate = 0;
	if ($oReportRow->redirects > 0) {
		$sConversionRate = number_format((($oReportRow->submits / $oReportRow->redirects)*100),1);
	}

	if (($sConversionRate < $sTriggerPercent) && $oReportRow->redirects >= $sTriggerGroupSize) {
		$sInternalReport .= "<tr><td>$oReportRow->sourceCode</td><td>$oReportRow->redirects</td>
					<td>$oReportRow->submits</td><td>$sConversionRate</td></tr>";
		
		$iRedirectTotal += $oReportRow->redirects;
		$iSubmitTotal += $oReportRow->submits;
		$iTempCount++;
	}
}

if ($iTempCount == 0) {
	$sInternalReport .= "<tr><td colspan=4>NONE</td></tr>";
	mail('spatel@amperemedia.com', __FILE__.'   '.__LINE__, __FILE__);
}

$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$sHeaders .= "From:nibbles@amperemedia.com";
	
$sEmailQuery = "SELECT * FROM   emailRecipients WHERE  purpose = 'click to submit ratio report'";
$rEmailResult = dbQuery($sEmailQuery);
while ($oEmailRow = dbFetchObject($rEmailResult)) {
	$sRecipients = $oEmailRow->emailRecipients;
	//$sRecipients = 'it@amperemedia.com';
}
	
$sSubject = "Click To Submit Ratio Report: $sYesterday";


if ($iTempCount > 0) {
	mail($sRecipients, $sSubject, $sInternalReport, $sHeaders);
}


?>
