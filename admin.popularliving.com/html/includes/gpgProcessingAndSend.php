<?php

include_once("paths.php");

// get today's leads folder
$sToday = date(Y).date(m).date(d);
$GPG = "/usr/bin/gpg";


		// START OF BMG - APPROVED
		$sTodaysLeadsFolder = "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/BMG/";
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
					$sFinalData = "HU010X".date(d)."00\r\nHB390".$i."00115          NN".date(Y)."-".date(m)."-".date(d)."N\r\n";
					for ($iTemp = 1; $iTemp<$iArrayCount; $iTemp++) {
						$sFinalData .= $aTemp['lines'][$iTemp];
						
						if ( $iTemp == 500 || $iTemp == 1000 || $iTemp == 1500 || $iTemp == 2000 || $iTemp == 2500 || $iTemp == 3000 || $iTemp == 3500 || $iTemp == 4000 || $iTemp == 4500 || $iTemp == 5000 ) {
							$i++;
							$sFinalData .= "HU010X".date(d)."00\r\nHB390".$i."00115          NN".date(Y)."-".date(m)."-".date(d)."N\r\n";
						}
					}

					if ($sFinalData != "") {
						$rFile = fopen("$sTodaysLeadsFolder/$sFile","w");
						if ($rFile) {
							$sTemp = fwrite($rFile, $sFinalData);
						}
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
		}
		// END OF BMG


		// START OF DSC_BMG
		$sTodaysLeadsFolder = "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/DSC_BMG/";
		$sOpenedDirectory = opendir( $sTodaysLeadsFolder );

		$sFile = "";
		$sBmgFile = '';
		unset($aTemp);
		$aTemp = array();
		$sFinalData = '';
		$sKey = "BASIDEV@cauto.com";
		$sEncryptedFile = "dmbe25".date(y).date(m).date(d)."Ampere.asc";
		// read directory and get file
		while( $sBmgFile = readdir( $sOpenedDirectory ) ) {
			if ( $sBmgFile != '.' && $sBmgFile != '..' && $sBmgFile != "$sEncryptedFile" ) {
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
					$sFinalData = "HU010X".date(d)."00\r\nHB390".$i."00115          NN".date(Y)."-".date(m)."-".date(d)."N\r\n";
					for ($iTemp = 1; $iTemp<$iArrayCount; $iTemp++) {
						$sFinalData .= $aTemp['lines'][$iTemp];
						if ( $iTemp == 500 || $iTemp == 1000 || $iTemp == 1500 || $iTemp == 2000 || $iTemp == 2500 || $iTemp == 3000 || $iTemp == 3500 || $iTemp == 4000 || $iTemp == 4500 || $iTemp == 5000 ) {
							$i++;
							$sFinalData .= "HU010X".date(d)."00\r\nHB390".$i."00115          NN".date(Y)."-".date(m)."-".date(d)."N\r\n";
						}
					}
				}

				if ($sFinalData != "") {
					$rFile = fopen("$sTodaysLeadsFolder/$sFile","w");
					if ($rFile) {
						$sTemp = fwrite($rFile, $sFinalData);
					}
				}
			} else {
				$sFile = "";
			}
		}
		
		if ($sFile != "") {
			$sPathAndFile = $sTodaysLeadsFolder.$sFile;
			$sCommand = "$GPG --compress-algo 1 --cipher-algo cast5 --yes -a -r $sKey -e $sPathAndFile";
			$rResult = shell_exec( $sCommand );

			$sFtp_Server = "ftp.cauto.com";
			$sFtp_User = "DMBE25";
			$sFtp_Pass = "Smart66";
			$sOfferCode = "DSC_BMG";
			$sFtpDir = "/BMG_email/dmbe25/To_Creative/";
			
			$sEncryptedFile = rename($sPathAndFile.".asc",$sTodaysLeadsFolder."dmbe25".date(y).date(m).date(d)."Ampere.pgp");
			$sEncryptedFile = "dmbe25".date(y).date(m).date(d)."Ampere.pgp";
			
			//Commented out by JRS on 11.21.06. Need to uncomment when offer goes live.
			//ftpFile($sOfferCode, $sFtp_Server, $sFtpDir, $sFtp_User, $sFtp_Pass, $sEncryptedFile, $sTodaysLeadsFolder);
			
			$sFile = "";
			unset($aTemp);
			$aTemp = array();
			$sFinalData = '';
		}
		// END OF DSC_BMG
		
		
		
		
		
		//start of KPAN_ZOOB	
		$sFile = "";
		$sZoobFile = '';
		unset($aTemp);
		$aTemp = array();
		$sFinalData = '';

		// START OF KPAN_ZOOB - APPROVED
		$sTodaysLeadsFolder = "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/KPAN_ZOOB/";
		$sOpenedDirectory = opendir( $sTodaysLeadsFolder );
		$sFile = "";
		$sKey = "qinteractive";
		
		// read directory and get file
		while( $sZoobFile = readdir( $sOpenedDirectory ) ) {
			if( $sZoobFile != '.' && $sZoobFile != '..' && $sZoobFile != "$sEncryptedFile" ) {
				$sContents = filesize($sTodaysLeadsFolder.$sZoobFile);
				$sFile = $sZoobFile;
				
				$rFile = fopen("$sTodaysLeadsFolder/$sFile","a");
				if ($rFile) {
					$sTemp = fwrite($rFile, "\r\n");
				}
				fclose($rFile);
			}
		}
		
		if ($sFile != "") {
			$sPathAndFile = $sTodaysLeadsFolder.$sFile;
			$sCommand = "$GPG --compress-algo 1 --cipher-algo cast5 --yes -a -r $sKey -e $sPathAndFile";
			$rResult = shell_exec( $sCommand );
		
			$sFtp_Server = "ftp.randomhouse.com";
			$sFtp_User = "ampere";
			$sFtp_Pass = "@B00k3r3";
			$sOfferCode = "KPAN_ZOOB";
			$sFtpDir = "/ampere_prod/";
			
			$sEncryptedFile = rename($sTodaysLeadsFolder.$sFile.".asc",$sTodaysLeadsFolder.$sFile.".pgp");
			$sEncryptedFile = "$sFile.pgp";
		
			ftpFile($sOfferCode, $sFtp_Server, $sFtpDir, $sFtp_User, $sFtp_Pass, $sEncryptedFile, $sTodaysLeadsFolder);
		}
		//end of KPAN_ZOOB



		
		/*
		// START OF MS_Prevacid
		$sTodaysLeadsFolder = "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/MS_Prevacid/";
		$sOpenedDirectory = opendir( $sTodaysLeadsFolder );
		$sFile = "";
		$sKey = "targetbase@targetbase.com";
		$sEncryptedFile = "MS_Prevacid_" .  date(m) . "_" . date(d) . "_" . date(Y) . "_Ampere.xml.pgp";
	
			
		// read directory and get file
		while( $sColumbiaFile = readdir( $sOpenedDirectory ) ) {
			if( $sColumbiaFile != '.' && $sColumbiaFile != '..' && $sColumbiaFile != "$sEncryptedFile" ) {
				$sContents = filesize($sTodaysLeadsFolder.$sColumbiaFile);
				$sFile = $sColumbiaFile;
				
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
			$sEncryptedFile = rename($sTodaysLeadsFolder."MS_Prevacid_" .  date(m) . "_" . date(d) . "_" . date(Y) . "_Ampere.xml.gpg",$sTodaysLeadsFolder."MS_Prevacid_" .  date(m) . "_" . date(d) . "_" . date(Y) . "_Ampere.xml.pgp");
			$sEncryptedFile = "MS_Prevacid_" .  date(m) . "_" . date(d) . "_" . date(Y) . "_Ampere.xml.pgp";
			$sEmail = "jsaltzman@amperemedia.com";
			$sSubject = "Ampere Media - MS_Prevacid_" .  date(m) . "-" . date(d) . "-" . date(Y);

			$sFtp_Server = "secftp.targetbase.com";
			$sFtp_User = "msist";
			$sFtp_Pass = "slvr6pvd";
			$sOfferCode = "MS_Prevacid";
			$sFtpDir = "ampere";
			
			ftpFileBinary($sOfferCode, $sFtp_Server, $sFtpDir, $sFtp_User, $sFtp_Pass, $sEncryptedFile, $sTodaysLeadsFolder);
		    //email($sEmail, $sSubject, $sEncryptedFile, $sTodaysLeadsFolder);
		}
		// END OF MS_Prevacid
		*/
		

		// START OF AHA_PDA - APPROVED
		$sTodaysLeadsFolder = "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/AHA_PDA/";
		$sOpenedDirectory = opendir( $sTodaysLeadsFolder );
		$sFile = "";
		$sKey = "JSackin@ahahome.com";
		$sEncryptedFile = "MY_B_0_12.".date(Y).date(m).date(d).".pgp";
		//MY_B_0_12.[yyyy][mm][dd]
		// read directory and get file
		while ($sAhaPdaFile = readdir( $sOpenedDirectory )) {
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
		}
		// END OF AHA_PDA


		// START OF AHA_ROBO - APPROVED
		$sTodaysLeadsFolder = "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/AHA_ROBO/";
		$sOpenedDirectory = opendir( $sTodaysLeadsFolder );
		$sFile = "";
		$sKey = "JSackin@ahahome.com";
		$sEncryptedFile = "AE_b_0_33.".date(Y).date(m).date(d).".pgp";
		//AE_b_0_33.20051007
		
		// read directory and get file
		while ($sAhaRoboFile = readdir( $sOpenedDirectory )) {
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
		}
		// START OF AHA_ROBO



		// START OF AHA_CAM - APPROVED
		$sTodaysLeadsFolder = "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/AHA_CAM/";
		$sOpenedDirectory = opendir( $sTodaysLeadsFolder );
		$sFile = "";
		$sKey = "JSackin@ahahome.com";
		$sEncryptedFile = "MY_B_0_12.".date(Y).date(m).date(d)."2.pgp";
		// read directory and get file
		while ($sAhaCamFile = readdir( $sOpenedDirectory )) {
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
		}
		// END OF AHA_CAM



	// START OF KDN_INV - APPROVED
	$sCountQuery = "SELECT count(*) as count
				FROM otDataHistory 
				WHERE offerCode = 'KDN_INV' 
				AND dateTimeAdded > date_add(CURRENT_DATE, INTERVAL -1 DAY) 
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
	//	Commented out on 11.22.06 by JRS -- offer is down
	if ($sKdnCount > 0) {
		mail("fred@amperemedia.com, kkousins@amperemedia.com, rizzo.michael@davison54.com", $sSubject, $sEmailBody, $sHeaders);
	}
	// END OF KDN_INV
	
	
	// START OF KFGR_COLUMBIA - APPROVED
		$sTodaysLeadsFolder = "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/KFGR_COLUMBIA/";
		$sOpenedDirectory = opendir( $sTodaysLeadsFolder );
		$sFile = "";
		$sKey = "web001@chpostman.columbiahouse.com";
		$sEncryptedFile = "KFGR_COLUMBIA_" .  date(m) . "_" . date(d) . "_" . date(Y) . "_Ampere.xml.pgp";
	
		
		//"KFGR_COLUMBIA01_31" . date(Y) . "_Ampere.xml"
		
		// read directory and get file
		while( $sColumbiaFile = readdir( $sOpenedDirectory ) ) {
			if( $sColumbiaFile != '.' && $sColumbiaFile != '..' && $sColumbiaFile != "$sEncryptedFile" ) {
				$sContents = filesize($sTodaysLeadsFolder.$sColumbiaFile);
				$sFile = $sColumbiaFile;
				
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
		$sEncryptedFile = rename($sTodaysLeadsFolder."KFGR_COLUMBIA_" .  date(m) . "_" . date(d) . "_" . date(Y) . "_Ampere.xml.gpg",$sTodaysLeadsFolder."KFGR_COLUMBIA_" .  date(m) . "_" . date(d) . "_" . date(Y) . "_Ampere.xml.pgp");
		$sEncryptedFile = "KFGR_COLUMBIA_" .  date(m) . "_" . date(d) . "_" . date(Y) . "_Ampere.xml.pgp";
		$sEmail = "jsaltzman@amperemedia.com";
		$sSubject = "Ampere Media - KFGR_COLUMBIA_" .  date(m) . "-" . date(d) . "-" . date(Y);
			    
			$sFtp_Server = "mail.singerdirect.com";
			$sFtp_User = "ampere";
			$sFtp_Pass = "amp2006";
			$sOfferCode = "KFGR_COLUMBIA";
			$sFtpDir = "ampere";
			
			ftpFileBinary($sOfferCode, $sFtp_Server, $sFtpDir, $sFtp_User, $sFtp_Pass, $sEncryptedFile, $sTodaysLeadsFolder);
		    //email($sEmail, $sSubject, $sEncryptedFile, $sTodaysLeadsFolder);
		}	
		// END OF KFGR_COLUMBIA

	// START OF DSC_COL
		$sTodaysLeadsFolder = "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/DSC_COL/";
		$sOpenedDirectory = opendir( $sTodaysLeadsFolder );
		$sFile = "";
		$sKey = "web001@chpostman.columbiahouse.com";
		$sEncryptedFile = "DSC_COL_" .  date(m) . "_" . date(d) . "_" . date(Y) . "_Ampere.xml.pgp";
	
		
		//"KFGR_COLUMBIA01_31" . date(Y) . "_Ampere.xml"
		
		// read directory and get file
		while( $sColumbiaFile = readdir( $sOpenedDirectory ) ) {
			if( $sColumbiaFile != '.' && $sColumbiaFile != '..' && $sColumbiaFile != "$sEncryptedFile" ) {
				$sContents = filesize($sTodaysLeadsFolder.$sColumbiaFile);
				$sFile = $sColumbiaFile;
				
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
			$sEncryptedFile = rename($sTodaysLeadsFolder."DSC_COL_" .  date(m) . "_" . date(d) . "_" . date(Y) . "_Ampere.xml.gpg",$sTodaysLeadsFolder."DSC_COL_" .  date(m) . "_" . date(d) . "_" . date(Y) . "_Ampere.xml.pgp");
			$sEncryptedFile = "DSC_COL_" .  date(m) . "_" . date(d) . "_" . date(Y) . "_Ampere.xml.pgp";
			$sEmail = "jsaltzman@amperemedia.com";
			$sSubject = "Ampere Media - DSC_COL_" .  date(m) . "-" . date(d) . "-" . date(Y);
			    
			$sFtp_Server = "mail.singerdirect.com";
			$sFtp_User = "ampere";
			$sFtp_Pass = "amp2006";
			$sOfferCode = "DSC_COL";
			$sFtpDir = "ampere";
			
			ftpFileBinary($sOfferCode, $sFtp_Server, $sFtpDir, $sFtp_User, $sFtp_Pass, $sEncryptedFile, $sTodaysLeadsFolder);
		    //email($sEmail, $sSubject, $sEncryptedFile, $sTodaysLeadsFolder);
		}
		// END OF DSC_COL



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
	$sSd_GevContent = "";
	$sSd_Gev4Content = "";
	

	$sSdGevPath = "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/SD_GEV/MF".date(y).$sJulianDate.".txt";
	$sSdGevSize = filesize($sSdGevPath);
	if ($sSdGevSize != 0) {
		$sSd_GevContent = file_get_contents($sSdGevPath)."\r\n";
	}


	// GET CONTENT FROM SD_GEV4
	$sSdGev4Path = "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/SD_GEV4/MF".date(y).$sJulianDate.".txt";
	$sSdGev4Size = filesize($sSdGev4Path);
	if ($sSdGev4Size != 0) {
		$sSd_Gev4Content = file_get_contents($sSdGev4Path);
	}

	// write content collected from sd_gev(2,3,4) to sd_gev file
	$sFinalDataSdGev = $sSd_GevContent.$sSd_Gev4Content;
	if ($sFinalDataSdGev != "") {
		$rFile = fopen("/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/SD_GEV/MF".date(y).$sJulianDate.".txt","w");
		if ($rFile) {
			$sTemp = fwrite($rFile, $sFinalDataSdGev);
		}
	}

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

	if ($sFile != "") {
		$sPathAndFile = $sTodaysLeadsFolder.$sFile;
		$sCommand = "$GPG -z 1 --cipher-algo cast5 -o $sPathAndFile.pgp -r $sKey -e -r $sKey2 -s --passphrase-fd 0 < /home/samirp/SD_GEV_Pass.txt $sPathAndFile";
		$rResult = shell_exec( $sCommand );

		$sEncryptedFile = "MF".date(y)."$sJulianDate.txt.pgp";
		$sFtp_Server = "ftp2.clientlogic.com";
		$sFtp_User = "gev_myfree";
		$sFtp_Pass = "M@rk719!";
		$sOfferCode = "SD_GEV";
		$sFtpDir = "/gev_myfree/upload/";
		//ftpFile($sOfferCode, $sFtp_Server, $sFtpDir, $sFtp_User, $sFtp_Pass, $sEncryptedFile, $sTodaysLeadsFolder);
	}

	$sCountQuery = "SELECT count(*) as count FROM otDataHistory 
				WHERE (offerCode = 'SD_GEV' OR offerCode = 'SD_GEV2' OR offerCode = 'SD_GEV3' OR offerCode = 'SD_GEV4')
				AND dateTimeAdded > date_add(CURRENT_DATE, INTERVAL -1 DAY) AND sendStatus = 's'";
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
//	$sEmail = "MilfordNeReleaseTeam@Clientlogic.com,MarisMcI@clientlogic.com,lynetsim@clientlogic.com,djohnsen@singerdirect.com,rbalancia@singerdirect.com";
	$sEmail = "josh@amperemedia.com";
	
	$sHeaders = "From: leads@AmpereMedia.com\r\n";
//	$sHeaders .= "Cc: leads@AmpereMedia.com,pschechter@amperemedia.com\r\n";

	$sSubject = "Ampere Media Leads - Gevalia $sDate";
	mail($sEmail, $sSubject, $sEmailBody , $sHeaders);
		// END: SEND SD_GEV COUNT - APPROVED
	// END OF SD_GEV

	
	
	//start addition by pd ---- TYES_HP
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	$sCountQuery = "SELECT count(*) as count
				FROM otDataHistory 
				WHERE offerCode = 'TYES_HP' 
				AND dateTimeAdded > date_add(CURRENT_DATE, INTERVAL -1 DAY) 
				AND sendStatus = 'S'";
	$rCountResult = dbQuery($sCountQuery);
	$oRow = dbFetchObject($rCountResult);
	$s_TYES_HP_Count = $oRow->count;								

						
	while( strlen($s_TYES_HP_Count) < 6 ) {
		$s_TYES_HP_Count = "0".$s_TYES_HP_Count;
	}

	$sTodaysLeadsFolder = "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TYES_HP/";
	$sOpenedDirectory = opendir( $sTodaysLeadsFolder );
	while( $sTYES_HPFile = readdir( $sOpenedDirectory ) ) {
		if( $sTYES_HPFile == '1.txt' ) {
			//HPLGAMP[mm][dd][yy][hp_count].txt]
			$sTempFileTemp = "HPLGAMP".date(m).date(d).date(y).$s_TYES_HP_Count.".txt";
			$s_TYES_HP_File = rename($sTodaysLeadsFolder."1.txt",$sTodaysLeadsFolder.$sTempFileTemp);
		}
	}

	$sFtp_Server = "EFT.SIMINFO.COM";
	$sFtp_User = "HWP1I3";
	$sFtp_Pass = "hew77pac";
	$sOfferCode = "TYES_HP";
	$sFtpDir = '/HWP1I3/';

	ftpFile($sOfferCode, $sFtp_Server, $sFtpDir, $sFtp_User, $sFtp_Pass, $sTempFileTemp, $sTodaysLeadsFolder);
	//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++}
	//end addition by pd
	
	
	
	
	
	
	
	
	//start  ---- TYES_HOME
	$rCountResult = dbQuery("SELECT count(*) as count FROM otDataHistory WHERE offerCode='TYES_HOME' AND dateTimeAdded > date_add(CURRENT_DATE, INTERVAL -1 DAY) AND sendStatus='S'");
	$oRow = dbFetchObject($rCountResult);
	$s_TYES_HOME_Count = $oRow->count;
	while( strlen($s_TYES_HOME_Count) < 6 ) {
		$s_TYES_HOME_Count = "0".$s_TYES_HOME_Count;
	}
	$sOpenedDirectory = opendir("/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TYES_HOME/");
	while($sTYES_HomeFile = readdir($sOpenedDirectory)) {
		if($sTYES_HomeFile == '1.txt') {
			$asdf = rename("/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TYES_HOME/1.txt","/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TYES_HOME/"."CWLGAMP".date(m).date(d).date(y)."$s_TYES_HOME_Count.txt");
			ftpFile("TYES_HOME", "eft.siminfo.com", '/SEMG1I3/', "SEMG1I3", "FWMFXw7L", "CWLGAMP".date(m).date(d).date(y)."$s_TYES_HOME_Count.txt", "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TYES_HOME/");
		}
	}
	// END - TYES_HOME
	
	
	
	
	
	
	
	
	
	//start  ---- TYES_HPDP
	$sCountQuery = "SELECT count(*) as count
				FROM otDataHistory 
				WHERE offerCode = 'TYES_HPDP' 
				AND dateTimeAdded > date_add(CURRENT_DATE, INTERVAL -1 DAY) 
				AND sendStatus = 'S'";
	$rCountResult = dbQuery($sCountQuery);
	$oRow = dbFetchObject($rCountResult);
	$s_TYES_HPDP_Count = $oRow->count;								

						
	while( strlen($s_TYES_HPDP_Count) < 6 ) {
		$s_TYES_HPDP_Count = "0".$s_TYES_HPDP_Count;
	}

	$sTodaysLeadsFolder = "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TYES_HPDP/";
	$sOpenedDirectory = opendir( $sTodaysLeadsFolder );
	while( $sTYES_HPFile = readdir( $sOpenedDirectory ) ) {
		if( $sTYES_HPFile == '1.txt' ) {
			$sTempFileTemp = "DPLGAMP".date(m).date(d).date(y).$s_TYES_HPDP_Count.".txt";
			$s_TYES_HPDP_File = rename($sTodaysLeadsFolder."1.txt",$sTodaysLeadsFolder.$sTempFileTemp);
		}
	}

	$sFtp_Server = "FTP.SIMINFO.COM";
	$sFtp_User = "SIMA1I3";
	$sFtp_Pass = "Bu9T3Bx4";
	$sOfferCode = "TYES_HPDP";
	$sFtpDir = '/SIMA1I3/';
	ftpFile($sOfferCode, $sFtp_Server, $sFtpDir, $sFtp_User, $sFtp_Pass, $sTempFileTemp, $sTodaysLeadsFolder);
	// END - TYES_HPDP
	
	
	
	
	
	
	
	//start  ---- TYES_METPCS
	$sCountQuery = "SELECT count(*) as count
				FROM otDataHistory 
				WHERE offerCode = 'TYES_METPCS' 
				AND dateTimeAdded > date_add(CURRENT_DATE, INTERVAL -1 DAY) 
				AND sendStatus = 'S'";
	$rCountResult = dbQuery($sCountQuery);
	$oRow = dbFetchObject($rCountResult);
	$s_TYES_METPCS_Count = $oRow->count;								

						
	while( strlen($s_TYES_METPCS_Count) < 6 ) {
		$s_TYES_METPCS_Count = "0".$s_TYES_METPCS_Count;
	}

	$sTodaysLeadsFolder = "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TYES_METPCS/";
	$sOpenedDirectory = opendir( $sTodaysLeadsFolder );
	while( $sTYES_HPFile = readdir( $sOpenedDirectory ) ) {
		if( $sTYES_HPFile == '1.txt' ) {
			$sTempFileTemp = "PSLGAMP".date(m).date(d).date(y).$s_TYES_METPCS_Count.".txt";
			$s_TYES_METPCS_File = rename($sTodaysLeadsFolder."1.txt",$sTodaysLeadsFolder.$sTempFileTemp);
		}
	}

	$sFtp_Server = "eft.siminfo.com";
	$sFtp_User = "HIL1I3";
	$sFtp_Pass = "QJq25FEa";
	$sOfferCode = "TYES_METPCS";
	$sFtpDir = '/HIL1I3/';
	ftpFile($sOfferCode, $sFtp_Server, $sFtpDir, $sFtp_User, $sFtp_Pass, $sTempFileTemp, $sTodaysLeadsFolder);
	// END - TYES_METPCS
	
	
	
	
	
	
	
	
	
	// TIDM_CHJ - START - Rep: Andy, Tech: Syed
	$sOpenedDirectory = opendir("/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ/");
	$sTIDM_CHJ_File = '';
	while ($sTempTIDM_CHJ_File = readdir($sOpenedDirectory)) {
		if ($sTempTIDM_CHJ_File != '.' && $sTempTIDM_CHJ_File != '..') {
			$sTIDM_CHJ_File = $sTempTIDM_CHJ_File;
		}
	}

	if (filesize("/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ/".$sTIDM_CHJ_File) > 0) {
		// set up basic connection	// login with username and password
		$sConnection_Id = ftp_connect("gateway.wamnet.com");
		$login_result = ftp_login($sConnection_Id, "amperem", '96u50');
	
		// check connection
		if (!$sConnection_Id) {
			$sEmailMessage = "FTP connection has failed!\n\n";
			$sEmailMessage .= "Attempted to connect to gateway.wamnet.com for user amperem\n\n";
			mail('it@amperemedia.com', "TIDM_CHJ", $sEmailMessage , "From: leads@amperemedia.com\r\n");
		} else {
			// upload a file
			if (ftp_put($sConnection_Id, "/Ship_Package/Address_Book/1566/"."$sTIDM_CHJ_File", "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ/"."$sTIDM_CHJ_File", FTP_ASCII)) {
				// this is required else the file on ftp server will disappear.  this will execute file on client's server.
				if (ftp_chdir($sConnection_Id, "/Ship_Package/Address_Book/1566/Ship_It")) {
				   echo "Current directory is now: " . ftp_pwd($sConnection_Id) . "\n";
				   echo "successfully uploaded $sTIDM_CHJ_File\n";
				   mail('leads@amperemedia.com,athomashow@amperemedia.com', "TIDM_CHJ", "FYI:  TIDM_CHJ uploaded to client's server successfully" , "From: leads@amperemedia.com\r\n");
				} else {
				   mail('it@amperemedia.com', "TIDM_CHJ", "Couldn't change directory\n" , "From: leads@amperemedia.com\r\n");
				}
			} else {
				$sEmailMessage = "There was a problem while uploading $sTIDM_CHJ_File\n";
				mail('it@amperemedia.com', "TIDM_CHJ", $sEmailMessage , "From: leads@amperemedia.com\r\n");
			}
			// close the FTP stream
			ftp_close($sConnection_Id);
		}
	}
	// TIDM_CHJ - END - Rep: Andy, Tech: Syed

	
	
	// TIDM_CHJ1 - START - Rep: Andy, Tech: Syed
	$sOpenedDirectory = opendir( "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ1/" );
	$sTIDM_CHJ1_File = '';
	while ($sTempTIDM_CHJ1_File = readdir($sOpenedDirectory)) {
		if ($sTempTIDM_CHJ1_File != '.' && $sTempTIDM_CHJ1_File != '..') {
			$sTIDM_CHJ1_File = $sTempTIDM_CHJ1_File;
		}
	}
	
	if (filesize("/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ1/".$sTIDM_CHJ1_File) > 0) {
		// set up basic connection
		// login with username and password
		$sConnection_Id = ftp_connect("gateway.wamnet.com");
		$login_result = ftp_login($sConnection_Id, "amperem", '96u50');
		
		// check connection
		if (!$sConnection_Id) {
			$sEmailMessage = "FTP connection has failed!\n\n";
			$sEmailMessage .= "Attempted to connect to gateway.wamnet.com for user amperem\n\n";
			mail('it@amperemedia.com', "TIDM_CHJ1", $sEmailMessage , "From: leads@amperemedia.com\r\n");
		} else {
			// upload a file
			if (ftp_put($sConnection_Id, "/Ship_Package/Address_Book/1566/"."$sTIDM_CHJ1_File", "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ1/"."$sTIDM_CHJ1_File", FTP_ASCII)) {
				// this is required else the file on ftp server will disappear. this will execute file on client's server.
				if (ftp_chdir($sConnection_Id, "/Ship_Package/Address_Book/1566/Ship_It")) {
					echo "Current directory is now: " . ftp_pwd($sConnection_Id) . "\n";
					echo "successfully uploaded $sTIDM_CHJ1_File\n";
					mail('leads@amperemedia.com,athomashow@amperemedia.com', "TIDM_CHJ1", "FYI:  TIDM_CHJ1 uploaded to client's server successfully" , "From: leads@amperemedia.com\r\n");
				} else {
					mail('it@amperemedia.com', "TIDM_CHJ1", "Couldn't change directory\n" , "From: leads@amperemedia.com\r\n");
				}
			} else {
				$sEmailMessage = "There was a problem while uploading $sTIDM_CHJ1_File\n";
				mail('it@amperemedia.com', "TIDM_CHJ1", $sEmailMessage , "From: leads@amperemedia.com\r\n");
			}
			// close the FTP stream
			ftp_close($sConnection_Id);
		}
	}
	// TIDM_CHJ1 - END - Rep: Andy, Tech: Syed
	
	
	
	
	
	// TIDM_CHJ2 - START - Rep: Andy, Tech: Syed
	$sOpenedDirectory = opendir( "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ2/" );
	$sTIDM_CHJ2_File = '';
	while ($sTempTIDM_CHJ2_File = readdir($sOpenedDirectory)) {
		if ($sTempTIDM_CHJ2_File != '.' && $sTempTIDM_CHJ2_File != '..') {
			$sTIDM_CHJ2_File = $sTempTIDM_CHJ2_File;
		}
	}
	
	if (filesize("/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ2/".$sTIDM_CHJ2_File) > 0) {
		// set up basic connection
		// login with username and password
		$sConnection_Id = ftp_connect("gateway.wamnet.com");
		$login_result = ftp_login($sConnection_Id, "amperem", '96u50');
		
		// check connection
		if (!$sConnection_Id) {
			$sEmailMessage = "FTP connection has failed!\n\n";
			$sEmailMessage .= "Attempted to connect to gateway.wamnet.com for user amperem\n\n";
			mail('it@amperemedia.com', "TIDM_CHJ2", $sEmailMessage , "From: leads@amperemedia.com\r\n");
		} else {
			// upload a file
			if (ftp_put($sConnection_Id, "/Ship_Package/Address_Book/1566/"."$sTIDM_CHJ2_File", "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ2/"."$sTIDM_CHJ2_File", FTP_ASCII)) {
				// this is required else the file on ftp server will disappear. this will execute file on client's server.
				if (ftp_chdir($sConnection_Id, "/Ship_Package/Address_Book/1566/Ship_It")) {
					echo "Current directory is now: " . ftp_pwd($sConnection_Id) . "\n";
					echo "successfully uploaded $sTIDM_CHJ2_File\n";
					mail('leads@amperemedia.com,athomashow@amperemedia.com', "TIDM_CHJ2", "FYI:  TIDM_CHJ2 uploaded to client's server successfully" , "From: leads@amperemedia.com\r\n");
				} else {
					mail('it@amperemedia.com', "TIDM_CHJ2", "Couldn't change directory\n" , "From: leads@amperemedia.com\r\n");
				}
			} else {
				$sEmailMessage = "There was a problem while uploading $sTIDM_CHJ2_File\n";
				mail('it@amperemedia.com', "TIDM_CHJ2", $sEmailMessage , "From: leads@amperemedia.com\r\n");
			}
			// close the FTP stream
			ftp_close($sConnection_Id);
		}
	}
	// TIDM_CHJ2 - END - Rep: Andy, Tech: Syed
	
	
	
	// TIDM_CHJ3 - START - Rep: Andy, Tech: Syed
	$sOpenedDirectory = opendir( "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ3/" );
	$sTIDM_CHJ3_File = '';
	while ($sTempTIDM_CHJ3_File = readdir($sOpenedDirectory)) {
		if ($sTempTIDM_CHJ3_File != '.' && $sTempTIDM_CHJ3_File != '..') {
			$sTIDM_CHJ3_File = $sTempTIDM_CHJ3_File;
		}
	}
	
	if (filesize("/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ3/".$sTIDM_CHJ3_File) > 0) {
		// set up basic connection
		// login with username and password
		$sConnection_Id = ftp_connect("gateway.wamnet.com");
		$login_result = ftp_login($sConnection_Id, "amperem", '96u50');
		
		// check connection
		if (!$sConnection_Id) {
			$sEmailMessage = "FTP connection has failed!\n\n";
			$sEmailMessage .= "Attempted to connect to gateway.wamnet.com for user amperem\n\n";
			mail('it@amperemedia.com', "TIDM_CHJ3", $sEmailMessage , "From: leads@amperemedia.com\r\n");
		} else {
			// upload a file
			if (ftp_put($sConnection_Id, "/Ship_Package/Address_Book/1566/"."$sTIDM_CHJ3_File", "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ3/"."$sTIDM_CHJ3_File", FTP_ASCII)) {
				// this is required else the file on ftp server will disappear. this will execute file on client's server.
				if (ftp_chdir($sConnection_Id, "/Ship_Package/Address_Book/1566/Ship_It")) {
					echo "Current directory is now: " . ftp_pwd($sConnection_Id) . "\n";
					echo "successfully uploaded $sTIDM_CHJ3_File\n";
					mail('leads@amperemedia.com,athomashow@amperemedia.com', "TIDM_CHJ3", "FYI:  TIDM_CHJ3 uploaded to client's server successfully" , "From: leads@amperemedia.com\r\n");
				} else {
					mail('it@amperemedia.com', "TIDM_CHJ3", "Couldn't change directory\n" , "From: leads@amperemedia.com\r\n");
				}
			} else {
				$sEmailMessage = "There was a problem while uploading $sTIDM_CHJ3_File\n";
				mail('it@amperemedia.com', "TIDM_CHJ3", $sEmailMessage , "From: leads@amperemedia.com\r\n");
			}
			// close the FTP stream
			ftp_close($sConnection_Id);
		}
	}
	// TIDM_CHJ3 - END - Rep: Andy, Tech: Syed
	
	
	
	// TIDM_CHJ4 - START - Rep: Andy, Tech: Syed
	$sOpenedDirectory = opendir( "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ4/" );
	$sTIDM_CHJ4_File = '';
	while ($sTempTIDM_CHJ4_File = readdir($sOpenedDirectory)) {
		if ($sTempTIDM_CHJ4_File != '.' && $sTempTIDM_CHJ4_File != '..') {
			$sTIDM_CHJ4_File = $sTempTIDM_CHJ4_File;
		}
	}
	
	if (filesize("/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ4/".$sTIDM_CHJ4_File) > 0) {
		// set up basic connection
		// login with username and password
		$sConnection_Id = ftp_connect("gateway.wamnet.com");
		$login_result = ftp_login($sConnection_Id, "amperem", '96u50');
		
		// check connection
		if (!$sConnection_Id) {
			$sEmailMessage = "FTP connection has failed!\n\n";
			$sEmailMessage .= "Attempted to connect to gateway.wamnet.com for user amperem\n\n";
			mail('it@amperemedia.com', "TIDM_CHJ4", $sEmailMessage , "From: leads@amperemedia.com\r\n");
		} else {
			// upload a file
			if (ftp_put($sConnection_Id, "/Ship_Package/Address_Book/1566/"."$sTIDM_CHJ4_File", "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ4/"."$sTIDM_CHJ4_File", FTP_ASCII)) {
				// this is required else the file on ftp server will disappear. this will execute file on client's server.
				if (ftp_chdir($sConnection_Id, "/Ship_Package/Address_Book/1566/Ship_It")) {
					echo "Current directory is now: " . ftp_pwd($sConnection_Id) . "\n";
					echo "successfully uploaded $sTIDM_CHJ4_File\n";
					mail('leads@amperemedia.com,athomashow@amperemedia.com', "TIDM_CHJ4", "FYI:  TIDM_CHJ4 uploaded to client's server successfully" , "From: leads@amperemedia.com\r\n");
				} else {
					mail('it@amperemedia.com', "TIDM_CHJ4", "Couldn't change directory\n" , "From: leads@amperemedia.com\r\n");
				}
			} else {
				$sEmailMessage = "There was a problem while uploading $sTIDM_CHJ4_File\n";
				mail('it@amperemedia.com', "TIDM_CHJ4", $sEmailMessage , "From: leads@amperemedia.com\r\n");
			}
			// close the FTP stream
			ftp_close($sConnection_Id);
		}
	}
	// TIDM_CHJ4 - END - Rep: Andy, Tech: Syed
	
	
	
	
	// TIDM_CHJ5 - START - Rep: Andy, Tech: Syed
	$sOpenedDirectory = opendir( "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ5/" );
	$sTIDM_CHJ5_File = '';
	while ($sTempTIDM_CHJ5_File = readdir($sOpenedDirectory)) {
		if ($sTempTIDM_CHJ5_File != '.' && $sTempTIDM_CHJ5_File != '..') {
			$sTIDM_CHJ5_File = $sTempTIDM_CHJ5_File;
		}
	}
	
	if (filesize("/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ5/".$sTIDM_CHJ5_File) > 0) {
		// set up basic connection
		// login with username and password
		$sConnection_Id = ftp_connect("gateway.wamnet.com");
		$login_result = ftp_login($sConnection_Id, "amperem", '96u50');
		
		// check connection
		if (!$sConnection_Id) {
			$sEmailMessage = "FTP connection has failed!\n\n";
			$sEmailMessage .= "Attempted to connect to gateway.wamnet.com for user amperem\n\n";
			mail('it@amperemedia.com', "TIDM_CHJ5", $sEmailMessage , "From: leads@amperemedia.com\r\n");
		} else {
			// upload a file
			if (ftp_put($sConnection_Id, "/Ship_Package/Address_Book/1566/"."$sTIDM_CHJ5_File", "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ5/"."$sTIDM_CHJ5_File", FTP_ASCII)) {
				// this is required else the file on ftp server will disappear. this will execute file on client's server.
				if (ftp_chdir($sConnection_Id, "/Ship_Package/Address_Book/1566/Ship_It")) {
					echo "Current directory is now: " . ftp_pwd($sConnection_Id) . "\n";
					echo "successfully uploaded $sTIDM_CHJ5_File\n";
					mail('leads@amperemedia.com,athomashow@amperemedia.com', "TIDM_CHJ5", "FYI:  TIDM_CHJ5 uploaded to client's server successfully" , "From: leads@amperemedia.com\r\n");
				} else {
					mail('it@amperemedia.com', "TIDM_CHJ5", "Couldn't change directory\n" , "From: leads@amperemedia.com\r\n");
				}
			} else {
				$sEmailMessage = "There was a problem while uploading $sTIDM_CHJ5_File\n";
				mail('it@amperemedia.com', "TIDM_CHJ5", $sEmailMessage , "From: leads@amperemedia.com\r\n");
			}
			// close the FTP stream
			ftp_close($sConnection_Id);
		}
	}
	// TIDM_CHJ5 - END - Rep: Andy, Tech: Syed
	
	
	
	
	// TIDM_CHJ6 - START - Rep: Andy, Tech: Syed
	$sOpenedDirectory = opendir( "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ6/" );
	$sTIDM_CHJ6_File = '';
	while ($sTempTIDM_CHJ6_File = readdir($sOpenedDirectory)) {
		if ($sTempTIDM_CHJ6_File != '.' && $sTempTIDM_CHJ6_File != '..') {
			$sTIDM_CHJ6_File = $sTempTIDM_CHJ6_File;
		}
	}
	
	if (filesize("/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ6/".$sTIDM_CHJ6_File) > 0) {
		// set up basic connection
		// login with username and password
		$sConnection_Id = ftp_connect("gateway.wamnet.com");
		$login_result = ftp_login($sConnection_Id, "amperem", '96u50');
		
		// check connection
		if (!$sConnection_Id) {
			$sEmailMessage = "FTP connection has failed!\n\n";
			$sEmailMessage .= "Attempted to connect to gateway.wamnet.com for user amperem\n\n";
			mail('it@amperemedia.com', "TIDM_CHJ6", $sEmailMessage , "From: leads@amperemedia.com\r\n");
		} else {
			// upload a file
			if (ftp_put($sConnection_Id, "/Ship_Package/Address_Book/1566/"."$sTIDM_CHJ6_File", "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ6/"."$sTIDM_CHJ6_File", FTP_ASCII)) {
				// this is required else the file on ftp server will disappear. this will execute file on client's server.
				if (ftp_chdir($sConnection_Id, "/Ship_Package/Address_Book/1566/Ship_It")) {
					echo "Current directory is now: " . ftp_pwd($sConnection_Id) . "\n";
					echo "successfully uploaded $sTIDM_CHJ6_File\n";
					mail('leads@amperemedia.com,athomashow@amperemedia.com', "TIDM_CHJ6", "FYI:  TIDM_CHJ6 uploaded to client's server successfully" , "From: leads@amperemedia.com\r\n");
				} else {
					mail('it@amperemedia.com', "TIDM_CHJ6", "Couldn't change directory\n" , "From: leads@amperemedia.com\r\n");
				}
			} else {
				$sEmailMessage = "There was a problem while uploading $sTIDM_CHJ6_File\n";
				mail('it@amperemedia.com', "TIDM_CHJ6", $sEmailMessage , "From: leads@amperemedia.com\r\n");
			}
			// close the FTP stream
			ftp_close($sConnection_Id);
		}
	}
	// TIDM_CHJ6 - END - Rep: Andy, Tech: Syed

	
	
	// TIDM_CHJ7 - START - Rep: Andy, Tech: Syed
	$sOpenedDirectory = opendir( "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ7/" );
	$sTIDM_CHJ7_File = '';
	while ($sTempTIDM_CHJ7_File = readdir($sOpenedDirectory)) {
		if ($sTempTIDM_CHJ7_File != '.' && $sTempTIDM_CHJ7_File != '..') {
			$sTIDM_CHJ7_File = $sTempTIDM_CHJ7_File;
		}
	}
	
	if (filesize("/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ7/".$sTIDM_CHJ7_File) > 0) {
		// set up basic connection
		// login with username and password
		$sConnection_Id = ftp_connect("gateway.wamnet.com");
		$login_result = ftp_login($sConnection_Id, "amperem", '96u50');
		
		// check connection
		if (!$sConnection_Id) {
			$sEmailMessage = "FTP connection has failed!\n\n";
			$sEmailMessage .= "Attempted to connect to gateway.wamnet.com for user amperem\n\n";
			mail('it@amperemedia.com', "TIDM_CHJ7", $sEmailMessage , "From: leads@amperemedia.com\r\n");
		} else {
			// upload a file
			if (ftp_put($sConnection_Id, "/Ship_Package/Address_Book/1566/"."$sTIDM_CHJ7_File", "/home/sites/admin.popularliving.com/html/admin/leads/$sToday/offers/TIDM_CHJ7/"."$sTIDM_CHJ7_File", FTP_ASCII)) {
				// this is required else the file on ftp server will disappear. this will execute file on client's server.
				if (ftp_chdir($sConnection_Id, "/Ship_Package/Address_Book/1566/Ship_It")) {
					echo "Current directory is now: " . ftp_pwd($sConnection_Id) . "\n";
					echo "successfully uploaded $sTIDM_CHJ7_File\n";
					mail('leads@amperemedia.com,athomashow@amperemedia.com', "TIDM_CHJ7", "FYI:  TIDM_CHJ7 uploaded to client's server successfully" , "From: leads@amperemedia.com\r\n");
				} else {
					mail('it@amperemedia.com', "TIDM_CHJ7", "Couldn't change directory\n" , "From: leads@amperemedia.com\r\n");
				}
			} else {
				$sEmailMessage = "There was a problem while uploading $sTIDM_CHJ7_File\n";
				mail('it@amperemedia.com', "TIDM_CHJ7", $sEmailMessage , "From: leads@amperemedia.com\r\n");
			}
			// close the FTP stream
			ftp_close($sConnection_Id);
		}
	}
	// TIDM_CHJ7 - END - Rep: Andy, Tech: Syed
	

	

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
		mail('it@amperemedia.com', $sOfferCode, $sEmailMessage , "From: leads@AmpereMedia.com\r\n");
	} else {
		// upload a file
		if (ftp_put($sConnection_Id, "$sFtpDir"."$sEncryptedFile", "$sTodaysLeadsFolder"."$sEncryptedFile", FTP_ASCII)) {
			echo "successfully uploaded $sEncryptedFile\n";
		} else {
			$sEmailMessage = "There was a problem while uploading $sEncryptedFile\n";
			mail('it@amperemedia.com', $sOfferCode, $sEmailMessage , "From: leads@AmpereMedia.com\r\n");
		}
		// close the FTP stream
		ftp_close($sConnection_Id);
	}
}	// END OF FTP FUNCTION


// FTP BINARY FUNTION STARTS
function ftpFileBinary($sOfferCode, $sFtp_Server, $sFtpDir, $sFtp_User, $sFtp_Pass, $sEncryptedFile, $sTodaysLeadsFolder) {
	// set up basic connection
	$sConnection_Id = ftp_connect($sFtp_Server);

	// login with username and password
	$login_result = ftp_login($sConnection_Id, $sFtp_User, $sFtp_Pass);

	// check connection
	if (!$sConnection_Id) {
		$sEmailMessage = "FTP connection has failed!\n\n";
		$sEmailMessage .= "Attempted to connect to $sFtp_Server for user $sFtp_User\n\n";
		mail('it@amperemedia.com', $sOfferCode, $sEmailMessage , "From: leads@AmpereMedia.com\r\n");
	} else {
		// upload a file
		if (ftp_put($sConnection_Id, "$sFtpDir"."$sEncryptedFile", "$sTodaysLeadsFolder"."$sEncryptedFile", FTP_BINARY)) {
			echo "successfully uploaded $sEncryptedFile\n";
		} else {
			$sEmailMessage = "There was a problem while uploading $sEncryptedFile\n";
			mail('it@amperemedia.com', $sOfferCode, $sEmailMessage , "From: leads@AmpereMedia.com\r\n");
		}
		// close the FTP stream
		ftp_close($sConnection_Id);
	}
}	// END OF FTP BINARY FUNCTION


?>
