<?php

include("paths.php");

// get today's leads folder
$sToday = date(Y).date(m).date(d);
$GPG = "/usr/bin/gpg";


		// START OF BMG - APPROVED
		$sTodaysLeadsFolder = "/home/sites/www_popularliving_com/html/admin/leads/$sToday/offers/BMG/";
		$sOpenedDirectory = opendir( $sTodaysLeadsFolder );

		$sFile = "";
		$sKey = "BASIDEV@cauto.com";
		$sEncryptedFile = "dmbe12".date(y).date(m).date(d)."2.asc";
		// read directory and get file
		while( $sBmgFile = readdir( $sOpenedDirectory ) ) {
			if( $sBmgFile != '.' && $sBmgFile != '..' && $sBmgFile != "$sEncryptedFile" ) {
				$sContents = filesize($sTodaysLeadsFolder.$sBmgFile);
				$sFile = $sBmgFile;

				
			if ($sContents != 0) {
				$iCount = 1;
				$rFile = fopen("$sTodaysLeadsFolder/$sFile","r");
				if ($rFile) {
					while (!feof($rFile)) {
						$aTemp['lines'][$iCount] = fgets($rFile,1024);
						$iCount ++;
					}
				}
				
				$iArrayCount = count($aTemp['lines']);
				$i = 1;
				//$sFinalData = "HU010X".date(d)."00\r\nHB390".$i."00115          NN".date(Y)."-".date(m)."-".date(d)."N\n";
				for ($iTemp = 1; $iTemp<$iArrayCount; $iTemp++) {
					$sFinalData .= $aTemp['lines'][$iTemp];
					
					if ( $iTemp == 500 || $iTemp == 1000 || $iTemp == 1500 || $iTemp == 2000 || $iTemp == 2500 || $iTemp == 3000 || $iTemp == 3500 || $iTemp == 4000 || $iTemp == 4500 || $iTemp == 5000 ) {
						$i++;
						$sFinalData .= "HU010X".date(d)."00\r\nHB390".$i."00115          NN".date(Y)."-".date(m)."-".date(d)."N\n";
					}
				}

				if ($sFinalData != "") {
					/*$rFile = fopen("$sTodaysLeadsFolder/$sFile","w");
					if ($rFile) {
						$sTemp = fwrite($rFile, $sFinalData);
					}*/
				}
			} else {
					$sFile = "";
				}
			}
		}
		
		if ($sFile != "") {
			$sPathAndFile = $sTodaysLeadsFolder.$sFile;
			$sCommand = "$GPG --compress-algo 1 --cipher-algo cast5 --yes -a -r $sKey -e $sPathAndFile";
			$rResult = shell_exec( $sCommand );

			$sFtp_Server = "ftp.cauto.com";
			$sFtp_User = "DMBE12";
			$sFtp_Pass = "Grapes9";
			$sOfferCode = "BMG";
			$sFtpDir = "/BMG_email/dmbe12/To_Creative/";
			
			$sEncryptedFile = rename($sTodaysLeadsFolder."dmbe12".date(y).date(m).date(d)."2.asc",$sTodaysLeadsFolder."dmbe12".date(y).date(m).date(d)."2.pgp");
			$sEncryptedFile = "dmbe12".date(y).date(m).date(d)."2.pgp";
			
			ftpFile($sOfferCode, $sFtp_Server, $sFtpDir, $sFtp_User, $sFtp_Pass, $sEncryptedFile, $sTodaysLeadsFolder);
		}		//end of BMG
		// END OF BMG

		
/*
		// START OF AHA_PDA - APPROVED
		$sTodaysLeadsFolder = "/home/sites/www_popularliving_com/html/admin/leads/$sToday/offers/AHA_PDA/";
		$sOpenedDirectory = opendir( $sTodaysLeadsFolder );
		$sFile = "";
		$sKey = "JSackin@ahahome.com";
		$sEncryptedFile = "MY_B_0_12.".date(Y).date(m).date(d).".pgp";
		//MY_B_0_12.[yyyy][mm][dd]
		
		// read directory and get file
		while( $sAhaPdaFile = readdir( $sOpenedDirectory ) ) {
			if( $sAhaPdaFile != '.' && $sAhaPdaFile != '..' && $sAhaPdaFile != "$sEncryptedFile" ) {
				$sContents = filesize($sTodaysLeadsFolder.$sAhaPdaFile);
				$sFile = $sAhaPdaFile;
				if ($sContents == 0) {
					$sFile = "";
				}
			}
		}
		
		if ($sFile != "") {
			$sPathAndFile = $sTodaysLeadsFolder.$sFile;
	
			//  $gpg --armor --recipient username --encrypt filename
			$sCommand = "$GPG --compress-algo 1 --cipher-algo cast5 --yes -r $sKey -e $sPathAndFile";
			$rResult = shell_exec( $sCommand );
			$sEncryptedFile = rename($sTodaysLeadsFolder."MY_B_0_12.".date(Y).date(m).date(d).".gpg",$sTodaysLeadsFolder."MY_B_0_12.".date(Y).date(m).date(d).".pgp");
			$sEncryptedFile = "MY_B_0_12.".date(Y).date(m).date(d).".pgp";
			$sEmail = "batchprocess@ahahome.com";
			$sSubject = "PDA file - ".date(m)."/".date(d)."/".date(y);
			email($sEmail, $sSubject, $sEncryptedFile, $sTodaysLeadsFolder);
		}	//end of AHA_PDA
		// END OF AHA_PDA



		// START OF AHA_ROBO - APPROVED
		$sTodaysLeadsFolder = "/home/sites/www_popularliving_com/html/admin/leads/$sToday/offers/AHA_ROBO/";
		$sOpenedDirectory = opendir( $sTodaysLeadsFolder );
		$sFile = "";
		$sKey = "JSackin@ahahome.com";
		$sEncryptedFile = "AE_b_0_33.".date(Y).date(m).date(d).".pgp";
		//AE_b_0_33.20051007
		
		// read directory and get file
		while( $sAhaRoboFile = readdir( $sOpenedDirectory ) ) {
			if( $sAhaRoboFile != '.' && $sAhaRoboFile != '..' && $sAhaRoboFile != "$sEncryptedFile" ) {
				$sContents = filesize($sTodaysLeadsFolder.$sAhaRoboFile);
				$sFile = $sAhaRoboFile;
				
				if ($sContents == 0) {
					$sFile = "";
				}
			}
		}
		
		if ($sFile != "") {
			$sPathAndFile = $sTodaysLeadsFolder.$sFile;
	
			//  $gpg --armor --recipient username --encrypt filename
			$sCommand = "$GPG --compress-algo 1 --cipher-algo cast5 --yes -r $sKey -e $sPathAndFile";
			$rResult = shell_exec( $sCommand );
			$sEncryptedFile = rename($sTodaysLeadsFolder."AE_b_0_33.".date(Y).date(m).date(d).".gpg",$sTodaysLeadsFolder."AE_b_0_33.".date(Y).date(m).date(d).".pgp");
			$sEncryptedFile = "AE_b_0_33.".date(Y).date(m).date(d).".pgp";
			$sEmail = "batchprocess@ahahome.com";
			$sSubject = "Robosweep file - ".date(m)."/".date(d)."/".date(y);
			email($sEmail, $sSubject, $sEncryptedFile, $sTodaysLeadsFolder);
		}	//end of AHA_ROBO
		// START OF AHA_ROBO



		// START OF AHA_CAM - APPROVED
		$sTodaysLeadsFolder = "/home/sites/www_popularliving_com/html/admin/leads/$sToday/offers/AHA_CAM/";
		$sOpenedDirectory = opendir( $sTodaysLeadsFolder );
		$sFile = "";
		$sKey = "JSackin@ahahome.com";
		$sEncryptedFile = "MY_B_0_12.".date(Y).date(m).date(d)."2.pgp";
		//MY_B_0_12.200510102
		
		// read directory and get file
		while( $sAhaCamFile = readdir( $sOpenedDirectory ) ) {
			if( $sAhaCamFile != '.' && $sAhaCamFile != '..' && $sAhaCamFile != "$sEncryptedFile" ) {
				$sContents = filesize($sTodaysLeadsFolder.$sAhaCamFile);
				$sFile = $sAhaCamFile;
				
				if ($sContents == 0) {
					$sFile = "";
				}
			}
		}
		
		if ($sFile != "") {
		$sPathAndFile = $sTodaysLeadsFolder.$sFile;

		//  $gpg --armor --recipient username --encrypt filename
		$sCommand = "$GPG --compress-algo 1 --cipher-algo cast5 --yes -r $sKey -e $sPathAndFile";
		$rResult = shell_exec( $sCommand );
		$sEncryptedFile = rename($sTodaysLeadsFolder."MY_B_0_12.".date(Y).date(m).date(d)."2.gpg",$sTodaysLeadsFolder."MY_B_0_12.".date(Y).date(m).date(d)."2.pgp");
		$sEncryptedFile = "MY_B_0_12.".date(Y).date(m).date(d)."2.pgp";
		$sEmail = "batchprocess@ahahome.com";
		$sSubject = "Camera file - ".date(m)."/".date(d)."/".date(y);
		email($sEmail, $sSubject, $sEncryptedFile, $sTodaysLeadsFolder);
		}	//end of AHA_CAM
		// END OF AHA_CAM



	// START OF KDN_INV - APPROVED
	if (date('D') =='Mon') {
		$iRealTimeDaysBack = "3";
	} else {
		$iRealTimeDaysBack = "1";
	}
	
	$sCountQuery = "SELECT count(*) as count
				FROM otDataHistory 
				WHERE offerCode = 'KDN_INV' 
				AND dateTimeAdded > date_add(CURRENT_DATE, INTERVAL -$iRealTimeDaysBack DAY) 
				AND sendStatus = 's'";
	$rCountResult = dbQuery($sCountQuery);
	$oRow = dbFetchObject($rCountResult);
	$sKdnCount = $oRow->count;
	
	$sHeaders  = "MIME-Version: 1.0\r\n";
	$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$sHeaders .= "From:leads@AmpereMedia.com\r\n";
	$sHeaders .= "Cc:leads@AmpereMedia.com\r\n";
	
	$sSubject = "Ampere Media - KDN_INV, $sKdnCount ".date(m)."-".date(d)."-".date(Y);
	$sEmailBody = "KDN_INV - $sKdnCount";
	mail("fred@amperemedia.com, kkousins@amperemedia.com, rizzo.michael@davison54.com", $sSubject, $sEmailBody, $sHeaders);
	// END OF KDN_INV

*/

	//START OF SD_GEV
	$sJulianDate = (date(z) + 1);

	// julian date must be 3 digit all the time
	if (strlen($sJulianDate) == 1) {
		$sJulianDate = "00".$sJulianDate;	
	}

	if (strlen($sJulianDate) == 2) {
		$sJulianDate = "0".$sJulianDate;	
	}

	// GET CONTENT FROM SD_GEV
	$sSdGevPath = "/home/sites/www_popularliving_com/html/admin/leads/$sToday/offers/SD_GEV/MF".date(y).$sJulianDate.".txt";
	$sSdGevSize = filesize($sSdGevPath);
	if ($sSdGevSize != 0) {
		$sSd_GevContent = file_get_contents($sSdGevPath)."\n";
	} else {
		$sSd_GevContent = "";
	}
			
	// GET CONTENT FROM SD_GEV2
	$sSdGev2Path = "/home/sites/www_popularliving_com/html/admin/leads/$sToday/offers/SD_GEV2/MF".date(y).$sJulianDate.".txt";
	$sSdGev2Size = filesize($sSdGev2Path);
	if ($sSdGev2Size != 0) {
		$sSd_Gev2Content = file_get_contents($sSdGev2Path)."\n";
	} else {
		$sSd_Gev2Content = "";
	}
	
	// GET CONTENT FROM SD_GEV3
	$sSdGev3Path = "/home/sites/www_popularliving_com/html/admin/leads/$sToday/offers/SD_GEV3/MF".date(y).$sJulianDate.".txt";
	$sSdGev3Size = filesize($sSdGev3Path);
	if ($sSdGev3Size != 0) {
		$sSd_Gev3Content = file_get_contents($sSdGev3Path)."\n";
	} else {
		$sSd_Gev3Content = "";
	}
	
	// GET CONTENT FROM SD_GEV4
	$sSdGev4Path = "/home/sites/www_popularliving_com/html/admin/leads/$sToday/offers/SD_GEV4/MF".date(y).$sJulianDate.".txt";
	$sSdGev4Size = filesize($sSdGev4Path);
	if ($sSdGev4Size != 0) {
		$sSd_Gev4Content = file_get_contents($sSdGev4Path)."\n";
	} else {
		$sSd_Gev4Content = "";
	}
	
	// write content collected from sd_gev(2,3,4) to sd_gev file
	$sFinalDataSdGev = $sSd_GevContent.$sSd_Gev2Content.$sSd_Gev3Content.$sSd_Gev4Content;
	if ($sFinalDataSdGev != "") {
		$rFile = fopen("/home/sites/www_popularliving_com/html/admin/leads/$sToday/offers/SD_GEV/MF".date(y).$sJulianDate.".txt","w");
		if ($rFile) {
			$sTemp = fwrite($rFile, $sFinalDataSdGev);
		}
	}

		$sTodaysLeadsFolder = "/home/sites/www_popularliving_com/html/admin/leads/$sToday/offers/SD_GEV/";
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
		
		if ($sFile != "") {
			$sPathAndFile = $sTodaysLeadsFolder.$sFile;
			$sCommand = "$GPG --compress-algo 1 --cipher-algo cast5 --yes -r $sKey -e $sPathAndFile";
			$rResult = shell_exec( $sCommand );

			$sEncryptedFile = rename($sTodaysLeadsFolder."MF".date(y)."$sJulianDate.txt.gpg",$sTodaysLeadsFolder."MF".date(y)."$sJulianDate.txt.pgp");
			$sEncryptedFile = "MF".date(y)."$sJulianDate.txt.pgp";
			
			$sFtp_Server = "ftp2.clientlogic.com";
			$sFtp_User = "gev_myfree";
			$sFtp_Pass = "M@rk719!";
			$sOfferCode = "SD_GEV";
			$sFtpDir = "/gev_myfree/upload/";
		//	ftpFile($sOfferCode, $sFtp_Server, $sFtpDir, $sFtp_User, $sFtp_Pass, $sEncryptedFile, $sTodaysLeadsFolder);
		}
/*
	// START: SEND SD_GEV COUNT EMAIL- APPROVED
	if (date('D') =='Mon') {
		$iRealTimeDaysBack = "3";
	} else {
		$iRealTimeDaysBack = "1";
	}
	
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
	$sDeleteQuery = "DELETE FROM offersTracking 
				WHERE offerCode = 'SD_GEV'
				AND date = \"$sTempDate\"";
	$rDeleteResult = dbQuery($sDeleteQuery);
	
	
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
	
	$sInsertQuery = "INSERT INTO offersTracking (offerCode, date, count, transmittalNo)
					VALUES ('SD_GEV', '$sDate', '$iCount', '$iTransNo')";
	$rInsertResult = dbQuery($sInsertQuery);
	$sEmail = "MarisMcI@clientlogic.com,BrigeRos@clientlogic.com,Patricha@clientlogic.com,nribbeklint@singerdirect.com";
	
	$sHeaders = "From: leads@AmpereMedia.com\r\n";
	$sHeaders .= "Cc: leads@AmpereMedia.com,pschechter@amperemedia.com\r\n";
	
	$sSubject = "Ampere Media Leads - Gevalia $sDate";
	mail($sEmail, $sSubject, $sEmailBody , $sHeaders);
		// END: SEND SD_GEV COUNT - APPROVED
	// END OF SD_GEV
*/

	//START EMAIL FUNCTION
function email($sEmail, $sSubject, $sEncryptedFile, $sTodaysLeadsFolder) {
	/*********  Send lead email with attaching file  **********/
	//$sTest = $sTodaysLeadsFolder.$sEncryptedFile;
	$sHeaders = '';
	$sEmailMessage = '';
	$sLeadFileData = '';
	$sBorderRandom = md5(time());
	$sMailBoundry = "==x{$sBorderRandom}x";
	$sHeaders="From: leads@AmpereMedia.com\r\n";
	$sHeaders.="Cc: leads@AmpereMedia.com\r\n";
	$sHeaders.="Reply-To: leads@AmpereMedia.com\r\n";
	$sHeaders.="X-Priority: 1\r\n";
	$sHeaders.="X-MSMail-Priority: High\r\n";
	$sHeaders.="X-Mailer: My PHP Mailer\r\n";
	$sHeaders.="Content-Type: multipart/mixed;\n\tboundary=\"{$sMailBoundry}\"\t\r\n";
	$sHeaders.="Content-Disposition: attachment;\n\t filename=\"{$sEncryptedFile}\"\r\n\r\n";
	$sHeaders.="MIME-Version: 1.0\r\n";

	$rFpLeadFile = fopen("$sTodaysLeadsFolder/$sEncryptedFile","r");
	if ($rFpLeadFile) {
		while (!feof($rFpLeadFile)) {
			$sLeadFileData .= fread($rFpLeadFile, 1024);
		}
		$sLeadFileData = base64_encode($sLeadFileData);
		$sLeadFileData = chunk_split($sLeadFileData);

		$sEmailMessage .= "--{$sMailBoundry}\r\n";
		$sEmailMessage .= "Content-type: text/plain;  name=\"{$sEncryptedFile}\"\r\n";
		$sEmailMessage .= "Content-Transfer-Encoding:base64\r\n";
		$sEmailMessage .= "Content-Disposition: attachment;\n\t filename=\"{$sEncryptedFile}\"\r\n\r\n";
		$sEmailMessage .= "$sLeadFileData\n";
		fclose($rFpLeadFile);
	}
	
	mail($sEmail, $sSubject, $sEmailMessage , $sHeaders);
	/**********  End of sending email with attaching the file  *********/
}
	//END EMAIL FUNCTION








// FTP FUNTION STARTS
function ftpFile($sOfferCode, $sFtp_Server, $sFtpDir, $sFtp_User, $sFtp_Pass, $sEncryptedFile, $sTodaysLeadsFolder) {
	// set up basic connection
	$sConnection_Id = ftp_connect($sFtp_Server);

	// login with username and password
	$login_result = ftp_login($sConnection_Id, $sFtp_User, $sFtp_Pass);

	// check connection
	if (!$sConnection_Id) {
		$sEmailMessage = "FTP connection has failed!\n\n";
		$sEmailMessage .= "Attempted to connect to $sFtp_Server for user $sFtp_User\n\n";
		mail('spatel@amperemedia.com', $sOfferCode, $sEmailMessage , "From: leads@AmpereMedia.com\r\n");
	} else {
		// upload a file
		if (ftp_put($sConnection_Id, "$sFtpDir"."$sEncryptedFile", "$sTodaysLeadsFolder"."$sEncryptedFile", FTP_ASCII)) {
			echo "successfully uploaded $sEncryptedFile\n";
		} else {
			$sEmailMessage = "There was a problem while uploading $sEncryptedFile\n";
			mail('spatel@amperemedia.com', $sOfferCode, $sEmailMessage , "From: leads@AmpereMedia.com\r\n");
		}
		// close the FTP stream
		ftp_close($sConnection_Id);
	}
}	// END OF FTP FUNCTION


?>
