<?php

include("includes/paths.php");
include_once("$sGblLibsPath/validationFunctions.php");

session_start();

include("$sGblIncludePath/e1PageInclude.php");

$iJoinListId = "162";

//echo $sRefererScriptFileName;
$sRedirectTo = eregi_replace("_e1.php", ".php", $sRefererScriptFileName);

if ( !(validateEmailFormat($sEmail))) {
	$sMessage .= "<li>Please Enter A Valid Email Address...";
	
	if (strstr($sRefererScriptFileName,"?")) {
		header("Location:$sRefererScriptFileName&sMessage=$sMessage&$sOutboundQueryString");
	} else {
		header("Location:$sRefererScriptFileName?sMessage=$sMessage&$sOutboundQueryString");
	}
	
} else {
	$_SESSION['sSesEmail'] = $sEmail;
	
	
	$sPasswd = substr(md5(uniqid(rand(), true)),0,5);
	
	$sRemoteIp = $_SERVER['REMOTE_ADDR'];
	
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
		$sPendingInsertQuery = "INSERT INTO joinEmailPending(email, joinListId, sourceCode, dateTimeAdded, passwd)
								VALUES(\"$sEmail\", \"$iJoinListId\", \"".$_SESSION['sSesSourceCode']."\", now(), \"$sPasswd\")";
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
		
		$sSubInsertQuery = "INSERT INTO joinEmailSub(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
							VALUES(\"$sEmail\", \"$iJoinListId\", \"".$_SESSION['sSesSourceCode']."\", \"$sRemoteIp\", now())";
		
		$rSubInsertResult = dbQuery($sSubInsertQuery);
		
		echo dbError();
		
		
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

	header("Location:$sRedirectTo?$sOutboundQueryString");
	
}
?>