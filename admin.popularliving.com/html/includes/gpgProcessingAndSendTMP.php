<?php

include_once("paths.php");

// get today's leads folder
$sToday = date(Y).date(m).date(d);
$GPG = "/usr/bin/gpg";


		



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
