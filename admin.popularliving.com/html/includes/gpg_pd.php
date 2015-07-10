<?php

include_once("paths.php");

// get today's leads folder
$sToday = date(Y).date(m).date(d);
$GPG = "/usr/bin/gpg";



	//START OF SD_GEV
	$sJulianDate = (date(z) + 1);

	// julian date must be 3 digit all the time
	if (strlen($sJulianDate) == 1) {
		$sJulianDate = "00".$sJulianDate;	
	}

	if (strlen($sJulianDate) == 2) {
		$sJulianDate = "0".$sJulianDate;	
	}

	
	
	/*
	
	
	
	
	// GET CONTENT FROM SD_GEV
	$sSdGevPath = "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/SD_GEV/MF".date(y).$sJulianDate.".txt";
	$sSdGevSize = filesize($sSdGevPath);
	if ($sSdGevSize != 0) {
		$sSd_GevContent = file_get_contents($sSdGevPath)."\n";
	} else {
		$sSd_GevContent = "";
	}
			
	// GET CONTENT FROM SD_GEV2
	$sSdGev2Path = "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/SD_GEV2/MF".date(y).$sJulianDate.".txt";
	$sSdGev2Size = filesize($sSdGev2Path);
	if ($sSdGev2Size != 0) {
		$sSd_Gev2Content = file_get_contents($sSdGev2Path)."\n";
	} else {
		$sSd_Gev2Content = "";
	}
	
	// GET CONTENT FROM SD_GEV3
	$sSdGev3Path = "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/SD_GEV3/MF".date(y).$sJulianDate.".txt";
	$sSdGev3Size = filesize($sSdGev3Path);
	if ($sSdGev3Size != 0) {
		$sSd_Gev3Content = file_get_contents($sSdGev3Path)."\n";
	} else {
		$sSd_Gev3Content = "";
	}
	
	// GET CONTENT FROM SD_GEV4
	$sSdGev4Path = "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/SD_GEV4/MF".date(y).$sJulianDate.".txt";
	$sSdGev4Size = filesize($sSdGev4Path);
	if ($sSdGev4Size != 0) {
		$sSd_Gev4Content = file_get_contents($sSdGev4Path)."\n";
	} else {
		$sSd_Gev4Content = "";
	}
	
	// write content collected from sd_gev(2,3,4) to sd_gev file
	$sFinalDataSdGev = $sSd_GevContent.$sSd_Gev2Content.$sSd_Gev3Content.$sSd_Gev4Content;
	if ($sFinalDataSdGev != "") {
		$rFile = fopen("/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/SD_GEV/MF".date(y).$sJulianDate."pd.txt","w");
		if ($rFile) {
			$sTemp = fwrite($rFile, $sFinalDataSdGev);
		}
	}



	
	
	
	*/
	
	
	


		$sTodaysLeadsFolder = "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/SD_GEV/";
		$sOpenedDirectory = opendir( $sTodaysLeadsFolder );
	
		$sFile = "";
		$sKey = "internetcontacts@clientlogic.com";
		$sKey2 = "ot@myfree.com";
		$sEncryptedFile = "MF".date(y)."$sJulianDate.txt.pgp";
	
		// read directory and get file
		while( $sSdGevFile = readdir( $sOpenedDirectory ) ) {
			if( $sSdGevFile != '.' && $sSdGevFile != '..' && $sSdGevFile != "$sEncryptedFile" ) {
				$sContents = filesize($sTodaysLeadsFolder.$sSdGevFile);
				$sFile = $sSdGevFile;

				if ($sContents == 0) {
					$sFile = "";
				}
			}
		}
		echo "hello ";
		if ($sFile != "") {
			$sPathAndFile = $sTodaysLeadsFolder.$sFile;
			//$sCommand = "$GPG --compress-algo 1 --cipher-algo cast5 --yes -se -r $sKey2 -se -r $sKey $sPathAndFile";
			//$sCommand = "$GPG -z 1 --cipher-algo cast5 -o $sPathAndFile.pgp -r $sKey -e $sPathAndFile";
			//$rResult = shell_exec( $sCommand );
			$sResult = `/usr/bin/gpg -z 1 --cipher-algo cast5 -o $sPathAndFile.pgp -r $sKey -e $sPathAndFile` ;

			//$sEncryptedFile = rename($sTodaysLeadsFolder."MF".date(y)."$sJulianDate.txt.gpg",$sTodaysLeadsFolder."MF".date(y)."$sJulianDate.txt.pgp");
			//$sEncryptedFile = "MF".date(y)."$sJulianDate.txt.pgp";
			
			$sFtp_Server = "ftp2.clientlogic.com";
			$sFtp_User = "gev_myfree";
			$sFtp_Pass = "M@rk719!";
			$sOfferCode = "SD_GEV";
			$sFtpDir = "/gev_myfree/upload/";
		//	ftpFile($sOfferCode, $sFtp_Server, $sFtpDir, $sFtp_User, $sFtp_Pass, $sEncryptedFile, $sTodaysLeadsFolder);
		}

	// START: SEND SD_GEV COUNT EMAIL- APPROVED
	//if (date('D') =='Mon') {
	//	$iRealTimeDaysBack = "3";
	//} else {
		$iRealTimeDaysBack = "1";
	//}
	
	$sCountQuery = "SELECT count(*) as count
				FROM otDataHistory 
				WHERE (offerCode = 'SD_GEV' 
				OR offerCode = 'SD_GEV2'
				OR offerCode = 'SD_GEV3'
				OR offerCode = 'SD_GEV4')
				AND dateTimeAdded > date_add(CURRENT_DATE, INTERVAL -$iRealTimeDaysBack DAY) 
				AND sendStatus = 's'";
	$rCountResult = dbQuery($sCountQuery);
	$oRow = dbFetchObject($rCountResult);
	$iCount = $oRow->count;
	
	$sTempDate = date(Y)."-".date(m)."-".date(d);
	//$sDeleteQuery = "DELETE FROM offersTracking 
		//		WHERE offerCode = 'SD_GEV'
			//	AND date = \"$sTempDate\"";
	//$rDeleteResult = dbQuery($sDeleteQuery);
	
	
	$sTransQuery = "SELECT max(transmittalNo) as transmittalNo 
				FROM offersTracking 
				WHERE offerCode = 'SD_GEV' 
				LIMIT 1";
	$rTransResult = dbQuery($sTransQuery);
	$oTransRow = dbFetchObject($rTransResult);
	$iTransNo = $oTransRow->transmittalNo + 1;
	
	$sEmailBody = "Filename: "."MF".date(y).$sJulianDate.".txt"."\n";
	$sEmailBody .= "Quantity: $iCount\n";
	$sEmailBody .= "Date: ".date(m)."/".date(d)."/".date(Y)."\n";
	$sEmailBody .= "Transmittal: $iTransNo\n";

	$sDate = date(Y)."-".date(m)."-".date(d);
	
	//$sInsertQuery = "INSERT INTO offersTracking (offerCode, date, count, transmittalNo)
	//				VALUES ('SD_GEV', '$sDate', '$iCount', '$iTransNo')";
	//$rInsertResult = dbQuery($sInsertQuery);
//	$sEmail = "MarisMcI@clientlogic.com,lynetsim@clientlogic.com,djohnsen@singerdirect.com,rbalancia@singerdirect.com,jenniRua@clientlogic.com";
	$sEmail = "pdrazba@amperemedia.com";
	
	$sHeaders = "From: leads@AmpereMedia.com\r\n";
//	$sHeaders .= "Cc: leads@AmpereMedia.com,pschechter@amperemedia.com\r\n";

	$sSubject = "Ampere Media Leads - Gevalia $sDate";
	mail($sEmail, $sSubject, $sEmailBody , $sHeaders);
		// END: SEND SD_GEV COUNT - APPROVED
	// END OF SD_GEV

	
	

?>
