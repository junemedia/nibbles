<?php

//script to handle BDA unsubs

include( "/home/scripts/includes/cssLogFunctions.php" );
$iScriptId = cssLogStart( "unsubBda.php" );

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

$scompUsername='bda-unsub';
$scompPasswd='1bda2unsub8';
$scompMailServer = 'mail.amperemedia.com';

$reportEmail = 'it@amperemedia.com';
//$reportEmail = 'matts@silvercarrot.com';

$iDebugNumExceptions = 0;

/************************* POP3 Class ****************************/

/*

class.POP3.php3 v1.0	99/03/24 CDI cdi@thewebmasters.net
Copyright (c) 1999 - CDI (cdi@thewebmasters.net) All Rights Reserved

An RFC 1939 compliant wrapper class for the POP3 protocol.
*/

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

	function POP3 ( $server = "", $timeout = "" )
	{
		settype($this->BUFFER,"integer");
		if(!empty($server))
		{
			// Do not allow programs to alter MAILSERVER
			// if it is already specified. They can get around
			// this if they -really- want to, so don't count on it.
			if(empty($this->MAILSERVER))
			{
				$this->MAILSERVER = $server;
			}
		}
		if(!empty($timeout))
		{
			settype($timeout,"integer");
			$this->TIMEOUT = $timeout;
			set_time_limit($timeout);
		}
		return true;
	}

	function update_timer ()
	{
		set_time_limit($this->TIMEOUT);
		return true;
	}

	function connect ($server, $port = 110)
	{
		//	Opens a socket to the specified server. Unless overridden,
		//	port defaults to 110. Returns true on success, false on fail

		// If MAILSERVER is set, override $server with it's value

		/*if(!empty($this->MAILSERVER))
		{
		$server = $this->MAILSERVER;
		}
		*/
		if(empty($server))
		{
			$this->ERROR = "POP3 connect: No server specified";
			unset($this->FP);
			return false;
		}

		$fp = fsockopen("$server", $port, &$errno, &$errstr);

		if(!$fp)
		{
			$this->ERROR = "POP3 connect: Error [$errno] [$errstr]";
			unset($this->FP);
			return false;
		}

		set_socket_blocking($fp,-1);
		$this->update_timer();
		$reply = fgets($fp,$this->BUFFER);
		$reply = $this->strip_clf($reply);
		if($this->DEBUG) { error_log("POP3 SEND [connect: $server] GOT [$reply]",0); }
		if(!$this->is_ok($reply))
		{
			$this->ERROR = "POP3 connect: Error [$reply]";
			unset($this->FP);
			return false;
		}
		$this->FP = $fp;
		//$this->BANNER = $this->parse_banner($reply);
		$this->RFC1939 = $this->noop();
		// changed following ---smita
		if($this->RFC1939)
		if(!$this->RFC1939)
		{
			$this->ERROR = "POP3: premature NOOP OK, NOT an RFC 1939 Compliant server";
			$this->quit();
			return false;
		}
		return true;
	}

	function noop ()
	{
		if(!isset($this->FP))
		{
			$this->ERROR = "POP3 noop: No connection to server";
			return false;
		}
		$cmd = "NOOP";
		$reply = $this->send_cmd($cmd);
		if(!$this->is_ok($reply))
		{
			return false;
		}
		return true;
	}

	function user ($user = "")
	{
		// Sends the USER command, returns true or false
		//echo "user $user $reply";
		if(empty($user))
		{
			$this->ERROR = "POP3 user: no user id submitted";
			return false;
		}
		if(!isset($this->FP))
		{
			$this->ERROR = "POP3 user: connection not established";
			return false;
		}
		$reply = $this->send_cmd("USER $user");
		//echo "user $user $reply";
		if(!$this->is_ok($reply))
		{
			$this->ERROR = "POP3 user: Error [$reply]";
			return false;
		}
		return true;
	}

	function pass ($pass = "")
	{
		// Sends the PASS command, returns # of msgs in mailbox,
		// returns false (undef) on Auth failure

		if(empty($pass))
		{
			$this->ERROR = "POP3 pass: no password submitted";
			return false;
		}
		if(!isset($this->FP))
		{

			$this->ERROR = "POP3 pass: connection not established";
			return false;
		}
		$reply = $this->send_cmd("PASS $pass");
		//echo "<BR>pass $pass ".$reply;
		if(!$this->is_ok($reply))
		{

			$this->ERROR = "POP3 pass: authentication failed [$reply]";
			$this->quit();
			return false;
		}
		//	Auth successful.
		$count = $this->last("count");
		//		echo "<BR>msg count $count";
		$this->COUNT = $count;
		$this->RFC1939 = $this->noop();
		if(!$this->RFC1939)
		{
			//echo "noop";
			$this->ERROR = "POP3 pass: NOOP failed. Server not RFC 1939 compliant";
			$this->quit();
			return false;
		}
		return $count;
	}

	function login ($login = "", $pass = "")
	{
		// Sends both user and pass. Returns # of msgs in mailbox or
		// false on failure (or -1, if the error occurs while getting
		// the number of messages.)


		if(!isset($this->FP))
		{
			$this->ERROR = "POP3 login: No connection to server";
			return false;

		}
		$fp = $this->FP;

		if(!$this->user($login))
		{
			//	Preserve the error generated by user()
			return false;

		}
		$count = $this->pass($pass);
		//echo "<br> count ".$count;
		if( (!$count) or ($count == -1) )
		{
			//	Preserve the error generated by last() and pass()
			return false;

		}
		return $count;
	}

	function top ($msgNum, $numLines = "0")
	{
		//	Gets the header and first $numLines of the msg body
		//	returns data in an array with each returned line being
		//	an array element. If $numLines is empty, returns
		//	only the header information, and none of the body.

		if(!isset($this->FP))
		{
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
		if(!$this->is_ok($reply))
		{
			$this->ERROR = "POP3 top: Error [$reply]";
			return false;
		}

		$count = 0;
		$MsgArray = array();

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


	function get ($msgNum)
	{
		//	Retrieve the specified msg number. Returns an array
		//	where each line of the msg is an array element.

		if(!isset($this->FP))
		{
			$this->ERROR = "POP3 get: No connection to server";
			return false;
		}

		$this->update_timer();

		$fp = $this->FP;
		$buffer = $this->BUFFER;
		$cmd = "RETR $msgNum";
		$reply = $this->send_cmd($cmd);

		if(!$this->is_ok($reply))
		{
			$this->ERROR = "POP3 get: Error [$reply]";
			return false;
		}

		$count = 0;
		$MsgArray = array();

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

if (! $pop3->connect($scompMailServer, 110))
{
	$mailMessage .= "Ooops $pop3->ERROR\n";
}

$msgCount = $pop3->login($scompUsername, $scompPasswd);

if($pop3->ERROR && ($msgCount == -1 || $msgCount == false))
{
	$mailMessage .= "Login Failed: $pop3->ERROR\n";
	//        exit;
}

if (!($pop3->ERROR) && $msgCount < 1 ){
	$mailMessage .= "Login OK: Inbox EMPTY\n";
} else if(! $pop3->ERROR) {
	$mailMessage .= "Login OK: Inbox contains [$msgCount] messages\n";
}


// START: Loop through each message in the mailbox.

// init counters & arrays
$totalUnsubs = 0;
$totalInvalid = 0;
$invalidLines = array();
for ($i=1; $i <= $msgCount; $i++) {

	// Get Message Information, NULL the "gotten information" from the last message

	$msgToDisplay = $pop3->get($i);
//	echo "\n\nMessage: ".print_r($msgToDisplay)."\n\n";

	// loop thru message. determine where body starts. add body to array.
	$isMsgBody = false;
	$msgBody = array();
	foreach($msgToDisplay as $line) {
		// if $isMsgBody is set, push current line onto message body
		$line = trim($line);
		if( $isMsgBody ) {
			$msgBody[] = $line;
		}

		// at first blank line, set flag that message body is starting
		if( $line == "" ) {
			$isMsgBody = true;
		}
	}

//	print_r( $msgBody );
	
	$code = '';
	$unsubscribeEmail = '';
	$attachment = false;
	$replyTo = false;
	$comment = false;
	$exceptionMessage = '';

	$sDebugOutput2 = implode( $msgToDisplay );
	$sDebugOutput1 = "Beginning Processing Checks...\n";

	// START: Loop through each line in the message body.
	foreach( $msgBody as $sEmail ) {

		//  PROBLEM: This is not necessarily defined!!  We may be running this section with a
		//  "blank email address"!!!
		if( eregi( "^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $sEmail)) {

			//print "$sEmail is a good email\n";

			// Remove this email/list from the inactive list table.
	
			$sInactiveDeleteQuery = "DELETE FROM joinEmailInactive
									 WHERE  email = '$sEmail'
									 AND	joinListId = '215'";
			$rInactiveDeleteResult = dbQuery($sInactiveDeleteQuery);
	
			// Insert this email into the "inactive" table, for this list.
	
			$sInactiveInsertQuery = "INSERT IGNORE INTO joinEmailInactive(email, joinListId, sourceCode, dateTimeAdded)
								 VALUES('$sEmail', '215', '', now())";
	
			$rInactiveInsertResult = dbQuery($sInactiveInsertQuery);
			echo dbError();
	
			// Insert this email/list into the unsubscribe table.

			$sUnsubInsertQuery = "INSERT INTO joinEmailUnsub(email, joinListId, sourceCode, remoteIp, dateTimeAdded, isPurge)
								  VALUES('$sEmail', '215', '', '', now(), '1')";
	
			$rUnsubInsertResult = dbQuery($sUnsubInsertQuery);
			echo dbError();
	
			// remove this email/list from the join table.
	
			$sActiveDeleteQuery = "DELETE FROM joinEmailActive
								   WHERE  email = '$sEmail'
								   AND    joinListId = '215'";
	
			$rActiveDeleteResult = dbQuery($sActiveDeleteQuery);
			echo dbError();
	
			// remove this email/list from any pending joins.
	
			$sPendingDeleteQuery = "DELETE FROM joinEmailPending
								    WHERE  email = '$sEmail'
								    AND    joinListId = '215'";
	
			$rPendingDeleteResult = dbQuery($sPendingDeleteQuery);
			echo dbError();

			$totalUnsubs++;

		} else {
			//print "$sEmail is a bad email\n";
			$totalInvalid++;
			$invalidLines[] = $sEmail;
		}
	}

	// Delete the email from the box.
	$pop3->delete($i);
	
//	mail( "matts@silvercarrot.com", "Test Email Output", $sDebugOutput1."\n\n\n".$sDebugOutput2 );

}
// END: Looping Messages in Box

// send report, with # of unsubs
$reportSubject = 'BDA unsubs from skylist';
$reportHeaders = "From: root\r\n";
$mailMessage .= "\n$totalUnsubs emails unsubbed from BDA\n\n";
$mailMessage .= "$totalInvalid blank or invalid emails were not unsubbed\n\n";
$mailMessage .= "Invalid lines:\n";
$mailMessage .= implode($invalidLines);
$sTempTotal = $totalUnsubs + $totalInvalid;

if ($sTempTotal > 0) {
	//mail( $reportEmail, $reportSubject, $mailMessage, $reportHeaders );
}

$pop3->quit();

cssLogFinish( $iScriptId );

?>
