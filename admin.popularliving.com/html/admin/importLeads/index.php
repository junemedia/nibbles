<?php

include("../../includes/paths.php");

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if ($sImportLeads) {

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Import leads\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles		
	
	
	if (file_exists($sGblImportLeadsDir)) {
		
		$rFpImportLeadsDir = openDir($sGblImportLeadsDir);
		if ($rFpImportLeadsDir) {
			
			while (($sLeadFile = readdir($rFpImportLeadsDir)) != false) {
				if(is_file("$sGblImportLeadsDir/$sLeadFile")) {
					// parse the lead file name to get offerCode and sourceCode
					
					$sFileNameComponents = explode(".",$sLeadFile);
					$sSourceCode = $sFileNameComponents[0];
					$sOfferCode = $sFileNameComponents[1];
					$rFpLeadFile = fopen("$sGblImportLeadsDir/$sLeadFile", r);
					$iAttempted = 0;
					$iSussessfullyInserted = 0;
					//$sFirstRow = 'Y';
					if ($rFpLeadFile) {						
						while (!feof($rFpLeadFile)) {
							
							$sLeadsData .= fread($rFpLeadFile, 1024);
							
							/*if ($sFirstRow == 'Y') {
								// check extra page2 fields here
								$sFirstRow = '';
							} else {
								$sLeadsData .= $sLeadRow;
							}*/
						}
						//echo $sLeadsData;
							$sLeadRowArray = explode("\r\n", $sLeadsData);
							//echo count($sLeadRowArray);
							if (count($sLeadRowArray) >0) {
							for($i=1;$i<count($sLeadRowArray);$i++) {
								//echo "<BR>i ".$i;
								$sLeadRow = $sLeadRowArray[$i];
							if (ltrim(rtrim($sLeadRow)) != '') {
									
									$sLeadRowComponents = explode("\",",$sLeadRow);
									//echo "<BR> lead ".$sLeadRow;
									// remove the sorrounding quotes
									$sSalutation = 	rtrim(ltrim($sLeadRowComponents[0],'\"'),'\"');
									$sFirst = rtrim(ltrim($sLeadRowComponents[1],'\"'),'\"');
									$sLast = rtrim(ltrim($sLeadRowComponents[2],'\"'),'\"');
									$sAddress = rtrim(ltrim($sLeadRowComponents[3],'\"'),'\"');
									$sAddress2 = rtrim(ltrim($sLeadRowComponents[4],'\"'),'\"');
									$sCity = rtrim(ltrim($sLeadRowComponents[5],'\"'),'\"');
									$sState = rtrim(ltrim($sLeadRowComponents[6],'\"'),'\"');
									$sZip = rtrim(ltrim($sLeadRowComponents[7],'\"'),'\"');
									$sPhone = rtrim(ltrim($sLeadRowComponents[8],'\"'),'\"');
									$sEmail = rtrim(ltrim($sLeadRowComponents[9],'\"'),'\"');
									$sDateTimeAdded = rtrim(ltrim($sLeadRowComponents[11],'\"'),'\"');
									$sRemoteIp = rtrim(ltrim($sLeadRowComponents[12],'\"'),'\"');
									
									//echo $sEmail;
									$iAttempted++;
									
									// check if user data already exists
									$sCheckUserHistoryQuery = "SELECT *
														   FROM   userDataHistory
														   WHERE email = '$sEmail'";
									$rCheckUserHistoryResult = dbQuery($sCheckUserHistoryQuery);
									echo dbError();
									$sCheckUserCurrentQuery = "SELECT *
														   FROM   userData
														   WHERE email = '$sEmail'";
									$rCheckUserCurrentResult = dbQuery($sCheckUserCurrentQuery);
									echo dbError();
									if (dbNumRows($rCheckUserCurrentResult) == 0 && dbNumRows($rCheckUserHistoryResult) == 0 ) {
										// insert lead into userDataHistory table									
										$sUserDataInsertQuery = "INSERT INTO userDataHistory(email, salutation, first, last, address, address2, city, state, zip, phoneNo, dateTimeAdded)
														 		 VALUES('$sEmail', '$sSalutation', '$sFirst', '$sLast', '$sAddress', '$sAddress2', '$sCity', '$sState', '$sZip', '$sPhone', '$sDateTimeAdded')";
										$rUserDataInsertResult = dbQuery($sUserDataInsertQuery);
										//echo "<BR>$sUserDataInsertQuery";
									}
									
									// check if user data already exists
									$sCheckOtHistoryQuery = "SELECT *
														   FROM   otDataHistory
														   WHERE email = '$sEmail'";
									$rCheckOtHistoryResult = dbQuery($sCheckOtHistoryQuery);
										echo dbError();
									$sCheckOtCurrentQuery = "SELECT *
														   FROM   otData
														   WHERE email = '$sEmail'";
									$rCheckOtCurrentResult = dbQuery($sCheckOtCurrentQuery);
									echo dbError();
									if (dbNumRows($rCheckOtCurrentResult) == 0 && dbNumRows($rCheckOtHistoryResult) == 0 ) {
										// insert lead into otDataHistory table
										$sOtDataInsertQuery = "INSERT INTO otDataHistory(email, offerCode, sourceCode, dateTimeAdded, remoteIp, mode)
													   VALUES('$sEmail', '$sOfferCode', '$sSourceCode', '$sDateTimeAdded', '$sRemoteIp', 'A')";
										$rOtDataInsertResult = dbQuery($sOtDataInsertQuery);
										//echo "<BR>".$sOtDataInsertQuery;
									}
										
									if ($rUserDataInsertResult && $rOtDataInsertResult) {
										$iSussessfullyInserted++;
									}
								}
								
							}
						}
						fclose($rFpLeadFile);
						// insert record in leadImportLog
						
						echo "<BR>$sLeadFile: Attempted - $iAttempted &nbsp; &nbsp; Sussessfully Inserted = $iSussessfullyInserted";
						
					}
					
				}
			}
		}
	}
}


// hidden variables to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";

include("../../includes/adminHeader.php");

?>
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<input type=submit name=sImportLeads value='Import Leads'>
</table>
</form>
	
<?php

include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>