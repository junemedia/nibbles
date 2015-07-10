<?php

include( "/home/scripts/includes/cssLogFunctions.php" );
$iScriptId = cssLogStart( "attachmentAPIPost.php" );

// Recipients:  jr@amperemedia.com,phil@amperemedia.com,carole@amperemedia.com,josh@amperemedia.com,susan@amperemedia.com,nradler@myfree.com,shochwert@amperemedia.com
/*********

This script grabs a .csv file from an email attachment sent to a given email mailbox (passed as an argument).
It then parses the .csv, and posts each line of the csv as an API.

*********/

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

/************************* POP3 Class ****************************/


class POP3
{
	var $ERROR		= "";		//	Error string.

	var $TIMEOUT	= 180;		//	Default timeout before giving up on a
	//	network operation.

	var $COUNT		= -1;		//	Mailbox msg count

	var $BUFFER		= 1024;		//	Socket buffer for socket fgets() calls.
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
		
		$this->RFC1939 = $this->noop();//"+OK done";
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
		//echo "$reply\n";
		if(!$this->is_ok($reply))
		{
			$this->ERROR = "POP3 pass: authentication failed [$reply]";
			$this->quit();
			return false;
		}
		//	Auth successful.
		$count = $this->last("count");

		$this->COUNT = $count;
		
		$this->RFC1939 = $this->noop();
		if(!$this->RFC1939)
		{
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
		while ( !(ereg("^\.\r\n",$line)))
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

		//if(!ereg("^RETR ",$cmd)) {echo "$cmd is command\n";}
		$fp = $this->FP;
		$buffer = $this->BUFFER;
		$this->update_timer();
		$bytes = fwrite($fp,"$cmd\r\n");
		$reply = fgets($fp,$buffer);
		/*
		if(!ereg("^RETR ",$cmd)) {
			if($reply){
				echo "$reply is reply\n";
			} else {
				echo "fgets failed\n";
			}
		}
		*/
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



//$monthArray = array('Jan','Feb','Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');


//************************ Date Difference function ************************/

Function DateDiff ($interval, $date1,$date2) {

	// get the number of seconds between the two dates
	$timedifference =  $date2 - $date1;

	switch ($interval) {
		case "w":
		$retval  = bcdiv($timedifference ,604800);
		break;
		case "d":
		$retval  = bcdiv( $timedifference,86400);
		break;
		case "h":
		$retval = bcdiv ($timedifference,3600);
		break;
		case "n":
		$retval  = bcdiv( $timedifference,60);
		break;
		case "s":
		$retval  = $timedifference;
		break;

	}
	return $retval;

}

//************************ Date Difference function ************************/


// Query to get all the Seed Email Accounts info

$accountQuery = "SELECT *
					 FROM   seedEmailAccounts
					 WHERE ISPType = 'pop3'
					 ORDER BY ISPName";
// AND ISPCode = 'L'
$accountResult = mysql_query($accountQuery);
$sTempMsg = '';

	// Create new pop3 object to access the mail box from the server
	$pop3 = new POP3();

	//echo join("\n",$argv);
	
	list($username,$bad) = explode('@',$argv[1]);
	//$username = 'gradloan_api';
	$passwd = 'asdf1234';
	$mailServer = 'mail.amperemedia.com';
	//$ISPName = $accountRow->ISPName;
	//$ISPCode = $accountRow->ISPCode;
	//$ISPType = $accountRow->ISPType;


		if (! $pop3->connect($mailServer, 110))	{
			$sTempMsg .= "Server: Ooops $pop3->ERROR <BR>\n";
		}

		$msgCount = $pop3->login($username, $passwd);

		if ($msgCount == -1) {
			$sTempMsg .= "<H1>Login Failed: $pop3->ERROR</H1>\n";
		}
		
		if (!($pop3->ERROR) && $msgCount < 1) {
			$sTempMsg .= "Login OK: Inbox EMPTY<BR>\n";
		} else if (! $pop3->ERROR) {
			$sTempMsg .= "Login OK: Inbox contains [$msgCount] messages<BR>\n";
		}
		
		//echo "<BR>count $mailServer $sTempMsg $msgCount  ".$pop3->ERROR."<BR>";
		
		$startNum = $msgCount;
		$i = $startNum;
		echo "$i is startNum\n";
		$aReport = array();
		while ($i >= 1) {

			echo "\n#".$i;


			$currMessageId='';
			$msgToDisplay = $pop3->get($i);
			
			//print_r($msgToDisplay);
			//$i -=1;
		
			if( (!($msgToDisplay)) or (gettype($msgToDisplay) != "array") )
			{
				$message = "oops, $pop3->ERROR<BR>\n";
				$sTempMsg .= "oops, $pop3->ERROR<BR>\n";
			}
			//Reset  newsLetterCode and newsLetterSentDate
			$newsLetterCode="";
			$newsLetterSentDate="";

			// Traverse through Message body
		
			$map = array('OfferCode'		=> 'sOfferCode',
							'SourceCode'	=> 'sSourceCode',
							'First'			=> 'sFirst',
							'Last'			=> 'sLast',
							'Address'		=> 'sAddress',
							'Address2'		=> 'sAddress2',
							'City'			=> 'sCity',
							'State'			=> 'sState',
							'Zip'			=> 'sZip',
							'Phone'			=> 'sPhone',
							'Email'			=> 'sEmail',
							'RemoteIp'		=> 'sRemoteIp');
			
			$aFile = array();
                        $iFileIndex = -1;
                        $bStarted = false;
			if(is_array($msgToDisplay)){
				foreach($msgToDisplay as $index => $line){
					if(strstr($line,'boundary="')){
						$sBoundary = str_replace('"','',str_replace('boundary=','',$line));
					}
				}
			
				foreach($msgToDisplay as $index => $line){
					if(!$bStarted){
						if(strstr($line,'filename=')){
							$bStarted = true;
						}
					} else if (strstr($line, $sBoundary)){
					$bStarted = false;
					} else if ($bStarted == true) {
						if(!(strstr($line, $sBoundary)) && !(strstr($line, '----------'))){
							array_push($aFile,$line);
						}
					}
				}
			}

			//
			//echo "before the parse:";
			//print_r($aFile);
			
			$sData = str_replace("=\r\n",'',join('',$aFile));
			$sData = base64_decode($sData);
			//echo "after the join:\n".$sData;
			$aFile = explode("\n",$sData);
			
			$someArray = array();
			foreach($aFile as $k => $value){
				$value = rtrim($value);
				if(($value != " ") &&($value != "") && (!strstr($value,',,,,,,,')) && (!strstr($value,'------'))){
					array_push($someArray,$value);
				}
			}
			$aFile = $someArray;
			
			//echo "after the parse:";
			//print_r($aFile);
			//exit();
			//so, now we should have the file in $aFile.
			//$aData = array();
			if(is_array($aFile) && count($aFile) > 0){
			foreach($aFile as $index => $line){
				$line = rtrim($line);
				//echo $line."\n";
				if($index == 0){
					//this is the column header line.
					$columnHeaders = split(',',$line);
					foreach($columnHeaders as $ind => $a){
						if(strstr($a,'"')){
							$columnHeaders[$ind] = str_replace('"','',$a);
						}
					}
				} else if(strlen($line) != 0){
					//$aData[$index] = array();
					
					//echo strlen($line)." => ".$line."\n";
					$aTemp = split(',',$line);
					$aLineValues = array();
					foreach($aTemp as $k => $v){
						$aLineValues[rtrim($columnHeaders[$k])] = $v;
					}
					
					$aData = array();
					$iOfferCodeIndex = '';
					foreach($aLineValues as $k => $v){
							
						if(strstr($v,'"')){
							$v = str_replace('"','',$v);
						}
											
						if((($k == 'sPhone')||($k == 'Phone'))&&(strlen($v) == 10)){
							$v = $v[0].$v[1].$v[2].'-'.$v[3].$v[4].$v[5].'-'.$v[6].$v[7].$v[8].$v[9];
						}
						
						if(in_array($k,array_keys($map))){
							array_push($aData,urlencode($map[rtrim($k)]).'='.urlencode(rtrim($v)));
						} else {
							array_push($aData,urlencode(rtrim($k)).'='.urlencode(rtrim($v)));
						}
						
						//also, record the index of the offer code, because we want to know it later
						if(($k == 'sOfferCode')||($k == 'Offer Code')||($k == 'OfferCode')){
							$iOfferCodeIndex = $v;
						}
						
					}
					
					$sHttpPostString = join('&',$aData);
					echo $sHttpPostString."\n";
					//exit();
					//post the data to the api script							
					$sScriptPath = '/partners/api/leadSubmit.php';
					$sRealTimeResponse = '';
					$rSocketConnection = fsockopen('www.popularliving.com', 80, $errno, $errstr, 30);
					if($rSocketConnection){
						echo "we're posting.\n";
						
						fputs($rSocketConnection, "POST $sScriptPath HTTP/1.1\r\n");
						fputs($rSocketConnection, "Host: www.popularliving.com\r\n");
						fputs($rSocketConnection, "Accept-Language: en\r\n");
						fputs($rSocketConnection, "Content-type: application/x-www-form-urlencoded \r\n");
						fputs($rSocketConnection, "Content-length: " . strlen($sHttpPostString) . "\r\n");
						fputs($rSocketConnection, "User-Agent: MSIE\r\n");
						fputs($rSocketConnection, "Connection: close\r\n\r\n");
						fputs($rSocketConnection, $sHttpPostString);
									
						while(!feof($rSocketConnection)) {
							$sRealTimeResponse .= fgets($rSocketConnection, 1024);
						}
						
					} else {
						echo "There was a problem with the socket.";
					}
					fclose($rSocketConnection);
					echo $sRealTimeResponse;
					
					//record the lead on a report
					//each line of the report will be keyed to offer code: 'accepted','failed','total'
					if(!is_array($aReport[$iOfferCodeIndex])){
						$aReport[$iOfferCodeIndex] = array('accepted' => 0, 'failed' => 0, 'total' => 0);
					}
					
					if(strstr($sRealTimeResponse,'Lead Rejected')){
						//if it's a failure, increment 'failure'
						$aReport[$iOfferCodeIndex]['failed'] += 1;
					} else if (strstr($sRealTimeResponse, 'Lead Accepted')){
						//if it's accepted, increment 'accepted'
						$aReport[$iOfferCodeIndex]['accepted'] += 1;
					}
						
					//increment the total
					$aReport[$iOfferCodeIndex]['total'] += 1;
					
				}
			}
			}
			
			$pop3->delete($i);
			$i--;
			
		} // end of for loop for message no.
	
	// Confirm Delete messages and quit the socket connection
	$pop3->quit();
	$sTempMsg .= "\n";
	
	$sReportBody = "Batch Email API Report\n\nOffer Code\tAccepted Failed\tTotal\n";
	$iTotalAccept = 0;
	$iTotalFail = 0;
	$iTotalTotal = 0;
	foreach($aReport as $k => $v){
		$sReportBody .= "$k\t".$v['accepted']."\t".$v['failed']."\t".$v['total']."\n";
		$iTotalAccept += $v['accepted'];
		$iTotalFail += $v['failed'];
		$iTotalTotal += $v['total'];
	}
	$sReportBody .= "\nTotal\t\t$iTotalAccept\t$iTotalFail\t$iTotalTotal";
	
	mail('spatel@amperemedia.com, skaplan@amperemedia.com','Batch Email API Report -- '.$username,$sReportBody);

cssLogFinish( $iScriptId );

?>
