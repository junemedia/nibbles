<?php

// Validation Library

/**
*
* Script for all the validation used in OT System.
*
* Script defines following validation functions:
* <ul>
* <li> @link http://cory.myfree.com/docs/Validation/_validate_php.html#functionisAlpha isAlpha()
*   - Validates if the value is Alpha
* <li> {@link isAlphaNumeric()}
*   - Validates if the value is AlphaNumeric
* <li> {@link isBannedWord()}
*   - Validates if the word is not a banned word
* <li> {@link isInteger()}
*   - Validates if the value is Integer
* <li> {@link validateAddress()}
*   - Validates if the address is a valid Address
* <li> {@link validateCityStateZip()}
*   - Validates City, State and Zip combination is a valid combination.
* <li> {@link validateEmail()}
*   - Validates eMail
* <li> {@link validateFirstName()}
*   - Validates First Name
* <li> {@link validateLastName()}
*   - Validates Last Name
* <li> {@link validatePhone()}
*	- Vallidates Phone No. is a valid phone no.
* </ul>
* @package Validation
*/

/**
* Validates First/Last Name
*
* Returns true if valid, false if not valid
* Validates for:
* <ul>
* <li> Not Banned Word
* <li> Length Greater Than 0
* <li> No 3 Vowels in a row
* <li> No 5 consentant in a row
* </ul>
* @return boolean
* @param string [$name] - First/Last Name to validate
* This function works with {@link isAlpha()} and {@link isBannedWord()}
*/

function validateName($name)
{
	//global $_SESSION;
	$valid = true ;
	$sFunction = "validateName";
	
	/** check if banned word
	*/
	
	if (isBannedWord($name)) {
			
		$valid = false ;
			
	}
	
	/** check that length is greater than 0
	*/
	
	if (strlen($name) < 2) {
		
		$valid = false ;
	}
	
	
	//must contain at least one letter
	
	if ( !eregi("[A-Z]{1,}", $name) )  {
		$valid = false;
	}
	
	// must not contain 2 or more dots in an order
	if ( eregi("[\.]{2,}", $name) )  {
		$valid = false;
	}
	
	// check if only alpha characters
	
	if (!isAlpha($name,"")) {
		$valid = false;
	}
	
	/** check four vowels or five consentant in row
	*/
	//|| eregi("[^aeiou]{5,}", $name)
	if ( eregi("[aeiou]{4,}", $name) || eregi("[^aeiou\.']{5,}", $name)) {
		
		$valid = false;
	}
	
	// must not contain 2 or more single quores in an order
	if ( eregi("[']{2,}", $name) ) {		
		$valid = false;
	}
	
	// check if any sequence of 2 chars more than 2 times or sequence of 3 chars more than 2 times in name
	if ( isSequence($name)) {
		$valid = false;		
	}
	
	
	/** return result
	*/
	if ($valid == false) {
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress)
						 VALUES(now(), '$name', '$sFunction', '".$_SESSION["sSesRemoteIp"]."')";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
	
	}
	
	return $valid;
	
}

/**
* Validates Address
*
* Returns true if valid, false if not valid
* Validates for:
* <ul>
* <li> Not Banned Word
* <li> Length Greater Than 0
* <li> Must contain Alpha, Number, , #, -, or '
* </ul>
* @return boolean
* @param string [$address] - Address to validate
*/
function validateAddress($address)
{
	$valid = true ;
	$sFunction = "validateAddress";
	
	// check if banned word
	if (isBannedWord($address)) {
		$valid = false ;
	}
	
	// check that length is greater than 1
	if (strlen($address) < 2) {
		$valid = false ;
	}
	//.,-#'[[:space:]]
	// only alpha characters
	if (!isAlphaNumeric($address, "#,\/")) {
		$valid = false;
	}
	
	// must not contain 2 or more dots in an order
	if ( eregi("[\.]{2,}", $address) )  {
		$valid = false;
	}
	
	// must not contain 2 or more single quotes in an order
	if ( eregi("[']{2,}", $name) ) {
		$valid = false;
	}

	// address must not start with zero
	if (ereg("^[0]{1,}", $address)) {
		$valid = false;
	}

	// check if any sequence of 2 chars more than 2 times or sequence of 3 chars more than 2 times in name
	if (isSequence($address)) {
		$valid = false;		
	}

	// check four vowels or five consentant in a row
	//|| eregi("[^aeiou]{5,}", $address)
	if ( eregi("[aeiou]{4,}", $address) || eregi("[^aeiou0-9[:space:]\.]{5,}", $address)) {
		$valid = false;
		
	}
		
	if ($valid == false) {
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress)
						 VALUES(now(), '$address', '$sFunction', '".$_SESSION["sSesRemoteIp"]."')";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	
	// return result
	return $valid;
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
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress)
						 VALUES(now(), '$city, $state, $zipCode', '$sFunction', '".$_SESSION["sSesRemoteIp"]."')";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	
	// return result
	if ($selectResult) {
		dbFreeResult($selectResult);
	}
	
	return $valid;
}

/**
* Verifies eMail format
*
* Returns true if valid email format, false if not valid format
* Validates for:
* <ul>
* <li> Format of the email is a valid format.
* </ul>
* @return boolean
*
* @param string [$eMail] - eMail address to validate
*
*/

function validateEmailFormat($eMail) {
	$valid = true;
	$sFunction = "validateEmailFormat";
	
	if ($eMail == '') {
		$valid = false;
	}
	// check if contains valid domain
	if (!(isValidDomain($eMail))) {
		$valid = false;
	}
	
	// check if contains banned domain
	if (isBannedDomain($eMail, $domainName)) {
		$valid = false;
	}
	
	// check starts with bannedEmailStart
	if (isBannedEmailStart($eMail)) {
		$valid = false;
	}
	
	// check if not a bannedEmail
	if (isBannedEmail($eMail)) {
		$valid =  false;
	}
	
	list ( $userName, $domain ) = split ("@",$eMail);
	
	// check if contains banned ip.
	$ipArray = gethostbynamel($domain);
	
	for ($i = 0; $i < count($ipArray); $i++) {
		if (isBannedIp($ipArray[$i])) {
			$valid = false;
		}
	}
	
	if ( !ereg(  "^[A-Za-z0-9\$\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $eMail) ) {
		//bad Email
		$valid = false;
	}
	
	if ($valid == false) {
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress)
						 VALUES(now(), '$eMail', '$sFunction', '".$_SESSION["sSesRemoteIp"]."')";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	return $valid;
}


function isValidDomain($email) {
	$found = 0;
	$valid = true;
	$sFunction = "isValidDomain";
	
	$domainQuery = "SELECT *
					FROM   validDomains";
	$domainResult = dbQuery($domainQuery);
	while ($domainRow = dbFetchObject($domainResult)) {
		if (strtolower($domainRow->domain) == strtolower(substr($email, strlen($email)-strlen($domainRow->domain)))) {
			$found++;
		}
	}
	
	if (!($found)) {
		$valid = false;
	}
	
	if ($valid == false) {
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress)
						 VALUES(now(), '$email', '$sFunction', '".$_SESSION["sSesRemoteIp"]."')";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	
	if ($domainResult) {
		dbFreeResult($domainResult);
	}
	return $valid;
}


function isBannedDomain($email, &$sDomainName) {
	$isBanned = false;
	$found = 0;
	$sFunction = "isBannedDomain";
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
	
	
	if ($isBanned == true) {
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress)
						 VALUES(now(), '$sDomainName', '$sFunction', '".$_SESSION["sSesRemoteIp"]."')";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	
	if ($domainResult) {
		dbFreeResult($domainResult);
	}
	
	return $isBanned;
}



function isBannedEmailStart($eMail) {
	$isBanned = false;
	
	$sFunction = "isBannedEmailStart";
	
	$checkQuery = "SELECT *
				   FROM   bannedEmailStart";
	$checkResult = dbQuery($checkQuery);
	
	while ($checkRow = dbFetchObject($checkResult)) {
		if (substr(strtolower($eMail),0,strlen($checkRow->startsWith)) == strtolower($checkRow->startsWith)) {
			$isBanned = true;
		}
	}
	
	if ($isBanned == true) {
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress)
						 VALUES(now(), '$eMail', '$sFunction', '".$_SESSION["sSesRemoteIp"]."')";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	
	if ($checkResult) {
		dbFreeResult($checkResult);
	}
	
	return $isBanned;
}

function isBannedEmail($eMail) {
	$isBanned = false;
	$sFunction = "isBannedEmail";
	$bannedQuery = "SELECT *
					FROM   bannedEmails
					WHERE  email = '$eMail'";
	$bannedResult = dbQuery($bannedQuery);
	if (dbNumRows($bannedResult) >0) {
		$isBanned = true;
	}
	
	if ($isBanned == true) {
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress)
						 VALUES(now(), '$eMail', '$sFunction', '".$_SESSION["sSesRemoteIp"]."')";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	
	if ($bannedResult) {
		dbFreeResult($bannedResult);
	}
	
	return $isBanned;
}


function isBannedIp($ip) {
	$isBanned = false;
	$sFunction = "isBannedIp";
	$bannedQuery = "SELECT *
					FROM   bannedIps
					WHERE  ipAddress = '$ip'";
	$bannedResult = dbQuery($bannedQuery);
	if (dbNumRows($bannedResult) > 0) {
		$isBanned = true;
	}
	
	if ($isBanned == true) {
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress)
						 VALUES(now(), '$ip', '$sfunction', '".$_SESSION["sSesRemoteIp"]."')";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	
	if ($bannedResult) {
		dbFreeResult($bannedResult);
	}
	
	return $isBanned;
	
}


/**
* Validates eMail
*
* Returns true if valid false if not valid
* Validates for:
* <ul>
* <li> Valid eMail format.
* <li> Contains valid domain name.
* <li> Checks against bannedEmailStart.
* <li> Checks against bannedEmail.
* <li> Checks it's an active eMail.
* </ul>
* @return boolean
* @param string [$eMail] - eMail address to validate
*/
function validateEmail($eMail, $domainLookup, &$connectionFailed, $debug=false) {
	$valid = true;
	if ( !ereg(  "^[A-Za-z0-9\$\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $eMail) ) {
		//Bad Email
		return false;
	}
	
	$found = 0;
	// check if contains valid domain
	if (!(isValidDomain($eMail))) {
		return false;
	}
	
	// check if contains banned domain
	if (isBannedDomain($eMail)) {
		return false;
	}
	
	// check starts with bannedEmailStart
	if (isBannedEmailStart($eMail)) {
		return false;
	}
	
	// check if not a bannedEmail
	if (isBannedEmail($eMail)) {
		return false;
	}
	
	$found = 0;
	
	// check if the email is an active email
	
	list ( $userName, $domain ) = split ("@",$eMail);
	
	// check if contains banned ip.
	$ipArray = gethostbynamel($domain);
	
	for ($i = 0; $i < count($ipArray); $i++) {
		if (isBannedIp($ipArray[$i])) {
			return false;
		}
	}
	
	if ($domainLookup) {
		// Check that MX(mail exchanger) record exists in domain.
		if ( checkdnsrr ( $domain, "MX" ) )  {
			if ($debug) {
				echo "\n\r\n\r Confirmation : MX record about {$domain} exists.<br>\n\r";
			}
			
			// If MX record exists, save MX record address.
			if ( getmxrr ($domain, $MXHost))  {
				if ($debug) {
					echo "Confirmation : Is confirming address by MX LOOKUP.<br>\n\r";
				}
				for ( $i = 0,$j = 1; $i < count ( $MXHost ); $i++,$j++ ) {
					if ($debug) {
						echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Result($j) - $MXHost[$i]<br>\n\r";
					}
				}
				
			}
			// Getmxrr function does to store MX record address about $Domain in arrangement form to $MXHost.
			// $ConnectAddress socket connection address.
			$connectAddress = $MXHost[0];
		}
		else {
			// If there is no MX record simply @ to next time address socket connection do .
			$valid = false;
			$connectAddress = $domain;
			if ($debug) echo "Confirmation : MX record about {$domain} does not exist.<br>\n\r";
			
			$connectionFailed = true;
		}
		
		$connect = fsockopen ( $connectAddress, 25,$errno,$errstr,30);
		
		// Success in socket connection
		if ($connect)
		{
			socket_set_timeout($connect, 30);
			
			if ($debug) echo "Connection succeeded to {$connectAddress} SMTP.<br>\n\r";
			// Judgment is that service is preparing though begin by 220 getting string after connection .
			if ( ereg ( "^220", $out = fgets ( $connect, 1024 ) ) ) {
				
				// Inform client's reaching to server who connect.
				if (strstr($eMail,"aol.com")) {
					fputs ( $connect, "HELO aol.com\r\n" );
				} else {
					fputs ( $connect, "HELO $HTTP_HOST\r\n" );
				}
				if ($debug) echo "Run : HELO $HTTP_HOST<br>\n\r";
				$i = 0;
				if (strstr($eMail,"aol.com")) {
					while ($i++ < 6) {
						$out .= fgets($connect,1024);
					}
				} else {
					$out = fgets($connect,1024);
				}
				if ($debug) {
					echo $out; // Receive server's answering cord.
				}
				
				$socketStatus = socket_get_status($connect);
				
				if ($debug) {
					echo "Socket Status:<BR>\n\r ";
					while ( list ( $key,$val ) = each ($socketStatus) ) {
						echo "$key = $val<br>\n\r";
					}
				}
				// Inform sender's address to server.
				if ($debug) {
					echo "Mail From: ";
				}
				if (strstr($eMail,"aol.com")) {
					fputs ( $connect, "mail from: <myfree3400@aol.com>" );
				} else {
					fputs ( $connect, "MAIL FROM: <{$eMail}>\r\n" );
				}
				$from = fgets ( $connect, 1024 ); // Receive server's answering cord.
				
				if ($debug) {
					echo "mail from: $from\n\r";
				}
				// Inform listener's address to server.
				fputs ( $connect, "RCPT TO: <{$eMail}>\r\n" );
				if ($debug) echo "Run : RCPT TO: &lt;{$eMail}&gt;<br>\n\r";
				
				$to = fgets ( $connect, 1024 ); // Receive server's answering cord.
				if ($debug) {
					echo "mail to: $to\n\r";
					
					echo "Socket Last Error: ".socket_strerror(socket_last_error($connect))."<BR>\n\r
					From: $from \n\r To: $to\n\r";
				}
			}
			
			// Finish connection.
			fputs ( $connect, "QUIT\r\n");
			if ($debug) {
				echo "Run : QUIT<br>\n\r";
			}
			
			$socketStatus = socket_get_status($connect);
			if ($debug) {
				echo "Socket Status:<BR>\n\r ";
				while ( list ( $key,$val ) = each ($socketStatus) ) {
					echo "$key = $val<br>\n\r";
				}
			}
			// Server's answering cord about MAIL and TO command checks.
			// Server about listener's address reacts to 550 codes if there does not exist
			// checking that mailbox is in own E-Mail account.
			if ( !(ereg ( "^250", $from ) && ereg ( "^250", $to )) ) {
				$valid = false;
				if ($debug) {
					echo "{$eMail} is address does not admit in E-Mail server.<br>\n\r";
				}
			}
			fclose($connect);
		} else {
			
			// Failure in socket connection
			$valid = false;
			if ($debug) {
				echo "Can not connect E-Mail server ({$connectAddress}).<br>\n\r";
			}
			$connectionFailed = true;
		}
		unset($connect);
		// End active email checking
	}
	
	return $valid;
	
}  //END function ValidateEmail



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
								  WHERE  word = '$tempString'" ; 
				
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
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress)
						 VALUES(now(), '$aString', '$sFunction', '".$_SESSION["sSesRemoteIp"]."')";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	
	if ($bannedResult) {
		dbFreeResult($bannedResult);
	}
	
	return $isBadWord;
}


/**
* Validates Integer Value
*
* Returns true if valid false if not valid.
* @return boolean
* @param string [$aString] - String to validate as Integer value
* @param string [$addOn] - Pattern to allow alongwith Integer, e.g. +,-
*/
function isInteger($aString, $addOn = "") {
	
	$aout = true;
	$sFunction = "isInteger";
	
	if ( !ereg("^[0-9$addOn]+$",$aString ) ) {
		$aout = false;
	}
	
	if ($aout == false) {
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress)
						 VALUES(now(), '$aString', '$sFunction', '".$_SESSION["sSesRemoteIp"]."')";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	return $aout;
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
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress)
						 VALUES(now(), '$aString', '$sFunction', '".$_SESSION["sSesRemoteIp"]."')";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	return $aout;
}


/**
* Validates AlphaNumeric Value
*
* Returns true if valid, false if not valid
* @return boolean
* @param string [$aString] - String to validate as Alpha value
* @param string [$addOn] - Pattern to allow alongwith Alpha, e.g. +,-
*/
function isAlpha($aString, $addOn = "") {
	$aString = trim($aString);
	$aout = true;
	$sFunction = "isAlpha";
	if ( !eregi("^[-$addOn A-Z[:space:]'\.]*$", $aString) )  {
		$aout = false;
	}
	
	
	if ($aout == false) {
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress)
						 VALUES(now(), '$aString', '$sFunction', '".$_SESSION["sSesRemoteIp"]."')";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	return $aout;
}


function validate($sString, $sCheck) {
	
	$bValid = true;
	$sFunction =  "validate";
	
	if (strtolower($sCheck) == 'alpha') {
		if (!eregi("^[A-Z]*$", $sString) ) {
			$bValid = false;
		}
	} else if (strtolower($sCheck) == 'number') {
		if (!eregi("^[0-9]*$", $sString) ) {
			$bValid = false;
		}
	} else if (strtolower($sCheck) == 'alphanumeric') {
		if (!eregi("^[A-Z0-9]*$", $sString) ) {
			$bValid = false;
		}
	}
	
	if ($bValid == false) {
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress)
						 VALUES(now(), '$sString', '$sFunction', '".$_SESSION["sSesRemoteIp"]."')";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	
	return $bValid;
}

/**
* Validates Phone No.
*
* Returns true if valid false if not valid.
* Validates for:
* <ul>
* <li> First Digit of $areaCode and $exchange is not zero.
* <li> $areaCode, $exchange and $four all are required fields and Integer.
* <li> $areaCode and $exchange does not contain 3 same digits.
* <li> Matches $areaCode within $state by database lookup.
* </ul>
*
* This function works with {@link isInteger()}
* @return boolean
*/

function validatePhone($area, $exchange, $four, $extension='', $state='') {
	
	$valid = "true";
	$sFunction = "validatePhone";
	
	//All 3 phone fields are required
	if (!$area || !$exchange || !$four) {
		$valid = false;
	}
	
	
	//All 3 phone Must Be Numberic
	if (!isInteger($area) || !isInteger($exchange) || !isInteger($four)) {
		$valid = false;
	}
	
	//First Digit of Area and Exchange can't be one or zero
	//if (substr($area,0,1) == "0" || substr($area,0,1) == "1" || substr($exchange,0,1) == "0" || substr($exchange,0,1) == "1") return false;
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
	
	//if ( ereg("[2{3}|3{3}|4{3}|5{3}|6{3}|7{3}|8{3}|9{3}]", $area) || ereg("^[222|333|444|555|666|777|888|999]", $exchange)) {
	//		return false;
	//	}
	
	//Make array of same 3 digit codes,
	$sa[] = "222";
	$sa[] = "333";
	$sa[] = "444";
	$sa[] = "555";
	$sa[] = "666";
	$sa[] = "777";
	$sa[] = "888";
	$sa[] = "999";
	
	//Test 3 digit codes against area & exchange
	for ($i=0; $i<count($sa); $i++) {
		if ($area == $sa[$i] || $exchange == $sa[$i]) {
			$valid = false;
			break;
		}
	}
	
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
		/*if($city=='northbrook'){
			echo $areaQuery.mysql_num_rows($areaResult);
		}*/
		
		if (!$areaResult) {
			echo dbError();
		} else if (dbNumRows($areaResult) == 0) {
			$valid = false;
		}
	}
	
	//Validate Extension for number:
	if ($extension) {
		if (!isInteger($extension)) {
			$valid = false;
		}
	}
	
	if ($valid == false) {
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress)
						 VALUES(now(), \"$area, $exchange, $four, $extension, $state\", '$sFunction', '".$_SESSION["sSesRemoteIp"]."')";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	
	if ($areaResult) {
		dbFreeResult($areaResult);
	}
	
	return $valid;
}



/**
* Gets the distance between two areas
*
* Checks if the values are Integer only.
* Checks if the parameter passed is zipcode or areacode+exchange.
* Gets the latitude and longitude accordingly.
* Calculate the distance between two areas as per formula
*
* This function works with {@link isInteger()}
* @return string [$dist] - Calculated distance between two areas.
*
* @param string [$area1] - First zipcode/Phone to calculate the distance
* @param string [$area2] - Second zipcode/Phone to calculate the distance
*/
function getDistance($area1, $area2) {
	
	$latt1 = 0;
	$long1 = 0;
	
	if (! (ereg("^[0-9-]+$", $area1) && ereg("^[0-9-]+$", $area2))) {
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
	/*if ($area1=='94103' || $area2 == '94103') {
	echo "<BR>lat $latt1 $long1 $latt2 $long2";
	}*/
	
	$a1 = sin($latt1/57.3) * sin($latt2/57.3);
	$a2 = cos($latt1/57.3) * cos($latt2/57.3) * cos($long2/57.3 - $long1/57.3);
	$a = $a1 + $a2;
	$dist = 3959 * atan(sqrt(1-($a*$a))/$a);
	
	if ($result1) {
		dbFreeResult($result1);
	}
	
	if ($result2) {
		dbFreeResult($result2);
	}
	
	return $dist;
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
function exceedsMaxDistance($area1, $area2, $maxDistance) {
	if (getDistance($area1, $area2) > $maxDistance) {
		return true;
	} else {
		return false;
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
		$sErrorLogQuery = "INSERT INTO errorLog(errorDateTime, valueInvalidated, function, ipAddress)
						 VALUES(now(), '$sZipCode', '$sFunction', '".$_SESSION["sSesRemoteIp"]."')";
		$rErrorLogResult = dbQuery($sErrorLogQuery);
		
	}
	return $valid;
	
}


// function to check any 2 char or 3 char sequence repeating more than 2 times in a string
// returns false if string is not valid containing any sequence in it
function isSequence($sString) {
	
	$j=0;
	
	$sequence = false;
	
	for ($i=$j;$i<strlen($sString);$i++) {

		$sSubStr1 = substr($sString,$i,2);  
		$sSubStr2 = substr($sString,$i+2,2);  
		$sSubStr3 = substr($sString,$i+4,2);  
		//if ($sString == 'smitita') 
		//echo "<BR>".$sSubStr1." 1 ".$sSubStr2." 2 ".$sSubStr3;
		if ($sSubStr1 == $sSubStr2 && $sSubStr2 == $sSubStr3) {
			$sequence = true;
			break;
		}	
		
		$sSubStr1 = substr($sString,$i,3);  
		$sSubStr2 = substr($sString,$i+3,3);  
		$sSubStr3 = substr($sString,$i+6,3);
		if ($sSubStr1 == $sSubStr2 && $sSubStr2 == $sSubStr3) {
			$sequence = true;
			break;
		}		
	}
		
	return $sequence;
}



// function to check if the integer passed to the function is a series of numbers only.

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


//// Other functions



?>