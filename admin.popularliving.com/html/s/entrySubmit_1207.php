<?php

include("../includes/paths.php");
include_once("$sGblLibsPath/validationFunctions.php");

session_start();

// generate new password
//$sPasswd = substr(md5(uniqid(rand(), true)),0,5);

// get remote ip address
$sRemoteIp = $_SERVER['REMOTE_ADDR'];

if ($sSave) {
	
	$sEmail = trim($sEmail);
	$sFirst = trim($sFirst);
	$sLast = trim($sLast);
	$sAddress = trim($sAddress);
	$sCity = trim($sCity);
	$sZip = trim($sZip);
	$sPhone = trim($sPhone);
	
	if ($sCity != '') {
		$sCity = ereg_replace("\."," ",$sCity);
		$sCity = ereg_replace("  "," ",$sCity);
	}
	
	
	if ( !(validateEmailFormat($sEmail)) )  {
		$sEmailErrorMessage = "<BR><font color=#FF0000 face='Verdana, Arial, Helvetica'><h3>Please Enter A Valid Email Address.</h3></font>";
		$sError = 'Y';
	}
	if ( !(validateName($sFirst))) {
		$sFirstErrorMessage = "<BR><font color=#FF0000 face='Verdana, Arial, Helvetica'><h3>Please Enter A Valid First Name.</h3></font>";
		$sError = 'Y';
	}
	
	if ( !(validateName($sLast))) {
		$sLastErrorMessage = "<BR><font color=#FF0000 face='Verdana, Arial, Helvetica'><h3>Please Enter A Valid Last Name.</h3></font>";
		$sError = 'Y';
	}
	if ( !(validateAddress($sAddress) ) ) {
		$sAddressErrorMessage = "<BR><font color=#FF0000 face='Verdana, Arial, Helvetica'><h3>Please Enter Valid Address.</h3></font>";
		$sError = 'Y';
	}
	if ($sCity == '') {
		$sCityErrorMessage = "<BR><font color=#FF0000 face='Verdana, Arial, Helvetica'><h3>City Is A Required Field.</h3></font>";
		$sError = 'Y';
	}
	if ( !(validateZip($sZip) )) {
		$sZipErrorMessage = "<BR><font color=#FF0000 face='Verdana, Arial, Helvetica'><h3>Please Enter Valid ZipCode.</h3></font>";
		$sError = 'Y';
	} else if ( !(validateCityStateZip($sCity, $sState, $sZip))) {
		$sZipErrorMessage .= "<BR><font color=#FF0000 face='Verdana, Arial, Helvetica'><h3>Please Enter A Valid Combination Of City, State And ZipCode.</h3></font>";
		$sError = 'Y';
	}
	
	$iPhoneZipDistance = getDistance($sPhone,$sZip);
	
	if ( strlen($sPhone) ==0 ) {
		$sPhoneErrorMessage = "<BR><font color=#FF0000 face='Verdana, Arial, Helvetica'><h3>Please Enter Primary Phone Number.</h3></font>";
		$sError = 'Y';
	} else if (strlen($sPhone) < 12) {
		$sPhoneErrorMessage = "<BR><font color=#FF0000 face='Verdana, Arial, Helvetica'><h3>Please Enter Valid Phone Number.</h3></font>";
		$sError = 'Y';
	} else if ( !(validatePhone(substr($sPhone,0,3), substr($sPhone,4,3), substr($sPhone,8,4), '', $sState))  ) {
		$sPhoneErrorMessage = "<BR><font color=#FF0000 face='Verdana, Arial, Helvetica'><h3>Phone Number You Entered Is Not A Valid Phone Number.</h3></font>";
		$sError = 'Y';
	} else if ( !($iPhoneZipDistance > 0 && $iPhoneZipDistance < 70 )) {
		$sPhoneErrorMessage = "<BR><font color=#FF0000 face='Verdana, Arial, Helvetica'><h3>Phone Number Is Not Valid For The Zipcode You Entered.</h3></font>";
		$sError = 'Y';
	}
	
	if ($iAge == '') {
		$sAgeErrorMessage = "<BR><font color=#FF0000 face='Verdana, Arial, Helvetica'><h3>Please enter your age.</h3></font>";
		$sError = 'Y';
	}
	
	if (isBannedIp($sRemoteIp)) {
		$sIpErrorMessage .= "<BR><font color=#FF0000 face='Verdana, Arial, Helvetica'><h3>We Do Not Accept Registration From $sRemoteIp...<BR>
					  For Additional Information, You Can Contact Us At abuse@AmpereMedia.com</h3></font>";
	} else if (isBannedDomain($sEmail, $sDomainName)) {
		$sEmailErrorMessage .= "<BR><font color=#FF0000 face='Verdana, Arial, Helvetica'><h3>We Do Not Accept Registration From ...$sDomainName<BR>
					  For Additional Information, You Can Contact Us At abuse@AmpereMedia.com</h3></font>";
	}
	
	
	if ($sError != 'Y') {
		$sInsertQuery = "INSERT IGNORE INTO sweepStakesEntries(email, sourceCode, first, last, address, city,
									 state, zip, phoneNo, age, sex, remoteIp, dateTimeAdded )
						 VALUES('$sEmail', '".$_SESSION['sSourceCode']."', '$sFirst', '$sLast', '$sAddress', '$sCity',
								'$sState', '$sZip' ,'$sPhoneNo', '$iAge','$sSex', '$sRemoteIp', now())";
		
		$rInsertResult = dbQuery($sInsertQuery);
		echo dbError();
		if ($rInsertResult) {
			$sListEmailQuery = "SELECT *
				    FROM   emailContents
				    WHERE  system = 'sweeps'
				    AND	   emailPurpose = 'thankYou' ";
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
				$sWelcomeEmailContent = ereg_replace("\[SOURCE_CODE\]", $_SESSION['sSourceCode'], $sWelcomeEmailContent);
				
				
				$sWelcomeEmailHeaders = "From: $sWelcomeEmailFromAddr\r\n";
				$sWelcomeEmailHeaders .= "X-Mailer: MyFree.com\r\n";
				$sWelcomeEmailHeaders .= "Content-Type: text/plain; charset=iso-8859-1\r\n"; // Mime type
				
				mail($sEmail, $sWelcomeEmailSubject, $sWelcomeEmailContent, $sWelcomeEmailHeaders);
				
			}
		}
		
		// if signed up for Best Deals Alert
		if ($iJoinListId) {
			
			// Insert email in joinEmailSub if not exists with the same listId
			$sSubInsertQuery = "INSERT INTO joinEmailSub(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
							VALUES('$sEmail', '$iJoinListId', '$sSourceCode', '$sRemoteIp', NOW() )";
			$rSubInsertResult = dbQuery($sSubInsertQuery);
			
			// Insert email in joinEmailConf if not exists with the same listId
			$sConfInsertQuery = "INSERT INTO joinEmailConfirm(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
							 VALUES('$sEmail', '$iJoinListId', '$sSourceCode', '$sRemoteIp', NOW() )";
			$rConfInsertResult = dbQuery($sConfInsertQuery);
			
			// Insert email in joinEmailActive if not exists with the same listId
			$sActiveInsertQuery = "INSERT IGNORE INTO joinEmailActive(email, joinListId, sourceCode, dateTimeAdded)
							   VALUES('$sEmail', '$iJoinListId', '$sSourceCode', NOW() )";
			$rActiveInsertResult = dbQuery($sActiveInsertQuery);
			
			// delete from joinEmailInactive
			$sInactiveDeleteQuery = "DELETE FROM joinEmailInactive
								 WHERE  email = '$sEmail'
								 AND    joinListId = '$iJoinListId'";	
			$rInactiveDeleteResult = dbQuery($sInactiveDeleteQuery);
			
			// delete from pending
			$sPendingDeleteQuery = "DELETE FROM joinEmailPending
								WHERE  email = '$sEmail'
								AND    joinListId = '$iJoinListId'";	
			$rPendingDeleteResult = dbQuery($sPendingDeleteQuery);
			
			
			
			//$bSendWelcomeEmail = false;
			$sCheckQuery ="SELECT *, date_format(dateTimeAdded,'%m-%d-%Y %H:%i') as dateTimeSubscribed
					   FROM   joinEmailConfirm
					   WHERE  email = '$sEmail'
					   AND    joinListId = '$iJoinListId'";
			//AND	  receivedWelcomeEmail = ''";
			
			$rCheckResult = dbQuery($sCheckQuery);
			while ($oCheckRow = dbFetchObject($rCheckResult)) {
				//	$iReceivedWelcomeEmail = $oCheckRow->receivedWelcomeEmail;
				$sDateTimeSubscribed = $oCheckRow->dateTimeSubscribed;
				//if (!($iReceivedWelcomeEmail)) {
				//$bTempSendWelcomeEmail = true;
				//}
			}
			
			
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
					$sWelcomeEmailFromAddr = $oListEmailRow->emailFrom;
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
		
		
		
		if (strtolower($confirm) == 'yes') {
			
			$sSelectQuery = "SELECT *, date_format(dateTimeAdded,'%m-%d-%Y %H:%i') as dateTimeSubscribed
				 FROM   joinEmailPending
				 WHERE  email = '$sEmail'" ;
			$rSelectResult = dbQuery($sSelectQuery);
			while ($oSelectRow = dbFetchObject($rSelectResult)) {
				
				$iJoinListId = $oSelectRow->joinListId;
				$sDateTimeSubscribed = $oSelectRow->dateTimeSubscribed;
		
				
				$sConfirmInsertQuery = "INSERT INTO joinEmailConfirm(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
							VALUES(\"$sEmail\", '$iJoinListId', \"".$_SESSION['sSesSourceCode']."\",'$sRemoteIp'  , now())";
				$rConfirmInsertResult = dbQuery($sConfirmInsertQuery);
				
				$sActiveInsertQuery = "INSERT IGNORE INTO joinEmailActive(email, joinListId, sourceCode, dateTimeAdded)
							VALUES(\"$sEmail\", '$iJoinListId', \"".$_SESSION['sSesSourceCode']."\", now())";
				$rActiveInsertResult = dbQuery($sActiveInsertQuery);
				
				// subscribe to list "total" if not already exists
				if ($rActiveInsertResult && !( stristr($sEmail,'aol.com') || stristr($sEmail,'aol.net') || stristr($sEmail,'rr.com')|| stristr($sEmail,'rr.net'))) {
					$sTotalInsertQuery1 = "INSERT IGNORE INTO joinEmailActive(email, joinListId, dateTimeAdded)
							  VALUES(\"$sEmail\", '901', now())"; 
					$rTotalInsertResult1 = dbQuery($sTotalInsertQuery1);
					
					$sTotalInsertQuery2 = "INSERT IGNORE INTO joinEmailConfirm(email, joinListId, dateTimeAdded)
							   VALUES(\"$sEmail\", '901', now())"; 
					$rTotalInsertResult2 = dbQuery($sTotalInsertQuery12);
					
					// delete from inactive if exists
					$sTotalDeleteQuery = "DELETE FROM joinEmailInactive
							  WHERE  email = '$sEmail'
							  AND    joinListId = '901'";
					$rTotalDeleteResult = dbQuery($sTotalDeleteQuery);
					
					
				}
				
				// if inserted in confirm and active successfully, delete from pending
				if ($rConfirmInsertResult && $rActiveInsertResult) {
					
					$sDeleteQuery = "DELETE FROM   joinEmailPending
				 					 WHERE  email = '$sEmail'";
					$rDeleteResult = dbQuery($sDeleteQuery);
					echo dbError();
				}
				
				// add into lyris
				
			}
			
			
			// get password now. It can be the old one pending or can be new if user signed up for any new joinList
			
			
			// send confirmation email
			
			$sListEmailQuery = "SELECT *
				    FROM   emailContents
				    WHERE  system = 'join'
				    AND	   emailPurpose = 'welcome' ";
			$rListEmailResult =  dbQuery($sListEmailQuery);
			echo dbError();
			while ($oListEmailRow = dbFetchObject($rListEmailResult)) {
				$sConfirmEmailContent = $oListEmailRow->emailBody;
				$sConfirmEmailSubject = $oListEmailRow->emailSub;
				$sConfirmEmailFromAddr = $oListEmailRow->emailFrom;
				
				$sConfirmEmailContent = ereg_replace("\[EMAIL\]", $sEmail, $sConfirmEmailContent);
				$sConfirmEmailContent = ereg_replace("\[REMOTE_IP\]", $sRemoteIp, $sConfirmEmailContent);
				$sConfirmEmailContent = ereg_replace("\[DATE_TIME_SUB\]", $sDateTimeSubscribed, $sConfirmEmailContent);
				$sConfirmEmailContent = ereg_replace("\[MMDDYY\]", date("m").date("d").date("y"), $sConfirmEmailContent);
				$sConfirmEmailContent = ereg_replace("\[SOURCE_CODE\]", $_SESSION['sSesSourceCode'], $sConfirmEmailContent);
				
				
				$sConfirmEmailHeaders = "From: $sConfirmEmailFromAddr\r\n";
				$sConfirmEmailHeaders .= "X-Mailer: MyFree.com\r\n";
				$sConfirmEmailHeaders .= "Content-Type: text/plain; charset=iso-8859-1\r\n"; // Mime type
				
				mail($sEmail, $sConfirmEmailSubject, $sConfirmEmailContent, $sConfirmEmailHeaders);
				
			}
			
		}
		
		
		header("Location:$sGblOtPagesUrl/onetime.php?header=sweeps");
	} else {
		$sMainErrorMessage = "We could not process your entry because of missing or invalid answers. Please review the request form and answer the missing questions:";
		$sMainErrorMessage .= $sIpErrorMessage;
	}
}

$sStateQuery = "SELECT *
				FROM   states
				ORDER BY state";
$rStateResult = dbQuery($sStateQuery);
echo dbError();
while ($oStateRow = dbFetchObject($rStateResult)) {
	if ($oStateRow->stateId == $sState) {
		$sSelected = "selected";
	} else {
		$sSelected = '';
	}
	$sStateOptions .= "<option value='$oStateRow->stateId' $sSelected>$oStateRow->state";
	
}

$sFemaleSelected = '';
$sMaleSelected = '';
if ($sSex =='F') {
	$sFemaleSelected = "selected";
}
if ($sSex =='M') {
	$sMaleSelected = "selected";
}


?>

<html>

<head>
<title>MyFree.com Sweepstakes</title>

</head>

<body bgcolor="#FFFFFF" text="#000000" link="#0000FF" vlink="#330033" alink="#006600">


<div align="center">
<img src="images/prizecrew.gif" >



<!--
We just need a few more pieces of information from you to&nbsp;<br>
complete your entry into the Prize-A-Month Giveaway:&nbsp; 
-->

</div>
<table align=center width=550><tr><td>
<font color="#FF0000" face="Verdana, Arial, Helvetica"><h3 align="center">
<?php echo $sMainErrorMessage;?>
</h3></font>
</td></tr>
</table>

<form action="<?php echo $PHP_SELF;?>" name="form1" method="post">

<table border="0" cellpadding="0" cellspacing="0" align="center" width="450" height="148">

<tr><td height="25" bgcolor="FFE8C8" valign=top>
<font face="Verdana, Arial, Helvetica">Email:</font>
</td>
      
<td align="left" height="25" bgcolor="FFE8C8">
<input type="text" name="sEmail" size="25" value='<?php echo $sEmail;?>'>

<?php echo $sEmailErrorMessage;?>
</td></tr>

<tr><td height="25" bgcolor="F0FFF0" valign=top><font face="Verdana, Arial, Helvetica">First Name:</font></td>
      <td align="left" height="25" bgcolor="F0FFF0"><input type="text" name="sFirst" size="25" value='<?php echo $sFirst;?>'>
      		<?php echo $sFirstErrorMessage;?></td>
    </tr>
    <tr>
      <td height="25" bgcolor="FFE8C8" valign=top><!--mstheme--><font face="Verdana, Arial, Helvetica">Last Name:</font></td>
      <td align="left" height="25" bgcolor="FFE8C8"><input type="text" name="sLast" size="25" value='<?php echo $sLast;?>'>
      <?php echo $sLastErrorMessage;?></td>
    </tr>
    <tr>
      <td height="25" bgcolor="F0FFF0" valign=top><font face="Verdana, Arial, Helvetica">Address:</font></td>
      <td align="left" height="25" bgcolor="F0FFF0"><input type="text" name="sAddress" size="25" value='<?php echo $sAddress;?>'>
      <?php echo $sAddressErrorMessage;?></td>
    </tr>
    <tr>
      <td height="25" bgcolor="FFE8C8" valign=top><font face="Verdana, Arial, Helvetica">City:</font></td>
      <td align="left" height="25" bgcolor="FFE8C8"><input type="text" name="sCity" size="25" value='<?php echo $sCity;?>'>
      <?php echo $sCityErrorMessage;?></td>
    </tr>
    <tr>
      <td height="23" bgcolor="F0FFF0" valign=top><!--mstheme--><font face="Verdana, Arial, Helvetica">State:</font></td>
      <td align="left" height="23" bgcolor="F0FFF0"><select name=sState>
      <?php echo $sStateOptions;?>
          </select></td>
    </tr>
    <tr>
      <td height="25" bgcolor="FFE8C8" valign=top><font face="Verdana, Arial, Helvetica">Zip Code:<br>
      <?php echo $sZipHelpMessage;?>
       </td>
    
      <td align="left" height="25" bgcolor="FFE8C8" valign=top><input type="text" name="sZip" size="10" value='<?php echo $sZip;?>'>
      <font size=-2>5 digits only</font>
      <?php echo $sZipErrorMessage;?>
	</td>
    </tr>        
    <tr>
      <td height="25" bgcolor="F0FFF0" valign=top>
<font face="Verdana, Arial, Helvetica">Phone Number:</font></td>
      <td align="left" height="25" bgcolor="F0FFF0"><input type="text" name="sPhone" size="10" value='<?php echo $sPhone;?>'>
	<font size=-2>Format: xxx-xxx-xxxx</font>
	<?php echo $sPhoneErrorMessage;?>
</td></tr>

<tr><td height="25" bgcolor="FFE8C8" valign=top>
<font face="Verdana, Arial,Helvetica">Age:</font>
</td>
      <td align="left" height="25" bgcolor="FFE8C8"><font face="Verdana, Arial, Helvetica" size="1">
        <p align="left"><input type="text" name="iAge" size="4" value='<?php echo $iAge;?>'>&nbsp;Must be at least 18 years old.
        <?php echo $sAgeErrorMessage;?>
</font>
</td></tr>
    

<tr>
      <td height="25" bgcolor="F0FFF0"><font face="Verdana, Arial, Helvetica">Sex:</font></td>


      <td align="left" height="25" bgcolor="F0FFF0">

<font face="Verdana,Arial, Helvetica">
        <select size="1" name="sSex">
          <option value="F" <?php echo $sFemaleSelected;?>>Female</option>
          <option value="M" <?php echo $sMaleSelected;?>>Male</option>
        </select></font>
</td></tr>
<tr>
      <td valign="top"  height="25" bgcolor="FFE8C8" colspan=2><font face="Verdana, Arial, Helvetica" size="1">
	<br>Join the "MyFree Best Deals Alert". FREE Membership, FREE Newsletter, FREE PRIZE-A-MONTH GIVEAWAY Entry! Don't miss out on all the special deals, new products and other third-party offers we know you'll love! Free Bonuses: $125+ Special Instant Shopping Spree Discount Package + Special Report "Free Samples from National Brands You Trust!" Recent featured free samples include Advil, Nesquik, Tide - and more! 
	<p><input CHECKED name="iJoinListId" type="radio" value="215"> Yes!
        I want to receive this information.<br>
        <input name="iJoinListId" type="radio" value="215"> No, please
        exclude me.	</font></td>
    </tr>
</table>

<font face="Verdana, Arial, Helvetica">
  
<p align="center"><input type="submit" value="Click Here To Complete Your Entry!" name="sSave"></p>
    <p align="center">Note: You must be a U.S. resident to enter<br>
      online.  See rules for alternate method of entry.
</p>

<br><br><br><br>

<p align="center"><font size="1" face="arial"><a href="http://www.popularliving.com/sweepsrules.php" target="_blank">Official Rules</a> - <a href="../privacy.php" target="_blank">Privacy Policy</a></font></p>
</font></font></center>
</form>
<font face="Verdana, Arial, Helvetica">
</font></body>
</html>
