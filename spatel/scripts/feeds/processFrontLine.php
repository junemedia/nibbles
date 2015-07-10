<?php


include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
$sYesterday = strftime ("%Y-%m-%d", strtotime("-1 day"));
$sYesterday = str_replace('/','-',$sYesterday);
$s15DaysBack = strftime ("%Y-%m-%d", strtotime("-15 day"));
$s15DaysBack = str_replace('/','-',$s15DaysBack);
$sFrom = $s15DaysBack." 00:00:00";
$sTo = $s15DaysBack." 23:59:59";


$sGetData = "SELECT userDataHistory.*, otDataHistory.remoteIp as tempRemoteIp, otDataHistory.sourceCode
			FROM userDataHistory, otDataHistory 
			WHERE userDataHistory.dateTimeAdded BETWEEN '$sFrom' AND '$sTo'
			AND userDataHistory.email = otDataHistory.email
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
	$sGender = $sData->gender;
	$sDateOfBirth = $sData->dateOfBirth;
	$sPhone = $sData->phoneNo;
	$sRemoteIp = $sData->tempRemoteIp;
	$sDateTimeAdded = $sData->dateTimeAdded;
	$sSourceCode = $sData->sourceCode;

	$sInsertQuery = "INSERT IGNORE INTO nibbles_temp.tempFrontLine (email,first,last,address,address2,city,state,zip, 
			gender,dob,phoneNo,remoteIp,dateTimeAdded,sourceCode)
		VALUES (\"$sEmail\",\"$sFirst\",\"$sLast\",\"$sAddress\",\"$sAddress2\", 
		\"$sCity\",\"$sState\",\"$sZip\",'$sGender',\"$sDateOfBirth\",
		\"$sPhone\",\"$sRemoteIp\",\"$sDateTimeAdded\",\"$sSourceCode\")";
	$rInsertResult = mysql_query($sInsertQuery);
}



$sGetJoinEmailSubDataQuery = "SELECT * FROM joinEmailSub WHERE dateTimeAdded BETWEEN '$sFrom' AND '$sTo'";
$rGetJoinEmailSubDataResult = mysql_query($sGetJoinEmailSubDataQuery);
while ($sJoinEmailSubRow = mysql_fetch_object($rGetJoinEmailSubDataResult)) {
	$sEmail = $sJoinEmailSubRow->email;
	$sSourceCode = $sJoinEmailSubRow->sourceCode;
	$sRemoteIp = $sJoinEmailSubRow->remoteIp;
	$sDateTimeAdded = $sJoinEmailSubRow->dateTimeAdded;
	
	$sTempDataQuery = "SELECT * FROM userDataHistory WHERE email = \"$sEmail\"";
	$rTempDataQueryResult = mysql_query($sTempDataQuery);
	if (mysql_num_rows($rTempDataQueryResult) > 0) {
		$sTempDataRow = mysql_fetch_object($rTempDataQueryResult);
		$sInsertQuery = "INSERT IGNORE INTO nibbles_temp.tempFrontLine (email,first,last,address,address2,city,state,zip, 
						gender,dob,phoneNo,remoteIp,dateTimeAdded,sourceCode)
					VALUES (\"$sEmail\",\"$sTempDataRow->first\",\"$sTempDataRow->last\",\"$sTempDataRow->address\",\"$sTempDataRow->address2\", 
					\"$sTempDataRow->city\",\"$sTempDataRow->state\",\"$sTempDataRow->zip\",'$sTempDataRow->gender',\"$sTempDataRow->dateOfBirth\",
					\"$sTempDataRow->phoneNo\",\"$sRemoteIp\",\"$sTempDataRow->dateTimeAdded\",\"$sSourceCode\")";
		$rInsertResult = mysql_query($sInsertQuery);
	} else {
		$sInsertQuery = "INSERT IGNORE INTO nibbles_temp.tempFrontLine (email,remoteIp,dateTimeAdded,sourceCode)
					VALUES (\"$sEmail\",\"$sRemoteIp\",\"$sDateTimeAdded\",\"$sSourceCode\")";
		$rInsertResult = mysql_query($sInsertQuery);
	}
}



// start delete
// IP and EMAIL are required so do not send any record with blank email or ip, so delete them 
// before we create the file.
$sDelete = "DELETE FROM nibbles_temp.tempFrontLine WHERE remoteIp=''";
$rDelete = mysql_query($sDelete);

$sDelete = "DELETE FROM nibbles_temp.tempFrontLine WHERE email=''";
$rDelete = mysql_query($sDelete);

$sUpdate = "UPDATE nibbles_temp.tempFrontLine SET dob='' WHERE dob='0000-00-00'";
$rUpdate = mysql_query($sUpdate);


$sGetQuery = "select TLDs FROM excludeTLDsDataSales";
$rGetResult = mysql_query($sGetQuery);
while ($rRow = mysql_fetch_object($rGetResult)) {
	$sDelete = "DELETE FROM nibbles_temp.tempFrontLine WHERE email LIKE '%$rRow->TLDs'";
	$rDelete = mysql_query($sDelete);
}

$sGetQuery = "select domain from excludeDomainsDataSales";
$rResult2 = mysql_query($sGetQuery);
while ($rRow2 = mysql_fetch_object($rResult2)) {
	$sDelete = "DELETE FROM nibbles_temp.tempFrontLine WHERE email LIKE '%$rRow2->domain'";
	$rDelete = mysql_query($sDelete);
}

$sGetQuery = "select email from excludeEmailDataSales";
$rResult3 = mysql_query($sGetQuery);
while ($rRow3 = mysql_fetch_object($rResult3)) {
	$sDelete = "DELETE FROM nibbles_temp.tempFrontLine WHERE email = '$rRow3->email'";
	$rDelete = mysql_query($sDelete);
}

$sGetQuery = "select distinct sourceCode
	from links, partnerCompanies
	where links.partnerId = partnerCompanies.id
	AND excludeDataSale = '1'";
$rResult4 = mysql_query($sGetQuery);
while ($rRow4 = mysql_fetch_object($rResult4)) {
	$sDelete = "DELETE FROM nibbles_temp.tempFrontLine WHERE sourceCode = '$rRow4->sourceCode'";
	$rDelete = mysql_query($sDelete);
}
// end delete


$iCount = 0;
$rFile = fopen("/home/frontLine/Ampere-".$sYesterday.".txt","w");
if ($rFile) {
	$sGetData = "SELECT * FROM nibbles_temp.tempFrontLine";
	$rGetData = mysql_query($sGetData);
	while ($sFinalData = mysql_fetch_object($rGetData)) {
		$sExportData = '"'.$sFinalData->email.'",';
		$sExportData .= '"'.$sFinalData->first.'",';
		$sExportData .= '"'.$sFinalData->last.'",';
		$sExportData .= '"'.$sFinalData->address.'",';
		$sExportData .= '"'.$sFinalData->address2.'",';
		$sExportData .= '"'.$sFinalData->city.'",';
		$sExportData .= '"'.$sFinalData->state.'",';
		$sExportData .= '"'.$sFinalData->zip.'",';
		$sExportData .= '"'.$sFinalData->gender.'",';
		$sExportData .= '"'.$sFinalData->dob.'",';
		$sExportData .= '"'.$sFinalData->phoneNo.'",';
		$sExportData .= '"'.''.'",';
		$sExportData .= '"'.$sFinalData->remoteIp.'",';
		$sExportData .= '"'.$sFinalData->dateTimeAdded.'",';
		$sExportData .= '"'."popularliving.com".'"';
		$sExportData .= "\r\n";
		$sTemp = fwrite($rFile, $sExportData);
		$iCount++;
	}
}

// Start of FTP script
$sFile = "Ampere-".$sYesterday.".txt";

// set up basic connection
$sFtp_User = "a1539_ampere";
$sFtp_Pass = "amp321";
$sFtp_Server = "frontlinedirectinc.com";
$sConnection_Id = ftp_connect($sFtp_Server);

// login with username and password
$sLoginResult = ftp_login($sConnection_Id, $sFtp_User, $sFtp_Pass);
// turn off passive mode so active mode will be turned on
ftp_pasv($sConnection_Id, false);

// check connection
if (!$sConnection_Id) {
	$sEmailMessage = "FTP connection has failed!\n\n";
	$sEmailMessage .= "Attempted to connect to $sFtp_Server for user $sFtp_User\n\n";
	mail('it@amperemedia.com', 'processFrontLine FTP Failed', $sEmailMessage , "From: spatel@amperemedia.com\r\n");
} else {
	// upload a file
	if (ftp_put($sConnection_Id, "$sFile", "/home/frontLine/"."$sFile", FTP_ASCII)) {
		echo "successfully uploaded $sFile\n";
	} else {
		$sEmailMessage = "There was a problem while uploading $sFile\n";
		mail('it@amperemedia.com', 'processFrontLine FTP Failed', $sEmailMessage , "From: spatel@amperemedia.com\r\n");
	}
	// close the FTP stream
	ftp_close($sConnection_Id);
}
// End of FTP script


$sDeleteQuery = "TRUNCATE TABLE nibbles_temp.tempFrontLine";
$rDeleteResult = mysql_query($sDeleteQuery);
if (!$rDeleteResult) {
	$rDeleteResult = mysql_query($sDeleteQuery);
}


$sToday = date('Y')."-".date('m')."-".date('d');
$sCheckQuery = "SELECT *
			FROM nibbles_datafeed.dataSentStats
			WHERE date = '$sToday'
			AND script = 'frontLine'";
$rCheckResult = mysql_query($sCheckQuery);
echo mysql_error();

if (mysql_num_rows($rCheckResult) == 0) {
	$sAddQuery = "INSERT INTO nibbles_datafeed.dataSentStats(count, date, script)
					  VALUES('$iCount', \"$sToday\", 'frontLine')";
	$rResultAdd = mysql_query($sAddQuery);
	echo mysql_error();
}

?>
