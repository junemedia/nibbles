<?php


include("../includes/paths.php");

session_start();

if ($sEmail !='') {
	$_SESSION['sSesEmail'] = $sEmail;
}

if ($_SESSION['sSesSourceCode'] == '' && $src !='') {
	$_SESSION['sSesSourceCode'] = $sSourceCode;
}
$sPasswd = substr(md5(uniqid(rand(), true)),0,5);

$sRemoteIp = $_SERVER['REMOTE_ADDR'];

$iJoinListId = '';

if ( count($aJoinListId) > 0 ) {
	
// make entry in pending

for ($i = 0; $i < count($aJoinListId); $i++) {
	
	$iJoinListId = $aJoinListId[$i];
	
	$sCheckQuery = "SELECT *
					FROM   joinEmailPending
					WHERE  email = '$sEmail'
					AND	   joinListId = '$iJoinListId'";
	$rCheckResult = dbQuery($sCheckQuery);
	
	$sCheckQuery2 = "SELECT *
					FROM   joinEmailActive
					WHERE  email = '$sEmail'
					AND	   joinListId = '$iJoinListId'";
	$rCheckResult2 = dbQuery($sCheckQuery2);
	
	echo dbError();
	if ( dbNumRows($rCheckResult) == 0 && dbNumRows($rCheckResult2) == 0) {
		
		$sListQuery = "SELECT *
					   FROM	  joinLists
					   WHERE  id = '$iJoinListId'";
		$rListResult = dbQuery($sListQuery) ;
		while ($oListRow = dbFetchObject($rListResult)) {
			$iRequiresConf = $oListRow->requiresConf;
			if ($iRequiresConf == '1') {
				$sPendingInsertQuery = "INSERT INTO joinEmailPending(email, joinListId, sourceCode, dateTimeAdded, passwd)
								VALUES(\"$sEmail\", \"$iJoinListId\", \"".$_SESSION['sSesSourceCode']."\", now(), \"$sPasswd\")";
				$rPendingInsertResult = dbQuery($sPendingInsertQuery);
				echo dbError();
				
				if ($rPendingInsertResult) {
					
					$sUpdateQuery =  "UPDATE joinEmailPending
							   SET	  passwd = '$sPasswd'
							   WHERE  email = '$sEmail'";
					$rUpdateResult = dbQuery($sUpdateQuery);
					echo dbError();
					
					// delete from inactive
					
					$sDelInactiveQuery = "DELETE FROM joinEmailInactive
			 					   WHERE  email = '$sEmail'
			 					   AND	  joinListId = '$iJoinListId'";
					$rDelInactiveResult = dbQuery($sDelInactiveQuery);
				}
			} else {
				$sConfirmInsertQuery = "INSERT INTO joinEmailConfirm(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
									VALUES(\"$sEmail\", '$iJoinListId', \"".$_SESSION['sSesSourceCode']."\",'$sRemoteIp'  , now())";
				$rConfirmInsertResult = dbQuery($sConfirmInsertQuery);
				
				$sActiveInsertQuery = "INSERT IGNORE INTO joinEmailActive(email, joinListId, sourceCode, dateTimeAdded)
							VALUES(\"$sEmail\", '$iJoinListId', \"".$_SESSION['sSesSourceCode']."\", now())";
				$rActiveInsertResult = dbQuery($sActiveInsertQuery);
				
				// if inserted in confirm and active successfully, delete from pending
				if ($rConfirmInsertResult && $rActiveInsertResult) {
					
					$sDeleteQuery = "DELETE FROM   joinEmailPending
				 				 WHERE  email = '$sEmail'";
					$rDeleteResult = dbQuery($sDeleteQuery);
					echo dbError();
					
				}
				$sDelInactiveQuery = "DELETE FROM joinEmailInactive
			 					   WHERE  email = '$sEmail'
			 					   AND	  joinListId = '$iJoinListId'";
				$rDelInactiveResult = dbQuery($sDelInactiveQuery);
				
				// send welcome letter
				$sCheckQuery ="SELECT *
					   FROM   joinEmailConfirm
					   WHERE  email = '$sEmail'
					   AND    joinListId = '$iJoinListId'
					   AND	  receivedWelcomeEmail = ''";
				
				$rCheckResult = dbQuery($sCheckQuery);
				if (dbNumRows($rCheckResult) > 0) {
					// send welcome email for this joinList if not sent already
					$sListEmailQuery = "SELECT *
							FROM   joinListEmailContents
							WHERE  joinListId = '$iJoinListId'
							AND	   emailPurpose = 'welcome'";
					$rListEmailResult = dbQuery($sListEmailQuery);
					while ($oListEmailRow = dbFetchObject($rListEmailResult)) {
						
						$sWelcomeEmailContent = $oListEmailRow->emailBody;
						$sWelcomeEmailSubject = $oListEmailRow->emailSub;
						$sWelcomeEmailFromAddr = $oListEmailRow->emailFromAddr;
						$sWelcomeEmailFormat = $oListEmailRow->emailFormat;
						
						$sWelcomeEmailContent = ereg_replace("\[EMAIL\]", $sEmail, $sWelcomeEmailContent);
						$sWelcomeEmailContent = ereg_replace("\[REMOTE_IP\]", $sRemoteIp, $sWelcomeEmailContent);
						$sWelcomeEmailContent = ereg_replace("\[DATE_TIME_SUB\]", $sDateTimeSubscribed, $sWelcomeEmailContent);
						$sWelcomeEmailContent = ereg_replace("\[MMDDYY\]", date("m").date("d").date("y"), $sWelcomeEmailContent);
						$sWelcomeEmailContent = ereg_replace("\[SOURCE_CODE\]", $sSourceCode, $sWelcomeEmailContent);
						
						
						
						$sWelcomeEmailHeaders = "From: $sWelcomeEmailFromAddr\r\n";
						$sWelcomeEmailHeaders .= "X-Mailer: MyFree.com\r\n";
						if ($sWelcomeEmailFormat == 'html') {
							$sWelcomeEmailContent = nl2br($sWelcomeEmailContent);
							$sWelcomeEmailHeaders .= "Content-Type: text/html; charset=iso-8859-1\r\n"; // Mime type
						} else {
							$sWelcomeEmailHeaders .= "Content-Type: text/plain; charset=iso-8859-1\r\n"; // Mime type
						}
						//if (substr($sEmail,0,5) == 'smita')  {
						
						mail($sEmail, $sWelcomeEmailSubject, $sWelcomeEmailContent, $sWelcomeEmailHeaders);
						
						$sUpdateQuery = "UPDATE joinEmailConfirm
							 SET    receivedWelcomeEmail = '1'
							 WHERE  email = '$sEmail'
							 AND    joinListId = '$iJoinListId'";
						$rUpdateResult = dbQuery($sUpdateQuery);
						echo dbError();
						//}
					}
				}
				
			}
			
			// make entry in emailSub
			
			$sSubInsertQuery = "INSERT INTO joinEmailSub(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
							VALUES(\"$sEmail\", \"$iJoinListId\", \"".$_SESSION['sSesSourceCode']."\", \"$sRemoteIp\", now())";
			
			$rSubInsertResult = dbQuery($sSubInsertQuery);
			
			echo dbError();
		}
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

} // end if sign up



?>

<html>

<script src="http://www.myfree.com/codelib/js-funcs.js"></script> 
<script LANGUAGE="javascript">

<!--
//POPUP ONETIME
var pop_url ="<?php echo $sGblSiteRoot;?>/p/onetime.php?src=myep";
window.open(pop_url,"Onetime","scrollbars=yes,resizeable=yes,height=600,width=800,left=0,top=0,screenX=0,screenY=0)");

-->

</script>

<head>

<title>Thank You!</title>


<meta name="Microsoft Theme" content="fiesta 000, default">
<meta name="Microsoft Border" content="none">
</head>



<body bgcolor="#FFFFFF" text="#000000" link="#0000FF" vlink="#330033" alink="#006600"><font face="Verdana, Arial, Helvetica">

<div align="center"><center>



</font><table border="0" width="375" bgcolor="#FFFF80" height="380" marginheight="5"

marginwidth="5" cellpadding="5">

  <tr>

    <td align="center" valign="top"><font face="Verdana, Arial, Helvetica"> <p align="center"><img border="0"

    src="images/popThanks.gif" WIDTH="364" HEIGHT="67"></p>

    <p align="center"><font face="Verdana"><font size="3">You will receive an e-mail asking

    you to confirm that you wish to receive this free newsletter.&nbsp; You MUST confirm your

    subscription by simply hitting &quot;reply&quot; when the e-mail is received.&nbsp; Enjoy

    your stay at our site.&nbsp; Also, this is one of our readers' and staff members' favorite

    offers:</font>&nbsp;</font></p>

    </center> <p align="center"><font face="Verdana" size="2"><b>Get Your Free Sample Of Jelly Belly Candy - Supplies are limited. Get your sample today with registration.<br>

    </b><br>

    <a href="http://bd.myfree.com/r/r.php?src=amptb100404045" target="_blank"><b>Click Here!</b></a></font> </p>

    </font></td>

  </tr>

</table><font face="Verdana, Arial, Helvetica">

</div>

<p>&nbsp;</p>
</font></body>

</html>

