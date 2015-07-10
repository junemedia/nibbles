<?php

include_once("includes/paths.php");
include_once("$sGblLibsPath/validationFunctions.php");
session_start();
include_once("$sGblIncludePath/e1PageInclude.php");

$_SESSION['sSesEpagePixelUrl'] = '';

if ($src != '') {
	if ((!ctype_alnum($src))) { $src = ''; }
	$_SESSION['sSesSourceCode'] = $src;
}
if ($ss != '') {
	if ((!ctype_alnum($ss))) { $ss = ''; }
	$_SESSION['sSesSubSourceCode'] = $ss;
}

if ((!ctype_digit($_SESSION['iSesPageId']))) { $_SESSION['iSesPageId'] = ''; }


$iJoinListId = "162";
$sRemoteIp = trim($_SERVER['REMOTE_ADDR']);
$sRedirectTo = eregi_replace("_e1.php", ".php", $sRefererScriptFileName);


$sEmail = makeQuerySafeToRun($sEmail);


// make entry into eTracking table to track submit action
$sTrackingQuery = "INSERT INTO eTracking(submitDateTime, pageId, sourceCode, subSourceCode, email, ipAddress)
				   VALUES(now(), '".$_SESSION['iSesPageId']."', '".$_SESSION['sSesSourceCode']."', 
				   '".$_SESSION['sSesSubSourceCode']."',\"$sEmail\", '$sRemoteIp' )"; 
$rTrackingResult = dbQuery($sTrackingQuery);
echo dbError();


if ( !(validateEmailFormat($sEmail))) {
	if ( !(isValidDomain($sEmail))) {
		echo "<script language=JavaScript>
				alert (\"You must be located in the US to use this system.\");
				window.location=\"http://www.myfree.com\";
				</script>";
		exit;
	}

	$sMessage .= "<li>Please Enter A Valid Email Address...";
	// increment e1 submit attempts and rejects

	$sCheckQuery = "SELECT *
					FROM   eTrackingSum
					WHERE  pageId = '".$_SESSION['iSesPageId']."'
					AND	   submitDate = CURRENT_DATE
					AND	   sourceCode = '".$_SESSION['sSesSourceCode']."'";
	$rCheckResult = dbQuery($sCheckQuery);
	if ( dbNumRows($rCheckResult) == 0 ) {
		// insert new record
		$sInsertQuery = "INSERT INTO eTrackingSum(pageId, submitDate, sourceCode, attempts, rejects)
						 VALUES('".$_SESSION['iSesPageId']."', CURRENT_DATE, '".$_SESSION['sSesSourceCode']."', '1', '1')";
		$rInsertResult = dbQuery($sInsertQuery);
		echo dbError();
	} else {
		// update record
		$sUpdateQuery = "UPDATE eTrackingSum
						 SET    attempts = attempts + 1,
								rejects = rejects + 1
						 WHERE	pageId = '".$_SESSION['iSesPageId']."'
						 AND	submitDate = CURRENT_DATE
						 AND    sourceCode = '".$_SESSION['sSesSourceCode']."'";
		$rUpdateResult = dbQuery($sUpdateQuery);
		echo dbError();
	}

	if (strstr($sRefererScriptFileName,"?")) {
		header("Location:$sRefererScriptFileName&sMessage=$sMessage&$sOutboundQueryString&".SID);
	} else {
		header("Location:$sRefererScriptFileName?sMessage=$sMessage&$sOutboundQueryString&".SID);
	}

	exit();
} else {

	// Check if an entry exist in the admin system for this source code.
	$sGetEOptions = "SELECT * FROM eOptions WHERE sourceCode='".$_SESSION['sSesSourceCode']."'";
	$rEOptionsResult = dbQuery($sGetEOptions);
	$iNumEOptionsRows = dbNumRows($rEOptionsResult);
	// If an entry exist in the admin system for this source code, then get days, redirectUrl, and pixel.
	if ($iNumEOptionsRows > 0) {
		while ($oOptionsRow = dbFetchObject($rEOptionsResult)) {
			$iDaysInTable = $oOptionsRow->days;
			$sRedirectUrl = $oOptionsRow->redirectUrl;
			if ($oOptionsRow->pixel != '') {
				$sPixelUrl = $oOptionsRow->pixel;
			} else {
				$sPixelUrl = '';
			}
		}
	}
	
	// If the cookie was previously set and there is an entry in the admin system for this source code.
	if (isset($_COOKIE['eOptions']) && $iNumEOptionsRows > 0) {
		//check when was the cookie set and determine the number of days since it was set.
		$iDaysInCookie = (strtotime(date("Y-m-d")) - strtotime($_COOKIE["eOptions"])) / (86400);
	
		// check if user already exists in the system.
		$sCheckJoinEmailSub = "SELECT * FROM joinEmailSub WHERE email='$sEmail'";
		$rCheckJoinEmailSub = dbQuery($sCheckJoinEmailSub);
		$iNumJoinEmailSubRows = dbNumRows($rCheckJoinEmailSub);
		// If cookie is older than the number of days defined AND
		// the email address is not in our system at all AND
		// there was a pixel defined in the admin system, then
		if (($iDaysInCookie > $iDaysInTable) && $iNumJoinEmailSubRows == 0 && $sPixelUrl !='') {
			// we fire the pixel that was defined on the next page.
			$_SESSION['sSesEpagePixelUrl'] = $sPixelUrl;
		}
		
		// If cookie is older than days defined OR
		// the email address was found in our system
		// AND redirect URL was specified in the admin.
		if ((($iDaysInCookie > $iDaysInTable) || $iNumJoinEmailSubRows > 0) && $sRedirectUrl != '') {
			// we redirect the user to that url
			$sRedirectTo = $sRedirectUrl;
		}	// else we send the user to the default url.
	}

	// If cookie is not set, we set a cookie containing the date.
	if (!(isset($_COOKIE['eOptions']))) {
		$sContent = date(Y).'-'.date(m).'-'.date(d);
		setcookie("eOptions", "$sContent", time()+157680000, "/", "", 0);
	}
	
	$_SESSION['sSesEmail'] = $sEmail;
	$sPasswd = substr(md5(uniqid(rand(), true)),0,5);
	if( ! $bGlobalJoinInsertDisable ) {
		$sCheckQuery = "SELECT *
					FROM   joinEmailPending
					WHERE  email = \"$sEmail\"
					AND	   joinListId = '$iJoinListId'";
		$rCheckResult = dbQuery($sCheckQuery);
		echo dbError();
		$sCheckQuery2 = "SELECT *
					FROM   joinEmailActive
					WHERE  email = \"$sEmail\"
					AND	   joinListId = '$iJoinListId'";
		$rCheckResult2 = dbQuery($sCheckQuery2);

		echo dbError();
		if ( dbNumRows($rCheckResult) == 0 && dbNumRows($rCheckResult2) == 0) {
			$sPendingInsertQuery = "INSERT INTO joinEmailPending(email, joinListId, sourceCode, dateTimeAdded, passwd, sourceE1Page)
								VALUES(\"$sEmail\", \"$iJoinListId\", \"".$_SESSION['sSesSourceCode']."\", now(), \"$sPasswd\", '".$_SESSION['iSesPageId']."')";
			$rPendingInsertResult = dbQuery($sPendingInsertQuery);
			echo dbError();

			if ($rPendingInsertResult) {
				$sUpdateQuery =  "UPDATE joinEmailPending
							   SET	  passwd = '$sPasswd'
							   WHERE  email = \"$sEmail\"";
				$rUpdateResult = dbQuery($sUpdateQuery);
				echo dbError();
				// delete from inactive
				$sDelInactiveQuery = "DELETE FROM joinEmailInactive
			 					   WHERE  email = \"$sEmail\"
			 					   AND	  joinListId = '$iJoinListId'";
				$rDelInactiveResult = dbQuery($sDelInactiveQuery);
			}

			// make entry in emailSub
			$sSubInsertQuery = "INSERT INTO joinEmailSub(email, joinListId, sourceCode, remoteIp, dateTimeAdded, sourceE1Page)
							VALUES(\"$sEmail\", \"$iJoinListId\", \"".$_SESSION['sSesSourceCode']."\", \"$sRemoteIp\", now(), '".$_SESSION['iSesPageId']."')";
			$rSubInsertResult = dbQuery($sSubInsertQuery);
			echo dbError();

			// increment e1 submit attempts and rejects
			$sCheckQuery = "SELECT *
					FROM   eTrackingSum
					WHERE  pageId = '".$_SESSION['iSesPageId']."'
					AND	   submitDate = CURRENT_DATE
					AND	   sourceCode = '".$_SESSION['sSesSourceCode']."'";
			$rCheckResult = dbQuery($sCheckQuery);
			if ( dbNumRows($rCheckResult) == 0 ) {
				// insert new record
				$sInsertQuery = "INSERT INTO eTrackingSum(pageId, submitDate, sourceCode, attempts, subs)
						 VALUES('".$_SESSION['iSesPageId']."', CURRENT_DATE, '".$_SESSION['sSesSourceCode']."', '1', '1')";
				$rInsertResult = dbQuery($sInsertQuery);
				echo dbError();

			} else {
				// update record
				$sUpdateQuery = "UPDATE eTrackingSum
						 SET    attempts = attempts + 1,
								subs = subs + 1
						 WHERE	pageId = '".$_SESSION['iSesPageId']."'
						 AND	submitDate = CURRENT_DATE
						 AND	sourceCode = '".$_SESSION['sSesSourceCode']."'";
				$rUpdateResult = dbQuery($sUpdateQuery);
				echo dbError();

			}
		}

		// get password now. It can be the old one pending or can be new if user signed up for any new joinList
		$sPasswdQuery = "SELECT *
				FROM   joinEmailPending
				WHERE  email = '$sEmail' LIMIT 0,1";
		$rPasswdResult = dbQuery($sPasswdQuery);
		echo dbError();
		while ($oPasswdRow = dbFetchObject($rPasswdResult)) {
			$sPasswd = $oPasswdRow->passwd;
			$sDateTimeSubscribed = $oPasswdRow->dateTimeAdded;
			// send confirmation email
			$sListEmailQuery = "SELECT *
				    FROM   emailContents
				    WHERE  system = 'join'
				    AND	   emailPurpose = 'requestConfirm' ";
			$rListEmailResult =  dbQuery($sListEmailQuery);
			echo dbError();
			while ($oListEmailRow = dbFetchObject($rListEmailResult)) {
				$sWelcomeEmailContent = $oListEmailRow->emailBody;
				$sWelcomeEmailSubject = $oListEmailRow->emailSub;
				$sWelcomeEmailFromAddr = $oListEmailRow->emailFrom;

				$sWelcomeEmailContent = ereg_replace("\[EMAIL\]", $sEmail, $sWelcomeEmailContent);
				$sWelcomeEmailContent = ereg_replace("\[REMOTE_IP\]", $sRemoteIp, $sWelcomeEmailContent);
				$sWelcomeEmailContent = ereg_replace("\[DATE_TIME_SUB\]", $sDateTimeSubscribed, $sWelcomeEmailContent);
				$sWelcomeEmailContent = ereg_replace("\[MMDDYY\]", date("m").date("d").date("y"), $sWelcomeEmailContent);
				$sWelcomeEmailContent = ereg_replace("\[SOURCE_CODE\]", $_SESSION['sSesSourceCode'], $sWelcomeEmailContent);

				$sWelcomeEmailContent = eregi_replace("\[CONFIRM_URL\]", "$sGblSiteRoot/j/c.php?e=$sEmail&p=$sPasswd&src=".$_SESSION['sSesSourceCode'], $sWelcomeEmailContent);

				$sWelcomeEmailHeaders = "From: $sWelcomeEmailFromAddr\r\n";
				$sWelcomeEmailHeaders .= "X-Mailer: MyFree.com\r\n";
				$sWelcomeEmailHeaders .= "Content-Type: text/plain; charset=iso-8859-1\r\n"; // Mime type
				mail($sEmail, $sWelcomeEmailSubject, $sWelcomeEmailContent, $sWelcomeEmailHeaders);
			}
		}
	}
	header("Location:$sRedirectTo?$sOutboundQueryString&".SID);
}
?>
