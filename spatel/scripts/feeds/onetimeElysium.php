<?php

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

$sYesterday = '2007-07-01';
$sFrom = $sYesterday." 00:00:00";
$sTo = $sYesterday." 23:59:59";
$iCount = 0;
$sDeleteQuery = "TRUNCATE TABLE nibbles_temp.tempElysium";
$rDeleteResult = mysql_query($sDeleteQuery);


// Exclude api, sources, domains, emails.
$sGetData = "SELECT userDataHistory.email, userDataHistory.dateTimeAdded, otDataHistory.remoteIp, otDataHistory.sourceCode
		FROM userDataHistory, otDataHistory 
		WHERE userDataHistory.dateTimeAdded BETWEEN '$sFrom' AND '$sTo'
		AND userDataHistory.email = otDataHistory.email
		AND otDataHistory.excludeDataSale != '1'
		AND otDataHistory.pageId != '238'";
$rResult = mysql_query($sGetData);
while ($sData = mysql_fetch_object($rResult)) {
	$sInsert = "INSERT IGNORE INTO nibbles_temp.tempElysium (email,dateTimeAdded,remoteIp,sourceCode)
		VALUES (\"$sData->email\",\"$sData->dateTimeAdded\",\"$sData->remoteIp\",\"$sData->sourceCode\")";
	$rInsertResult = mysql_query($sInsert);
}


$sGetJoinEmailConfirmDataQuery = "SELECT * FROM joinEmailSub
				WHERE dateTimeAdded BETWEEN '$sFrom' AND '$sTo'";
$rGetJoinEmailConfirmDataResult = mysql_query($sGetJoinEmailConfirmDataQuery);
while ($sJoinEmailRow = mysql_fetch_object($rGetJoinEmailConfirmDataResult)) {
	$sInsert = "INSERT IGNORE INTO nibbles_temp.tempElysium (email,dateTimeAdded,remoteIp,sourceCode)
		VALUES (\"$sJoinEmailRow->email\",\"$sJoinEmailRow->dateTimeAdded\",\"$sJoinEmailRow->remoteIp\",\"$sJoinEmailRow->sourceCode\")";
	$rInsertResult = mysql_query($sInsert);
}

// start delete
$sGetQuery = "select TLDs FROM excludeTLDsDataSales";
$rGetResult = mysql_query($sGetQuery);
while ($rRow = mysql_fetch_object($rGetResult)) {
	$sDelete = "DELETE FROM nibbles_temp.tempElysium WHERE email LIKE '%$rRow->TLDs'";
	$rDelete = mysql_query($sDelete);
}
	
$sGetQuery = "select domain from excludeDomainsDataSales";
$rResult2 = mysql_query($sGetQuery);
while ($rRow2 = mysql_fetch_object($rResult2)) {
	$sDelete = "DELETE FROM nibbles_temp.tempElysium WHERE email LIKE '%$rRow2->domain'";
	$rDelete = mysql_query($sDelete);
}

$sGetQuery = "select email from excludeEmailDataSales";
$rResult3 = mysql_query($sGetQuery);
while ($rRow3 = mysql_fetch_object($rResult3)) {
	$sDelete = "DELETE FROM nibbles_temp.tempElysium WHERE email = '$rRow3->email'";
	$rDelete = mysql_query($sDelete);
}
	
$sGetQuery = "select distinct sourceCode
	from links, partnerCompanies
	where links.partnerId = partnerCompanies.id
	AND excludeDataSale = '1'";
$rResult4 = mysql_query($sGetQuery);
while ($rRow4 = mysql_fetch_object($rResult4)) {
	$sDelete = "DELETE FROM nibbles_temp.tempElysium WHERE sourceCode = '$rRow4->sourceCode'";
	$rDelete = mysql_query($sDelete);
}
// end delete

$sExportData = "";
$sGetData = "SELECT * FROM nibbles_temp.tempElysium";
$rGetData = mysql_query($sGetData);
while ($sFinalData = mysql_fetch_object($rGetData)) {
	$sExportData .= "\"$sFinalData->email\",\"$sFinalData->dateTimeAdded\",\"$sFinalData->remoteIp\"\r\n";
	$iCount++;
}

$rFile = fopen("/home/elysium/ampere_".$sYesterday.".csv","w");
if ($rFile) {
	$sTemp = fwrite($rFile, $sExportData);
}
	
	
	
	
$sDeleteQuery = "TRUNCATE TABLE nibbles_temp.tempElysium";
$rDeleteResult = mysql_query($sDeleteQuery);
	
$sToday = date('Y')."-".date('m')."-02";
$sCheckQuery = "SELECT *
			FROM nibbles_datafeed.dataSentStats
			WHERE date = '$sToday'
			AND script = 'elysium'";
$rCheckResult = mysql_query($sCheckQuery);
echo mysql_error();
if (mysql_num_rows($rCheckResult) == 0) {
	$sAddQuery = "INSERT INTO nibbles_datafeed.dataSentStats(count, date, script)
					  VALUES('$iCount', \"$sToday\", 'elysium')";
	$rResultAdd = mysql_query($sAddQuery);
	echo mysql_error();
}

// Start of FTP script
$sFile = "ampere_".$sYesterday.".csv";

// set up basic connection
$sFtp_User = "silverc";
$sFtp_Pass = "viiraj24";
$sFtp_Server = "198.65.133.49";
$sConnection_Id = ftp_connect($sFtp_Server);

// login with username and password
$sLoginResult = ftp_login($sConnection_Id, $sFtp_User, $sFtp_Pass);

// check connection
if (!$sConnection_Id) {
	$sEmailMessage = "FTP connection has failed!\n\n";
	$sEmailMessage .= "Attempted to connect to $sFtp_Server for user $sFtp_User\n\n";
	mail('it@amperemedia.com', 'processElysium FTP Failed', $sEmailMessage , "From: spatel@amperemedia.com\r\n");
} else {
	// upload a file
	if (ftp_put($sConnection_Id, "$sFile", "/home/elysium/"."$sFile", FTP_ASCII)) {
		echo "successfully uploaded $sFile\n";
	} else {
		$sEmailMessage = "There was a problem while uploading $sFile\n";
		mail('it@amperemedia.com', 'processElysium FTP Failed', $sEmailMessage , "From: spatel@amperemedia.com\r\n");
	}
	// close the FTP stream
	ftp_close($sConnection_Id);
}
// End of FTP script


?>
