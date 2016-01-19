<?php


echo "Page Disabled - Please notify IT if it is needed.";
exit();

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Prospecting";

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {



	$sTestMessageEmail = "jr@amperemedia.com";

	$aBannedEmailStart = array("abuse", "postmaster", "webmaster", "spam");

	$sProspectsTable = "prospectingProspects";
	$sMessagesTable = "prospectingMessages";
	$sMessageLogTable = "prospectingMessageLog";
	$sBlacklistTable = "prospectingBlacklist";

	//
	$sDbQuery = "CREATE DATABASE IF NOT EXISTS $dbase";
	$rDbResult = mysql_query($sDbQuery);

	mysql_select_db ($dbase);

	// create prospectingProspects table if not exists
	$sCheckQuery = "SHOW TABLES LIKE '$sProspectsTable'";
	$rCheckResult = mysql_query($sCheckQuery);

	if (mysql_num_rows($rCheckResult) == 0) {
		$sCreateQuery = "CREATE TABLE $sProspectsTable (
	id int(11) NOT NULL auto_increment,
	email varchar(100) NOT NULL default '',
  	PRIMARY KEY  (id)
	)";
		$rCreateResult = mysql_query($sCreateQuery);
	}


	// create prospectingMessages table if not exists
	$sCheckQuery = "SHOW TABLES LIKE '$sMessagesTable'";
	$rCheckResult = mysql_query($sCheckQuery);

	if (mysql_num_rows($rCheckResult) == 0) {
		$sCreateQuery = "CREATE TABLE $sMessagesTable (
	id int(11) NOT NULL auto_increment,
 	 fromAddr varchar(100) NOT NULL default '',
  	subject varchar(255) NOT NULL default '',
  	message text NOT NULL,
  	PRIMARY KEY  (id)
	)";
		$rCreateResult = mysql_query($sCreateQuery);
	}


	// create prospectingMessageLog table if not exists
	$sCheckQuery = "SHOW TABLES LIKE '$sMessageLogTable'";
	$rCheckResult = mysql_query($sCheckQuery);

	if (mysql_num_rows($rCheckResult) == 0) {
		$sCreateQuery = "CREATE TABLE $sMessageLogTable (
	id int(11) NOT NULL auto_increment,
 	messageId int(11) NOT NULL default '0',
  	dateSent date NOT NULL default '0000-00-00',
  	email varchar(100) NOT NULL default '',
  	PRIMARY KEY  (id)
	)";
		$rCreateResult = mysql_query($sCreateQuery);
	}

	// create prospectingBlacklist table if not exists
	$sCheckQuery = "SHOW TABLES LIKE '$sBlacklistTable'";
	$rCheckResult = mysql_query($sCheckQuery);

	if (mysql_num_rows($rCheckResult) == 0) {
		$sCreateQuery = "CREATE TABLE $sBlacklistTable (
	id int(11) NOT NULL,
	email varchar(150),
	PRIMARY KEY  (email)	
	)";
		$rCreateResult = mysql_query($sCreateQuery);
	}


	// If working with ProspectingMessages
	if ($sMessages || $sAddMessage) {


		if ($sAddMessage == "Add Message") {

			if (!($iId)) {
				$sInsertQuery = "INSERT INTO $sMessagesTable(fromAddr, subject, message)
						 VALUES('$sFromAddr', \"$sSubject\", \"$sMessageBody\")";
				$rInsertResult = mysql_query($sInsertQuery);

				if ($rInsertResult) {
					// clear the fields
					$sFromAddr = '';
					$sSubject = '';
					$sMessageBody = '';
				} else {
					$sMessage =  mysql_error();
				}
			} else if ($iId) {
				$sUpdateQuery = "UPDATE $sMessagesTable
						 SET    fromAddr = '$sFromAddr',
								subject = '$sSubject',
								message = \"$sMessageBody\"
						 WHERE  id = '$iId'";
				$rUpdateResult = mysql_query($sUpdateQuery);
				// clear the fields
				$iId= '';
				$sFromAddr = '';
				$sSubject = '';
				$sMessageBody = '';

			}
		} else if ($sDelete == "Delete") {
			$sDeleteQuery = "DELETE FROM $sMessagesTable
						 WHERE  id = '$iId'";
			$rDeleteResult = mysql_query($sDeleteQuery);
		}

		// display messages list
		$sMessagesQuery = "SELECT *
					   FROM   $sMessagesTable
					   ORDER BY subject";
		$rMessagesResult = mysql_query($sMessagesQuery);
		if ( mysql_num_rows($rMessagesResult) >0) {

			$sContentList = "<BR><BR><b>Content List</b><BR><RB><table border=1><tr><td>From Address</td><td>Subject</td><td>Message Body</td><td>&nbsp;</td></tr>";
			while ($oMessagesRow = mysql_fetch_object($rMessagesResult)) {
				if ($oMessagesRow->id == $iId) {
					$sFromAddr = $oMessagesRow->fromAddr;
					$sSubject = $oMessagesRow->subject;
					$sMessageBody = $oMessagesRow->message;
				}

				$sContentList .= "<tr><td>$oMessagesRow->fromAddr</td>
							  <td>$oMessagesRow->subject</td>
							  <td>$oMessagesRow->message</td>
							  <td><a href='$PHP_SELF?sMessages=Messages&iId=$oMessagesRow->id'>Edit</a>
								&nbsp; <a href='$PHP_SELF?sMessages=Messages&sDelete=Delete&iId=$oMessagesRow->id'>Delete</a>
								&nbsp; <a href='$PHP_SELF?sMessages=Messages&sTestMessage=test&iId=$oMessagesRow->id'>Send Test Message</a></td></tr>";
			}
			$sContentList .= "</table>";
		} else {
			$sContentList = "No Records Exists...";
		}

		// If send test message link clicked
		if ($sTestMessage) {
			$sTempMessageBody = ereg_replace("\[EMAIL\]", $sTestMessageEmail, $sMessageBody);
			mail($sTestMessageEmail, $sSubject, $sTempMessageBody, "From:$sFromAddr");
			$sMessage = "Test Message Sent to $sTestMessageEmail";
			$iId = '';
			$sFromAddr = '';
			$sSubject = '';
			$sMessageBody = '';

		}

		$sAddForm = "<tr><td>From Address</td><td><input type=text name=sFromAddr value='$sFromAddr'></td></tr>
				<tr><td>Subject</td><td><input type=text name=sSubject value='$sSubject' size=60></td></tr>
				<tr><td>Message Body</td><td><textarea name=sMessageBody rows=5 cols=60>$sMessageBody</textarea>
						<BR>[EMAIL] will be replaced with Prospects e-mail address.<br><BR></td></tr>
				<tr><td></td><td><input type=submit name=sAddMessage value='Add Message'>
						<input type=hidden name=iId value='$iId'>
					</td></tr>";

	} else if($sBlacklists || $sAddBlacklist) {

		// If working with Blacklist Management

		if ($sAddBlacklist == "Add To Blacklist") {

			if (!($iId)) {
				$sCheckQuery = "SELECT *
							FROM   $sBlacklistTable
							WHERE  email = '$sEmail'";
				$rCheckResult = mysql_query($sCheckQuery);
				if (mysql_num_rows($rCheckResult) >0 ) {
					$sMessage =  "Entry Exists In Blacklist Table...";
				} else {
					$sCheckQuery2 = "SELECT *
				 				  FROM   $sProspectsTable
				 				  WHERE  email = '$sEmail'";
					$rCheckResult2 = mysql_query($sCheckQuery2);
					if (mysql_num_rows($rCheckResult2) >0) {
						$sMessage = "Entry Exists In Prospects Table...";
					} else {
						$sInsertQuery = "INSERT INTO $sBlacklistTable(email)
						 VALUES('$sEmail')";
						$rInsertResult = mysql_query($sInsertQuery);

						if ($rInsertResult) {
							// clear the fields
							$sEmail = '';
						} else {
							$sMessage =  mysql_error();
						}
					}
				}
			} else if ($iId) {
				$sCheckQuery = "SELECT *
							FROM   $sProspectsTable
							WHERE  email = '$sEmail'";
				$rCheckResult = mysql_query($sCheckQuery);

				if (mysql_num_rows($rCheckResult) >0 ) {
					$sMessage =  "Entry Exists In Prospects Table...";
				} else {
					$sCheckQuery2 = "SELECT *
				 				  FROM   $sBlacklistTable
				 				  WHERE  email = '$sEmail'";
					$rCheckResult2 = mysql_query($sCheckQuery2);
					if (mysql_num_rows($rCheckResult2) >0) {
						$sMessage =  "Entry Exists In Blacklist Table...";
					} else {
						$sUpdateQuery = "UPDATE $sBlacklistTable
						 	 SET    email = '$sEmail'
						 	 WHERE  id = '$iId'";
						$rUpdateResult = mysql_query($sUpdateQuery);
						// clear the fields
						$iId= '';
						$sEmail = '';
					}
				}
			}
		} else if ($sDelete == "Delete") {
			$sDeleteQuery = "DELETE FROM $sBlacklistTable
						 WHERE  id = '$iId'";
			$rDeleteResult = mysql_query($sDeleteQuery);
		}

		// display messages list
		$sBlacklistQuery = "SELECT *
					   FROM   $sBlacklistTable
					   ORDER BY email";
		$rBlacklistResult = mysql_query($sBlacklistQuery);
		if ( mysql_num_rows($rBlacklistResult) >0) {

			$sContentList = "<BR><BR><b>Content List</b><BR><RB><table border=1><tr><td>Email</td><td>&nbsp;</td></tr>";
			while ($oBlacklistRow = mysql_fetch_object($rBlacklistResult)) {
				if ($oBlacklistRow->id == $iId) {
					$sEmail = $oBlacklistRow->email;
				}

				$sContentList .= "<tr><td>$oBlacklistRow->email</td>
							  <td> <a href='$PHP_SELF?sBlacklists=Blacklists&sDelete=Delete&iId=$oBlacklistRow->id'>Delete</a></td></tr>";
			}
			$sContentList .= "</table>";
		} else {
			$sContentList = "No Records Exists...";
		}

		$sAddForm = "<tr><td>Email</td><td><input type=text name=sEmail value='$sEmail' size=50></td></tr>
				 <tr><td></td><td><input type=submit name=sAddBlacklist value='Add To Blacklist'>
					<input type=hidden name=iId value='$iId'>
				    </td></tr>";

	} else if($sProspects || $sAddProspect) {
		// If working with Prospects Management

		if ($sAddProspect == "Add Prospect") {
			$bValid = true;
			// check if prospect does not start with banned emails...
			for ($i=0; $i<count($aBannedEmailStart);$i++) {
				if (substr(strtolower($sEmail),0,strlen($aBannedEmailStart[$i])) == strtolower($aBannedEmailStart[$i])) {
					$sMessage = "Prospect Is Not Valid...";
					$bValid = false;
				}
			}
			// check prospect format
			if ( !ereg(  "^[A-Za-z0-9\$._-]+[@]{1,1}[A-Za-z0-9-]+[.]{1}[A-Za-z0-9.-]+[A-Za-z]$", $sEmail) ) {
				$bValid = false;
			}

			if ($bValid == true) {
				if (!($iId)) {
					$sCheckQuery = "SELECT *
							FROM   $sProspectsTable
							WHERE  email = '$sEmail'";
					$rCheckResult = mysql_query($sCheckQuery);

					if (mysql_num_rows($rCheckResult) >0 ) {
						$sMessage =  "Entry Exists In Prospects Table...";
					} else {
						$sCheckQuery2 = "SELECT *
				 				  FROM   $sBlacklistTable
				 				  WHERE  email = '$sEmail'";
						$rCheckResult2 = mysql_query($sCheckQuery2);

						echo mysql_error();
						if (mysql_num_rows($rCheckResult2) >0) {
							$sMessage = "Entry Exists In Blacklist Table...";
						} else {
							$sInsertQuery = "INSERT INTO $sProspectsTable(email)
									 VALUES('$sEmail')";
							$rInsertResult = mysql_query($sInsertQuery);

							if ($rInsertResult) {
								// clear the fields
								$sEmail = '';
							} else {
								$sMessage =  mysql_error();
							}
						}
					}
				} else if ($iId) {
					$sCheckQuery = "SELECT *
							FROM   $sProspectsTable
							WHERE  email = '$sEmail'";
					$rCheckResult = mysql_query($sCheckQuery);

					if (mysql_num_rows($rCheckResult) >0 ) {
						$sMessage =  "Entry Exists In Prospects Table...";
					} else {
						$sCheckQuery2 = "SELECT *
				 				  FROM   $sBlacklistTable
				 				  WHERE  email = '$sEmail'";
						$rCheckResult2 = mysql_query($sCheckQuery2);
						if (mysql_num_rows($rCheckResult2) >0) {
							$sMessage =  "Entry Exists In Blacklist Table...";
						} else {
							$sUpdateQuery = "UPDATE $sProspectsTable
						 	 SET    email = '$sEmail'
						 	 WHERE  id = '$iId'";
							$rUpdateResult = mysql_query($sUpdateQuery);
							// clear the fields
							$iId= '';
							$sEmail = '';
						}
					}
				}
			}
		} else if ($sDelete == "Delete") {
			$sDeleteQuery = "DELETE FROM $sProspectsTable
						 WHERE  id = '$iId'";
			$rDeleteResult = mysql_query($sDeleteQuery);
		}

		// display messages list
		$sProspectsQuery = "SELECT *
					   FROM   $sProspectsTable
					   ORDER BY email";
		$rProspectsResult = mysql_query($sProspectsQuery);
		$iCount = mysql_num_rows($rProspectsResult);

		if ( $iCount >0) {

			$sContentList = "<BR><BR><b>Prospects List</b><BR>Total $iCount Records<BR><RB><table border=1><table border=1><tr><td>Email</td><td>&nbsp;</td></tr>";
			while ($oProspectsRow = mysql_fetch_object($rProspectsResult)) {
				if ($oProspectsRow->id == $iId) {
					$sEmail = $oProspectsRow->email;
				}

				$sContentList .= "<tr><td>$oProspectsRow->email</td>
							  <td>
			<a href='$PHP_SELF?sProspects=Prospects&sDelete=Delete&iId=$oProspectsRow->id'>Delete</a></td></tr>";
			}
			$sContentList .= "</table>";
		} else {
			$sContentList = "No Records Exists...";
		}

		$sAddForm = "<tr><td>Email</td><td><input type=text name=sEmail value='$sEmail' size=50></td></tr>
				 <tr><td></td><td><input type=submit name=sAddProspect value='Add Prospect'>
					<input type=hidden name=iId value='$iId'>
				    </td></tr>";
	} else if ($sSendMessages) {

		// get messages one by one
		$sMessageQuery = "SELECT *
					  FROM   $sMessagesTable";
		$rMessageResult = mysql_query($sMessageQuery);
		while ($oMessageRow = mysql_fetch_object($rMessageResult)) {
			$iMessageId = $oMessageRow->id;
			$sFromAddr = $oMessageRow->fromAddr;
			$sSubject = $oMessageRow->subject;
			$sMessageBody = $oMessageRow->message;

			$iNoOfProspects = 0;
			// get prospects
			$sProspectsQuery = "SELECT *
							FROM   $sProspectsTable";
			$rProspectsResult = mysql_query($sProspectsQuery);
			while ($oProspectsRow = mysql_fetch_object($rProspectsResult)) {
				$sProspectEmail = $oProspectsRow->email;

				// check if message sent to this email address
				$sCheckQuery = "SELECT *
							FROM   $sMessageLogTable
							WHERE  messageId = '$iMessageId'
							AND    email = '$sProspectEmail'";
				$rCheckResult = mysql_query($sCheckQuery);

				// if message not sent, send it now
				if (  mysql_num_rows($rCheckResult) == 0) {

					$sTempMessageBody = ereg_replace("\[EMAIL\]", $sProspectEmail, $sMessageBody);
					mail($sProspectEmail, $sSubject, $sTempMessageBody, "From:$sFromAddr");

					// update the log table

					$sMessageLogQuery = "INSERT INTO $sMessageLogTable(messageId, dateSent, email)
									 VALUES('$iMessageId', CURRENT_DATE, '$sProspectEmail')";
					$rMessageLogResult = mysql_query($sMessageLogQuery);

					$iNoOfProspects++;
				}
			}
			$sMessage .= "<BR>Message $iMessageId($sSubject) sent to $iNoOfProspects prospects.";
		}
	}

	// If Home page, display messages counts
	if (!($QUERY_STRING)) {
		// get total prospects

		$sProspectsQuery = "SELECT count(*) AS counts
					   FROM   $sProspectsTable";
		$rProspectsResult = mysql_query($sProspectsQuery);
		while ($oProspectsRow = mysql_fetch_object($rProspectsResult)) {
			$iProspectCount = $oProspectsRow->counts;
		}

		$sMessageQuery = "SELECT *
					  FROM   $sMessagesTable";
		$rMessageResult = mysql_query($sMessageQuery);
		if ( mysql_num_rows($rMessageResult) >0 ) {
			$sContentList = "Message Statistics<BR><table border=1>
					<tr><td><b>Message</b></td><td><b>Sent Count</b></td><td><b>Remaining To Send</b></td><td><b>Total</b></td></tr>";
			while ($oMessageRow = mysql_fetch_object($rMessageResult)) {
				$iMessageId = $oMessageRow->id;
				$sContentList .= "<tr><td>Message $iMessageId</td>";
				$sCountQuery = "SELECT count(id) AS sentCount
						FROM   $sMessageLogTable
						WHERE  messageId = '$iMessageId'";
				$rCountResult = mysql_query($sCountQuery);
				while ($oCountRow = mysql_fetch_object($rCountResult)) {
					$iMessageSentCount = $oCountRow->sentCount;
				}
				$iMessageRemainingCount = $iProspectCount - $iMessageSentCount;
				$sContentList .= "<td>$iMessageSentCount</td><td>$iMessageRemainingCount</td><td>$iProspectCount</td>";
				//
				$sContentList .= "</tr>";
			}
			$sContentList .= "</table>";

		}
	}

?>



<html> 
<head> 
<title>Nibbles - Prospecting System</title> 

<LINK REL=StyleSheet HREF="../style.css" TYPE="text/css" MEDIA=screen>  

</head> 

<body> 


<table width="90%" align="center"><tr><td align="center">




<img src = "http://www.popularliving.com/admin/nibbles_header.gif">

<br><br>

<b>Prospecting System</b>

<br><br>

<a href="http://www.popularliving.com/admin/index.php">Return to Nibbles Main Menu</a>

<br><br>



<form action='<?php echo $PHP_SELF;?>'>
<table>
<tr><td><font color=red><?php echo $sMessage;?></font></td></tR>
</table>
<table>
<?php echo $sAddForm;?>
</table>

<?php echo $sContentList;?>

<BR><BR>
<table>
<tr><td>
	<input type=button name=sHome value='Home' onClick='JavaScript:window.location.href="prospecting.php";'>
	&nbsp; &nbsp; <input type=submit name=sMessages value='Messages'>
	&nbsp; &nbsp; <input type=submit name=sBlacklists value='Blacklist'>
	&nbsp; &nbsp; <input type=submit name=sProspects value='Prospects'>
	&nbsp; &nbsp; <input type=submit name=sSendMessages value='Send Messages'>
	</td>
</tr>
</table>

<?php	

} else {
	echo "You are not authoresed to access this page...";
}

?>

<br><br>

<a href="http://www.popularliving.com/admin/index.php">Return to Nibbles Main Menu</a>

</body>
</html>