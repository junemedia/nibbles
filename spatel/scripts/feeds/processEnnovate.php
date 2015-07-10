<?php
	
	include( "/home/scripts/includes/cssLogFunctions.php" );
	$iScriptId = cssLogStart( "processEnnovate.php" );
	
	include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
	
	$sYesterday = strftime ("%Y-%m-%d", strtotime("-1 day"));
	$sYesterday = str_replace('/','-',$sYesterday);
	$s31DaysBack = strftime ("%Y-%m-%d", strtotime("-31 day"));
	$s31DaysBack = str_replace('/','-',$s31DaysBack);
	$sFrom = $s31DaysBack." 00:00:00";
	$sTo = $s31DaysBack." 23:59:59";
	
	$sDeleteQuery = "TRUNCATE TABLE nibbles_temp.ennovateTemp";
	$rDeleteResult = mysql_query($sDeleteQuery);

	$sGetData = "SELECT userDataHistory.*, otDataHistory.remoteIp, otDataHistory.sourceCode
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
		$sZip = $sData->zip;
		$sSalutation = $sData->salutation;
		$sDateTimeAdded = $sData->dateTimeAdded;
		$iIp = $sData->remoteIp;
		$sSourceCode = $sData->sourceCode;

		if ($sSalutation == "" || $sSalutation == "Dr." || $sSalutation == "Other") {
			$sGender = "NULL";
		} else if ($sSalutation == "Mr.") {
			$sGender = "Male";
		} else if ($sSalutation == "Mrs." || $sSalutation == "Ms.") {
			$sGender = "Female";
		}
		
		$sInsertQuery = "INSERT IGNORE INTO nibbles_temp.ennovateTemp (email,first,last,zip,gender,dob,dateTimeAdded,site,ip,sourceCode)
					VALUES (\"$sEmail\",\"$sFirst\",\"$sLast\",\"$sZip\",\"$sGender\",'NULL',\"$sDateTimeAdded\",'popularliving.com',\"$iIp\",\"$sSourceCode\")";
		$rInsertResult = mysql_query($sInsertQuery);
	}



	$sGetJoinEmailSubDataQuery = "SELECT * FROM joinEmailSub WHERE dateTimeAdded BETWEEN '$sFrom' AND '$sTo'";
	$rGetJoinEmailSubDataResult = mysql_query($sGetJoinEmailSubDataQuery);
	while ($sJoinEmailSubRow = mysql_fetch_object($rGetJoinEmailSubDataResult)) {
		$sTempDataQuery = "SELECT * FROM userDataHistory WHERE email = \"$sJoinEmailSubRow->email\"";
		$rTempDataQueryResult = mysql_query($sTempDataQuery);
		if (mysql_num_rows($rTempDataQueryResult) > 0) {
				$sTempDataRow = mysql_fetch_object($rTempDataQueryResult);
				$sFirst = $sTempDataRow->first;
				$sLast = $sTempDataRow->last;
				$sZip = $sTempDataRow->zip;
				$sSalutation = $sTempDataRow->salutation;
				$sDateTimeAdded = $sTempDataRow->dateTimeAdded;
				$iIp = $sTempDataRow->remoteIp;
				$sSourceCode = $sTempDataRow->sourceCode;
	
				if ($sSalutation == "" || $sSalutation == "Dr." || $sSalutation == "Other") {
					$sGender = "NULL";
				} else if ($sSalutation == "Mr.") {
					$sGender = "Male";
				} else if ($sSalutation == "Mrs." || $sSalutation == "Ms.") {
					$sGender = "Female";
				}

				$sInsert3Query = "INSERT IGNORE INTO nibbles_temp.ennovateTemp (email,first,last,zip,gender,dob,dateTimeAdded,site,ip,sourceCode)
							VALUES (\"$sJoinEmailSubRow->email\",\"$sFirst\",\"$sLast\",\"$sZip\",\"$sGender\",'NULL',\"$sDateTimeAdded\",'popularliving.com',\"$sJoinEmailSubRow->remoteIp\",\"$sJoinEmailSubRow->sourceCode\")";
				$rInsert3Result = mysql_query($sInsert3Query);
		} else {
				$sInsert3Query = "INSERT IGNORE INTO nibbles_temp.ennovateTemp (email,first,last,zip,gender,dob,dateTimeAdded,site,ip,sourceCode)
							VALUES (\"$sJoinEmailSubRow->email\",\"NULL\",\"NULL\",\"NULL\",\"NULL\",'NULL',\"$sJoinEmailSubRow->dateTimeAdded\",'popularliving.com',\"$sJoinEmailSubRow->remoteIp\",\"$sJoinEmailSubRow->sourceCode\")";
				$rInsert3Result = mysql_query($sInsert3Query);
		}
	}
	
	
	// start delete
	$sGetQuery = "select TLDs FROM excludeTLDsDataSales";
	$rGetResult = mysql_query($sGetQuery);
	while ($rRow = mysql_fetch_object($rGetResult)) {
		$sDelete = "DELETE FROM nibbles_temp.ennovateTemp WHERE email LIKE '%$rRow->TLDs'";
		$rDelete = mysql_query($sDelete);
	}
	
	$sGetQuery = "select domain from excludeDomainsDataSales";
	$rResult2 = mysql_query($sGetQuery);
	while ($rRow2 = mysql_fetch_object($rResult2)) {
		$sDelete = "DELETE FROM nibbles_temp.ennovateTemp WHERE email LIKE '%$rRow2->domain'";
		$rDelete = mysql_query($sDelete);
	}

	$sGetQuery = "select email from excludeEmailDataSales";
	$rResult3 = mysql_query($sGetQuery);
	while ($rRow3 = mysql_fetch_object($rResult3)) {
		$sDelete = "DELETE FROM nibbles_temp.ennovateTemp WHERE email = '$rRow3->email'";
		$rDelete = mysql_query($sDelete);
	}
	
	$sGetQuery = "select distinct sourceCode
		from links, partnerCompanies
		where links.partnerId = partnerCompanies.id
		AND excludeDataSale = '1'";
	$rResult4 = mysql_query($sGetQuery);
	while ($rRow4 = mysql_fetch_object($rResult4)) {
		$sDelete = "DELETE FROM nibbles_temp.ennovateTemp WHERE sourceCode = '$rRow4->sourceCode'";
		$rDelete = mysql_query($sDelete);
	}
	// end delete
	
	
	$sExportData = "EMAIL|FNAME|LNAME|ZIP|GENDER|DOB|JOINDATE|SOURCEURL|SOURCEIP\r\n";
	$iCount = 0;

	$sGetData = "SELECT * FROM nibbles_temp.ennovateTemp";
	$rGetData = mysql_query($sGetData);
	while ($sFinalData = mysql_fetch_object($rGetData)) {
		$sExportData .= "$sFinalData->email|$sFinalData->first|$sFinalData->last|$sFinalData->zip|$sFinalData->gender|$sFinalData->dob|$sFinalData->dateTimeAdded|$sFinalData->site|$sFinalData->ip\r\n";
		$iCount++;
	}

	
	$rFile = fopen("/home/ennovate/Ampere-".$sYesterday.".txt","w");
	if ($rFile) {
		$sTemp = fwrite($rFile, $sExportData);
	}


	
	// Start of FTP script
	$sFile = "Ampere-".$sYesterday.".txt";
	
	// set up basic connection
	$sFtp_User = "ampere";
	$sFtp_Pass = "ampl33tyeah";
	$sFtp_Server = "mx7.mgrav.com";
	$sConnection_Id = ftp_connect($sFtp_Server);

	// login with username and password
	$sLoginResult = ftp_login($sConnection_Id, $sFtp_User, $sFtp_Pass);
	
	// turn off passive mode so active mode will be turned on
	ftp_pasv($sConnection_Id, false);

	// check connection
	if (!$sConnection_Id) {
		$sEmailMessage = "FTP connection has failed!\n\n";
		$sEmailMessage .= "Attempted to connect to $sFtp_Server for user $sFtp_User\n\n";
		mail('it@amperemedia.com', 'processEnnovate FTP Failed', $sEmailMessage , "From: spatel@amperemedia.com\r\n");
	} else {
		// upload a file
		if (ftp_put($sConnection_Id, "$sFile", "/home/ennovate/"."$sFile", FTP_ASCII)) {
			echo "successfully uploaded $sFile\n";
		} else {
			$sEmailMessage = "There was a problem while uploading $sFile\n";
			mail('it@amperemedia.com', 'processEnnovate FTP Failed', $sEmailMessage , "From: spatel@amperemedia.com\r\n");
		}
		// close the FTP stream
		ftp_close($sConnection_Id);
	}
	// End of FTP script
	

	$sDeleteQuery = "TRUNCATE TABLE nibbles_temp.ennovateTemp";
	$rDeleteResult = mysql_query($sDeleteQuery);
	
	
	
	$sToday = date(Y)."-".date(m)."-".date(d);
	$sCheckQuery = "SELECT *
				FROM nibbles_datafeed.dataSentStats
				WHERE date = '$sToday'
				AND script = 'ennovate'";
	$rCheckResult = mysql_query($sCheckQuery);
	echo mysql_error();

	if (mysql_num_rows($rCheckResult) == 0) {
		$sAddQuery = "INSERT INTO nibbles_datafeed.dataSentStats(count, date, script)
						  VALUES('$iCount', \"$sToday\", 'ennovate')";
		$rResultAdd = mysql_query($sAddQuery);
		echo mysql_error();
	}

	cssLogFinish( $iScriptId );
	
?>
