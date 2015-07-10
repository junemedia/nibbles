<?php

ini_set('max_execution_time', 5000000);

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

$sYesterday = strftime ("%Y-%m-%d", strtotime("-1 day"));
$sYesterday = str_replace('/','-',$sYesterday);
$s31DaysBack = strftime ("%Y-%m-%d", strtotime("-31 day"));
$s31DaysBack = str_replace('/','-',$s31DaysBack);
$sFrom = $s31DaysBack." 00:00:00";
$sTo = $s31DaysBack." 23:59:59";

$rResult = mysql_query("TRUNCATE TABLE nibbles_temp.temp18thStoryData");

echo "\n\nStep1\n\n";
$sGetData = "SELECT userDataHistory.*, otDataHistory.remoteIp as tempRemoteIp, otDataHistory.sourceCode
			FROM userDataHistory, otDataHistory 
			WHERE userDataHistory.dateTimeAdded BETWEEN '$sFrom' AND '$sTo'
			AND userDataHistory.email = otDataHistory.email
			AND (userDataHistory.email LIKE '%@aol.com' 
				OR userDataHistory.email LIKE '%@cs.com' 
				OR userDataHistory.email LIKE '%@wmconnect.com' 
				OR userDataHistory.email LIKE '%@netscape.net' 
				OR userDataHistory.email LIKE '%@netscape.com' )
			AND otDataHistory.excludeDataSale != '1'
			AND otDataHistory.pageId != '238'";
$rResult = mysql_query($sGetData);
	
while ($sData = mysql_fetch_object($rResult)) {
	$sEmail = $sData->email;
	$sFirst = $sData->first;
	$sLast = $sData->last;
	$sAddress = $sData->address;
	$sAddress2 = $sData->address2;
	$sCity = $sData->city;
	$sState = $sData->state;
	$sZip = $sData->zip;
	$sRemoteIp = $sData->tempRemoteIp;
	$sDateTime = $sData->dateTimeAdded;
	$sSourceCode = $sData->sourceCode;
	$sGender = $sData->gender;

	$sInsertQuery = "INSERT IGNORE INTO nibbles_temp.temp18thStoryData (email,first,last,address,address2,city,state,zip,ip,date,gender,src)
		VALUES (\"$sEmail\",\"$sFirst\",\"$sLast\",\"$sAddress\",\"$sAddress2\", 
		\"$sCity\",\"$sState\",\"$sZip\",\"$sRemoteIp\",\"$sDateTime\", \"$sGender\", \"$sSourceCode\")";
	$rInsertResult = mysql_query($sInsertQuery);
}

echo "\n\nStep2\n\n";

$sGetJoinEmailSubDataQuery = "SELECT * FROM joinEmailSub 
				WHERE dateTimeAdded BETWEEN '$sFrom' AND '$sTo'
				AND (email LIKE '%@aol.com' OR email LIKE '%@cs.com' OR email LIKE '%@wmconnect.com' 
					OR email LIKE '%@netscape.net' OR email LIKE '%@netscape.com')";
$rGetJoinEmailSubDataResult = mysql_query($sGetJoinEmailSubDataQuery);

while ($sJoinEmailSubRow = mysql_fetch_object($rGetJoinEmailSubDataResult)) {
	$sEmail = $sJoinEmailSubRow->email;
	$sSourceCode = $sJoinEmailSubRow->sourceCode;
	$sRemoteIp = $sJoinEmailSubRow->remoteIp;
	$sDateTime = $sJoinEmailSubRow->dateTimeAdded;
	
	$sTempDataQuery = "SELECT * FROM userDataHistory WHERE email = \"$sEmail\"";
	$rTempDataQueryResult = mysql_query($sTempDataQuery);
	if (mysql_num_rows($rTempDataQueryResult) > 0) {
			$sTempDataRow = mysql_fetch_object($rTempDataQueryResult);
			$sFirst = $sTempDataRow->first;
			$sLast = $sTempDataRow->last;
			$sAddress = $sTempDataRow->address;
			$sAddress2 = $sTempDataRow->address2;
			$sCity = $sTempDataRow->city;
			$sState = $sTempDataRow->state;
			$sZip = $sTempDataRow->zip;
			$sDateTime = $sTempDataRow->dateTimeAdded;
			$sGender = $sTempDataRow->gender;

			$sInsertQuery = "INSERT IGNORE INTO nibbles_temp.temp18thStoryData (email,first,last,address,address2,city,state,zip,ip,date,gender,src)
				VALUES (\"$sEmail\",\"$sFirst\",\"$sLast\",\"$sAddress\",\"$sAddress2\", 
				\"$sCity\",\"$sState\",\"$sZip\",\"$sRemoteIp\",\"$sDateTime\", \"$sGender\", \"$sSourceCode\")";
			$rInsertResult = mysql_query($sInsertQuery);
	} else {
		$sInsertQuery = "INSERT IGNORE INTO nibbles_temp.temp18thStoryData (email,ip,date,sourceCode)
		VALUES (\"$sEmail\",\"$sRemoteIp\",\"$sDateTime\",\"$sSourceCode\")";
		$rInsertResult = mysql_query($sInsertQuery);
	}
}

echo "\n\nStep3\n\n";

// start delete
// IP and EMAIL are required so do not send any record with blank email or ip, so delete them 
// before we create the file.
$sDelete = "DELETE FROM nibbles_temp.temp18thStoryData WHERE ip=''";
$rDelete = mysql_query($sDelete);

echo "\n\nStep4\n\n";

$sGetQuery = "select TLDs FROM excludeTLDsDataSales";
$rGetResult = mysql_query($sGetQuery);
while ($rRow = mysql_fetch_object($rGetResult)) {
	$sDelete = "DELETE FROM nibbles_temp.temp18thStoryData WHERE email LIKE '%$rRow->TLDs'";
	$rDelete = mysql_query($sDelete);
}

$sGetQuery = "select domain from excludeDomainsDataSales";
$rResult2 = mysql_query($sGetQuery);
while ($rRow2 = mysql_fetch_object($rResult2)) {
	$sDelete = "DELETE FROM nibbles_temp.temp18thStoryData WHERE email LIKE '%$rRow2->domain'";
	$rDelete = mysql_query($sDelete);
}

echo "\n\nStep5\n\n";

$sGetQuery = "select email from excludeEmailDataSales";
$rResult3 = mysql_query($sGetQuery);
while ($rRow3 = mysql_fetch_object($rResult3)) {
	$sDelete = "DELETE FROM nibbles_temp.temp18thStoryData WHERE email = '$rRow3->email'";
	$rDelete = mysql_query($sDelete);
}

$sGetQuery = "select distinct sourceCode
	from links, partnerCompanies
	where links.partnerId = partnerCompanies.id
	AND excludeDataSale = '1'";
$rResult4 = mysql_query($sGetQuery);
while ($rRow4 = mysql_fetch_object($rResult4)) {
	$sDelete = "DELETE FROM nibbles_temp.temp18thStoryData WHERE sourceCode = '$rRow4->sourceCode'";
	$rDelete = mysql_query($sDelete);
}

// end delete
echo "\n\nStep6\n\n";

$iCount = 0;
$rFile = fopen("/home/18thStory/Ampere-".$sYesterday.".csv","w");
if ($rFile) {
	$sFinalDataQuery = "SELECT * FROM nibbles_temp.temp18thStoryData";
	$rFinalDataResult = mysql_query($sFinalDataQuery);
	while ($sFinalFinalData = mysql_fetch_object($rFinalDataResult)) {
		$sExportData = '"'.$sFinalFinalData->email.'",';
		$sExportData .= '"'.$sFinalFinalData->first.'",';
		$sExportData .= '"'.$sFinalFinalData->last.'",';
		$sExportData .= '"'.$sFinalFinalData->address.'",';
		$sExportData .= '"'.$sFinalFinalData->address2.'",';
		$sExportData .= '"'.$sFinalFinalData->city.'",';
		$sExportData .= '"'.$sFinalFinalData->state.'",';
		$sExportData .= '"'.$sFinalFinalData->zip.'",';
		$sExportData .= '"'.$sFinalFinalData->date.'",';
		$sExportData .= '"'.$sFinalFinalData->gender.'",';
		$sExportData .= '"'.$sFinalFinalData->ip.'",';
		$sExportData .= '"'."popularliving.com".'"';
		$sExportData .= "\r\n";
		$sTemp = fwrite($rFile, $sExportData);
		$iCount++;
	}
}
echo "\n\nStep7\n\n";

// Start of FTP script
$sFile = "Ampere-".$sYesterday.".csv";

// set up basic connection
$sFtp_User = "18thstory3";
$sFtp_Pass = "zack1818";
$sFtp_Server = "64.15.76.111";
$sConnection_Id = ftp_connect($sFtp_Server);

// login with username and password
$sLoginResult = ftp_login($sConnection_Id, $sFtp_User, $sFtp_Pass);

// turn off passive mode so active mode will be turned on
ftp_pasv($sConnection_Id, false);

// check connection
if (!$sConnection_Id) {
	$sEmailMessage = "FTP connection has failed!\n\n";
	$sEmailMessage .= "Attempted to connect to $sFtp_Server for user $sFtp_User\n\n";
	mail('it@amperemedia.com', 'process18thStory FTP Failed', $sEmailMessage , "From: spatel@amperemedia.com\r\n");
} else {
	// upload a file
	if (ftp_put($sConnection_Id, "$sFile", "/home/18thStory/"."$sFile", FTP_ASCII)) {
		echo "successfully uploaded $sFile\n";
	} else {
		$sEmailMessage = "There was a problem while uploading $sFile\n";
		mail('it@amperemedia.com', 'process18thStory FTP Failed', $sEmailMessage , "From: spatel@amperemedia.com\r\n");
	}
	// close the FTP stream
	ftp_close($sConnection_Id);
}
// End of FTP script

echo "\n\nStep8\n\n";
$sBody = "18th Story Script - $iCount Records Created.";
$sSubject = "Run 18th Story: " . date('Y-m-d');
//mail('spatel@amperemedia.com', $sSubject, $sBody);

echo "\n\nStep9\n\n";

$sToday = date('Y')."-".date('m')."-".date('d');
$sCheckQuery = "SELECT *
			FROM nibbles_datafeed.dataSentStats
			WHERE date = '$sToday'
			AND script = '18thStory'";
$rCheckResult = mysql_query($sCheckQuery);
echo mysql_error();

if (mysql_num_rows($rCheckResult) == 0) {
	$sAddQuery = "INSERT INTO nibbles_datafeed.dataSentStats(count, date, script)
					  VALUES('$iCount', \"$sToday\", '18thStory')";
	$rResultAdd = mysql_query($sAddQuery);
	echo mysql_error();
}

echo "\n\nStep10\n\n";
$rResult = mysql_query("TRUNCATE TABLE nibbles_temp.temp18thStoryData");

?>
