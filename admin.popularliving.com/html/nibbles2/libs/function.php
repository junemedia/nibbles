<?php

function killChars($sValue) {
	$bValidChar = true;
	$aCharsToKill = array('select', 'drop', ';', '--', 'insert', 'delete', 
				'xp_', 'truncate', 'update', '1=1', 'or 1=1 --', "''=''");
	foreach ($aCharsToKill as $sBadChar) {
		if (stristr($sValue) == $sBadChar) {
			$bValidChar = false;
		}
	}
	return $bValidChar;
}


function validateEmail($eMail, $sOptional='') {
	$valid = true;

	if ($eMail == '') {
		$valid = false;
	}

	if ( !eregi(  "^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $eMail) ) {	
		$valid = false;
	}

	// check if contains valid domain
	$found = 0;
	$domainQuery = "SELECT *
					FROM   validDomains";
	$domainResult = dbQuery($domainQuery);
	while ($domainRow = dbFetchObject($domainResult)) {
		if (strtolower($domainRow->domain) == strtolower(substr($eMail, strlen($eMail)-strlen($domainRow->domain)))) {
			$found++;
		}
	}
	if (!($found)) {
		$valid = false;
	}
	
	// check if contains banned domain
	$domainQuery = "SELECT * FROM   bannedDomains";
	$domainResult = dbQuery($domainQuery);
	while ($domainRow = dbFetchObject($domainResult)) {
		if ($domainRow->domain == substr($eMail, strlen($eMail)-strlen($domainRow->domain))) {
			$sDomainName = $domainRow->domain;
			$valid = false;
			break;
		}
	}
	
	// check starts with bannedEmailStart
	$checkQuery = "SELECT *
				   FROM   bannedEmailStart";
	$checkResult = dbQuery($checkQuery);
	while ($checkRow = dbFetchObject($checkResult)) {
		if (substr(strtolower($eMail),0,strlen($checkRow->startsWith)) == strtolower($checkRow->startsWith)) {
			$valid = false;
		}
	}
	
	// check if email contains banned email string
	$checkQuery = "SELECT * FROM bannedEmailString";
	$checkResult = dbQuery($checkQuery);
	while ($checkRow = dbFetchObject($checkResult)) {
		if (strstr($eMail,$checkRow->emailString)) {
			$valid = false;
		}
	}
	
	// check if not a bannedEmail
	$bannedQuery = "SELECT *
					FROM   bannedEmails
					WHERE  email = '$eMail'";
	$bannedResult = dbQuery($bannedQuery);
	if (dbNumRows($bannedResult) > 0) {
		$valid =  false;
	}
	
	// check if contains banned ip.
	list ( $userName, $domain ) = split ("@",$eMail);
	$ipArray = gethostbynamel($domain);
	for ($i = 0; $i < count($ipArray); $i++) {
		$bannedQuery = "SELECT *
					FROM   bannedIps
					WHERE  ipAddress = '".$ipArray[$i]."'";
		$bannedResult = dbQuery($bannedQuery);
		if (dbNumRows($bannedResult) > 0) {
			$valid = false;
		}
	}
	
	// Check DNS records corresponding to a given domain
	// Get MX records corresponding to a given domain.
	if (!getmxrr($domain, $mxhosts)) {
		$valid = false;
	}
	return $valid;
}




// Validate First and Last Name
function validateName($name) {
	$valid = true;

	$name = addslashes($name);
	$name = ltrim(rtrim($name));
	$aStringArray = split(" ",$name);
	for ($i=0;$i<count($aStringArray);$i++) {
		$tempString = $aStringArray[$i];
		$tempString = stripslashes($tempString);
		$tempString = ltrim(rtrim($tempString));
		$bannedQuery = "SELECT word
						FROM   bannedWords" ; 
		$bannedResult = dbQuery($bannedQuery) ;
		while ($bannedRow = dbFetchObject($bannedResult)) {
			$badWord = $bannedRow->word;
			if (strstr(strtolower($tempString),strtolower($badWord))) {
				$valid = false ;
				$approvedQuery = "SELECT word FROM   approvedWords
								  WHERE  upper(word) = upper('$tempString')" ; 
				$approvedResult = dbQuery($approvedQuery);
				if ( dbNumRows($approvedResult) >0 ) {
					$valid = true;
				}
			}
		}
	}

	//** check that length is greater than 0
	if (strlen($name) < 2) {
		$valid = false ;
	}
	
	//must contain at least one letter
	if (!eregi("[A-Z]{1,}", $name))  {
		$valid = false;
	}
	
	// must not contain 2 or more dots in an order
	if (eregi("[\.]{2,}", $name))  {
		$valid = false;
	}
	
	// check if only alpha characters
	$addOn = '';
	if (!eregi("^[-$addOn A-Z[:space:]'\.]*$", $name))  {
		$valid = false;
	}
	
	//** check four vowels or five consentant in row
	if ( eregi("[aeiouy]{4,}", $name) || eregi("[^aeiouy\.']{6,}", $name)) {
		$valid = false;
	}
	
	// must not contain 2 or more single quores in an order
	if ( eregi("[']{2,}", $name) ) {		
		$valid = false;
	}
	
	// check if any sequence of 2 chars more than 2 times or sequence of 3 chars more than 2 times in name
	// function to check any 2 char or 3 char sequence repeating more than 2 times in a string
	// returns false if string is not valid containing any sequence in it
	$j=0;
	for ($i=$j;$i<strlen($name);$i++) {
		$sSubStr1 = substr($name,$i,2);  
		$sSubStr2 = substr($name,$i+2,2);  
		$sSubStr3 = substr($name,$i+4,2);  
		if ($sSubStr1 == $sSubStr2 && $sSubStr2 == $sSubStr3 && trim($sSubStr1) != '' && trim($sSubStr2) != '') {
			$valid = false;
			break;
		}	
		
		$sSubStr1 = substr($name,$i,3);  
		$sSubStr2 = substr($name,$i+3,3);  
		$sSubStr3 = substr($name,$i+6,3);
		if ($sSubStr1 == $sSubStr2 && $sSubStr2 == $sSubStr3 && trim($sSubStr1) != '' && trim($sSubStr2) != '') {
			$valid = false;
			break;
		}
	}
	return $valid;
}


function isBannedIp($ip) {
	$isBanned = false;
	$bannedQuery = "SELECT *
					FROM   bannedIps
					WHERE  ipAddress = '$ip'";
	$bannedResult = dbQuery($bannedQuery);
	if (dbNumRows($bannedResult) > 0) {
		$isBanned = true;
	}
	return $isBanned;
}



function isBannedDomain($email, &$sDomainName) {
	$isBanned = false;
	$found = 0;
	$domainQuery = "SELECT *
				FROM   bannedDomains";
	$domainResult = dbQuery($domainQuery);
	while ($domainRow = dbFetchObject($domainResult)) {
		if ($domainRow->domain == substr($email, strlen($email)-strlen($domainRow->domain))) {
			$sDomainName = $domainRow->domain;
			$isBanned = true;
			break;
		}
	}
	return $isBanned;
}



function validateBirthDate($iBirthYear, $iBirthMonth, &$iBirthDay) {
	$sReturnVal = false;
	if ($iBirthYear != '' && $iBirthMonth != '' && $iBirthDay != '') {
		$bTempReturnVal = true;
		if ($iBirthMonth == 04 || $iBirthMonth == 06 || $iBirthMonth == 09 || $iBirthMonth == 11) {
			if ($iBirthDay > 30) {
				$bTempReturnVal = false;
			}
		}
		
		if ($iBirthMonth == 02) {
			if ($iBirthDay > 28) {
				$iBirthDay = ($iBirthYear % 4 == 0 && ($iBirthYear % 100 != 0 || $iBirthYear % 400 == 0)) ? $iBirthDay = 29 : $iBirthDay = 28;
			}
		}

		if ($bTempReturnVal) {
			$sOldDate = strtotime("-18 years");
			$sSelectedDate = strtotime("$iBirthMonth-$iBirthDay-$iBirthYear");
			if ($iBirthYear <= 1987) {
				$sReturnVal = true;
			} else {
				if ($sSelectedDate <= $sOldDate) {
					$sReturnVal = true;
				}
			}
		}
	}
	return $sReturnVal;
}




function getDistance($area1, $area2, $sSourceCode='') {
	if ($sSourceCode !='') {
		$sCheckPhoneResult = dbQuery("SELECT * FROM phoneValidateBypass WHERE sourceCode = \"$sSourceCode\"");
		if (dbNumRows($sCheckPhoneResult) > 0 ) {
			return true;
		}
	}
	
	$latt1 = 0;
	$long1 = 0;
	
	if (!(ereg("^[0-9-]+$", $area1) && ereg("^[0-9-]+$", $area2))) {
		return false;
	}
	
	if (strlen($area1) == 5 || (strlen($area1) == 9 && isInteger($area1)) || (strlen($area1) == 10 && ereg("[-]{1}", $area1)) ) {
		$area1 = substr($area1,0,5);
		
		// get lattitude and longitude for zipCode1
		$query1 = "SELECT latitude, longitude
			   FROM   zipData
			   WHERE  zip = '$area1'";
		$result1 = dbQuery($query1);
		while ($row1 = dbFetchObject($result1)) {
			$latt1 = $row1->latitude;
			$long1 = $row1->longitude;
		}
	} else {
		$areaCode1 = substr($area1, 0, 3);
		$prefix1 = substr($area1, 4, 3);
		
		// get lattitude and longitude for phone1
		$query1 = "SELECT latitude, longitude
			   FROM   phoneData
			   WHERE  areaCode = '$areaCode1'
			   AND    prefix = '$prefix1'";
		//echo $query1;
		$result1 = dbQuery($query1);
		while ($row1 = dbFetchObject($result1)) {
			$latt1 = $row1->latitude;
			$long1 = $row1->longitude;
		}
	}
	if (strlen($area2) == 5 || (strlen($area2) == 9 && isInteger($area2)) || (strlen($area2) == 10 && ereg("[-]{1}", $area2)) ) {
		$area2 = substr($area2,0,5);
		// get lattitude and longitude for zipCode1
		$query2 = "SELECT latitude, longitude
			   FROM   zipData
			   WHERE  zip = '$area2'";
		$result2 = dbQuery($query2);
		while ($row2 = dbFetchObject($result2)) {
			$latt2 = $row2->latitude;
			$long2 = $row2->longitude;
		}
	} else {
		$areaCode2 = substr($area2, 0, 3);
		$prefix2 = substr($area2, 4, 3);
		// get lattitude and longitude for phone1
		$query2 = "SELECT latitude, longitude
			   FROM   phoneData
			   WHERE  areaCode = '$areaCode2'
			   AND    prefix = '$prefix2'";
		$result2 = dbQuery($query2);
		//echo mysql_error();
		while ($row2 = dbFetchObject($result2)) {
			$latt2 = $row2->latitude;
			$long2 = $row2->longitude;
		}
	}
	$a1 = sin($latt1/57.3) * sin($latt2/57.3);
	$a2 = cos($latt1/57.3) * cos($latt2/57.3) * cos($long2/57.3 - $long1/57.3);
	$a = $a1 + $a2;
	$dist = 3959 * atan(sqrt(1-($a*$a))/$a);
	if ($dist > 250) {
		return false;
	} else {
		return true;
	}
}





function validatePhone($area, $exchange, $four, $extension='', $state='', $sSourceCode='') {
	$valid = true;
	
	if ($sSourceCode !='') {
		$sCheckPhoneResult = dbQuery("SELECT * FROM phoneValidateBypass WHERE sourceCode = \"$sSourceCode\"");
		if (dbNumRows($sCheckPhoneResult) > 0 ) {
			//mail('bbevis@amperemedia.com','hey hey, my my', "src is $sSourceCode");
			return true;
		}
	}
	//mail('bbevis@amperemedia.com','hey hey, my my', "source code was empty");
	//All 3 phone fields are required
	if (!$area || !$exchange || !$four) {
		$valid = false;
	}
	
	if(strlen($area) != 3 || strlen($exchange) != 3 || strlen($four) != 4){
		$valid = false;
	}
	
	//All 3 phone Must Be Numeric
	if (!ctype_digit($area) || !ctype_digit($exchange) || !ctype_digit($four)) {
		$valid = false;
	}
	
	//First Digit of Area and Exchange can't be one or zero
	if ( ereg("^[01]{1}", $area) || ereg("^[01]{1}", $exchange)) {
		$valid = false;
	}
	
	// area code 800, 855, 877 are not accepted
	if ($area == '800' || $area == '855' || $area == '877') {
		$valid = false;
	}

	$lastSevenDigits = $exchange.$four;
	// check if last 7 digits of phone no contains numbers in series only
	// First 3 digits need not to be checked as those are checked with valid zipcode
	if (isNumberSeries($lastSevenDigits)) {
		$valid = false;		
	}
	
	//Make array of same 3 digit codes,
	$sa[] = "222";
	$sa[] = "333";
	$sa[] = "444";
	$sa[] = "555";
	$sa[] = "666";
	$sa[] = "777";
	$sa[] = "888";
	$sa[] = "999";
	
	// check if banned phone no.
	$sBannQuery = "SELECT *
				  FROM   bannedPhones
				  WHERE  phone = '".$area."-".$exchange."-".$four."'";				
	//echo $sBannQuery;
	$rBannResult = dbQuery($sBannQuery);
	
	if (dbNumRows($rBannResult) > 0 ) {
		$valid = false;
	}
	
		
	//Area Code / State Lookup
	if ($state) {
		$areaQuery = "SELECT *
					  FROM   phoneData
					  WHERE  areaCode = '$area'
					  AND    state = '$state'";
		
		$areaResult = dbQuery($areaQuery);
		if (!$areaResult) {
			echo dbError();
		} else if (dbNumRows($areaResult) == 0) {
			$valid = false;
		}
	}
	
	//Validate Extension for number:
	if ($extension) {
		if (!ctype_digit($extension)) {
			$valid = false;
		}
	}
	return $valid;
}



function isNumberSeries($iNumber) {
	$series = false;
	$iNumLen = strlen($iNumber);
	for ($i=0;$i<10;$i++) {
		$iTempNum = '';
		$k = $i;
		// make a temporary number containing searies of same length as the number passed to function
		for ($j=0;$j<$iNumLen;$j++) {
			$iTempNum .= $k;
			$k = $k+1;
		}
		
		// check if the number passed is same as the temporary series number
		if ($iNumber == $iTempNum) {
			$series = true;
			break;
		}
	}
	return $series;
}





function validateTargetState($sTargetState) {
	if ($sTargetState != '') {
		$sValidStateCheck = "/^(([a-z]{2},)*)+[a-z]{2}$/";
		if (preg_match($sValidStateCheck, $sTargetState)) {
			$sCheckStateQuery = "SELECT stateId FROM nibbles.states";
			$rCheckStateResult = dbQuery($sCheckStateQuery);
			$aTempState = explode(",", $sTargetState);
			$iTempStateCount = count($aTempState);
			$iValidCount = 0;
			while ($oStateRow = dbFetchObject($rCheckStateResult)) {
				for ($i=0; $i<=$iTempStateCount; $i++) {
					if ($aTempState[$i] == strtolower($oStateRow->stateId)) {
						$iValidCount++;
					}
				}
			}
			
			if ($iValidCount == $iTempStateCount) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	} else {
		return true;
	}
}



function httpFormPostGet ($sHttpPostString,$sUrlPart,$sPostingUrl,$iDeliveryMethodId,$sOfferCode,$sEmail,$sHowSent) {

	// separate host part and script path
	$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
	$sHostPart = ereg_replace("\/","",$sHostPart);
	$sScriptPath = substr($sUrlPart,strlen($sHostPart));
	
	if (strstr($sPostingUrl, "https:")) {
		$rSocketConnection = fsockopen("ssl://".$sHostPart, 443, $errno, $errstr, 30);
	} else {
		$rSocketConnection = fsockopen($sHostPart, 80, $errno, $errstr, 30);
	}

	if ($rSocketConnection) {
		if ($iDeliveryMethodId == '2') {	// http form post - GET
			$sScriptPath  .= "?".$sHttpPostString;
			fputs($rSocketConnection, "GET $sScriptPath HTTP/1.1\r\n");
			fputs($rSocketConnection, "Host: $sHostPart\r\n");
			if($sOfferCode == 'R411_TAX'){
				fputs($rSocketConnection, "Referer: http://www.411taxrelief.com/index.cgi?cid=2080\r\n");
			}
			fputs($rSocketConnection, "Accept-Language: en\r\n");
			fputs($rSocketConnection, "User-Agent: MSIE\r\n");
			fputs($rSocketConnection, "Connection: close\r\n\r\n");
		} else if ($iDeliveryMethodId == '3') {	// http form post - POST
			fputs($rSocketConnection, "POST $sScriptPath HTTP/1.1\r\n");
			fputs($rSocketConnection, "Host: $sHostPart\r\n");
			if($sOfferCode == 'R411_TAX'){
				fputs($rSocketConnection, "Referer: http://www.411taxrelief.com/index.cgi?cid=2080\r\n");
			}
			fputs($rSocketConnection, "Accept-Language: en\r\n");
			fputs($rSocketConnection, "Content-type: application/x-www-form-urlencoded \r\n");
			fputs($rSocketConnection, "Content-length: " . strlen($sHttpPostString) . "\r\n");
			fputs($rSocketConnection, "User-Agent: MSIE\r\n");
			fputs($rSocketConnection, "Connection: close\r\n\r\n");
			fputs($rSocketConnection, $sHttpPostString);
		}

		if ($sOfferCode != 'VIDEOPROF') {
			if (strstr($sPostingUrl, "https:")) {
				while(!feof($rSocketConnection)) {
                                        $sRealTimeResponse .= @fgets($rSocketConnection, 1024);
					//2007-01-03 BB - This suppresses errors from a known PHP bug.
                                }
			} else {
				while(!feof($rSocketConnection)) {
					$sRealTimeResponse .= fgets($rSocketConnection, 1024);
				}
			}
		}
		fclose($rSocketConnection);
	}
	
	$sTrackHttpPostInfo = "INSERT INTO tempHttpFormPostTracking
			( dateTimePosted, email, offerCode, httpPostString ) values
			( now(), \"$sEmail\", \"$sOfferCode\", \"".addslashes($sPostingUrl."?".$sHttpPostString)."\" )";
	$rTrackHttpPostResult = dbQuery($sTrackHttpPostInfo);
	echo dbError();
	
	$sUpdateStatusQuery = "UPDATE otData
			   SET    processStatus = 'P',
					  sendStatus = 'S',
					  howSent = '$sHowSent',
					  dateTimeProcessed = now(),
					  dateTimeSent = now(),
					  realTimeResponse = \"".addslashes($sRealTimeResponse)."\"
			   WHERE  email='$sEmail' and offerCode='$sOfferCode'";
	$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
	
}


function checkForOtDataDups ($sEmail,$sOfferCode) {
	$bFoundDuplicateLead = false;
	
	$sCheckDupQuery = "SELECT * FROM otData
		   WHERE  offerCode = '$sOfferCode'
		   AND    email = '$sEmail'";
	$rCheckDupResult = dbQuery($sCheckDupQuery);
	if ( dbNumRows($rCheckDupResult) > 0 ) {
		$bFoundDuplicateLead = true;
	}
	
	return $bFoundDuplicateLead;
}




function validateCreditCard($sCardType, $iCardNumber)
{
	$bValid = true;
	
	if ( !ereg("^[0-9]{15,16}$", $iCardNumber) )
	{
		
		//alert ("Error in Card number");
		$bValid = false;
	}

	$ckCdLen = strlen($iCardNumber);
	
	//var cardType = "";
	//var ckCdS = cardNumber.substr(0,1);echo "Fgfg".isVisa($iCardNumber);
	if ($bValid == true) {
	if (strtolower(substr($sCardType,0,4)) == "visa" && (!isVisa($iCardNumber))) {
		//alert('visa invalid');
		
		$bValid =  false;
	}
	
	if ((strtolower(substr($sCardType, 0, 6)) == "master") && !(isMasterCard($iCardNumber))) {
		//alert('master invalid');
		$bValid =  false;
	} 
			
	if ( ( strtolower(substr($sCardType, 0, 3)) == "ame" ) && !(isAmericanExpress($iCardNumber))) {
		//alert('amex invalid');
		$bValid =  false;
	}
	
	if ((strtolower(substr($sCardType, 0, 8)) == "discover") && !(isDiscover($iCardNumber))) {
		//alert('discover invalid');
		$bValid =  false;
	}
	
	}
	
	if ($bValid == true) {
	$cdN1 = '';
	$cdN2 = '';

	for ($i= 0; $i < $ckCdLen; $i++)
	{
		if ($i % 2 != 0 ) {
			$cdN1 = substr($iCardNumber, $i,1); 
		}

		if ($i % 2 == 0) {
			$cdN1 = substr($iCardNumber, $i, 1) * 2;
		}

		if ($cdN1 > 9 ) {
			$cdN2 += $cdN1 - 9;
		} else {
				$cdN2 += $cdN1;
			
		}

	}

	$mod10 = $cdN2 % 10;
	
	if ($mod10 == 0){ 
		$bValid = true;
	} else {
		$bValid = false;
		
	}
	}
	return $bValid;
}



function isVisa($cc)
{
	$bValid = true;
	
  	if (((strlen($cc) == 16) || (strlen($cc) == 13)) && substr($cc, 0, 1) == 4) {  
    	  $bValid = true;   
    	 // echo "gfgf".$cc;
    	 // echo "Dfdf";  
    } else {     
  		$bValid = false;  		
      }
      //echo $bValid;
   return $bValid;
}  // END FUNCTION isVisa()



function isMasterCard($cc){
	$bValid = true;
	
  $firstdig = substr($cc, 0, 1);
  $seconddig = substr($cc, 1, 2);
  if ((strlen($cc) == 16) && ($firstdig == 5) &&
      (($seconddig >= 1) && ($seconddig <= 5))){
      
    $bValid = true;
      } else {
      
  	$bValid = false;
      }
      
  return $bValid;
} // END FUNCTION isMasterCard()



function isAmericanExpress($cc)
{

  $bValid = true;
  $firstdig = substr($cc, 0, 1);
  $seconddig = substr($cc, 1, 2);
  if ((strlen($cc) == 15) && ($firstdig == 3) && ($seconddig == 4 || $seconddig == 7) ) {
     
    $bValid = true;
      } else {
    
  	$bValid = false;
      }
      
      return $bValid;
} // END FUNCTION isAmericanExpress()


function isDiscover($cc)
{
	$bValid = true;
  $first4digs = substr($cc, 0, 4);
  if ((strlen($cc) == 16) && ($first4digs == "6011")) {
    $bValid = true;
  } else {
 	$bValid = false;
  }

  return $bValid;
  
} // END FUNCTION isDiscover()


//// Other functions



function calculateAge($iDobMonth, $iDobDay, $iDobYear) {
	$iAge = 0;
	$sBirthDate = $iDobYear."-".$iDobMonth."-".$iDobDay;
	
	$sAgeQuery = "SELECT YEAR(CURDATE())-YEAR('$sBirthDate') - (RIGHT(CURDATE(),5)<RIGHT('$sBirthDate',5)) AS age";
	
	$rAgeResult = dbQuery($sAgeQuery);
	echo dbError();
	while ($oAgeRow = dbFetchObject($rAgeResult)) {
		$iAge = $oAgeRow->age;
	}
	return $iAge;
}



function validateSocialSecurity($sSs, $iSsLength='11') {

	$bValid = true;
	
	if ($iSsLength == 11) {
		if (!eregi("^[0-9]{3}-[0-9]{2}-[0-9]{4}$", $sSs) ) {
			$bValid = false;
		}
	} else if ($iSsLength == 9) {
		if (!eregi("^[0-9]{9}$", $sSs) ) {
			$bValid = false;
		}
	} else {
		$bValid = false;
	}
	
	return $bValid;
}


if ($_SERVER['SERVER_ADDR']=='64.132.70.111') {
	include_once('/home/sites/admin.popularliving.com/validateAddress/validateAddressAo.php');
} else {
	include_once('/home/sites/www_popularliving_com/validateAddress/validateAddressAo.php');
}

function validateAddress($sAddress, $sCity, $sState, $sZip, $sGblRoot){
	//$a = validateAddressAo( addslashes($sAddress), '', $sCity, $sState, $sZip, $sGblRoot );
	//return "$sAddress, $sCity, $sState, $sZip, $sGblRoot => ".print_r($a);
	$sAoValidation = validateAddressAo( addslashes($sAddress), '', $sCity, $sState, $sZip, $sGblRoot );

	if( substr( $sAoValidation, 0, 7 ) == "Failure" ) {
		$aAoErrorLine = explode( "|", $sAoValidation );
		$sAoErrorCode = $aAoErrorLine[1];
		$sAoErrorText = $aAoErrorLine[2];
		switch ( $sAoErrorCode ) {
			case 'AM':
			case 'R':
			case 'U':
			case 'X':
			case 'T':
			case 'Z':
			case 'W':
				return "$sAoErrorCode";
				break;
			default:
				//mail( "it@amperemedia.com", "AO Address Validation Hiccup", $sAoValidation );
				return "0";
				break;
		}
	} elseif ( substr( $sAoValidation, 0, 6 ) == "update" ) {
		// EX: update|address=3198  Darby St |address2= |city=Simi Valley|state=CA|zip=93063|oldaddress=3198 Darby St|oldaddress2=115|oldcity=Simi Valley|oldstate=CA|oldzip=93065|
		$aAoErrorLine = explode( "|", $sAoValidation );
		
		$aNewAddressText = explode( "=", $aAoErrorLine[1] );
		$aNewAddress2Text = explode( "=", $aAoErrorLine[2] );
		$aNewCityText = explode( "=", $aAoErrorLine[3] );
		$aNewStateText = explode( "=", $aAoErrorLine[4] );
		$aNewZipText = explode( "=", $aAoErrorLine[5] );
		
		$sAddress = $aNewAddressText[1];
		$sAddress2 = $aNewAddress2Text[1];
		$sCity = $aNewCityText[1];
		$sState = $aNewStateText[1];
		$sZip = $aNewZipText[1];
		
		return "update||$sAddress,$sAddress2-$sCity-$sZip";
		exit;
		
	} else {
		return "1";
		exit;
	}
}

/**
* Validates City State and Zipcode if all are matched
*
* Validate a city, state and zip combination
* Returns true if valid false if not valid
* @return boolean
* @param string [$city] - City Name to validate
* @param string [$state] - State to validate
* @param string [$zipCode] - zipCode to validate
*/

function validateCityStateZip($city, $state, $zipCode)
{
	$sFunction = "validateCityStateZip";
	$valid = true;
	$selectQuery = "SELECT *
					FROM   zipStateCity
                	WHERE  zip = '".substr($zipCode,0,5)."'
					AND    state = '$state' 
					AND    city = '$city'";
	$selectResult = dbQuery($selectQuery);
	if (!$selectResult)
	echo dbError();
	
	if (dbNumRows($selectResult)) {
		$valid = true;
	} else {
		$valid = false;
	}
	
	if ($valid == false) {
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress, sourceCode, pageId)
						 VALUES(now(), '$city, $state, $zipCode', '$sFunction', '".$_SESSION["sSesRemoteIp"]."',\"".$_SESSION["sSesSourceCode"]."\",\"".$_SESSION["iSesPageId"]."\")";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	
	// return result
	if ($selectResult) {
		dbFreeResult($selectResult);
	}
	
	return $valid;
}

function isInteger($aString, $addOn = "") {
	
	$aout = true;
	$sFunction = "isInteger";
	
	if ( !ereg("^[0-9$addOn]+$",$aString ) ) {
		$aout = false;
	}
	
	if ($aout == false) {
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress, sourceCode, pageId)
						 VALUES(now(), '$aString', '$sFunction', '".$_SESSION["sSesRemoteIp"]."',\"".$_SESSION["sSesSourceCode"]."\",\"".$_SESSION["iSesPageId"]."\")";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	return $aout;
}





/**
* Gets the distance between two areas and checks if it exceeds the max distance
*
* Calls the {@link getDistance()} function to calculate the distance
* Checks if the distance exceeds the maximum allowed distance
*
* This function works with {@link getDistance()}
* @return boolean
*
* @param string [$area1] - First zipcode/Phone to calculate the distance
* @param string [$area2] - Second zipcode/Phone to calculate the distance
* @param string [$maxDistance] - Maximum allowed distance to compare with
*/
function exceedsMaxDistance($area1, $area2, $maxDistance,$sSourceCode='') {
	if ($sSourceCode !='') {
		$sCheckPhoneResult = dbQuery("SELECT * FROM phoneValidateBypass WHERE sourceCode = \"$sSourceCode\"");
		if (dbNumRows($sCheckPhoneResult) > 0 ) {
			return true;
		}
	}
	
	$sFunction = "exceedsMaxDistance";
	$dist = getDistance($area1, $area2,$sSourceCode);
	
	if ( $dist > $maxDistance) {
				
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress, sourceCode, pageId)
						 VALUES(now(), \"$area1, $area2, $maxDistance\", '$sFunction', '".$_SESSION["sSesRemoteIp"]."',\"".$_SESSION["sSesSourceCode"]."\",\"".$_SESSION["iSesPageId"]."\")";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		echo dbError();
		return false;	
		
	} else {
		return true;
	}
}

function validateZip($sZipCode) {
	$valid = true;
	$sFunction = "validateZip";
	
	if ( ereg("^[0-9]{5}$", $sZipCode) ||  ereg("^[0-9]{9}$", $sZipCode)  || ereg("^[0-9]{5}-[0-9]{4}$", $sZipCode) ) {
		$valid = true;
	} else {
		$valid = false;
	}
	
	if ($valid == false) {
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress, sourceCode, pageId)
						 VALUES(now(), '$sZipCode', '$sFunction', '".$_SESSION["sSesRemoteIp"]."',\"".$_SESSION["sSesSourceCode"]."\",\"".$_SESSION["iSesPageId"]."\")";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	return $valid;
	
}



/**
* Validates the string does not contain banned word
*
* Returns true if valid, false if not valid.
* @return boolean
* @param string [$aString] - String to validate as Integer value
*/
function isBannedWord($aString) {
	$isBadWord = false;
	$sFunction = "isBannedWord";
	
	$aString = addslashes($aString);
	$aString = ltrim(rtrim($aString));
	$aStringArray = split(" ",$aString);
	for ($i=0;$i<count($aStringArray);$i++) {
		
		$tempString = $aStringArray[$i];
		$tempString = stripslashes($tempString);
		$tempString = ltrim(rtrim($tempString));

		$bannedQuery = "SELECT word
						FROM   bannedWords" ; 
		
		$bannedResult = dbQuery($bannedQuery) ;
		//echo $bannedQuery. mysql_error();
		
		
		while ($bannedRow = dbFetchObject($bannedResult)) {
			$badWord = $bannedRow->word;
			
			if (strstr(strtolower($tempString),strtolower($badWord)))
			{
				/*if ($aString == '1234 pass') {
					echo "<BR>1 ".$badWord." aaa ".$tempString." aaa";
				}*/
				
				$isBadWord = true;
				$approvedQuery = "SELECT word
								  FROM   approvedWords
								  WHERE  upper(word) = upper('$tempString')" ; 
				
				$approvedResult = dbQuery($approvedQuery) ;
				if ( dbNumRows($approvedResult) >0 ) {
					$isBadWord = false;
					/*if ($aString == '1234 pass') {
						echo "<BR>approved ".$badWord." ".$tempString;
					}*/
				}
				
			}
			
		}
	}
	
	
	if ($isBadWord == true) {
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress, sourceCode, pageId)
						 VALUES(now(), '$aString', '$sFunction', '".$_SESSION["sSesRemoteIp"]."',\"".$_SESSION["sSesSourceCode"]."\",\"".$_SESSION["iSesPageId"]."\")";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	
	if ($bannedResult) {
		dbFreeResult($bannedResult);
	}
	
	return $isBadWord;
}


/**
* Validates AlphaNumeric Value
*
* Returns true if valid, false if not valid
* @return boolean
* @param string [$aString] - String to validate as Alpha or Numeric value
* @param string [$addOn] - Pattern to allow alongwith alphaNumeric, e.g. +,-

Alpha char is a-z, apostophe, space, hyphen, period.

*/
function isAlphaNumeric($aString, $addOn = "") {
	
	$aout = true;
	$sFunction = "isAlphaNumeric";
	
	if ( !eregi("^[-$addOn A-Z0-9[:space:]'\.]{0,}$", $aString) )  {
		$aout = false;
	}
	
	if ($aout == false) {
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress, sourceCode, pageId)
						 VALUES(now(), '$aString', '$sFunction', '".$_SESSION["sSesRemoteIp"]."',\"".$_SESSION["sSesSourceCode"]."\",\"".$_SESSION["iSesPageId"]."\")";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	return $aout;
}


// function to check any 2 char or 3 char sequence repeating more than 2 times in a string
// returns false if string is not valid containing any sequence in it
function isSequence($sString) {
	
	$sFunction = "isSequence";
	
	$j=0;
	
	$sequence = false;
	
	for ($i=$j;$i<strlen($sString);$i++) {

		$sSubStr1 = substr($sString,$i,2);  
		$sSubStr2 = substr($sString,$i+2,2);  
		$sSubStr3 = substr($sString,$i+4,2);  
		//if ($sString == 'smitita') 
		//echo "<BR>".$sSubStr1." 1 ".$sSubStr2." 2 ".$sSubStr3;
		//if ($_SESSION["sSesRemoteIp"] =='198.63.247.2')
		//	echo "<BR>aa".$sSubStr1." 1 ".$sSubStr2." 2 ".$sSubStr3;	
		if ($sSubStr1 == $sSubStr2 && $sSubStr2 == $sSubStr3 && trim($sSubStr1) != '' && trim($sSubStr2) != '') {
			$sequence = true;			
			break;
		}	
		
		$sSubStr1 = substr($sString,$i,3);  
		$sSubStr2 = substr($sString,$i+3,3);  
		$sSubStr3 = substr($sString,$i+6,3);
		if ($sSubStr1 == $sSubStr2 && $sSubStr2 == $sSubStr3 && trim($sSubStr1) != '' && trim($sSubStr2) != '') {
			$sequence = true;
			break;
		}		
	}
		
	if ($sequence == true) {
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress, sourceCode, pageId)
							 VALUES(now(), '$sString', '$sFunction', '".$_SESSION["sSesRemoteIp"]."',\"".$_SESSION["sSesSourceCode"]."\",\"".$_SESSION["iSesPageId"]."\")";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
	}
	return $sequence;
}


?>
