<?php


include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
$iCount = 0;
$sYesterday = strftime ("%Y-%m-%d", strtotime("-1 day"));
$sYesterday = str_replace('/','-',$sYesterday);
$s31DaysBack = strftime ("%Y-%m-%d", strtotime("-31 day"));
$s31DaysBack = str_replace('/','-',$s31DaysBack);
$sToday = date('Y')."-".date('m')."-".date('d');

$rDeleteResult = mysql_query("TRUNCATE TABLE nibbles_temp.tempPartnerData");

$rHistoryResult = mysql_query("INSERT IGNORE INTO nibbles_temp.tempPartnerData 
			(email,first,last,address,city,state,zip,remoteIp,dateTimeAdded,sourceCode)
			SELECT userDataHistory.email, first, last, address, city, state, zip, otDataHistory.remoteIp, otDataHistory.dateTimeAdded, sourceCode
			FROM userDataHistory, otDataHistory 
			WHERE userDataHistory.dateTimeAdded BETWEEN '$s31DaysBack 00:00:00' AND '$s31DaysBack 23:59:59'
			AND userDataHistory.email = otDataHistory.email
			AND otDataHistory.excludeDataSale != '1'
			AND otDataHistory.pageId != '238'");
$rSubResult = mysql_query("SELECT * FROM joinEmailSub WHERE dateTimeAdded BETWEEN '$s31DaysBack 00:00:00' AND '$s31DaysBack 23:59:59'");
while ($sRow2 = mysql_fetch_object($rSubResult)) {
	$sInsertQuery = "INSERT IGNORE INTO nibbles_temp.tempPartnerData (email,remoteIp,dateTimeAdded,sourceCode)
					VALUES (\"$sRow2->email\",\"$sRow2->remoteIp\",\"$sRow2->dateTimeAdded\",\"$sRow2->sourceCode\")";
	
	$rResult = mysql_query("SELECT * FROM userDataHistory WHERE email = \"$sRow2->email\"");
	if (mysql_num_rows($rResult) > 0) {
		$sRow = mysql_fetch_object($rResult);
		$sInsertQuery = "INSERT IGNORE INTO nibbles_temp.tempPartnerData
			(email,first,last,address,city,state,zip,remoteIp,dateTimeAdded,sourceCode)
			VALUES (\"$sRow2->email\",\"$sRow->first\",\"$sRow->last\",\"$sRow->address\", 
			\"$sRow->city\",\"$sRow->state\",\"$sRow->zip\",\"$sRow2->remoteIp\",\"$sRow2->dateTimeAdded\",\"$sRow2->sourceCode\")";
	}
	$rInsertResult = mysql_query($sInsertQuery);
}



// start delete
// IP and EMAIL are required so do not send any record with blank email or ip, so delete them before we create the file.
$rDelete = mysql_query("DELETE FROM nibbles_temp.tempPartnerData WHERE email='';DELETE FROM nibbles_temp.tempPartnerData WHERE remoteIp='';");

$rGetResult = mysql_query("SELECT TLDs FROM excludeTLDsDataSales");
while ($rRow = mysql_fetch_object($rGetResult)) {
	$rDelete = mysql_query("DELETE FROM nibbles_temp.tempPartnerData WHERE email LIKE '%$rRow->TLDs'");
}


$rResult2 = mysql_query("SELECT domain FROM excludeDomainsDataSales");
while ($rRow2 = mysql_fetch_object($rResult2)) {
	$rDelete = mysql_query("DELETE FROM nibbles_temp.tempPartnerData WHERE email LIKE '%$rRow2->domain'");
}


$rResult3 = mysql_query("SELECT email FROM excludeEmailDataSales");
while ($rRow3 = mysql_fetch_object($rResult3)) {
	$rDelete = mysql_query("DELETE FROM nibbles_temp.tempPartnerData WHERE email = '$rRow3->email'");
}


$rRst = mysql_query("SELECT DISTINCT sourceCode FROM links,partnerCompanies WHERE links.partnerId=partnerCompanies.id AND excludeDataSale='1'");
while ($rRow4 = mysql_fetch_object($rRst)) {
	$rDelete = mysql_query("DELETE FROM nibbles_temp.tempPartnerData WHERE sourceCode = '$rRow4->sourceCode'");
}
// end delete



$rFile = fopen("/home/partnerData/Ampere-".$sYesterday.".txt","w");
if ($rFile) {
	$rGetData = mysql_query("SELECT * FROM nibbles_temp.tempPartnerData");
	while ($sData = mysql_fetch_object($rGetData)) {
		$sExpData = $sData->email.'|'.$sData->first.'|'.$sData->last.'|'.$sData->address.'|';
		$sExpData .= $sData->city.'|'.$sData->state.'|'.$sData->zip.'|'.$sData->remoteIp.'|popularliving.com|'.$sData->dateTimeAdded."\r\n";
		$sTemp = fwrite($rFile, $sExpData);
		$iCount++;
	}
}


/*
$sFile = "Ampere-".$sYesterday.".txt";
$sConnection_Id = ftp_connect("71.5.84.3");	// set up basic connection
$sLoginResult = ftp_login($sConnection_Id, "ftpuser22", "m0r3f1l35");	// login with username and password
ftp_pasv($sConnection_Id, false);	// turn off passive mode so active mode will be turned on
if (!$sConnection_Id) {	// check connection
	mail('it@amperemedia.com', 'processPartnerData FTP Failed', "FTP connection has failed for 'Partner Data' data feed!");
} else {
	if (ftp_put($sConnection_Id, "$sFile", "/home/partnerData/"."$sFile", FTP_ASCII)) {	// upload a file
		echo "successfully uploaded - Feed file for 'Partner Data'";
	} else {
		mail('it@amperemedia.com', 'processPartnerData FTP Failed', "There was a problem while uploading data feed file for 'Partner Data'");
	}
	ftp_close($sConnection_Id);	// close the FTP stream
}
*/


$rDeleteResult = mysql_query("TRUNCATE TABLE nibbles_temp.tempPartnerData");

$rCheckResult = mysql_query("SELECT * FROM nibbles_datafeed.dataSentStats WHERE date='$sToday' AND script='partnerData'");
if (mysql_num_rows($rCheckResult) == 0) {
	//$rResultAdd = mysql_query("INSERT INTO nibbles_datafeed.dataSentStats(count, date, script) VALUES('$iCount', '$sToday', 'partnerData')");
}

?>
