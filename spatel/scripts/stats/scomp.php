<?php

//script to handle AOL complaints
$scompMailServer = '';
$scompUsername = '';
$scompPasswd = '';
$mailMessage = '';
include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

ini_set('max_execution_time', 5000);


//include_once( "/home/scripts/stats/scompInclude.php" );


$scompUsername='scomp';
$scompPasswd='scomp200';
$scompMailServer = 'mail.amperemedia.com';
$unsubscribeNumbersAtATime = 50;

$exceptionEmail = "scompExceptions@amperemedia.com";

$iDebugNumExceptions = 0;




//class.POP3.php3 v1.0	99/03/24 CDI cdi@thewebmasters.net
//Copyright (c) 1999 - CDI (cdi@thewebmasters.net) All Rights Reserved
//An RFC 1939 compliant wrapper class for the POP3 protocol.


class POP3
{
	var $ERROR		= "";		//	Error string.

	var $TIMEOUT	= 180;		//	Default timeout before giving up on a
	//	network operation.

	var $COUNT		= -1;		//	Mailbox msg count

	var $BUFFER		= 512;		//	Socket buffer for socket fgets() calls.
	//	Per RFC 1939 the returned line a POP3
	//	server can send is 512 bytes.

	var $FP			= "";		//	The connection to the server's
	//	file descriptor

	var $MAILSERVER	= "";		// Set this to hard code the server name

	var $DEBUG		= false;	// set to true to echo pop3
	// commands and responses to error_log
	// this WILL log passwords!

	//var $BANNER		= "";		//	Holds the banner returned by the
	//	pop server - used for apop()

	var $RFC1939	= true;		//	Set by noop(). See rfc1939.txt
	//

	//var $ALLOWAPOP	= false;	//	Allow or disallow apop()
	//	This must be set to true
	//	manually.

	function POP3 ( $server = "", $timeout = "" ) {
		settype($this->BUFFER,"integer");
		if(!empty($server))	{
			// Do not allow programs to alter MAILSERVER
			// if it is already specified. They can get around
			// this if they -really- want to, so don't count on it.
			if(empty($this->MAILSERVER)) {
				$this->MAILSERVER = $server;
			}
		}
		if(!empty($timeout)) {
			settype($timeout,"integer");
			$this->TIMEOUT = $timeout;
			set_time_limit($timeout);
		}
		return true;
	}

	function update_timer () {
		set_time_limit($this->TIMEOUT);
		return true;
	}

	function connect ($server, $port = 110) {
		//	Opens a socket to the specified server. Unless overridden,
		//	port defaults to 110. Returns true on success, false on fail

		// If MAILSERVER is set, override $server with it's value

		/*if(!empty($this->MAILSERVER))
		{
		$server = $this->MAILSERVER;
		}
		*/
		if(empty($server)) {
			$this->ERROR = "POP3 connect: No server specified";
			unset($this->FP);
			return false;
		}

		//$fp = fsockopen("$server", $port, &$errno, &$errstr);
		$fp = fsockopen("$server", $port, $errno, $errstr);

		if(!$fp) {
			$this->ERROR = "POP3 connect: Error [$errno] [$errstr]";
			unset($this->FP);
			return false;
		}

		stream_set_blocking($fp,-1);
		$this->update_timer();
		$reply = fgets($fp,$this->BUFFER);
		$reply = $this->strip_clf($reply);
		if($this->DEBUG) { error_log("POP3 SEND [connect: $server] GOT [$reply]",0); }
		if(!$this->is_ok($reply)) {
			$this->ERROR = "POP3 connect: Error [$reply]";
			unset($this->FP);
			return false;
		}
		$this->FP = $fp;
		//$this->BANNER = $this->parse_banner($reply);
		$this->RFC1939 = $this->noop();
		// changed following ---smita
		if($this->RFC1939)
		if(!$this->RFC1939) {
			$this->ERROR = "POP3: premature NOOP OK, NOT an RFC 1939 Compliant server";
			$this->quit();
			return false;
		}
		return true;
	}

	function noop () {
		if(!isset($this->FP)) {
			$this->ERROR = "POP3 noop: No connection to server";
			return false;
		}
		$cmd = "NOOP";
		$reply = $this->send_cmd($cmd);
		if(!$this->is_ok($reply)) {
			return false;
		}
		return true;
	}

	function user ($user = "") {
		// Sends the USER command, returns true or false
		//echo "user $user $reply";
		if(empty($user)) {
			$this->ERROR = "POP3 user: no user id submitted";
			return false;
		}
		if(!isset($this->FP)) {
			$this->ERROR = "POP3 user: connection not established";
			return false;
		}
		$reply = $this->send_cmd("USER $user");
		//echo "user $user $reply";
		if(!$this->is_ok($reply)) {
			$this->ERROR = "POP3 user: Error [$reply]";
			return false;
		}
		return true;
	}

	function pass ($pass = "") {
		// Sends the PASS command, returns # of msgs in mailbox,
		// returns false (undef) on Auth failure

		if(empty($pass)) {
			$this->ERROR = "POP3 pass: no password submitted";
			return false;
		}
		if(!isset($this->FP)) {

			$this->ERROR = "POP3 pass: connection not established";
			return false;
		}
		$reply = $this->send_cmd("PASS $pass");
		//echo "<BR>pass $pass ".$reply;
		if(!$this->is_ok($reply)) {
			$this->ERROR = "POP3 pass: authentication failed [$reply]";
			$this->quit();
			return false;
		}
		//	Auth successful.
		$count = $this->last("count");
		//		echo "<BR>msg count $count";
		$this->COUNT = $count;
		$this->RFC1939 = $this->noop();
		if(!$this->RFC1939) {
			//echo "noop";
			$this->ERROR = "POP3 pass: NOOP failed. Server not RFC 1939 compliant";
			$this->quit();
			return false;
		}
		return $count;
	}

	function login ($login = "", $pass = "") {
		// Sends both user and pass. Returns # of msgs in mailbox or
		// false on failure (or -1, if the error occurs while getting
		// the number of messages.)


		if(!isset($this->FP)) {
			$this->ERROR = "POP3 login: No connection to server";
			return false;

		}
		$fp = $this->FP;

		if(!$this->user($login)) {
			//	Preserve the error generated by user()
			return false;

		}
		$count = $this->pass($pass);
		//echo "<br> count ".$count;
		if( (!$count) or ($count == -1) ) {
			//	Preserve the error generated by last() and pass()
			return false;

		}
		return $count;
	}

	function top ($msgNum, $numLines = "0") {
		//	Gets the header and first $numLines of the msg body
		//	returns data in an array with each returned line being
		//	an array element. If $numLines is empty, returns
		//	only the header information, and none of the body.

		if(!isset($this->FP)) {
			$this->ERROR = "POP3 top: No connection to server";
			return false;
		}
		$this->update_timer();

		$fp = $this->FP;
		$buffer = $this->BUFFER;
		$cmd = "TOP $msgNum $numLines";
		fwrite($fp, "TOP $msgNum $numLines\r\n");
		$reply = fgets($fp, $buffer);
		$reply = $this->strip_clf($reply);
		if($this->DEBUG) { @error_log("POP3 SEND [$cmd] GOT [$reply]",0); }
		if(!$this->is_ok($reply)) {
			$this->ERROR = "POP3 top: Error [$reply]";
			return false;
		}

		$count = 0;
		$MsgArray = array();

		$line = fgets($fp,$buffer);
		while ( !ereg("^\.\r\n",$line)) {
			$MsgArray[$count] = $line;
			$count++;
			$line = fgets($fp,$buffer);
			if(empty($line))	{ break; }
		}

		return $MsgArray;
	}


	function get ($msgNum)
	{
		//	Retrieve the specified msg number. Returns an array
		//	where each line of the msg is an array element.
		$count = 0;
		$MsgArray = array();
		
		if(!isset($this->FP))
		{
			$this->ERROR = "POP3 get: No connection to server";
			echo "POP3 get: No connection to server";
			return $MsgArray;
		}

		$this->update_timer();

		$fp = $this->FP;
		$buffer = $this->BUFFER;
		$cmd = "RETR $msgNum";
		$reply = $this->send_cmd($cmd);

		if(!$this->is_ok($reply))
		{
			$this->ERROR = "POP3 get: Error [$reply]";
			echo "POP3 get: Error [$reply]";
			return $MsgArray;
		}

		$line = fgets($fp,$buffer);
		while ( !ereg("^\.\r\n",$line))
		{
			$MsgArray[$count] = $line;
			$count++;
			$line = fgets($fp,$buffer);
			if(empty($line))	{ break; }
		}
		return $MsgArray;
	}

	function last ( $type = "count" )
	{
		//	Returns the highest msg number in the mailbox.
		//	returns -1 on error, 0+ on success, if type != count
		//	results in a popstat() call (2 element array returned)

		$last = -1;
		if(!isset($this->FP))
		{
			$this->ERROR = "POP3 last: No connection to server";
			return $last;
		}

		$reply = $this->send_cmd("STAT");
		//echo " last ".$reply."<BR>";
		if(!$this->is_ok($reply))
		{
			$this->ERROR = "POP3 last: error [$reply]";
			return $last;
		}

		$Vars = explode(" ",$reply);
		$count = $Vars[1];
		$size = $Vars[2];
		settype($count,"integer");
		settype($size,"integer");
		if($type != "count")
		{
			return array($count,$size);
		}
		return $count;
	}

	function send_cmd ( $cmd = "" )
	{
		//	Sends a user defined command string to the
		//	POP server and returns the results. Useful for
		//	non-compliant or custom POP servers.
		//	Do NOT include the \r\n as part of your command
		//	string - it will be appended automatically.

		//	The return value is a standard fgets() call, which
		//	will read up to $this->BUFFER bytes of data, until it
		//	encounters a new line, or EOF, whichever happens first.

		//	This method works best if $cmd responds with only
		//	one line of data.

		if(!isset($this->FP))
		{
			$this->ERROR = "POP3 send_cmd: No connection to server";
			return false;
		}

		if(empty($cmd))
		{
			$this->ERROR = "POP3 send_cmd: Empty command string";
			return "";
		}

		$fp = $this->FP;
		$buffer = $this->BUFFER;
		$this->update_timer();
		fwrite($fp,"$cmd\r\n");
		$reply = fgets($fp,$buffer);
		//echo "send cmd ".$reply;
		$reply = $this->strip_clf($reply);
		if($this->DEBUG) { @error_log("POP3 SEND [$cmd] GOT [$reply]",0); }
		return $reply;
	}

	function quit ()
	{
		//	Closes the connection to the POP3 server, deleting
		//	any msgs marked as deleted.

		if(!isset($this->FP))
		{
			$this->ERROR = "POP3 quit: connection does not exist";
			return false;
		}
		$fp = $this->FP;
		$cmd = "QUIT";
		fwrite($fp,"$cmd\r\n");
		$reply = fgets($fp,$this->BUFFER);
		$reply = $this->strip_clf($reply);
		if($this->DEBUG) { @error_log("POP3 SEND [$cmd] GOT [$reply]",0); }
		fclose($fp);
		unset($this->FP);
		return true;
	}

	function delete ($msgNum = "")
	{
		//	Flags a specified msg as deleted. The msg will not
		//	be deleted until a quit() method is called.

		if(!isset($this->FP))
		{
			$this->ERROR = "POP3 delete: No connection to server";
			return false;
		}
		if(empty($msgNum))
		{
			$this->ERROR = "POP3 delete: No msg number submitted";
			return false;
		}
		$reply = $this->send_cmd("DELE $msgNum");
		if(!$this->is_ok($reply))
		{
			$this->ERROR = "POP3 delete: Command failed [$reply]";
			return false;
		}
		return true;
	}

	//	*********************************************************

	//	The following methods are internal to the class.

	function is_ok ($cmd = "")
	{
		//	Return true or false on +OK or -ERR

		if(empty($cmd))					{ return false; }
		if ( ereg ("^\+OK", $cmd ) )	{ return true; }
		return false;
	}

	function strip_clf ($text = "")
	{
		// Strips \r\n from server responses

		if(empty($text)) { return $text; }
		$stripped = ereg_replace("\r","",$text);
		$stripped = ereg_replace("\n","",$stripped);
		return $stripped;
	}

}	// End class


/************************* End of POP3 Class ****************************/
$pop3 = new POP3();

if (! $pop3->connect($scompMailServer, 110)) {
	$mailMessage .= "Ooops $pop3->ERROR <BR>\n";
}

$msgCount = $pop3->login($scompUsername, $scompPasswd);

if($msgCount == -1) {
	$mailMessage .= "<H1>Login Failed: $pop3->ERROR</H1>\n";
}

echo "<BR>".$msgCount."<BR>";
if (!($pop3->ERROR) && $msgCount < 1 ) {
	$mailMessage .= "Login OK: Inbox EMPTY<BR>\n";
} else if(! $pop3->ERROR) {
	$mailMessage .= "Login OK: Inbox contains [$msgCount] messages<BR>\n";
}
echo $mailMessage.$msgCount;



// START: Loop through each message in the mailbox.

for ($i=1; ($i <= $msgCount && $i<= $unsubscribeNumbersAtATime); $i++) {
	$sDomain = '';
	echo "\nStart $i / $msgCount \n";
	
	// Get Message Information, NULL the "gotten information" from the last message
	$msgToDisplay = $pop3->get($i);
	
	if (count($msgToDisplay)>0) {
		//print_r($msgToDisplay);
		$code = '';
		$unsubscribeEmail = '';
		$attachment = false;
		$replyTo = false;
		$comment = false;
		$exceptionMessage = '';
	
		$sDebugOutput2 = implode( $msgToDisplay );
		$sDebugOutput1 = "Beginning Processing Checks...\n";
	
		// START: Loop through each line in the message.

		while (list(, $value) = each ( $msgToDisplay )) {
	
			// If the current line contains "*mf ", process it for the Code, and UnsubscribeEmail
			// "Code" is the 2nd and 3rd columns in the data from the line.  Email is 4th.
			
			if (stristr($value, "From: ") && $sDomain == '') {
				$sFromCode = '';
				$sDomain = strstr($value, '@');
				$sDomain = str_replace(">",'',$sDomain);
				$sDomain = str_replace("\r\n",'',$sDomain);
				$sDomain = trim($sDomain);
				$sDomain = strtolower($sDomain);
				if ($sDomain == "@msn.com" || $sDomain == "@hotmail.com" || $sDomain == "@microsoft.com") {
					$sFromCode = "microsoft";
					$sSourceCode = "microsoftUnsub";
					$sReason = "Microsoft Complaint";
					$subject = "Microsoft Exception...";
					//mail('spatel@amperemedia.com', 'hotmail/msn', 'line 511 - hotmail/msn');
				}

				if ($sDomain == "@juno.com" || $sDomain == "@netzero.net" || $sDomain == "@support.netzero.com" || $sDomain == "@support.juno.com") {
					$sFromCode = "juno-netzero";
					$sSourceCode = "juno-netzeroUnsub";
					$sReason = "Juno - Netzero Complaint";
					$subject = "Juno - Netzero Exception...";
				}

				if ($sFromCode == "") {
					$sFromCode = "aol";
					$sSourceCode = "aolUnsub";
					$sReason = "AOL Complaint";
					$subject = "AOL Exception...";
				}
				echo "\nDomain: $sDomain\n";
				echo "\nFrom: $sFromCode\n";
			}

			if(stristr($value, "*mf ")) {
				$value = ereg_replace("&nbsp;"," ",$value);
				$sDebugOutput1 .= "Found *mf: $value\n";
				$codeEmailArray = explode(" ", strip_tags($value));
	
				// If code contains more than 3 columns, then we can get values.
				// This includes the *mf and mf* tags.
	
				if(count($codeEmailArray)>3) {
					$code = $codeEmailArray[1]." ".$codeEmailArray[2];
					$unsubscribeEmail = $codeEmailArray[3];
					$unsubscribeEmail = strip_tags($unsubscribeEmail);
					$sDebugOutput1 .= "Is new *mf Format, code=$code, usEmail=$unsubscribeEmail\n";
				}
			}
		}

		$sTempVal = addslashes($sDebugOutput2);
		$sInsertLog = "INSERT INTO nibbles.scompLog (dateTimeAdded,domain,body)
					VALUES (NOW(),\"$sDomain\",\"$sTempVal\")";
		$rInsertLogResult = dbQuery($sInsertLog);
		
		
		// END: Cycle every line in this email.
	
		$sEmail = $unsubscribeEmail;
	
		
		if ($sEmail != '') {
			// Banned email
			$sBannedEmailQuery = "INSERT IGNORE INTO bannedEmails (email) VALUES (\"$sEmail\")";
			$rBannedEmailResult = dbQuery($sBannedEmailQuery);
			
			
			// update otDataHistory table and mark entry as excluded from data sales.
			$sUpdateHistoryQuery = "UPDATE LOW_PRIORITY otDataHistory
							SET excludeDataSale = '1'
							WHERE email = \"$sEmail\"
							AND excludeDataSale != '1'";
			$rUpdateHistoryResult = dbQuery($sUpdateHistoryQuery);
			
			$sUpdateHistoryQuery = "UPDATE LOW_PRIORITY otDataHistoryArchive2001120320050403
							SET excludeDataSale = '1'
							WHERE email = \"$sEmail\"
							AND excludeDataSale != '1'";
			$rUpdateHistoryResult = dbQuery($sUpdateHistoryQuery);
			
			$sUpdateHistoryQuery = "UPDATE LOW_PRIORITY otDataHistoryArchive2005040420050717
							SET excludeDataSale = '1'
							WHERE email = \"$sEmail\"
							AND excludeDataSale != '1'";
			$rUpdateHistoryResult = dbQuery($sUpdateHistoryQuery);
			
			// update abandedOffersHistory table and mark entry as excluded from data sales.
			$sUpdateEmailAbanded = "UPDATE LOW_PRIORITY abandedOffersHistory
							SET excludeDataSale = '1'
							WHERE email = \"$sEmail\"
							AND excludeDataSale != '1'";
			$rUpdateEmailAbanded = dbQuery($sUpdateEmailAbanded);
		}
		
		//  Take "email" as "unsubscribeEmail" from the previous loop (the value received from
		//  looping this email).
	
		//  PROBLEM: This is not necessarily defined!!  We may be running this section with a
		//  "blank email address"!!!
	
		$sJoinListQuery = "SELECT * FROM   joinLists";
		$rJoinListResult = dbQuery($sJoinListQuery);

		// START: Loop through Join Lists.
	
		while ($oJoinListRow = dbFetchObject($rJoinListResult)) {
	
			$iJoinListId = $oJoinListRow->id;
	
			// Remove this email/list from the active list table.
	
			$sInactiveDeleteQuery = "DELETE FROM joinEmailInactive
									 WHERE  email = '$sEmail'
									 AND	joinListId = '$iJoinListId'";
			$rInactiveDeleteResult = dbQuery($sInactiveDeleteQuery);
	
			// Insert this email into the "inactive" table, for this list.
	
			$sInactiveInsertQuery = "INSERT IGNORE INTO joinEmailInactive(email, joinListId, sourceCode, dateTimeAdded)
								 VALUES('$sEmail', '$iJoinListId', '$sSourceCode', now())";
	
			$rInactiveInsertResult = dbQuery($sInactiveInsertQuery);
			echo dbError();
	
			// Insert this email/list into the unsubscribe table.
	
			$sUnsubInsertQuery = "INSERT INTO joinEmailUnsub(email, joinListId, sourceCode, remoteIp, dateTimeAdded, isPurge)
								  VALUES('$sEmail', '$iJoinListId', '$sSourceCode', '', now(), '1')";
	
			$rUnsubInsertResult = dbQuery($sUnsubInsertQuery);
			echo dbError();
	
			// remove this email/list from the join table.
	
			$sActiveDeleteQuery = "DELETE FROM joinEmailActive
								   WHERE  email = '$sEmail'
								   AND    joinListId = '$iJoinListId'";
	
			$rActiveDeleteResult = dbQuery($sActiveDeleteQuery);
			echo dbError();
	
			// remove this email/list from any peniding joins.
	
			$sPendingDeleteQuery = "DELETE FROM joinEmailPending
								    WHERE  email = '$sEmail'
								    AND    joinListId = '$iJoinListId'";
	
			$rPendingDeleteResult = dbQuery($sPendingDeleteQuery);
			echo dbError();
		}
	
		//  END: Loop through join lists
	
	
		// START: Remove from Non-AmpereMedia Lists
	
		$sNonAmpereListQuery = "SELECT *
								FROM   joinListsNonAmpere";
		$rNonAmpereListResult = dbQuery($sNonAmpereListQuery);
		echo dbError();
	
		// START: Looping Non-AmpereMedia Lists
	
		while ($oNonAmpereListRow = dbFetchObject($rNonAmpereListResult)) {
	
			$sShortName = $oNonAmpereListRow->shortName;
	
			$sInsertQuery = "INSERT INTO myfree.mw(email, action, list)
							 VALUES('$sEmail', 'd', '$sShortName')";
			//$rInsertResult = dbQuery($sInsertQuery);
			//echo dbError();
	
			$insertQuery = "INSERT INTO mwfeedback(email, listid, reason, dateTimeAdded)
							VALUES('$sEmail', '$sShortName', '$sReason', now())";
	
			$result = dbQuery($insertQuery);
		}
	
		// END: Looping Non-AmpereMedia Lists
	
		// If we know the "code" from the line, record statistics.
	
		if($code !='') {
			$sDebugOutput1 .= "Code Not Blank, process\n";
	
			// Check if entry exists for the same code in same date
			$checkQuery = "SELECT *
								   FROM scompCodeStats
								   WHERE code='$code'
								   AND sender='$sFromCode'
								   AND   unsubDate = CURRENT_DATE ";
			$checkResult = dbQuery($checkQuery);
			if(dbNumRows($checkResult)==0) {
				//Enter data in ScompCodeStatistics table;
				$insertQuery = "INSERT INTO scompCodeStats(code, unsubDate, counts, sender)
										VALUES('$code', CURRENT_DATE, '1', '$sFromCode')";
				$result = dbQuery($insertQuery);
			} else {
				// Update record of same code and same date and increment count to 1
				$updateQuery = "UPDATE scompCodeStats
										SET    counts = counts+1
										WHERE  code = '$code'
										AND	   sender = '$sFromCode'
										AND    unsubDate = CURRENT_DATE";
				$updateResult = dbQuery($updateQuery);
			}
		}
	
		// If we know the "code" from the line, record statistics.
	
		if($unsubscribeEmail !='') {
			$sDebugOutput1 .= "usEmail Not Blank, process\n";
			// check email validity
			if (eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $unsubscribeEmail))  {
				// Check if entry exists for the same email in same date
				$checkEmailQuery = "SELECT *
								   FROM scompEmailStats
								   WHERE email='$unsubscribeEmail'
								   AND sender='$sFromCode'
								   AND   unsubDate = CURRENT_DATE ";
				$checkEmailResult = dbQuery($checkEmailQuery);
				if(dbNumRows($checkEmailResult)==0) {
					//Enter data in ScompEmailStatistics table;
					$insertQuery = "INSERT INTO scompEmailStats(email, unsubDate, counts, sender)
										VALUES(LOWER('$unsubscribeEmail'), CURRENT_DATE, '1', '$sFromCode')";
					$result = dbQuery($insertQuery);
				} else {
					// Update record of same email and same date and increment count to 1
					$updateQuery = "UPDATE scompEmailStats
										SET    counts = counts+1
										WHERE  email = LOWER('$unsubscribeEmail')
										AND    sender = '$sFromCode'
										AND    unsubDate = CURRENT_DATE";
					$updateResult = dbQuery($updateQuery);
				}
			}
		}
	
		// If we know the "code" from the line, record statistics.
	
		// Insert record into ScompCodeEmails
		if($code !='' || $unsubscribeEmail !='') {
			$sDebugOutput1 .= "Code and usEmail not blank, process\n";
			$selectQuery = "SELECT *
									FROM   scompCodeEmails
									WHERE  code = '$code'
									AND    sender = '$sFromCode'
									AND    email = '$unsubscribeEmail'
									AND    unsubDate = CURRENT_DATE";
			$selectResult = dbQuery($selectQuery);
			if(dbNumRows($selectResult)==0) {
				$insertQuery = "INSERT INTO scompCodeEmails(code, email, unsubDate, sender)
										VALUES('$code', '$unsubscribeEmail', CURRENT_DATE, '$sFromCode')";
				$insertResult = dbQuery($insertQuery);
			}
		}
	
		// Delete the email from the box.
		//if ($unsubscribeEmail != '' && $code != '') {
		$pop3->delete($i);
		echo "\nDeleted $i / $msgCount \n";
		echo "\nDone $i / $msgCount \n";
		//}

		// If exceptionMessage is not blank,
		//    and the unsubscribeEmail is blank
		//    and the code is blank,
		// THEN send the exception Email to the exceptions address.
	
		$sDebugOutput1 .= "\n\nException Message: $exceptionMessage\n";
		$sDebugOutput1 .= "unsubscribeEmail:  $unsubscribeEmail\n";
		$sDebugOutput1 .=          "Code:              $code\n\n";
		
		if( $unsubscribeEmail =='' && $code=='') {
			$sDebugOutput1 .= "Should be sending an exception email\n";
			$iDebugNumExceptions++;
			
			//  Send Exception message here
			$exceptionHeaders .= "From: $scompMailServer\r\n";
			$exceptionMessage = ereg_replace("&nbsp;"," ",$exceptionMessage);
	
			mail($exceptionEmail, $subject, implode($msgToDisplay), $exceptionHeaders);
			$exceptionMessage='';
			
			//mail('spatel@amperemedia.com', $subject, implode($msgToDisplay), $exceptionHeaders);
			$exceptionMessage='';
		}
		// mail( "jr@amperemedia.com,spatel@amperemedia.com", "Scomp Email Output", $sDebugOutput1."\n\n\n".$sDebugOutput2 );
	}
}

// END: Looping Messages in Box



$pop3->quit();

if ($iDebugNumExceptions > 0) {
	//mail( "jr@amperemedia.com,spatel@amperemedia.com", "\"Scomp.php\" Expected Exceptions: $iDebugNumExceptions", "Expected Exceptions: $iDebugNumExceptions" );
}


?>
