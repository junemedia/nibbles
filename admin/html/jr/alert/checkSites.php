<?php

/*
Config info in config file.

Schema for table users is; id, first, last, password, url, testString,
alert emails. These are all varchar(255) except for ID. 
Alert emails holds comma separated email addresses.

If ping or test sting fail, alert email sent to each address. Text of
message in config file. Tags to substitute URL, email, first, last,
current date, current time, what failed.

Schema for table history is; id, dateTime, ping, testString. Ping and
testString are Boolean.

Every time script runs it pings the URL and adds and entry to the
history table. True or false depending on if the ping is successful.
Also every time it runs it does an fopen on the url and checks for the
test string. Puts true or false depending on if string is found.

If testString is null then don't run testString test.

*/

// test ping pass 64.132.70.200
// test ping fail 64.132.70.91
// test popularliving .244 


include("config.php");

$sQuery = "SELECT *
		   FROM    users";
$rResult = mysql_query($sQuery);
echo mysql_error();

while ($oRow = mysql_fetch_object($rResult)) {
	$sFirst = $oRow->first;
	$sLast = $oRow->last;	
	$sUrl= $oRow->url;
	$sTestString = $oRow->testString;
	$sAlertEmails = $oRow->alertEmails;
		
	$sEmailTo = substr($sAlertEmails,0,strlen($sAlertEmails)-strrpos(strrev($sAlertEmails),","));
	$sCcTo = substr($sAlertEmails,strlen($sEmailTo));
		
	// ping url
	$sPingReturn = phpPing($sUrl, 2);
	
	
	// send email if test failed
	if ($sPingReturn == "False") {
		
		$sHeaders  = "MIME-Version: 1.0\r\n";
		$sHeaders .= "Content-type: text/plain; charset=iso-8859-1\r\n";
		$sHeaders .= "From:$sGblEmailFrom\r\n";
		$sHeaders .= "cc: ";
		
		$sHeaders .= ", $sCcTo";
	
		$sHeaders .= "\r\n";
		
		
		$sMessage = ereg_replace("\[URL\]", $sUrl, $sGblMessageText);
		$sMessage = ereg_replace("\[FIRST\]", $sFirst, $sMessage);
		$sMessage = ereg_replace("\[LAST\]", $sLast, $sMessage);
		$sMessage = ereg_replace("\[DATE_TIME\]", date("Y-m-d H:i:s"), $sMessage);
		$sMessage = ereg_replace("\[TEST_FAILED\]", "Ping", $sMessage);
		
		$sSubject = ereg_replace("\[URL\]", $sUrl, $sGblEmailSubject);
		
		mail($sEmailTo, $sSubject, $sMessage, $sHeaders);
	
	}
	
	
	$rUrlPageRead = fopen("http://$sUrl/index.php", "r");
					
	$sTempData = '';
				
	if ($rUrlPageRead) {
							
		while (!feof($rUrlPageRead)) {
			$sTempData .= fread($rUrlPageRead, 1024);
		}
							
		fclose($rUrlPageRead);
	}

	if (stristr($sTempData, $sTestString)) {
		$sTestStringReturn = "True";
	} else {
		$sTestStringReturn = "False";
	}
	
	//echo $sTestStringReturn;
	
	// send email if test failed
	if ($sTestStringReturn == "False") {
		
		$sHeaders  = "MIME-Version: 1.0\r\n";
		$sHeaders .= "Content-type: text/plain; charset=iso-8859-1\r\n";
		$sHeaders .= "From:$sGblEmailFrom\r\n";
		$sHeaders .= "cc: ";
		
		$sHeaders .= ", $sCcTo";
	
		$sHeaders .= "\r\n";
		
		
		$sMessage = ereg_replace("\[URL\]", $sUrl, $sGblMessageText);
		$sMessage = ereg_replace("\[FIRST\]", $sFirst, $sMessage);
		$sMessage = ereg_replace("\[LAST\]", $sLast, $sMessage);
		$sMessage = ereg_replace("\[DATE_TIME\]", date("Y-m-d H:i:s"), $sMessage);
		$sMessage = ereg_replace("\[TEST_FAILED\]", "Test String", $sMessage);
		
		$sSubject = ereg_replace("\[URL\]", $sUrl, $sGblEmailSubject);
		
		mail($sEmailTo, $sSubject, $sMessage, $sHeaders);
	
	}
	
	
	// make entry into history
	$sPingInsertQuery = "INSERT INTO history(url, dateTimeTested, ping, testString)
						 VALUES(\"$sUrl\", now(), '$sPingReturn', '$sTestStringReturn')";
	$rPingHistoryResult = mysql_query($sPingInsertQuery);
	echo mysql_error();
	
	
	
}



function phpPing($host, $count) {
$max_count = 10; //maximum count for ping command
$unix      =  1; //set this to 1 if you are on a *unix system      
$windows   =  0; //set this to 1 if you are on a windows system
// -------------------------
// nothing more to be done.
// -------------------------
//globals on or off ?
//$register_globals = (bool) ini_get('register_gobals');
//$system = ini_get('system');
$unix = (bool) $unix;
$win  = (bool)  $windows;


   // over count ?
   If ($count > $max_count) 
   {
      $count = $max_count;
   }
   else 
   {
      // replace bad chars
      $host= preg_replace ("/[^A-Za-z0-9.-]/","",$host);
      $count= preg_replace ("/[^0-9]/","",$count);
      
      $result .= "Ping Output:\r\n\r\n"; 
              
      //check target IP or domain
      if ($unix) 
      {
         system ("ping -c$count -w$count $host", $bPingReturn);
        
      }
      else
      {
         system("ping -n $count $host");
      }
     
    }

    if ($bPingReturn)
    	return "False";
    else 
    	return "True";
    //return $bPingReturn;
}

?>