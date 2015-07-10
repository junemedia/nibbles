<?php

ini_set('max_execution_time', 5000000);

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
$sYesterday = strftime ("%Y-%m-%d", strtotime("-1 day"));
$sYesterday = str_replace('/','-',$sYesterday);
$s31DaysBack = strftime ("%Y-%m-%d", strtotime("-31 day"));
$s31DaysBack = str_replace('/','-',$s31DaysBack);
$sFrom = $s31DaysBack." 00:00:00";
$sTo = $s31DaysBack." 23:59:59";


$rResult = mysql_query("TRUNCATE TABLE nibbles_temp.tempRpmLiveFeed");

echo "\n\nStep1\n\n";


$sGetData = "INSERT IGNORE INTO nibbles_temp.tempRpmLiveFeed (email,dateTimeAdded,ip,fname,lname,address,city,state,zip,phone,sourceCode)
			SELECT userDataHistory.email, userDataHistory.dateTimeAdded, otDataHistory.remoteIp,
			first,last,address,city,state,zip,phoneNo,sourceCode
			FROM userDataHistory, otDataHistory 
			WHERE userDataHistory.dateTimeAdded BETWEEN '$sFrom' AND '$sTo'
			AND userDataHistory.email = otDataHistory.email
			AND otDataHistory.excludeDataSale != '1'
			AND otDataHistory.pageId != '238'
			LIMIT 100000";
$rResult = mysql_query($sGetData);



echo "\n\nStep2\n\n";

$sGetJoinEmailSubDataQuery = "SELECT * FROM joinEmailSub 
				WHERE dateTimeAdded BETWEEN '$sFrom' AND '$sTo'";
$rGetJoinEmailSubDataResult = mysql_query($sGetJoinEmailSubDataQuery);
while ($sJoinEmailSubRow = mysql_fetch_object($rGetJoinEmailSubDataResult)) {
	$sEmail = $sJoinEmailSubRow->email;
	$sSourceCode = $sJoinEmailSubRow->sourceCode;
	$sRemoteIp = $sJoinEmailSubRow->remoteIp;
	$sDateTime = $sJoinEmailSubRow->dateTimeAdded;
	
	$sTempDataQuery = "SELECT *	FROM userDataHistory
						WHERE email = \"$sEmail\"";
	$rTempDataQueryResult = mysql_query($sTempDataQuery);
	if (mysql_num_rows($rTempDataQueryResult) > 0) {
		$sTempDataRow = mysql_fetch_object($rTempDataQueryResult);
		$sFirst = $sTempDataRow->first;
		$sLast = $sTempDataRow->last;
		$sAddress = $sTempDataRow->address;
		$sCity = $sTempDataRow->city;
		$sState = $sTempDataRow->state;
		$sZip = $sTempDataRow->zip;
		$sPhoneNo = $sTempDataRow->phoneNo;
		$sDateTime = $sTempDataRow->dateTimeAdded;

		$sInsertQuery = "INSERT IGNORE INTO nibbles_temp.tempRpmLiveFeed 
				(email,dateTimeAdded,ip,fname,lname,address,city,state,zip,phone,sourceCode)
			VALUES (\"$sEmail\",\"$sDateTime\",\"$sRemoteIp\",\"$sFirst\",\"$sLast\",\"$sAddress\",\"$sCity\",
			\"$sState\",\"$sZip\",\"$sPhoneNo\",\"$sSourceCode\")";
		$rInsertResult = mysql_query($sInsertQuery);
	} else {
		$sInsertQuery = "INSERT IGNORE INTO nibbles_temp.tempRpmLiveFeed (email,dateTimeAdded,ip,sourceCode)
			VALUES (\"$sEmail\",\"$sDateTime\",\"$sRemoteIp\",\"$sSourceCode\")";
		$rInsertResult = mysql_query($sInsertQuery);
	}
}

echo "\n\nStep3\n\n";


// start delete
// delete if email, dateTimeAdded, and ip is blank because those are required fields
$rDelete = mysql_query("DELETE FROM nibbles_temp.tempRpmLiveFeed WHERE email=''");
$rDelete = mysql_query("DELETE FROM nibbles_temp.tempRpmLiveFeed WHERE dateTimeAdded=''");
$rDelete = mysql_query("DELETE FROM nibbles_temp.tempRpmLiveFeed WHERE ip=''");


echo "\n\nStep4\n\n";

$rGetResult = mysql_query("select TLDs FROM excludeTLDsDataSales");
while ($rRow = mysql_fetch_object($rGetResult)) {
	$sDelete = "DELETE FROM nibbles_temp.tempRpmLiveFeed WHERE email LIKE '%$rRow->TLDs'";
	$rDelete = mysql_query($sDelete);
}

$rResult2 = mysql_query("select domain from excludeDomainsDataSales");
while ($rRow2 = mysql_fetch_object($rResult2)) {
	$sDelete = "DELETE FROM nibbles_temp.tempRpmLiveFeed WHERE email LIKE '%$rRow2->domain'";
	$rDelete = mysql_query($sDelete);
}

echo "\n\nStep5\n\n";

$rResult3 = mysql_query("select email from excludeEmailDataSales");
while ($rRow3 = mysql_fetch_object($rResult3)) {
	$sDelete = "DELETE FROM nibbles_temp.tempRpmLiveFeed WHERE email = '$rRow3->email'";
	$rDelete = mysql_query($sDelete);
}


$rResult4 = mysql_query("select distinct sourceCode
	from links, partnerCompanies
	where links.partnerId = partnerCompanies.id
	AND excludeDataSale = '1'");
while ($rRow4 = mysql_fetch_object($rResult4)) {
	$sDelete = "DELETE FROM nibbles_temp.tempRpmLiveFeed WHERE sourceCode = '$rRow4->sourceCode'";
	$rDelete = mysql_query($sDelete);
}

// end delete
echo "\n\nStep6\n\n";

$iCount = 0;
$rFile = fopen("/home/rpmLiveFeed/Ampere-".$sYesterday.".txt","w");
if ($rFile) {
	$sFinalDataQuery = "SELECT * FROM nibbles_temp.tempRpmLiveFeed";
	$rFinalDataResult = mysql_query($sFinalDataQuery);
	$sExportData = "email\tjoin_date\tsource_site\tsource_ip\tfname\tlname\taddress\tcity\tstate\tprovince\tzip\tphone\tcountry\tExt_recip_id\r\n";
	$sTemp = fwrite($rFile, $sExportData);
	while ($sFinalFinalData = mysql_fetch_object($rFinalDataResult)) {
		$sExportData = $sFinalFinalData->email."\t";
		$sExportData .= $sFinalFinalData->dateTimeAdded."\t";
		$sExportData .= "popularliving.com"."\t";
		$sExportData .= $sFinalFinalData->ip."\t";
		$sExportData .= $sFinalFinalData->fname."\t";
		$sExportData .= $sFinalFinalData->lname."\t";
		$sExportData .= $sFinalFinalData->address."\t";
		$sExportData .= $sFinalFinalData->city."\t";
		$sExportData .= $sFinalFinalData->state."\t";
		$sExportData .= "\t"; // province
		$sExportData .= $sFinalFinalData->zip."\t";
		$sExportData .= $sFinalFinalData->phone."\t";
		$sExportData .= "US\t"; // country
		$sExportData .= "\r\n"; // Ext_recip_id
		$sTemp = fwrite($rFile, $sExportData);
		$iCount++;
	}
}
echo "\n\nStep7\n\n";

// Start of FTP script
$sFile = "Ampere-".$sYesterday.".txt";


// If we want to set this data feed as real time form post/get
// partner tag: ampere
// partner key: a7ccd25
// posting url: http://track.rpmlivefeed.com/livefeed.data

// set up basic connection
$sFtp_User = "ampere";
$sFtp_Pass = "@mp3re";
$sFtp_Server = "ftp.rpmlivefeed.com";
$sConnection_Id = ftp_connect($sFtp_Server);

// login with username and password
$sLoginResult = ftp_login($sConnection_Id, $sFtp_User, $sFtp_Pass);

// turn off passive mode so active mode will be turned on
ftp_pasv($sConnection_Id, false);

// check connection
if (!$sConnection_Id) {
	$sEmailMessage = "FTP connection has failed!\n\n";
	$sEmailMessage .= "Attempted to connect to $sFtp_Server for user $sFtp_User\n\n";
	mail('it@amperemedia.com', 'RPM FTP Failed', $sEmailMessage , "From: spatel@amperemedia.com\r\n");
} else {
	// upload a file
	if (ftp_put($sConnection_Id, "$sFile", "/home/rpmLiveFeed/"."$sFile", FTP_ASCII)) {
		echo "successfully uploaded $sFile\n";
	} else {
		$sEmailMessage = "There was a problem while uploading $sFile\n";
		mail('it@amperemedia.com', 'RPM FTP Failed', $sEmailMessage , "From: spatel@amperemedia.com\r\n");
	}
	// close the FTP stream
	ftp_close($sConnection_Id);
}
// End of FTP script

echo "\n\nStep8\n\n";


$sToday = date('Y')."-".date('m')."-".date('d');
$sCheckQuery = "SELECT *
			FROM nibbles_datafeed.dataSentStats
			WHERE date = '$sToday'
			AND script = 'RPM'";
$rCheckResult = mysql_query($sCheckQuery);
echo mysql_error();
if (mysql_num_rows($rCheckResult) == 0) {
	$sAddQuery = "INSERT INTO nibbles_datafeed.dataSentStats(count, date, script)
					  VALUES('$iCount', \"$sToday\", 'RPM')";
	$rResultAdd = mysql_query($sAddQuery);
	echo mysql_error();
}

echo "\n\nStep10\n\n";


$rResult = mysql_query("TRUNCATE TABLE nibbles_temp.tempRpmLiveFeed");

?>
