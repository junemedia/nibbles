<?php
// Shree Ganeshay Namah...
// Hare Pritam...

include_once("../includes/paths.php");
include_once("libs/function.php");
include_once("libs/fields.php");
include_once("libs/pixel.php");
include_once("credit.php");

/*
include("/home/sites/www_popularliving_com/html/includes/paths.php");
include('/home/sites/www_popularliving_com/html/nibbles2/session_handlers.php');
include_once("/home/sites/www_popularliving_com/html/nibbles2/libs/function.php");
include("/home/sites/www_popularliving_com/html/nibbles2/libs/fields.php");*/

if (!(isset($_POST['PHPSESSID'])) && !(isset($_GET['PHPSESSID']))) {
	session_start();
	error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
	$_SESSION['aSesPage2Offers'] = array();
	$sOfferCode = trim($_GET['sOfferCode']);
	$_SESSION['sSesRemoteIp'] = trim($_SERVER['REMOTE_ADDR']);
	$_SESSION['sSesServerIp'] = trim($_SERVER['SERVER_ADDR']);
	$_SESSION['sSesOfferType'] = '';
	$_SESSION['sSesJavaScriptPrePop'] = '';
	$_SESSION['bSubmit'] = true;
	
	
	// set cookie with session id - cookie expires after 60 mins
	setcookie("AmpereSessionId", session_id(), time()+3600, "/", ".popularliving.com", 0);
	setcookie("AmpereSessionId", session_id(), time()+3600, "/", '', 0);
	
	// Get all data from querystring and trim it.
	$sSourceCode = (!(ctype_alnum(trim($_GET['src']))) ? '' : trim($_GET['src']));
	$sSubSourceCode = (!(ctype_alnum(trim($_GET['ss']))) ? '' : trim($_GET['ss']));
	
	$_SESSION['sSesSourceCode'] = $sSourceCode;
	$_SESSION['sSesRevSourceCode'] = strrev($sSourceCode);
	$_SESSION['sSesSubSourceCode'] = $sSubSourceCode;
	
	$_SESSION['sSesHiddenSourceCode'] = "<input type='hidden' name='hiddenSrc' value='$sSourceCode'>";
	
	$sEmail = (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", trim($_GET['e']))) ? '' : trim($_GET['e']);
	$sFirst = (!ctype_alpha(trim($_GET['f'])) ? '' : trim($_GET['f']));
	$sLast = (!eregi("^[-A-Z[:space:]'\.]*$", trim($_GET['l'])) ? '' : trim($_GET['l']));
	
	$sGender = ((strtoupper(trim($_GET['gn'])) == 'M' || strtoupper(trim($_GET['gn'])) == 'F') ? strtoupper(trim($_GET['gn'])) : '');

	$iBirthDay = '';
	if (strlen(trim($_GET['bd'])) == 2 && ctype_digit(trim($_GET['bd'])) && trim($_GET['bd']) <= 31) {
		$iBirthDay = trim($_GET['bd']);
	}
	
	$iBirthMonth = '';
	if (strlen(trim($_GET['bm'])) == 2 && ctype_digit(trim($_GET['bm'])) && trim($_GET['bm']) <= 12) {
		$iBirthMonth = trim($_GET['bm']);
	}
	
	$iBirthYear = '';
	if (strlen(trim($_GET['by'])) == 4 && ctype_digit(trim($_GET['by'])) && trim($_GET['by']) < date('Y')) {
		$iBirthYear = trim($_GET['by']);
	}

	$sPhone = (!(ereg("^[0-9-]+$", trim($_GET['p']))) ? '' : trim($_GET['p']));
	$sPhoneExtension = (ctype_digit(trim($_GET['ext'])) ? trim($_GET['ext']) : '');


	$sPhone_areaCode = '';
	if (ctype_digit(trim($_GET['pa'])) && strlen(trim($_GET['pa'])) == 3 && trim($_GET['pa']) >= 200) {
		$sPhone_areaCode = trim($_GET['pa']);
	}

	$sPhone_exchange = '';
	if (ctype_digit(trim($_GET['pe'])) && strlen(trim($_GET['pe'])) == 3 && trim($_GET['pe']) >= 200) {
		$sPhone_exchange = trim($_GET['pe']);
	}
	
	$sPhone_number = '';
	if (ctype_digit(trim($_GET['pnum'])) && strlen(trim($_GET['pnum'])) == 4) {
		$sPhone_number = trim($_GET['pnum']);
	}
	
	
	$sAddress = (!ereg("^[a-zA-Z0-9 \'\x2e\#\:\\\/\,\’\&\@()\°_-]{1,}$", trim($_GET['a1'])) ? '' : trim($_GET['a1']));
	$sAddress2 = (!ereg("^[a-zA-Z0-9 \'\x2e\#\:\\\/\,\’\&\@()\°_-]*$", trim($_GET['a2'])) ? '' : trim($_GET['a2']));
	$sCity = (!ereg( "^[a-zA-Z0-9 \'\x2e\-\’\`\&]{1,}$", trim($_GET['c'])) ? '' : trim($_GET['c']));
	$sState = (!ereg("^[A-Z]{2,2}$", strtoupper(trim($_GET['s']))) ? '' : strtoupper(trim($_GET['s'])));
	$sZip = (!ereg("^[0-9-]{5,}$", strtoupper(trim($_GET['z']))) ? '' : strtoupper(trim($_GET['z'])));
	
	// if g variable is available, put it in session.
	$_SESSION["sSesRedirectUrl"] = '';
	if (trim($_GET['g']) != '') {
		$_SESSION["sSesRedirectUrl"] = trim($_GET['g']);
	}
	
	
	// If email is valid email, set it in session
	if (validateEmail($sEmail)) {
		$_SESSION['sSesEmail'] = $sEmail;
	}
	
	// If first name is valid, set it in session
	if (validateName($sFirst)) {
		$_SESSION['sSesFirst'] = $sFirst;
	}
	
	// If last name is valid, set it in session
	if (validateName($sLast)) {
		$_SESSION['sSesLast'] = $sLast;
	}
	
	// If gender is M or F, then set it in session
	if ($sGender == 'M' || $sGender == 'F') {
		$_SESSION['sSesGender'] = $sGender;
		$_SESSION['sSesTargetGender'] = $sGender;
	}

	if ($iBirthYear !='') {
		$_SESSION['iSesBirthYear'] = $iBirthYear;
		$_SESSION['sSesTargetYear'] = $iBirthYear;
	}
		
	if ($iBirthMonth !='') {
		$_SESSION['iSesBirthMonth'] = $iBirthMonth;
	}
	
	if ($iBirthDay !='') {
		$_SESSION['iSesBirthDay'] = $iBirthDay;
	}

	if ($iBirthYear !='' && $iBirthMonth !='' && $iBirthDay !='' ) {
		$_SESSION['sSesBirthDate'] = $iBirthYear."-".$iBirthMonth."-".$iBirthDay;
	}
	
	// If phone number is not valid for zipcode entered, make it null.
	if ($sPhone !='' && $sZip !='') {
		$iPhoneZipDistance = getDistance($sPhone,$sZip);
		if ($iPhoneZipDistance > 70) {
			exceedsMaxDistance($sPhone, $sZip, 70);
			$sPhone = '';
		}
	}
	
	if (strlen($sPhone) == 10) {
		if (strlen(substr($sPhone, 0, 3)) == 3 && ctype_digit(substr($sPhone, 0, 3)) && substr($sPhone, 0, 3) >= 200) {
			$sPhone_areaCode = substr($sPhone,0,3);
		}
	
		if (strlen(substr($sPhone, 3, 3)) == 3 && ctype_digit(substr($sPhone, 3, 3)) && substr($sPhone, 3, 3) >= 200) {
			$sPhone_exchange = substr($sPhone,3,3);
		}
		
		if (strlen(substr($sPhone, 6,4)) == 4 && ctype_digit(substr($sPhone, 6,4))) {
			$sPhone_number = substr($sPhone,6,4);
		}
	} else if (strlen($sPhone) == 12) {
		if (strlen(substr($sPhone, 0, 3)) == 3 && ctype_digit(substr($sPhone, 0, 3)) && substr($sPhone, 0, 3) >= 200) {
			$sPhone_areaCode = substr($sPhone, 0, 3);
		}
		
		if (strlen(substr($sPhone, 4, 3)) == 3 && ctype_digit(substr($sPhone, 4, 3)) && substr($sPhone, 4, 3) >= 200) {
			$sPhone_exchange = substr($sPhone, 4, 3);
		}
		
		if (strlen(substr($sPhone, 8,4)) == 4 && ctype_digit(substr($sPhone, 8,4))) {
			$sPhone_number = substr($sPhone, 8,4);
		}
	}
	
	if ($sPhone !='') {
		if ((validatePhone($sPhone_areaCode, $sPhone_exchange, $sPhone_number, '', $sState))) {
			$_SESSION['sSesPhone'] = $sPhone_areaCode.'-'.$sPhone_exchange.'-'.$sPhone_number;
			$_SESSION['sSesPhoneNoDash'] = $sPhone_areaCode.$sPhone_exchange.$sPhone_number;
			$_SESSION['sSesPhoneAreaCode'] = $sPhone_areaCode;
			$_SESSION['sSesPhoneExchange'] = $sPhone_exchange;
			$_SESSION['sSesPhoneNumber'] = $sPhone_number;
			$_SESSION['sSesTargetExchange'] = $sPhone_exchange;
		}
	}
	

	$_SESSION['sSesPage2RegUserForm'] = "<style type='text/css'>
		.fieldnames {
			font: bold 13px Arial, Helvetica, sans-serif;
		}
		</style>
		
		<br><br>
			<table align='[ALIGN_LEFT_CENTER_RIGHT]' border='0' cellpadding='2' cellspacing='0'>
			<tr>
				<td align='right' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>First Name </b></font>
				</td>
				<td>
					[FIRST_FIELD]
				</td>
			</tr>
			<tr>
				<td align='right' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Last Name </b></font>
				</td>
				<td>
					[LAST_FIELD]
				</td>
			</tr>
			<tr>
				<td align='right' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Address </b></font>
				</td>
				<td>
					[ADDRESS_FIELD]
				</td>
			</tr>
			<tr>
				<td align='right' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>City </b></font>
				</td>
				<td>
					[CITY_FIELD]
				</td>
			</tr>
			<tr>
				<td align='right' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Zip Code </b></font>
				</td>
				<td>
					[ZIP_FIELD]
					[STATE_FIELD]
				</td>
			</tr>
			<tr>
				<td align='right' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Phone Number </b></font>
				</td>
				<td>
					[PHONE_GROUP]
				</td>
			</tr>
				<tr>
				<td align='right' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>E-Mail Address </b></font>
				</td>
				<td>
					[EMAIL_FIELD]
				</td>
			</tr>
			<tr>
				<td align='right' nowrap='nowrap' class='fieldnames'>Birth Date </td>
				<td>[DOB_GROUP]</td>
			</td></tr>
			<tr><td align='right' nowrap='nowrap' class='fieldnames'>Gender </td><td>
			    	[GENDER_RADIO]
				</td></tr>
	</table>";
	
	
	$_SESSION['sSesPage2ThinnerUserForm'] = "<style type='text/css'>
		.fieldnames {
			font: bold 13px Arial, Helvetica, sans-serif;
		}
		</style>
		
		<br><br>
			<table align='[ALIGN_LEFT_CENTER_RIGHT]' border='0' cellpadding='2' cellspacing='0'>
			<tr>
				<td align='left' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>First Name </b></font>
				</td>
			</tr><tr>
				<td>
					[FIRST_FIELD]
				</td>
			</tr>
			<tr>
				<td align='left' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Last Name </b></font>
				</td>
			</tr><tr>
				<td>
					[LAST_FIELD]
				</td>
			</tr>
			<tr>
				<td align='left' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Address </b></font>
				</td>
			</tr><tr>
				<td>
					[ADDRESS_FIELD]
				</td>
			</tr>
			<tr>
				<td align='left' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>City </b></font>
				</td>
			</tr><tr>
				<td>
					[CITY_FIELD]
				</td>
			</tr>
			<tr>
				<td align='left' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Zip Code</b></font>
				</td>
			</tr><tr>
				<td>
					[ZIP_FIELD]
					[STATE_FIELD]
				</td>
			</tr>
			<tr>
				<td align='left' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Phone Number</b></font>
				</td>
			</tr><tr>
				<td>
					[PHONE_GROUP]
				</td>
			</tr>
			<tr>
				<td align='left' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>E-Mail Address</b></font>
				</td>
			</tr><tr>
				<td>
					[EMAIL_FIELD]
				</td>
			</tr>
			<tr>
				<td align='left' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Birth Date</b></font>
				</td>
			</tr><tr>
				<td>
					[DOB_GROUP]
				</td>
			</tr>
		<tr>
				<td align='left' nowrap='nowrap' class='fieldnames'>
					<font size='2' face='Arial,Helvetica'><b>Gender</b></font>
				</td>
			</tr><tr>
				<td>
					[GENDER_RADIO]
				</td>
			</tr>
	</table>";
} else {
	if($_POST['PHPSESSID']){
		$PHPSESSID = $_POST['PHPSESSID'];
	} else {
		$PHPSESSID = $_GET['PHPSESSID'];
	}
	session_start();

	
	$iCurrMonth = date('m');
	$iCurrDay = date('d');
	$iCurrYear = date('Y');
	$iCurrYearTwoDigit = date('y');
	$iCurrHH = date('H');
	$iCurrMM = date('i');
	$iCurrSS = date('s');
			
}



if ($sSubmit == 'submit') {
	//if (count($_SESSION['aSesPage2Offers']) == 0) {
		if ($_SESSION['sSesFirst'] == '') { $_SESSION['sSesFirst'] = trim($_POST['sFirst']); }
		if ($_SESSION['sSesLast'] == '') { $_SESSION['sSesLast'] = trim($_POST['sLast']); }
		if ($_SESSION['sSesAddress'] == '') { $_SESSION['sSesAddress'] = trim($_POST['sAddress']); }
		if ($_SESSION['sSesAddress2'] == '') { $_SESSION['sSesAddress2'] = trim($_POST['sAddress2']); }
		if ($_SESSION['sSesCity'] == '') { $_SESSION['sSesCity'] = trim($_POST['sCity']); }
		if ($_SESSION['sSesState'] =='') { $_SESSION['sSesState'] = trim($_POST['sState']); }
		if ($_SESSION["sSesZip"] == '') { $_SESSION["sSesZip"] = trim($_POST['sZip']); }
		if ($_SESSION['sSesGender'] == '') { $_SESSION['sSesGender'] = trim($_POST['sGender']); }
		if ($_SESSION['sSesEmail'] == '') { $_SESSION['sSesEmail'] = trim($_POST['sEmail']); }
		if ($_SESSION['iSesBirthYear'] == '') { $_SESSION['iSesBirthYear'] = trim($_POST['iBirthYear']); }
		if ($_SESSION['iSesBirthMonth'] =='') { $_SESSION['iSesBirthMonth'] = trim($_POST['iBirthMonth']); }
		if ($_SESSION['iSesBirthDay']=='') { $_SESSION['iSesBirthDay'] = trim($_POST['iBirthDay']); }
		$_SESSION['sSesBirthDate'] = $_SESSION['iSesBirthYear'].'-'.$_SESSION['iSesBirthMonth'].'-'.$_SESSION['iSesBirthDay'];
		
		if ($_SESSION['sSesPhoneAreaCode'] == '') { $_SESSION['sSesPhoneAreaCode'] = trim($_POST['sPhone_areaCode']); }
		if ($_SESSION['sSesPhoneExchange']=='') { $_SESSION['sSesPhoneExchange'] = trim($_POST['sPhone_exchange']); }
		if ($_SESSION['sSesPhoneNumber']== '') { $_SESSION['sSesPhoneNumber'] = trim($_POST['sPhone_number']); }
		$_SESSION['sSesPhoneExtension'] = trim($_POST['sPhoneExtension']);
		$_SESSION['sSesPhoneNoDash'] = $_SESSION['sSesPhoneAreaCode'].$_SESSION['sSesPhoneExchange'].$_SESSION['sSesPhoneNumber'];
		$_SESSION['sSesPhone'] = $_SESSION['sSesPhoneAreaCode'].'-'.$_SESSION['sSesPhoneExchange'].'-'.$_SESSION['sSesPhoneNumber'];
		
		$sFirst = $_SESSION['sSesFirst'];
		$sLast = $_SESSION['sSesLast'];
		$sEmail = $_SESSION['sSesEmail'];
		$sAddress = $_SESSION['sSesAddress'];
		$sAddress2 = $_SESSION['sSesAddress2'];
		$sCity = $_SESSION['sSesCity'];
		$sState = $_SESSION['sSesState'];
		$sZip = $_SESSION['sSesZip'];
		$iBirthYear = $_SESSION['iSesBirthYear'];
		$iBirthMonth = $_SESSION['iSesBirthMonth'];
		$iBirthDay = $_SESSION['iSesBirthDay'];
		$sGender = $_SESSION['sSesGender'];
		$sSourceCode = $_SESSION['sSesSourceCode'];
		$sSubSourceCode = $_SESSION['sSesSubSourceCode'];
		
		// check if entry exists in active table
		$sActiveCheckQuery = "SELECT * FROM userData WHERE email = \"$sEmail\"";
		$rActiveCheckResult = dbQuery($sActiveCheckQuery);
			
		$sCurrentDateTime = date('Y-m-d H:i:s');
		$sBirthDate = $_SESSION['sSesBirthDate'];
		$sPhoneNo = $_SESSION['sSesPhone'];
			
		if ( dbNumRows($rActiveCheckResult) == 0 ) {
			$sInsertQuery = "INSERT INTO userData(email, first, last, address,
							address2, city, state, zip, phoneNo, dateTimeAdded, postalVerified, sessionId, remoteIp, 
							dateOfBirth, gender)
						 VALUES(\"$sEmail\", \"$sFirst\", \"$sLast\", \"$sAddress\", \"$sAddress2\", \"$sCity\", \"$sState\", 
							'$sZip', \"$sPhoneNo\",	'$sCurrentDateTime', 'V', '".session_id()."', 
							\"".$_SESSION['sSesRemoteIp']."\", \"$sBirthDate\", \"$sGender\")";
			$rInsertResult = dbQuery($sInsertQuery);
		} else {
			$sUpdateQuery = "UPDATE userData 
						SET first = \"$sFirst\", last = \"$sLast\", 
							address = \"$sAddress\", address2 = \"$sAddress2\", 
							city = \"$sCity\", state = \"$sState\",
							zip = '$sZip', phoneNo = \"$sPhoneNo\", 
							postalVerified = 'V', sessionId = '".session_id()."', 
							remoteIp = \"".$_SESSION['sSesRemoteIp']."\", 
							dateOfBirth = \"$sBirthDate\", gender = \"$sGender\" 
							WHERE email = \"$sEmail\"";
			$rUpdateResult = dbQuery( $sUpdateQuery );
		}

		// check if offer has page2 info
		$sTempOffersCode = $aOffersChecked[0];
		$iPage2Info = '';
		$iDeliveryMethodId = '';
		$sOfferQuery = "SELECT O.*, OL.deliveryMethodId, OL.singleEmailSubject, OL.singleEmailFromAddr, OL.singleEmailBody,
					OL.leadsEmailRecipients, OL.postingUrl, OL.httpPostString
					FROM   offers AS O LEFT JOIN offerLeadSpec AS OL 
					ON  O.offerCode = OL.offerCode
					WHERE	   O.offerCode = '".$sTempOffersCode."'";
		$rOfferResult = dbQuery($sOfferQuery);
		echo dbError();
		while ($oOfferRow = dbFetchObject($rOfferResult)) {
			$iDeliveryMethodId = '';
			$fRevPerLead = $oOfferRow->revPerLead;
			$iPage2Info = $oOfferRow->page2Info;
			$sOfferMode = $oOfferRow->mode;
			$iOfferAutoEmail = $oOfferRow->autoRespEmail;
			$sOfferAutoEmailFormat = $oOfferRow->autoRespEmailFormat;
			$sOfferAutoEmailSub = $oOfferRow->autoRespEmailSub;
			$sOfferAutoEmailBody = $oOfferRow->autoRespEmailBody;
			$sOfferAutoEmailFromAddr = $oOfferRow->autoRespEmailFromAddr;
		
			// get fields which are used to send real time email
			$iDeliveryMethodId = $oOfferRow->deliveryMethodId;
			$sPostingUrl = $oOfferRow->postingUrl;
			$sHttpPostString = $oOfferRow->httpPostString;
			$sLeadsEmailRecipients = $oOfferRow->leadsEmailRecipients;
			$sSingleEmailFromAddr = $oOfferRow->singleEmailFromAddr;
			$sSingleEmailSubject = $oOfferRow->singleEmailSubject;
			$sSingleEmailBody = $oOfferRow->singleEmailBody;
			$sOfferType = $oOfferRow->offerType;

			$sDeliveryMethodQuery = "SELECT *
								 FROM   deliveryMethods
								 WHERE  id = '$iDeliveryMethodId'";
			$rDeliveryMethodResult = dbQuery($sDeliveryMethodQuery);
			while ($oDeliveryMethodRow = dbFetchObject($rDeliveryMethodResult)) {
				$sHowSent = $oDeliveryMethodRow->shortMethod;
			}
		}

		if ($sOfferType == 'CTH' || $sOfferType == 'OTH_CTH') {
			$_SESSION['sSesOfferType'] = 'CTH';
			$iPage2Info = '1';
		}
		
		/*
		if ($iPage2Info) {
			array_push($_SESSION['aSesPage2Offers'], $sTempOffersCode);
			$_SESSION['bSubmit'] = false;
		} else {*/
			// If doesn't have page2Info, record the lead
			// put the checked offer in offersTaken array
			// If return false, then insert into otData.
			// User already took offer if return true.
			$bFoundDuplicateLead = checkForOtDataDups ($sEmail,$sTempOffersCode);
		
			$sOfferIsCoRegQuery = "SELECT * FROM offers WHERE offerCode = '".$sTempOffersCode."' AND 
				((isCoRegPopUp = 'Y' and isCoRegPopPixelEnable != 'Y') OR 
		 		 (isCloseTheyHost = 'Y' and isCloseTheyHostPixelEnable != 'Y') OR 
		 		 (isCloseTheyHost = 'N' and isCoRegPopUp = 'N'))";
			$rOfferIsCoRegResult = dbQuery($sOfferIsCoRegQuery);
					
			//mail('bbevis@amperemedia.com',__line__.': conditions',dbNumRows($rOfferIsCoRegResult)." is dbnumrows\n\n, should be >0");
			if (dbNumRows($rOfferIsCoRegResult)) {
				if ($bFoundDuplicateLead == false) {
					$sCurrentDateTime = date('Y-m-d H:i:s');
					$sLeadInsertQuery = "INSERT IGNORE INTO otData (email, offerCode, revPerLead, sourceCode, 
							subSourceCode, pageId, dateTimeAdded, remoteIp, serverIp, mode, 
								sessionId, postalVerified)
								VALUES('".$_SESSION['sSesEmail']."', \"".$sTempOffersCode."\", 
								\"$fRevPerLead\", \"".$_SESSION["sSesSourceCode"]."\", \"".$_SESSION["sSesSubSourceCode"]."\", 
								'', '$sCurrentDateTime', '".$_SESSION["sSesRemoteIp"]."', '".$_SESSION["sSesServerIp"]."', 
								'T', '".session_id()."', 'V')";
					$rLeadInsertResult = dbQuery($sLeadInsertQuery);
					
					//mail('bbevis@amperemedia.com',__line__.": insert query",$sLeadInsertQuery."\n\n".dbError());
				}
			}

			// Send Offer Auto Responder email if set to do so
			if ($iOfferAutoEmail) {
				$sOfferAutoEmailBody = eregi_replace("\[EMAIL\]", $sEmail, $sOfferAutoEmailBody);
				$sOfferAutoEmailHeaders = "From: $sOfferAutoEmailFromAddr\r\n";
				$sOfferAutoEmailHeaders .= "X-Mailer: MyFree.com\r\n";
				if ($sOfferAutoEmailFormat == 'html') {
					$sOfferAutoEmailHeaders .= "Content-Type: text/html; charset=iso-8859-1\r\n"; // Mime type
				}
				mail($sEmail, $sOfferAutoEmailSub, $sOfferAutoEmailBody, $sOfferAutoEmailHeaders);
			}

			// ********************* post real time leads *************************
			// following condition commented to allow posting test leads in real time if "3401 dundee" address not used
			// for offer taken stat info
			$sOfferTakenStatInfo .= $sTempOffersCode. ",";
			$sRealTimeResponse = '';
			
			if ($bFoundDuplicateLead == false && !(strtolower(substr($_SESSION["sSesAddress"],0,11))=='3401 dundee' && $_SESSION["sSesZip"]=='60062')) {
				if ($iDeliveryMethodId == 2 || $iDeliveryMethodId == 3) {
					// *************************** Start posting real time lead ********************
					// 2 = real time form post - GET
					// 3 = real time form post - POST
								
					if ($sTempOffersCode == 'MS_Eversave') {
						if ($iBirthMonth < 10) {
							$iBirthMonth = substr($iBirthMonth,1,1);
						}
						if ($iBirthDay < 10) {
							$iBirthDay = substr($iBirthDay,1,1);
						}
					}
		
					$aUrlArray = explode("//", $sPostingUrl);
					$sUrlPart = $aUrlArray[1];
					
					$sBinGender = (($sGender ? $sGender : $_SESSION["sSesGender"]) == 'M' ? '1' :(($sGender ? $sGender : $_SESSION["sSesGender"]) == 'F' ? '0' : ''));
					
					$sHttpPostString = ereg_replace("\[salutation\]",urlencode(($sSalutation ? $sSalutation : $_SESSION["sSesSalutation"])), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[email\]",urlencode($_SESSION['sSesEmail']), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[first\]",urlencode(($sFirst ? $sFirst : $_SESSION["sSesFirst"])), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[last\]",urlencode(($sLast ? $sLast : $_SESSION["sSesLast"])), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[address\]",urlencode(($sAddress ? $sAddress : $_SESSION["sSesAddress"])), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[address2\]",urlencode(($sAddress2 ? $sAddress2 : $_SESSION["sSesAddress2"])), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[city\]",urlencode(($sCity ? $sCity : $_SESSION["sSesCity"])), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[state\]",urlencode(($sState ? $sState : $_SESSION["sSesState"])), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[zip\]",urlencode(($sZip ? $sZip : $_SESSION["sSesZip"])), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[zip5only\]",urlencode(substr(($sZip ? $sZip : $_SESSION["sSesZip"]), 0, 5)), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[phone\]",urlencode(($sPhone ? $sPhone : $_SESSION["sSesPhone"])), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[ipAddress\]",urlencode(($sRemoteIp ? $sRemoteIp : $_SESSION["sSesRemoteIp"])), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[phone_areaCode\]", urlencode($_SESSION['sSesPhoneAreaCode']), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[phone_exchange\]", urlencode($_SESSION['sSesPhoneExchange']), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[phone_number\]", urlencode($_SESSION['sSesPhoneNumber']), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[mm\]", urlencode(date('m')), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[dd\]", urlencode(date('d')), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[yyyy\]", urlencode(date('Y')), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[yy\]", urlencode(date('y')), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[hh\]", urlencode(date('H')), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[ii\]", urlencode(date('i')), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[ss\]", urlencode(date('s')), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[birthYear\]", urlencode(($iBirthYear ? $iBirthYear : $_SESSION["iSesBirthYear"])), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[birthMonth\]", urlencode(($iBirthMonth ? $iBirthMonth : $_SESSION["iSesBirthMonth"])), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[birthDay\]", urlencode(($iBirthDay ? $iBirthDay : $_SESSION["iSesBirthDay"])), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[gender\]", urlencode(($sGender ? $sGender : $_SESSION["sSesGender"])), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[binary_gender\]", urlencode($sBinGender), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[sourcecode\]", urlencode($_SESSION["sSesSourceCode"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[revSrc\]", urlencode($_SESSION["sSesRevSourceCode"]), $sHttpPostString);
							
					if ($sTempOffersCode == 'MS_Eversave') {
						if ($iBirthMonth < 10) {
							$iBirthMonth = '0'.$iBirthMonth;
						}
						if ($iBirthDay < 10) {
							$iBirthDay = '0'.$iBirthDay;
						}
					}
		
					// separate host part and script path
					$sResult = httpFormPostGet ($sHttpPostString,$sUrlPart,$sPostingUrl,$iDeliveryMethodId,$sTempOffersCode,$_SESSION['sSesEmail'],$sHowSent);
					// *************************** End posting real time lead ********************
				} else if ($iDeliveryMethodId == 4) {
					// send lead email if lead delivery method set as "Real Time Email"
					// only if mode is active
	
					$sSingleEmailHeaders = "From: $sSingleEmailFromAddr\r\n";
					$sSingleEmailHeaders .= "X-Mailer: MyFree.com\r\n";
		
					$sSingleEmailSubject = ereg_replace("\[offerCode\]",$sTempOffersCode, $sSingleEmailSubject);
					if (strstr($sSingleEmailSubject,"[d-")) {
						//get date arithmetic number
						$iDateArithNum = substr($sSingleEmailSubject,strpos($sSingleEmailSubject,"[d-")+3,1);
									
						$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
						$rTempResult = dbQuery($sTempQuery);
						while ($oTempRow = dbFetchObject($rTempResult)) {
							$sNewDate = $oTempRow->newDate;
						}
		
						if ($rTempResult) {
							dbFreeResult($rTempResult);
						}
		
						$sSingleEmailSubject = ereg_replace("\[dd\]", substr($sNewDate,8,2), $sSingleEmailSubject);
						$sSingleEmailSubject = ereg_replace("\[mm\]", substr($sNewDate,5,2), $sSingleEmailSubject);
						$sSingleEmailSubject = ereg_replace("\[yyyy\]", substr($sNewDate,0,4), $sSingleEmailSubject);
						$sDateArithString = substr($sSingleEmailSubject, strpos($sSingleEmailSubject,"[d-"),5);
						$sSingleEmailSubject = str_replace($sDateArithString, "", $sSingleEmailSubject);
					} else {
						$sSingleEmailSubject = ereg_replace("\[dd\]", date(d), $sSingleEmailSubject);
						$sSingleEmailSubject = ereg_replace("\[mm\]", date(m), $sSingleEmailSubject);
						$sSingleEmailSubject = ereg_replace("\[yyyy\]", date(Y), $sSingleEmailSubject);
					}
							
					$sBinGender = (($sGender ? $sGender : $_SESSION["sSesGender"]) == 'M' ? '1' :(($sGender ? $sGender : $_SESSION["sSesGender"]) == 'F' ? '0' : ''));
							
					$sSingleEmailBody = ereg_replace("\[salutation\]",urlencode(($sSalutation ? $sSalutation : $_SESSION["sSesSalutation"])), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[email\]",urlencode($_SESSION['sSesEmail']), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[first\]",urlencode(($sFirst ? $sFirst : $_SESSION["sSesFirst"])), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[last\]",urlencode(($sLast ? $sLast : $_SESSION["sSesLast"])), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[address\]",urlencode(($sAddress ? $sAddress : $_SESSION["sSesAddress"])), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[address2\]",urlencode(($sAddress2 ? $sAddress2 : $_SESSION["sSesAddress2"])), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[city\]",urlencode(($sCity ? $sCity : $_SESSION["sSesCity"])), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[state\]",urlencode(($sState ? $sState : $_SESSION["sSesState"])), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[zip\]",urlencode(($sZip ? $sZip : $_SESSION["sSesZip"])), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[zip5only\]",urlencode(substr(($sZip ? $sZip : $_SESSION["sSesZip"]), 0, 5)), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[phone\]",urlencode(($sPhone ? $sPhone : $_SESSION["sSesPhone"])), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[ipAddress\]",urlencode(($sRemoteIp ? $sRemoteIp : $_SESSION["sSesRemoteIp"])), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[phone_areaCode\]", urlencode(($sPhone_areaCode ? $sPhone_areaCode : $_SESSION['sSesPhoneAreaCode'])), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[phone_exchange\]", urlencode(($sPhone_exchange ? $sPhone_exchange : $_SESSION['sSesPhoneExchange'])), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[phone_number\]", urlencode(($sPhone_number ? $sPhone_number : $_SESSION['sSesPhoneNumber'])), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[mm\]", urlencode(date('m')), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[dd\]", urlencode(date('d')), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[yyyy\]", urlencode(date('Y')), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[yy\]", urlencode(date('y')), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[hh\]", urlencode(date('H')), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[ii\]", urlencode(date('i')), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[ss\]", urlencode(date('s')), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[birthYear\]", urlencode(($iBirthYear ? $iBirthYear : $_SESSION["iSesBirthYear"])), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[birthMonth\]", urlencode(($iBirthMonth ? $iBirthMonth : $_SESSION["iSesBirthMonth"])), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[birthDay\]", urlencode(($iBirthDay ? $iBirthDay : $_SESSION["iSesBirthDay"])), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[gender\]", urlencode(($sGender ? $sGender : $_SESSION["sSesGender"])), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[binary_gender\]", urlencode($sBinGender), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[sourcecode\]", urlencode($_SESSION["sSesSourceCode"]), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[revSrc\]", urlencode($_SESSION["sSesRevSourceCode"]), $sSingleEmailBody);
									
					$aSingleEmailBodyArray = explode("\\r\\n",$sSingleEmailBody);
					$sSingleEmailBody = "";
			
					for ($x=0;$x<count($aSingleEmailBodyArray);$x++) {
						$sSingleEmailBody .= $aSingleEmailBodyArray[$x]."\r\n";
					}
					mail($sLeadsEmailRecipients, $sSingleEmailSubject, $sSingleEmailBody, $sSingleEmailHeaders);

					$sUpdateStatusQuery = "UPDATE otData
								   SET    processStatus = 'P',
										  sendStatus = 'S',
										  howSent = '$sHowSent'
								   WHERE  email='".$_SESSION['sSesEmail']."' AND offerCode='".$sTempOffersCode."'";
					$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
				}
			} // end if test lead
			if ($_SESSION["sSesRedirectUrl"] != '') {
				echo "<script language=JavaScript>parent.location='".$_SESSION["sSesRedirectUrl"]."'</script>";
			} else {
				echo "<script language=JavaScript>parent.location='/p/onetime.php?".SID."'</script>";
			}
		//}
	//}
	//mail('bbevis@amperemedia.com',__line__.': conditions',count($_SESSION['aSesPage2Offers']).' is count(($_SESSION[\'aSesPage2Offers\']))'."\n".($_SESSION['bSubmit'] ? 'true' : 'false').' is $_SESSION[\'bSubmit\']'."\n\n, should be >0 && true");
	if (count($_SESSION['aSesPage2Offers']) > 0 && $_SESSION['bSubmit'] == true) {
		$sCurrentDateTime = date('Y-m-d H:i:s');
		$sOfferCode = $_SESSION['aSesPage2Offers'][0];
		$sPage2Data = '';
		$sLeadPage2Data = '';

		// Get all Page2 Fields from page2Map in nibbles database.
		$sPage2MapQuery = "SELECT *
						   FROM   page2Map
			 			   WHERE offerCode = '".$sOfferCode."'
			 			   ORDER BY storageOrder ";
		$rPage2MapResult = dbQuery($sPage2MapQuery);
		// to track empty page2Data - UNKNOWN
	
		// Loop through all Page2 Field Names.
		while ($oPage2MapRow = dbFetchObject($rPage2MapResult)) {
			$sActualFieldName = $oPage2MapRow->actualFieldName;
			$_SESSION['page2'][$sActualFieldName] = ${$sActualFieldName};
			$sLeadPage2Data .= "\r\n$sActualFieldName: ".${$sActualFieldName};
			$sPage2Data .= "\"".${$sActualFieldName}."\"|";
		}
		
		$sPage2Data = addslashes($sPage2Data);
		

		/********************* Loop through page2 offers ***************************/
		// If any offer dropped, at this point, it was removed from $_SESSION["aSesPage2Offers"]
			/************************ Get offer details  ****************************/
			$iDeliveryMethodId = '';
			$sOfferQuery = "SELECT O.*, OL.deliveryMethodId, OL.singleEmailSubject, OL.singleEmailFromAddr, OL.singleEmailBody,
				   OL.leadsEmailRecipients, OL.postingUrl, OL.httpPostString
					FROM   offers AS O, offerLeadSpec AS OL
					WHERE  O.offerCode = OL.offerCode
					AND	   O.offerCode = '".$sOfferCode."'";
		
			$rOfferResult = dbQuery($sOfferQuery);
			echo dbError();
			while ($oOfferRow = dbFetchObject($rOfferResult)) {
				$iDeliveryMethodId = '';
				$fRevPerLead = $oOfferRow->revPerLead;
				$iOfferAutoEmail = $oOfferRow->autoRespEmail;
				$sOfferAutoEmailFormat = $oOfferRow->autoRespEmailFormat;
				$sOfferAutoEmailSub = $oOfferRow->autoRespEmailSub;
				$sOfferAutoEmailBody = $oOfferRow->autoRespEmailBody;
				$sOfferAutoEmailFromAddr = $oOfferRow->autoRespEmailFromAddr;
	
				// get fields which are used to send real time email
				$iDeliveryMethodId = $oOfferRow->deliveryMethodId;
				$sPostingUrl = $oOfferRow->postingUrl;
				$sHttpPostString = $oOfferRow->httpPostString;
				$sLeadsEmailRecipients = $oOfferRow->leadsEmailRecipients;
				$sSingleEmailFromAddr = $oOfferRow->singleEmailFromAddr;
				$sSingleEmailSubject = $oOfferRow->singleEmailSubject;
				$sSingleEmailBody = $oOfferRow->singleEmailBody;
	
				$sDeliveryMethodQuery = "SELECT *
						 FROM   deliveryMethods
						 WHERE  id = '$iDeliveryMethodId'";
				$rDeliveryMethodResult = dbQuery($sDeliveryMethodQuery);
				while ($oDeliveryMethodRow = dbFetchObject($rDeliveryMethodResult)) {
					$sHowSent = $oDeliveryMethodRow->shortMethod;
				}
			}
			
			// If return false, then insert into otData.
			// User already took offer if return true.
			$bFoundDuplicateLead = checkForOtDataDups ($_SESSION["sSesEmail"],$sOfferCode);
			
			//mail('bbevis@amperemedia.com',__line__.": we're inside the loop.",($bFoundDuplicateLead == false ? 'false' : 'true')."\n\n should be 'false', $sOfferCode is sOfferCode, ".$_SESSION["sSesEmail"]." is email");
			
			if ($bFoundDuplicateLead == false) {
				$sLeadInsertQuery = "INSERT IGNORE INTO otData(email, offerCode, revPerLead, sourceCode, subSourceCode, pageId, dateTimeAdded, remoteIp, serverIp, page2Data, mode, sessionId, postalVerified )
					 VALUES(\"".$_SESSION["sSesEmail"]."\", \"".$sOfferCode."\", \"$fRevPerLead\", \"".$_SESSION["sSesSourceCode"]."\",  \"".$_SESSION["sSesSubSourceCode"].
					"\", \"".$_SESSION["iSesPageId"]."\", '$sCurrentDateTime', '".$_SESSION["sSesRemoteIp"]."', '".$_SESSION["sSesServerIp"]."', \"".$sPage2Data."\", 
					'T', '".session_id()."', 'V')";
				$rLeadInsertResult = dbQuery($sLeadInsertQuery);
				//mail('bbevis@amperemedia.com',__line__.": insert query",$sLeadInsertQuery."\n\n".dbError());
			}
			
	
			/************** send offer auto email if offer is set to do so ***************/
			if ($iOfferAutoEmail) {
				$sOfferAutoEmailBody = eregi_replace("\[EMAIL\]", $_SESSION["sSesEmail"], $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[salutation\]",urlencode($sSalutation), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[email\]",urlencode($sEmail), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[first\]",urlencode($sFirst), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[last\]",urlencode($sLast), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[address\]",urlencode($sAddress), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[address2\]",urlencode($sAddress2), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[city\]",urlencode($sCity), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[state\]",urlencode($sState), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[zip\]",urlencode($sZip), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[zip5only\]",urlencode(substr($sZip, 0, 5)), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[phone\]",urlencode($sPhone), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[ipAddress\]",urlencode($sRemoteIp), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[phone_areaCode\]", urlencode($sPhone_areaCode), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[phone_exchange\]", urlencode($sPhone_exchange), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[phone_number\]", urlencode($sPhone_number), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[mm\]", urlencode($iCurrMonth), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[dd\]", urlencode($iCurrDay), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[yyyy\]", urlencode($iCurrYear), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[yy\]", urlencode($iCurrYearTwoDigit), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[hh\]", urlencode($iCurrHH), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[ii\]", urlencode($iCurrMM), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[ss\]", urlencode($iCurrSS), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[sourcecode\]", urlencode($_SESSION["sSesSourceCode"]), $sOfferAutoEmailBody);
				$sOfferAutoEmailBody = ereg_replace("\[revSrc\]", urlencode($_SESSION["sSesRevSourceCode"]), $sOfferAutoEmailBody);
				
				$sOfferAutoEmailHeaders = "From: $sOfferAutoEmailFromAddr\r\n";
				$sOfferAutoEmailHeaders .= "X-Mailer: MyFree.com\r\n";
				if ($sOfferAutoEmailFormat == 'html') {
					$sOfferAutoEmailHeaders .= "Content-Type: text/html; charset=iso-8859-1\r\n"; // Mime type
				}
				mail($_SESSION["sSesEmail"], $sOfferAutoEmailSub, $sOfferAutoEmailBody, $sOfferAutoEmailHeaders);
			}
			/*************************** End sending offer auto responder ********************/
			
		
			/*********************** Send real time leads ***********************/
			if ($bFoundDuplicateLead == false && !(strtolower(substr($_SESSION["sSesAddress"],0,11)) == '3401 dundee' && $_SESSION["sSesZip"] == '60062') ) {
				$sRealTimeResponse = '';
				if ($iDeliveryMethodId == 2 || $iDeliveryMethodId == 3) {
					// 2 = real time form post - GET
					// 3 = real time form post - POST
					$aUrlArray = explode("//", $sPostingUrl);
					$sUrlPart = $aUrlArray[1];
					
					if ($sOfferCode == 'MS_Eversave') {
						if ($_SESSION["iSesBirthMonth"] < 10) {
							$_SESSION["iSesBirthMonth"] = substr($_SESSION["iSesBirthMonth"],1,1);
						}
						if ($_SESSION["iSesBirthDay"] < 10) {
							$_SESSION["iSesBirthDay"] = substr($_SESSION["iSesBirthDay"],1,1);
						}
					}
		
					$sHttpPostString = ereg_replace("\[salutation\]",urlencode($_SESSION["sSesSalutation"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[email\]",urlencode($_SESSION["sSesEmail"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[first\]",urlencode($_SESSION["sSesFirst"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[last\]",urlencode($_SESSION["sSesLast"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[address\]",urlencode($_SESSION["sSesAddress"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[address2\]",urlencode($_SESSION["sSesAddress2"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[city\]",urlencode($_SESSION["sSesCity"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[state\]",urlencode($_SESSION["sSesState"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[zip\]",urlencode($_SESSION["sSesZip"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[zip5only\]",urlencode(substr($_SESSION["sSesZip"],0,5)), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[phone\]",urlencode($_SESSION["sSesPhone"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[ipAddress\]",urlencode($_SESSION["sSesRemoteIp"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[phone_areaCode\]", urlencode($_SESSION['sSesPhoneAreaCode']), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[phone_exchange\]", urlencode($_SESSION['sSesPhoneExchange']), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[phone_number\]", urlencode($_SESSION['sSesPhoneNumber']), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[mm\]", urlencode($iCurrMonth), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[dd\]", urlencode($iCurrDay), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[yyyy\]", urlencode($iCurrYear), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[yy\]", urlencode($iCurrYearTwoDigit), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[hh\]", urlencode($iCurrHH), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[ii\]", urlencode($iCurrMM), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[ss\]", urlencode($iCurrSS), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[sourcecode\]", urlencode($_SESSION["sSesSourceCode"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[birthYear\]", urlencode($_SESSION["iSesBirthYear"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[birthMonth\]", urlencode($_SESSION["iSesBirthMonth"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[birthDay\]", urlencode($_SESSION["iSesBirthDay"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[gender\]", urlencode($_SESSION["sSesGender"]), $sHttpPostString);
					$sHttpPostString = ereg_replace("\[revSrc\]", urlencode($_SESSION["sSesRevSourceCode"]), $sHttpPostString);
						
					if ($sOfferCode == 'MS_Eversave') {
						if ($_SESSION["iSesBirthMonth"] < 10) {
							$_SESSION["iSesBirthMonth"] = '0'.$_SESSION["iSesBirthMonth"];
						}
						if ($_SESSION["iSesBirthDay"] < 10) {
							$_SESSION["iSesBirthDay"] = '0'.$_SESSION["iSesBirthDay"];
						}
					}
								
					// get all the page2 fields of this offer and replace
					$sPage2MapQuery = "SELECT *
								   FROM   page2Map
				 	 			   WHERE offerCode = '$sOfferCode'
				 				   ORDER BY storageOrder ";
	
					$rPage2MapResult = dbQuery($sPage2MapQuery);
					$f = 1;
					while ($oPage2MapRow = dbFetchObject($rPage2MapResult)) {
						$sActualFieldName = $oPage2MapRow->actualFieldName;
						$sFieldVar = "FIELD".$f;
						$sHttpPostString = eregi_replace("\[$sFieldVar\]",urlencode($$sActualFieldName), $sHttpPostString);
						$f++;
					}
			
					// separate host part and script path
					$sResult = httpFormPostGet($sHttpPostString,$sUrlPart,$sPostingUrl,$iDeliveryMethodId,$sOfferCode,$_SESSION["sSesEmail"],$sHowSent);
					//mail('bbevis@amperemedia.com', __line__.'asdf',"$sHttpPostString\n\n,$sUrlPart\n\n,$sPostingUrl\n\n,$iDeliveryMethodId\n\n,$sOfferCode\n\n,$sEmail\n\n,$sHowSent\n\n");
				} else if ($iDeliveryMethodId == 4) {
					// send lead email if lead delivery method set as real time email
					// only if mode is active
					$sEmail = $_SESSION["sSesEmail"];
	
					$sSingleEmailHeaders = "From: $sSingleEmailFromAddr\r\n";
					$sSingleEmailHeaders .= "X-Mailer: MyFree.com\r\n";
					$sSingleEmailSubject = ereg_replace("\[offerCode\]",$sOfferCode, $sSingleEmailSubject);
	
	
					if (strstr($sSingleEmailSubject,"[d-")) {
						//get date arithmetic number
						$iDateArithNum = substr($sSingleEmailSubject,strpos($sSingleEmailSubject,"[d-")+3,1);
						$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
						$rTempResult = dbQuery($sTempQuery);
						while ($oTempRow = dbFetchObject($rTempResult)) {
							$sNewDate = $oTempRow->newDate;
						}
			
						$sSingleEmailSubject = ereg_replace("\[dd\]", substr($sNewDate, 8, 2), $sSingleEmailSubject);
						$sSingleEmailSubject = ereg_replace("\[mm\]", substr($sNewDate, 5, 2), $sSingleEmailSubject);
						$sSingleEmailSubject = ereg_replace("\[yyyy\]", substr($sNewDate, 0, 4), $sSingleEmailSubject);
						$sDateArithString = substr($sSingleEmailSubject, strpos($sSingleEmailSubject,"[d-"),5);
						$sSingleEmailSubject = str_replace($sDateArithString, '', $sSingleEmailSubject);
					} else {
						$sSingleEmailSubject = ereg_replace("\[dd\]", date(d), $sSingleEmailSubject);
						$sSingleEmailSubject = ereg_replace("\[mm\]", date(m), $sSingleEmailSubject);
						$sSingleEmailSubject = ereg_replace("\[yyyy\]", date(Y), $sSingleEmailSubject);
					}
				
					$sSingleEmailBody = ereg_replace("\[email\]",$_SESSION["sSesEmail"], $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[salutation\]",$_SESSION["sSesSalutation"], $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[first\]",$_SESSION["sSesFirst"], $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[last\]",$_SESSION["sSesLast"], $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[address\]",$_SESSION["sSesAddress"], $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[address2\]",$_SESSION["sSesAddress2"], $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[city\]",$_SESSION["sSesCity"], $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[state\]",$_SESSION["sSesState"], $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[zip\]",$_SESSION["sSesZip"], $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[zip5only\]",substr($_SESSION["sSesZip"],0,5), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[phone\]",$_SESSION["sSesPhone"], $sSingleEmailBody);
	            	$sSingleEmailBody = ereg_replace("\[phone_areaCode\]",substr($_SESSION["sSesPhone"],0,3), $sSingleEmailBody);
	            	$sSingleEmailBody = ereg_replace("\[phone_exchange\]",substr($_SESSION["sSesPhone"],4,3), $sSingleEmailBody);
	  	        	$sSingleEmailBody = ereg_replace("\[phone_number\]",substr($_SESSION["sSesPhone"],8,4), $sSingleEmailBody); 
					$sSingleEmailBody = ereg_replace("\[ipAddress\]",$_SESSION["sSesRemoteIp"], $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[mm\]", urlencode($iCurrMonth), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[dd\]", urlencode($iCurrDay), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[yyyy\]", urlencode($iCurrYear), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[yy\]", urlencode($iCurrYearTwoDigit), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[hh\]", urlencode($iCurrHH), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[ii\]", urlencode($iCurrMM), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[ss\]", urlencode($iCurrSS), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[birthYear\]", urlencode($_SESSION["iSesBirthYear"]), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[birthMonth\]", urlencode($_SESSION["iSesBirthMonth"]), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[birthDay\]", urlencode($_SESSION["iSesBirthDay"]), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[gender\]", urlencode($_SESSION["sSesGender"]), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[sourcecode\]", urlencode($_SESSION["sSesSourceCode"]), $sSingleEmailBody);
					$sSingleEmailBody = ereg_replace("\[revSrc\]", urlencode($_SESSION["sSesRevSourceCode"]), $sSingleEmailBody);
		
					// get all the page2 fields of this offer and replace
					$sPage2MapQuery = "SELECT *
								   FROM   page2Map
				 	 			   WHERE offerCode = '$sOfferCode'
				 				   ORDER BY storageOrder ";
					$rPage2MapResult = dbQuery($sPage2MapQuery);
					$f = 1;
					while ($oPage2MapRow = dbFetchObject($rPage2MapResult)) {
						$sActualFieldName = $oPage2MapRow->actualFieldName;
						$sFieldVar = "FIELD".$f;
						$sSingleEmailBody = eregi_replace("\[$sFieldVar\]",$$sActualFieldName, $sSingleEmailBody);
						$f++;
					}
			
					$aSingleEmailBodyArray = explode("\\r\\n",$sSingleEmailBody);
					$sSingleEmailBody = '';
					for ( $x = 0; $x < count($aSingleEmailBodyArray); $x++ ) {
						$sSingleEmailBody .= $aSingleEmailBodyArray[$x]."\r\n";
					}
		
					mail($sLeadsEmailRecipients, $sSingleEmailSubject, $sSingleEmailBody, $sSingleEmailHeaders);
					$sCurrentDateTime = date('Y-m-d H:i:s');
					$sUpdateStatusQuery = "UPDATE otData
									   		SET   processStatus = 'P',
												  sendStatus = 'S',
												  dateTimeProcessed = '$sCurrentDateTime',
												  dateTimeSent = '$sCurrentDateTime',
											 	  howSent = '$sHowSent'
									 	  WHERE   email='$sEmail' and offerCode='$sOfferCode'";
					$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
				}
			}
			// **********************  End sending real time leads  *****************
			if ($_SESSION["sSesRedirectUrl"] != '') {
				echo "<script language=JavaScript>parent.location='".$_SESSION["sSesRedirectUrl"]."'</script>";
			} else {
				echo "<script language=JavaScript>parent.location='/p/onetime.php?".SID."'</script>";
			}
		//}
	}
}



if (count($_SESSION['aSesPage2Offers']) == 0) {
	$sOffersQuery = "SELECT * FROM offers WHERE  offerCode = '$sOfferCode'";
	$rOffersResult = dbQuery($sOffersQuery);
	if (dbNumRows($rOffersResult) > 0) {
		while ($oOffersRow = dbFetchObject($rOffersResult)) {
			if($oOffersRow->page2Template != ''){
				array_push($_SESSION['aSesPage2Offers'],$sOfferCode);	
			}
			$sOfferChecked = '';
			$sOfferCode = $oOffersRow->offerCode;
			$sOfferHeadline = $oOffersRow->headline;
			$sOfferDescription = $oOffersRow->shortDescription;
			$sOfferImageName = $oOffersRow->smallImageName;
			$iPrecheck = $oOffersRow->precheck;
			$sAddiInfoFormat = $oOffersRow->addiInfoFormat;
			$sAddiInfoTitle = $oOffersRow->addiInfoTitle;
			$sAddiInfoText = $oOffersRow->addiInfoText;
			$sAddiInfoPopupSize = $oOffersRow->addiInfoPopupSize;
			$sOfferMode = $oOffersRow->mode;
			
			$sIsCoRegPopup = $oOffersRow->isCoRegPopUp;
			$sIsCoRegPopupPassOnCode = $oOffersRow->coRegPopPassOnPrepopCodes;
			$sCoRegVarMap = $oOffersRow->coRegPopPassOnCodeVarMap;
			$sCoRegPopupUrl = $oOffersRow->coRegPopUrl;
			
			$sCoRegPopTrigger = $oOffersRow->coRegPopUpTriggerOn;
			
			
			$sCoRegOutBoundPassOnCode = '';
			$sOnClickPopUpCoRegPopup = '';
			if ($sIsCoRegPopup == 'Y' && $sIsCoRegPopupPassOnCode == 'Y') {
				if ($sCoRegVarMap !='') {	// Replace our vars with client's var - outbound query
					$aPassOnCodeVarMap = explode(",",$sPassOnCodeVarMap);
					for ($i=0; $i<count($aPassOnCodeVarMap); $i++) {
						$aKeyValuePair = explode("=",$aPassOnCodeVarMap[$i]);
						if ($aKeyValuePair[0] == 'e') { $sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesEmail"]."&"; }
						if ($aKeyValuePair[0] == 'f') {	$sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesFirst"]."&"; }
						if ($aKeyValuePair[0] == 'l') {	$sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesLast"]."&"; }
						if ($aKeyValuePair[0] == 'a1') { $sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesAddress"]."&"; }
						if ($aKeyValuePair[0] == 'a2') { $sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesAddress2"]."&"; }
						if ($aKeyValuePair[0] == 'c') {	$sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesCity"]."&"; }
						if ($aKeyValuePair[0] == 's') {	$sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesState"]."&"; }
						if ($aKeyValuePair[0] == 'z') {	$sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesZip"]."&"; }
						if ($aKeyValuePair[0] == 'p') {	$sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesPhone"]."&"; }
						if ($aKeyValuePair[0] == 'pnd') { $sCoRegOutBoundPassOnCode .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesPhoneNoDash"]."&"; }
					}
				} else {	// use our vars
					$sCoRegOutBoundPassOnCode .= "e=".$_SESSION["sSesEmail"]."&";
					$sCoRegOutBoundPassOnCode .= "f=".$_SESSION["sSesFirst"]."&";
					$sCoRegOutBoundPassOnCode .= "l=".$_SESSION["sSesLast"]."&";
					$sCoRegOutBoundPassOnCode .= "a1=".$_SESSION["sSesAddress"]."&";
					$sCoRegOutBoundPassOnCode .= "a2=".$_SESSION["sSesAddress2"]."&";
					$sCoRegOutBoundPassOnCode .= "c=".$_SESSION["sSesCity"]."&";
					$sCoRegOutBoundPassOnCode .= "s=".$_SESSION["sSesState"]."&";
					$sCoRegOutBoundPassOnCode .= "z=".$_SESSION["sSesZip"]."&";
					$sCoRegOutBoundPassOnCode .= "p=".$_SESSION["sSesPhone"]."&";
					$sCoRegOutBoundPassOnCode .= "pnd=".$_SESSION["sSesPhoneNoDash"]."&";
				}
				$sCoRegOutBoundPassOnCode .= "sesId=".session_id()."&";
				$sCoRegOutBoundPassOnCode = substr($sCoRegOutBoundPassOnCode,0, strlen($sCoRegOutBoundPassOnCode)-1);

				if ($sCoRegOutBoundPassOnCode != '') {
					$sCoRegPopupUrl = $sCoRegPopupUrl.'?'.$sCoRegOutBoundPassOnCode;
				}
			}

			$sYesCoRegPopOnClick = '';
			$sNoCoRegPopOnClick = '';
			if ($sIsCoRegPopup == 'Y') {
				if ($sCoRegPopTrigger == 'Y') {
					$sYesCoRegPopOnClick = "onClick=\"window.open('$sCoRegPopupUrl','','width=800,height=650,top=0,directories=no,location=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,menubar=no');\"";
				} else {
					$sNoCoRegPopOnClick = "onClick=\"window.open('$sCoRegPopupUrl','','width=800,height=650,top=0,directories=no,location=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,menubar=no');\"";
				}
			}
			
			if ($iPrecheck) {
				$sOfferChecked = "checked";
			}
	
			// if offer is checked and user came back
			for ($i=0; $i<count($aOffersChecked);$i++) {
				if ($aOffersChecked[$i] == $sOfferCode) {
					$sOfferChecked = "checked";
					break;
				}
			}
			
			if ($sBgColor == $sOfferBgColor1 || $sBgColor == '') {
				$sBgColor = $sOfferBgColor2;
			} else {
				$sBgColor = $sOfferBgColor1;
			}
			
			$sOfferListVariable = "sPageOffersList";
			
			$$sOfferListVariable .= "<tr><td bgcolor=$sBgColor>
								<table class=table650 align=center cellpaddint=0 cellspacing=0 border=0>
									<tr bgcolor=$sBgColor>
									<td width=15%><input type=radio name='aOffersChecked[]' id='100' value='$sOfferCode' $sOfferChecked $sYesCoRegPopOnClick>Yes 
									<input type=radio name='aOffersChecked[]' id='101' value='N' $sOfferNoChecked $sNoCoRegPopOnClick>No &nbsp;&nbsp;
								</td>
									<td width=15%><img src='$sGblOfferImageUrl/$sOfferCode/$sOfferImageName'></td>
										<td width=80% class='offer11' valign=center>$sOfferDescription
										</td>
									</tr>
									<tr bgcolor=$sBgColor>";

			$$sOfferListVariable .= "<td></td><td></td><td>";
			
			if ($sAddiInfoText != '') {
				// add additional information link for popup
				$aAddiInfoPopupSizeArray = explode(",",$sAddiInfoPopupSize);
				$iAddiInfoPopupWidth = $aAddiInfoPopupSizeArray[0];
				$iAddiInfoPopupHeight = $aAddiInfoPopupSizeArray[1];
				$$sOfferListVariable .= " <a href='JavaScript:void(window.open(\"$sGblSiteRoot/offerAddiInfo.php?sOfferCode=$sOfferCode\",\"addiInfo\",\"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Additional Information</a>";
			}
			
			$$sOfferListVariable .= "</td></tr><tr bgcolor=$sBgColor><td colspan=3 align=center ><HR width=580></td></tr></table></td></tr>";
			// offer display stats
			$sPageOfferDisplayed = '';
		}
	}
}
//} else {
if(count($_SESSION['aSesPage2Offers']) > 0){
	$sPage2Offers = "'".implode("','",$_SESSION['aSesPage2Offers'])."'";
	$sOffersOnPage2 = '';
	$bRequireSSL = false;
	$sOffersQuery = "SELECT * FROM offers WHERE offerCode IN ($sPage2Offers)";
	$rOffersResult = dbQuery($sOffersQuery);
	$_SESSION['bSubmit'] = true;


	if ($_SESSION['sSesOfferType'] == 'CTH') {
		while ($oOffersRow = dbFetchObject($rOffersResult)) {
			$iFrameHeight = $oOffersRow->iFrameHeight;
			$sCthUrl = $oOffersRow->closeTheyHostUrl;
			//$sCthHeader = $oOffersRow->closeTheyHostHeader;
			if ($oOffersRow->closeTheyHostHeader == '') {
				$sCthHeader = "<img src='http://www.popularliving.com/images/thHeaderDefault.gif'>";
			} else {
				$sCthHeader = "<img src='http://www.popularliving.com/images/offers/$oOffersRow->offerCode/$oOffersRow->closeTheyHostHeader'>";
			}
		}
	} else {
		// write the javascript functions and page2Validation function to call on submit
		$sPage2JavaScript = "
			<script type=\"text/javascript\" >
				function page2Validation() {
				var errMessage = '';";

		while ($oOffersRow = dbFetchObject($rOffersResult)) {
			$sOfferCode = $oOffersRow->offerCode;
			if ($oOffersRow->isRequireSSL == 'Y') {
				$bRequireSSL = true;
			}

			if ($oOffersRow->isCoolSavings == 'Y') {
				$sOfferPage2Template = "<table border='0' cellpadding='0' cellspacing='0' width='750'>
						<tr><td><img src=\"http://images.popularliving.com/images/offers/$sOfferCode/$oOffersRow->smallImageName\" /></td>
						<td width='10' bgcolor='#EFEFEF'></td><td class=offer11 bgcolor='#EFEFEF'>
						$oOffersRow->shortDescription</td></tr></table>".$oOffersRow->page2Template;
			} else {
				$sOfferPage2Template = $oOffersRow->page2Template;
			}
							
			if ($oOffersRow->sShowRegForm == 'Y') {
				if (strstr($sOfferPage2Template,"THINNER_USER_FORM")) {
					$sUserForm = $_SESSION['sSesPage2ThinnerUserForm'];
					if (strstr($sOfferPage2Template,"<!--[THINNER_USER_FORM_LEFT]-->")) {
						$sUserForm = str_replace("[ALIGN_LEFT_CENTER_RIGHT]", 'left', $sUserForm);
						$sOfferPage2Template = str_replace("<!--[THINNER_USER_FORM_LEFT]-->", $sUserForm, $sOfferPage2Template);
					}
					if (strstr($sOfferPage2Template,"<!--[THINNER_USER_FORM_CENTER]-->")) {
						$sUserForm = str_replace("[ALIGN_LEFT_CENTER_RIGHT]", 'center', $sUserForm);
						$sOfferPage2Template = str_replace("<!--[THINNER_USER_FORM_CENTER]-->", $sUserForm, $sOfferPage2Template);
					}
					if (strstr($sOfferPage2Template,"<!--[THINNER_USER_FORM_RIGHT]-->")) {
						$sUserForm = str_replace("[ALIGN_LEFT_CENTER_RIGHT]", 'right', $sUserForm);
						$sOfferPage2Template = str_replace("<!--[THINNER_USER_FORM_RIGHT]-->", $sUserForm, $sOfferPage2Template);
					}
				} else {
					$sUserForm = $_SESSION['sSesPage2RegUserForm'];
					if (strstr($sOfferPage2Template,"<!--[USER_FORM_C_LEFT]-->")) {
						$sUserForm = str_replace("[ALIGN_LEFT_CENTER_RIGHT]", 'left', $sUserForm);
						//$sOfferPage2Template = str_replace("<!--[USER_FORM_C_LEFT]-->", $sUserForm, $sOfferPage2Template);
						$sOfferPage2Template = str_replace("<!--[USER_FORM_C_LEFT]-->", '', $sOfferPage2Template);
					}
					if (strstr($sOfferPage2Template,"<!--[USER_FORM_C_CENTER]-->")) {
						$sUserForm = str_replace("[ALIGN_LEFT_CENTER_RIGHT]", 'center', $sUserForm);
						//$sOfferPage2Template = str_replace("<!--[USER_FORM_C_CENTER]-->", $sUserForm, $sOfferPage2Template);
						$sOfferPage2Template = str_replace("<!--[USER_FORM_C_CENTER]-->", '', $sOfferPage2Template);
					}
					if (strstr($sOfferPage2Template,"<!--[USER_FORM_C_RIGHT]-->")) {
						$sUserForm = str_replace("[ALIGN_LEFT_CENTER_RIGHT]", 'right', $sUserForm);
						//$sOfferPage2Template = str_replace("<!--[USER_FORM_C_RIGHT]-->", $sUserForm, $sOfferPage2Template);
						$sOfferPage2Template = str_replace("<!--[USER_FORM_C_RIGHT]-->", '', $sOfferPage2Template);
					}
				}
			}
	
			$sOfferDroppedVar = $sOfferCode."Dropped";
			if ($oOffersRow->page2JavaScript != '') {
				$sPage2JavaScript .= "var $sOfferDroppedVar = false;
				for (i = 0; i < document.form1.length; i++) {
				   if (document.form1.elements[i].name.indexOf(\"aDropOffers\") !=-1) {
				        if (document.form1.elements[i].checked && document.form1.elements[i].value == \"".$sOfferCode."\") {
				           $sOfferDroppedVar = true;
				        }
				   }
				}
				if ($sOfferDroppedVar != true) {
					$oOffersRow->page2JavaScript
				}";
			}
	
			if ($sBgColor == $_SESSION['sOfferBgColor1'] || $sBgColor == '') {
				$sBgColor = $_SESSION['sOfferBgColor2'];
			} else {
				$sBgColor = $_SESSION['sOfferBgColor1'];
			}

			// If 2nd page questions required ssl, change all image links from http to https
			if ($bRequireSSL) {
				$sOfferPage2Template = str_replace("http://","https://",$sOfferPage2Template);
			}
	
			$sOffersOnPage2 .= "<tr bgcolor=$sBgColor><td>$sOfferPage2Template
						</td></tr>".($oOffersRow->iDoNotWantThisOffer != 'N'? "<tr bgcolor=$sBgColor><td>I do not want this offer: <input type=checkbox name=aDropOffers[] value='$sOfferCode' onClick='sureOptOut(this);'></td></tr>" : "")."<tr bgcolor=$sBgColor><td><hr></td></tr>";
		}
						
		$sPage2JavaScript .= "
			if (errMessage != '') {
	    		alert(errMessage);
	    		return false;
	  		} else {
	    		return true;
	  		}
		}
		function sureOptOut(chkBox) {
			if (chkBox.checked) {
				if(!confirm(\"Are You Sure You Don\'t Want This Great Offer?\\nClick 'Cancel' to finish requesting offer.\\nClick 'OK' to confirm you don't want this offer.\")) {
					chkBox.checked = false;
				}
			}
		}
		</script>";
		$sPageOffersList = $sOffersOnPage2;
	}
}

$sHiddenSrc = $_SESSION['sSesHiddenSourceCode'];
$_SESSION['sSesJavaScriptPrePop'] = "<script type=\"text/javascript\" >";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sEmail = '".$_SESSION['sSesEmail']."';";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sSalutation = '".$_SESSION['sSesSalutation']."';";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sFirst = \"".$_SESSION['sSesFirst']."\";";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sLast = \"".$_SESSION['sSesLast']."\";";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sAddress = \"".$_SESSION['sSesAddress']."\";";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sAddress2 = \"".$_SESSION['sSesAddress2']."\";";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sCity = '".$_SESSION['sSesCity']."';";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sState = '".$_SESSION['sSesState']."';";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sZip = '".$_SESSION['sSesZip']."';";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sPhone = '".$_SESSION['sSesPhone']."';";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sPhone_areaCode = '".$_SESSION['sSesPhoneAreaCode']."';";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sPhone_exchange = '".$_SESSION['sSesPhoneExchange']."';";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sPhone_number = '".$_SESSION['sSesPhoneNumber']."';";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sPhone_ext = '".$_SESSION['sSesPhoneExtension']."';";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sPhoneNoDash = '".$_SESSION['sSesPhoneNoDash']."';";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sSourceCode = '".$_SESSION['sSesSourceCode']."';";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sSubSourceCode = '".$_SESSION['sSesSubSourceCode']."';";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sGender = '".$_SESSION['sSesGender']."';";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sRemoteIp = '".$_SESSION['sSesRemoteIp']."';";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var iBirthYear = '".$_SESSION['iSesBirthYear']."';";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var iBirthMonth = '".$_SESSION['iSesBirthMonth']."';";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var iBirthDay = '".$_SESSION['iSesBirthDay']."';\n";
$_SESSION['sSesJavaScriptPrePop'] .= "\n var sRevSourceCode = '".$_SESSION['sSesRevSourceCode']."';\n";
$_SESSION['sSesJavaScriptPrePop'] .= "</script>";


//if (count($_SESSION['aSesPage2Offers']) == 0) {
	// 1st page
	$sValidationFunction = "validation()";
//} else {
	// 2nd page
//	$sValidationFunction = "page2Validation()";
//}

$sPageTemplate = "<html>
<head>
<SCRIPT LANGUAGE=JavaScript SRC='http://www.popularliving.com/libs/javaScriptFunctions.js' TYPE=text/javascript></script>
<SCRIPT LANGUAGE=JavaScript SRC='http://www.popularliving.com/nibbles2/libs/ajax.js' TYPE=text/javascript></script>
[PAGE2_JAVA_SCRIPT]
[JAVASCRIPT_PREPOP]
<script language='javascript'>
function validation(){
	if((FieldErrors.length == 0)&& ([CALL_FUNCTION])){
		return true;
	} else {
		if(FieldErrors.length != 0){
			var errMessage = '';
			for (i=0;i<FieldErrors.length;i++) {
				if(document.form1.elements[FieldErrors[i]]) val = document.form1.elements[FieldErrors[i]].value
				else val = '';
				if (FieldErrors[i] != 'sPhoneDistance') {
				msg = ErrorMessage(FieldErrors[i],val,'[sourcecode]');
				if(msg != '') errMessage += msg;
				}
			}
			if (errMessage == '') {
				msg = ErrorMessage('sPhoneDistance','','[sourcecode]');
				if(msg != '') errMessage += msg;
			}
			alert(errMessage);
		}
		return false;
	}
}
</script>


[PAGE1_JAVA_SCRIPT]



<title>Offer Preview - $sOfferCode</title>
<LINK rel=\"stylesheet\" href=\"../pageStyles.css\" type=\"text/css\">
</head>
<body>
<br>

<form name=form1 action='sop.php' method=POST onSubmit=\"if([PAGE_VALIDATION]){return submit_form();}else{return false;} \">
$sHiddenSrc
<table class=table760 align=center cellpadding=0 cellspacing=0>
[OFFERS_LIST]
</table>


[USER_REGISTRATION_FORM]


<BR>
<center>
[SUBMIT_BUTTON]
<input name='sSubmit' value='submit' type='hidden'>
</form>
</center>

<br><br>
<table width='800' border='0' align='center' cellpadding='0' cellspacing='0'>
  <tr>
    <td width='400' align='right'><img src='http://images.popularliving.com/p/scf_insiderdeals/images/partnersleft.gif' alt='' border='0' /></td>
        <td width='400' class='disclaimerBg' align='left'>
        <A href='#' onClick=\"window.open('http://www.myfree.com/terms.php', 'tc', 'width=400, height=520, directories=no,location=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,menubar=no' );\">
        Terms & Conditions</A> | 
        <A href='#' onClick=\"javascript:window.open('http://www.myfree.com/privacy.php' ,'fs_privacy','width=700,height=575,directories=no,location=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,menubar=no,top=20,left=screen.width / 2 - 300' );\">
        Privacy</A></TD>
      </TR>
    </TABLE>
<br><br><br><br><br>

</body>
</html>";

if ($_SESSION['sSesOfferType'] == 'CTH') {

	$sPageTemplate = "<html><head><title>Offer Preview - $sOfferCode</title>
	<LINK rel='stylesheet' href='../pageStyles.css' type='text/css'>
	</head><body>
	<center>
	
	$sCthHeader
	
	<div align='right'>
			<img src='http://www.popularliving.com/images/nothanks.gif' style='cursor: pointer;' 
			onClick=\"parent.location='/p/onetime.php'\"></div>
	
	</center>
	<BR>
		<iframe src=\"$sCthUrl\" width=100% height=$iFrameHeight scrolling='no' frameborder=0 ></iframe>
	<BR>
	<center>
	<input name='sSkip' value='Skip' type='button' onclick=\"parent.location='/p/onetime.php'\">
	<input name='sSubmit' value='submit' type='hidden'>
	$sHiddenSrc
	</form>
	</center>
	
	<br><br>
	<table width='800' border='0' align='center' cellpadding='0' cellspacing='0'>
	  <tr>
	    <td width='400' align='right'><img src='http://images.popularliving.com/p/scf_insiderdeals/images/partnersleft.gif' alt='' border='0' /></td>
	        <td width='400' class='disclaimerBg' align='left'>
	        <A href='#' onClick=\"window.open('http://www.myfree.com/terms.php', 'tc', 'width=400, height=520, directories=no,location=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,menubar=no' );\">
	        Terms & Conditions</A> | 
	        <A href='#' onClick=\"javascript:window.open('http://www.myfree.com/privacy.php' ,'fs_privacy','width=700,height=575,directories=no,location=no,resizable=yes,scrollbars=yes,status=no,toolbar=no,menubar=no,top=20,left=screen.width / 2 - 300' );\">
	        Privacy</A></TD>
	      </TR>
	    </TABLE>
	<br><br><br><br><br>
	</body>
	</html>";
}


$sPage1JavaScript = "
<script type=\"text/javascript\">
<!--
function submit_form () {
  if (document.getElementById('100').checked == false && document.getElementById('101').checked == false) {
	alert('Please check the offer.');
	return false;
  } else {
  	return true;
  }
}
// -->
</script>";

$submit = new SubmitButton();
$sPageTemplate = str_replace('[OFFERS_LIST]',$sPageOffersList,$sPageTemplate);
$sTempVal = $_SESSION['sSesPage2RegUserForm'];



$sPageTemplate = str_replace('[PAGE_VALIDATION]',$sValidationFunction,$sPageTemplate);

/*
if (count($_SESSION['aSesPage2Offers']) == 0) {
	$sPageTemplate = str_replace('[PAGE1_JAVA_SCRIPT]',$sPage1JavaScript,$sPageTemplate);
	$sPageTemplate = str_replace('[PAGE2_JAVA_SCRIPT]','',$sPageTemplate);
	$sTempVal = str_replace('[ALIGN_LEFT_CENTER_RIGHT]','center',$sTempVal);
	$sPageTemplate = str_replace('[USER_REGISTRATION_FORM]',$sTempVal,$sPageTemplate);
	$sPageTemplate = str_replace('[CALL_FUNCTION]',"submit_form()",$sPageTemplate);
	$sPageTemplate = str_replace('[JAVASCRIPT_PREPOP]','',$sPageTemplate);
} else {
	$sPageTemplate = str_replace('[PAGE1_JAVA_SCRIPT]','<script>function submit_form () { }</script>',$sPageTemplate);
	$sPageTemplate = str_replace('[PAGE2_JAVA_SCRIPT]',$sPage2JavaScript,$sPageTemplate);
	$sPageTemplate = str_replace('[USER_REGISTRATION_FORM]','',$sPageTemplate);
	$sPageTemplate = str_replace('[CALL_FUNCTION]',"page2Validation()",$sPageTemplate);
	$sPageTemplate = str_replace('[JAVASCRIPT_PREPOP]',$_SESSION['sSesJavaScriptPrePop'],$sPageTemplate);
}*/
$sPageTemplate = str_replace('[PAGE1_JAVA_SCRIPT]',$sPage1JavaScript,$sPageTemplate);
$sPageTemplate = str_replace('[PAGE2_JAVA_SCRIPT]',$sPage2JavaScript,$sPageTemplate);
$sTempVal = str_replace('[ALIGN_LEFT_CENTER_RIGHT]','center',$sTempVal);
$sPageTemplate = str_replace('[USER_REGISTRATION_FORM]',$sTempVal,$sPageTemplate);
$sPageTemplate = str_replace('[CALL_FUNCTION]',"page2Validation() && submit_form()",$sPageTemplate);
$sPageTemplate = str_replace('[JAVASCRIPT_PREPOP]',$_SESSION['sSesJavaScriptPrePop'],$sPageTemplate);


if (strstr($sPageTemplate,'[EMAIL_FIELD]')) {
	$email = new EmailField();
	$email->value = $_SESSION['sSesEmail'];
	$sPageTemplate = str_replace('[EMAIL_FIELD]',$email->html(),$sPageTemplate);
}

	
if (strstr($sPageTemplate,'[SALUTATION_FIELD]')) {
	$sal = new SalutationSelect();
	$sPageTemplate = str_replace('[SALUTATION_FIELD]',$sal->html(),$sPageTemplate);
}
	
if (strstr($sPageTemplate,'[FIRST_FIELD]')) {
	$f = new FNameField();
	$f->value = $_SESSION['sSesFirst'];
	$sPageTemplate = str_replace('[FIRST_FIELD]',$f->html(),$sPageTemplate);
}
	
	
if (strstr($sPageTemplate,'[LAST_FIELD]')) {
	$l = new LNameField();
	$l->value = $_SESSION['sSesLast'];
	$sPageTemplate = str_replace('[LAST_FIELD]',$l->html(),$sPageTemplate);
}
	
	
if (strstr($sPageTemplate,'[ADDRESS_GROUP]')) {
	$add = new AddressGroup();
	$sPageTemplate = str_replace('[ADDRESS_GROUP]',$add->html(),$sPageTemplate);
}
	 

if (strstr($sPageTemplate,'[ADDRESS_FIELD]')) {
	$address = new AddressField();
	$address->value = $sAddress;
	$sPageTemplate = str_replace('[ADDRESS_FIELD]',$address->html(),$sPageTemplate);
}
	
if (strstr($sPageTemplate,'[ADDRESS2_FIELD]')) {
	$address2 = new AddressField();
	$address2->value = $sAddress2;
	$sPageTemplate = str_replace('[ADDRESS2_FIELD]',$address2->html(),$sPageTemplate);
}
	
if (strstr($sPageTemplate,'[CITY_FIELD]')) {
	$city = new CityField();
	$city->value = $sCity;
	$sPageTemplate = str_replace('[CITY_FIELD]',$city->html(),$sPageTemplate);
}
	
if (strstr($sPageTemplate,'[STATE_FIELD]')) {
	$state = new StateField();
	$state->value = $sState;
	$sPageTemplate = str_replace('[STATE_FIELD]',$state->html(),$sPageTemplate);
}
	
if (strstr($sPageTemplate,'[ZIP_FIELD]')) {
	$zip = new ZipField();
	$zip->value = $sZip;
	$sPageTemplate = str_replace('[ZIP_FIELD]',$zip->html(),$sPageTemplate);
}

if (strstr($sPageTemplate,'[GENDER_FIELD]')) {
	$gender = new GenderSelect();
	$gender->value = $_SESSION['sSesGender'];
	$sPageTemplate = str_replace('[GENDER_FIELD]',$gender->html(),$sPageTemplate);
}
	
if (strstr($sPageTemplate,'[GENDER_RADIO]')) {
	$gen = new GenderRadio();
	$gen->value = $_SESSION['sSesGender'];
	$sPageTemplate = str_replace('[GENDER_RADIO]',$gen->html(),$sPageTemplate);
}

if (strstr($sPageTemplate,'[PHONE_GROUP]')) {
	$phone = new PhoneField();
	$phone->value = $_SESSION['sSesPhone'];
	$sPageTemplate = str_replace('[PHONE_GROUP]',$phone->html(),$sPageTemplate);
}

if (strstr($sPageTemplate,'[DOB_GROUP]')) {
	$dob = new DOBField();
	$sPageTemplate = str_replace('[DOB_GROUP]',$dob->html($_SESSION['iSesBirthMonth']."/".$_SESSION['iSesBirthDay']."/".$_SESSION['iSesBirthYear']),$sPageTemplate);
}

if (strstr($sPageTemplate,'[SUBMIT_BUTTON]')) {
	$submit->extra .= 'f=0; ';
	$sPageTemplate = str_replace('[SUBMIT_BUTTON]',$submit->html(),$sPageTemplate);
}

echo $sPageTemplate;

?>