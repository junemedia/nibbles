<?php

include_once("/home/scripts/includes/cssLogFunctions.php");
$iScriptId = cssLogStart( "processHydraMedia.php" );

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
$sYesterday = strftime ("%Y-%m-%d", strtotime("-1 day"));
$sYesterday = str_replace('/','-',$sYesterday);
$s31DaysBack = strftime ("%Y-%m-%d", strtotime("-31 day"));
$s31DaysBack = str_replace('/','-',$s31DaysBack);
$sFrom = $s31DaysBack." 00:00:00";
$sTo = $s31DaysBack." 23:59:59";
$sDeleteQuery = "TRUNCATE TABLE nibbles_temp.tempHydraMedia";
$rDeleteResult = mysql_query($sDeleteQuery);


$sGetData = "SELECT userDataHistory.email, userDataHistory.dateTimeAdded, otDataHistory.sourceCode 
			FROM userDataHistory, otDataHistory 
			WHERE userDataHistory.dateTimeAdded BETWEEN '$sFrom' AND '$sTo'
			AND userDataHistory.email = otDataHistory.email
			AND otDataHistory.excludeDataSale != '1'
			AND otDataHistory.pageId != '238'";
$rResult = mysql_query($sGetData);
while ($sData = mysql_fetch_object($rResult)) {
	$sInsertQuery = "INSERT IGNORE INTO nibbles_temp.tempHydraMedia (email,dateTimeAdded,sourceCode)
				VALUES (\"$sData->email\",\"$sData->dateTimeAdded\",\"$sData->sourceCode\")";
	$rInsertResult = mysql_query($sInsertQuery);
}
	

$sGetJoinEmailConfirmDataQuery = "SELECT * FROM joinEmailSub WHERE dateTimeAdded BETWEEN '$sFrom' AND '$sTo'";
$rGetJoinEmailConfirmDataResult = mysql_query($sGetJoinEmailConfirmDataQuery);
while ($sJoinEmailRow = mysql_fetch_object($rGetJoinEmailConfirmDataResult)) {
	$sInsertQuery = "INSERT IGNORE INTO nibbles_temp.tempHydraMedia (email,dateTimeAdded,sourceCode)
				VALUES (\"$sJoinEmailRow->email\",\"$sJoinEmailRow->dateTimeAdded\",\"$sJoinEmailRow->sourceCode\")";
	$rInsertResult = mysql_query($sInsertQuery);
}

	
	
// start delete
$sGetQuery = "select TLDs FROM excludeTLDsDataSales";
$rGetResult = mysql_query($sGetQuery);
while ($rRow = mysql_fetch_object($rGetResult)) {
	$sDelete = "DELETE FROM nibbles_temp.tempHydraMedia WHERE email LIKE '%$rRow->TLDs'";
	$rDelete = mysql_query($sDelete);
}
	
$sGetQuery = "select domain from excludeDomainsDataSales";
$rResult2 = mysql_query($sGetQuery);
while ($rRow2 = mysql_fetch_object($rResult2)) {
	$sDelete = "DELETE FROM nibbles_temp.tempHydraMedia WHERE email LIKE '%$rRow2->domain'";
	$rDelete = mysql_query($sDelete);
}

$sGetQuery = "select email from excludeEmailDataSales";
$rResult3 = mysql_query($sGetQuery);
while ($rRow3 = mysql_fetch_object($rResult3)) {
	$sDelete = "DELETE FROM nibbles_temp.tempHydraMedia WHERE email = '$rRow3->email'";
	$rDelete = mysql_query($sDelete);
}
	
$sGetQuery = "select distinct sourceCode
	from links, partnerCompanies
	where links.partnerId = partnerCompanies.id
	AND excludeDataSale = '1'";
$rResult4 = mysql_query($sGetQuery);
while ($rRow4 = mysql_fetch_object($rResult4)) {
	$sDelete = "DELETE FROM nibbles_temp.tempHydraMedia WHERE sourceCode = '$rRow4->sourceCode'";
	$rDelete = mysql_query($sDelete);
}
// end delete
	
	

$sExportData = "";
$iCount = 0;
$sGetData = "SELECT * FROM nibbles_temp.tempHydraMedia";
$rGetData = mysql_query($sGetData);
while ($sFinalData = mysql_fetch_object($rGetData)) {
	$sExportData .= "\"$sFinalData->email\",\"$sFinalData->dateTimeAdded\"\r\n";
	$iCount++;
}


$rFile = fopen("/home/hydramedia/".$sYesterday.".csv","w");
if ($rFile) {
	$sTemp = fwrite($rFile, $sExportData);
}


$sDeleteQuery = "TRUNCATE TABLE nibbles_temp.tempHydraMedia";
$rDeleteResult = mysql_query($sDeleteQuery);
	
	
	
$sToday = date(Y)."-".date(m)."-".date(d);
$sCheckQuery = "SELECT *
			FROM nibbles_datafeed.dataSentStats
			WHERE date = '$sToday'
			AND script = 'hydra'";
$rCheckResult = mysql_query($sCheckQuery);
echo mysql_error();

if (mysql_num_rows($rCheckResult) == 0) {
	$sAddQuery = "INSERT INTO nibbles_datafeed.dataSentStats(count, date, script)
					  VALUES('$iCount', \"$sToday\", 'hydra')";
	$rResultAdd = mysql_query($sAddQuery);
	echo mysql_error();
}

	
cssLogFinish( $iScriptId );
?>
