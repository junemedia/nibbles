<?php

ini_set('max_execution_time', 5000000);

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
$sToday = date('Y')."-".date('m')."-".date('d');
$sYesterday = strftime ("%Y-%m-%d", strtotime("-1 day"));
$sYesterday = str_replace('/','-',$sYesterday);
$s31DaysBack = strftime ("%Y-%m-%d", strtotime("-31 day"));
$s31DaysBack = str_replace('/','-',$s31DaysBack);
$sFrom = $s31DaysBack." 00:00:00";
$sTo = $s31DaysBack." 23:59:59";


$rResult = mysql_query("TRUNCATE TABLE nibbles_temp.tempOfflineDirect");

$rResult = mysql_query("INSERT IGNORE INTO nibbles_temp.tempOfflineDirect (email,dateTimeAdded,ip,fname,lname,zip,sourceCode)
			SELECT userDataHistory.email, userDataHistory.dateTimeAdded, otDataHistory.remoteIp,first,last,zip,sourceCode
			FROM userDataHistory, otDataHistory 
			WHERE userDataHistory.dateTimeAdded BETWEEN '$sFrom' AND '$sTo'
			AND userDataHistory.email = otDataHistory.email
			AND otDataHistory.excludeDataSale != '1'
			AND otDataHistory.pageId != '238'");

$result = mysql_query("SELECT * FROM joinEmailSub WHERE dateTimeAdded BETWEEN '$sFrom' AND '$sTo'");
while ($sJoinRow = mysql_fetch_object($result)) {
	$rUserResult = mysql_query("SELECT * FROM userDataHistory WHERE email = \"$sJoinRow->email\"");
	if (mysql_num_rows($rUserResult) > 0) {
		$sUserRow = mysql_fetch_object($rUserResult);
		$sInsertQuery = "INSERT IGNORE INTO nibbles_temp.tempOfflineDirect (email,dateTimeAdded,ip,fname,lname,zip,sourceCode)
			VALUES (\"$sJoinRow->email\",\"$sJoinRow->dateTimeAdded\",\"$sJoinRow->remoteIp\",\"$sUserRow->first\",\"$sUserRow->last\",
			\"$sUserRow->zip\",\"$sJoinRow->sourceCode\")";
	} else {
		$sInsertQuery = "INSERT IGNORE INTO nibbles_temp.tempOfflineDirect (email,dateTimeAdded,ip,sourceCode)
			VALUES (\"$sJoinRow->email\",\"$sJoinRow->dateTimeAdded\",\"$sJoinRow->remoteIp\",\"$sJoinRow->sourceCode\")";
	}
	$rInsertResult = mysql_query($sInsertQuery);
}


// start delete
// delete if email, dateTimeAdded, and ip is blank because those are required fields
$rDelete = mysql_query("DELETE FROM nibbles_temp.tempOfflineDirect WHERE email=''");
$rDelete = mysql_query("DELETE FROM nibbles_temp.tempOfflineDirect WHERE dateTimeAdded=''");
$rDelete = mysql_query("DELETE FROM nibbles_temp.tempOfflineDirect WHERE ip=''");


$result = mysql_query("select TLDs FROM excludeTLDsDataSales");
while ($row = mysql_fetch_object($result)) {
	$asdf = mysql_query("DELETE FROM nibbles_temp.tempOfflineDirect WHERE email LIKE '%$row->TLDs'");
}

$result = mysql_query("select domain from excludeDomainsDataSales");
while ($row = mysql_fetch_object($result)) {
	$asdf = mysql_query("DELETE FROM nibbles_temp.tempOfflineDirect WHERE email LIKE '%$row->domain'");
}

$result = mysql_query("select email from excludeEmailDataSales");
while ($row = mysql_fetch_object($result)) {
	$asdf = mysql_query("DELETE FROM nibbles_temp.tempOfflineDirect WHERE email = '$row->email'");
}


$result = mysql_query("select distinct sourceCode from links, partnerCompanies where links.partnerId = partnerCompanies.id AND excludeDataSale = '1'");
while ($row = mysql_fetch_object($result)) {
	$asdf = mysql_query("DELETE FROM nibbles_temp.tempOfflineDirect WHERE sourceCode = '$row->sourceCode'");
}
// end delete


$iCount = 0;
$sFile = "Ampere-".$sYesterday.".txt";
$rFile = fopen("/home/offlineDirect/$sFile","w");
if ($rFile) {
	$result = mysql_query("SELECT * FROM nibbles_temp.tempOfflineDirect");
	$sTemp = fwrite($rFile, "Email\tFirst Name\tLast Name\tZip\tJoindate\tURL\tIP\r\n");
	while ($row = mysql_fetch_object($result)) {
		$sTemp = fwrite($rFile, "$row->email\t$row->fname\t$row->lname\t$row->zip\t$row->dateTimeAdded\tpopularliving.com\t$row->ip\r\n");
		$iCount++;
	}
}


$sConnection_Id = ftp_connect("qs1520.pair.com");
$sLoginResult = ftp_login($sConnection_Id, "zenmedia_ampere", "aMp3R3M1d5");	// login with username and password
ftp_pasv($sConnection_Id, false);	// turn off passive mode so active mode will be turned on
if (!$sConnection_Id) {	// check connection
	mail('it@amperemedia.com', __FILE__.' FTP Failed', "FTP connection has failed!\n\nAttempted to connect to qs1520.pair.com for user ampere" , "From: spatel@amperemedia.com\r\n");
} else {
	if (ftp_put($sConnection_Id, "$sFile", "/home/offlineDirect/"."$sFile", FTP_ASCII)) {	// upload a file
		echo "successfully uploaded $sFile\n";
	} else {
		mail('it@amperemedia.com', __FILE__.' FTP Failed', "There was a problem while uploading $sFile\n" , "From: spatel@amperemedia.com\r\n");
	}
	ftp_close($sConnection_Id);	// close the FTP stream
}




$rCheckResult = mysql_query("SELECT * FROM nibbles_datafeed.dataSentStats WHERE date = '$sToday' AND script = 'offlineDirect'");
if (mysql_num_rows($rCheckResult) == 0) {
	$rResultAdd = mysql_query("INSERT INTO nibbles_datafeed.dataSentStats(count, date, script) VALUES('$iCount', \"$sToday\", 'offlineDirect')");
}


$rResult = mysql_query("TRUNCATE TABLE nibbles_temp.tempOfflineDirect");

?>
