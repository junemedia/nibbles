<?php

echo '..........';

exit;



/*********

Script to display Compliance Report

*********/

set_time_limit(3600);

include("../../includes/paths.php");
include("../../libs/dateFunctions.php");
include("POP3-1.0/class.POP3.php3");

$sPageTitle="Compliance Reporting";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	

	// access mailboxes only if different date range is not selected
	if($accessMailBoxes) {
		// Query to get all the test accounts
		
		$accountQuery = "SELECT *
					 FROM   seedEmailAccounts					 
					 WHERE ISPType = 'POP3'		
					 AND ISPCode!='L'
					 ORDER BY ISPName";
		$accountResult = mysql_query($accountQuery);
		
while ($accountRow = mysql_fetch_object($accountResult)) {
	
	// Create new pop3 object to access the mail box from the server
	$pop3 = new POP3();
	
	$username = $accountRow->userName;
	$passwd = $accountRow->passwd;
	$mailServer = $accountRow->mailServer;
	$ISPName = $accountRow->ISPName;
	$ISPCode = $accountRow->ISPCode;
	$ISPType = $accountRow->ISPType;	
	//$codeLegendRow .= $ISPCode . " - ". $ISPName." &nbsp; &nbsp; &nbsp; ";
	
	// Check if POP3 Account and username/password/mailserver is filled
	if ($ISPType == "POP3" && $username != '' && $passwd != '' && $mailServer != '') {
		
		if (! $pop3->connect($mailServer, 110))
		{
			$mailMessage .= "Server: '$ISPName' Ooops $pop3->ERROR <BR>\n";
		}
		
		$msgCount = $pop3->login($username, $passwd);
		
		if ($msgCount == -1)
		{
			$mailMessage .= "<H1>Login Failed: $pop3->ERROR</H1>\n";
			//        exit;
		}
		if (!($pop3->ERROR) && $msgCount < 1)
		{
			$mailMessage .= "Server '$ISPName' Login OK: Inbox EMPTY<BR>\n";
		} else if (! $pop3->ERROR) {
			$mailMessage .= "Server '$ISPName' Login OK: Inbox contains [$msgCount] messages<BR>\n";
		}
		//echo "dfdf".$mailMessage.$msgCount;
		//echo "<BR>count $mailServer $message $msgCount  error".$pop3->ERROR."<BR>";
		// Get the messages one by one

		/*** READ MESSAGE REVERSE, TO PROCESS ONLY REMAINING MESSAGES
		     BREAK FROM THE LOOP WHEN RECORD FOR A MESSAGEID EXISTS IN THE DATABASE ****/
		 if ($ISPCode == 'L') {
		 	$startNum = "1";
		 } else {
		 	$startNum = $msgCount;
		 }
		 $i = $startNum;
		 //echo "messages ".$i;
		 while (($i < $msgCount && $ISPCode == 'L') || ($i > 1 && $ISPCode != 'L')) {
		
		 	//echo "message ".$i;
		 	
			if ($ISPCode == 'L') {
		 		$i++;
		 	} else {
		 		$i--;
		 	}		
			$currMessageId='';
			
			$msgToDisplay = $pop3->get($i);
			
			if ( (! ($msgToDisplay)) or (gettype($msgToDisplay) != "array") )
			{
				echo "<BR>$ISPCode ".$i;
				$message = "oops, $pop3->ERROR<BR>\n";
				
			}
			//Reset  newsLetterCode and newsLetterSentDate
			$newsLetterCode = "";
			$newsLetterSentDate = "";
			
			// Traverse through Message body
			if (is_array($msgToDisplay)) {
			while ( list ( $lineNum,$line ) = each ($msgToDisplay) )
			{
				//echo "$line <BR>\n";
				
				// get the Message's received date in format dd-mon-yyyy from the message body
				if (substr($line, 0, 4) == "Date") {
					$receivedDate = substr($line, 11, 11);
					$receivedYear = substr($receivedDate, 7, 4);
					$receivedMonth = substr($receivedDate, 3, 3);
					for ($j = 0; $j < count($aGblMonthsArray); $j++) {
						if($receivedMonth == $aGblMonthsArray[$j]) {
							$receivedMonth = $j + 1;
							
							if ($receivedMonth < 10) {
								$receivedMonth = "0".$receivedMonth;
							}
						}
					}
					
					$receivedDay = substr($receivedDate, 0, 2);
					$receivedDate = $receivedYear ."-".$receivedMonth."-".$receivedDay;
				}
				
				// New Format: get the line containing NewsLetter code and sent date from the message body
				// New Format: *mf CODE DATE EMAIL mf*
				
				if (stristr($line, "*mf")) {
					$codeLinePartArray = '';
					$newsLetterCodeArray = '';
					$newsLetterCode = '';
					$tempSentDate = '';
					$newsLetterSentDate = '';
					
					$line = ereg_replace("&nbsp;"," ", $line);
					$codeLinePartArray = explode("*mf", strip_tags($line));
					
					$codeLinePartArray = explode("mf*", trim($codeLinePartArray[1]));
					
					$newsLetterCodeArray = explode(" ", strip_tags(trim($codeLinePartArray[0])));
					
					
					$newsLetterCode = trim($newsLetterCodeArray[0]);
					$tempSentDate = trim($newsLetterCodeArray[1]);
					// Convert date in mysql format(yyyy-mm-dd)
					if (strlen($tempSentDate) == 6) {
						// If date is in mmddyy format
						$newsLetterSentDate = substr($tempSentDate,4,2)."-". substr($tempSentDate,0,2)."-". substr($tempSentDate,2,2);
					} else {
						// If  date is in mm-dd-yyyy format
						$newsLetterSentDate = substr($tempSentDate,6,4)."-".substr($tempSentDate,0,2)."-".substr($tempSentDate,3,2);
					}
				}
				
				//get the message ID of this message
				if (strtolower(substr($line, 0,10)) == strtolower("Message-ID")) {
					$messageIdArray = explode("<",$line);
					$messageIdArray = explode(">",$messageIdArray[1]);
					$currMessageId = $messageIdArray[0];
				}
				
			}// end of while loop to traverse through the message body
			}
			
			// Check if received date is before 14 days...
			// If so, then Delete this message from mail box and NewsLetters table
			// else 
			//{
				// check if message with this messageId already exists in newsLetter table
				// If doesn't, make an entry for this message in NewsLetters table
				//if(!($delete))
				
				$selectQuery = "SELECT messageId
    						FROM   newsLetters
    						WHERE  messageId = '$currMessageId'
							AND    ISPCode = '$ISPCode'";
				$selectResult = mysql_query($selectQuery);
				if (mysql_num_rows($selectResult)==0) {
					// if not exists, insert record into newsLetter table
					
					$insertQuery = "INSERT INTO newsLetters(messageId, publicationCode, ISPCode, sentDate, receivedDate)
     							VALUES('$currMessageId', '$newsLetterCode', '$ISPCode', '$newsLetterSentDate', '$receivedDate')";
					$result = mysql_query($insertQuery);
					//echo "<BR>".$insertQuery;
				} 
			//}			
			if (DateDiff("d",mktime(0, 0, 0, $receivedMonth, $receivedDay, $receivedYear), mktime(0, 0, 0, date(m), date(d), date(Y))) > 3
			|| ($newsLetterCode == '' && $newsLetterSentDate == '' ))
			{
				// delete the message with this message number from INBOX
				$pop3->delete($i);
				
			}
			
		} // end of for loop for message no.
	} else {
		// Continue if not pop3 account or no username/password/mailserver
		continue;
	}//
	// Confirm Delete messages and quit the socket connection
	$pop3->quit();
} // end of account query while loop
		
} // end of ViewReport check

	// To display date range of last 7 days
	$currYear = date(Y);
	$currMonth = date(m); //01 to 12
	
	$currDay = date(d); // 01 to 31
	
	// set curr date values to be selected by default
	if (!($save)) {
		$monthTo = $currMonth;
		$dayTo = $currDay;
		$yearTo = $currYear;
		
	}
	// To date in mysql format to find out date before 7 days
	$mySqlDateTo = $yearTo."-".$monthTo."-".$dayTo;
	// to get the date before 7 days for default range of last 7 days
	
	$dateQuery = "SELECT DATE_ADD('".$mySqlDateTo."', INTERVAL -6 DAY) dateFrom";
	
	$dateResult = mysql_query($dateQuery);
	while ($dateRow = mysql_fetch_object($dateResult)) {
		$dateFrom = $dateRow->dateFrom;
	}
	// set default date range options for To date
	$yearFrom = substr($dateFrom,0,4);
	$monthFrom = substr($dateFrom, 5,2);
	$dayFrom = substr($dateFrom, 8,2);
	
	
	$dateFrom = "$yearFrom-$monthFrom-$dayFrom";
	$dateTo = "$yearTo-$monthTo-$dayTo";
	
	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		$value = $i+1;
		if ($value < 10) {
			$value ="0".($value);
		} 
		if ($value == $monthTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		$monthToOptions .= "<option value='$value' $toSel>$aGblMonthsArray[$i]";
	}
	
	// prepare day options for From and To date
	for ($i = 1; $i <= 31; $i++) {
		
		if ($i < 10) {
			$value = "0".$i;
		} else {
			$value = $i;
		}
		
		if ($value == $dayTo) {
			$toSel = "selected";
		} else {
			$toSel = "";
		}
		$dayToOptions .= "<option value='$value' $toSel>$i";
	}
	
	// prepare year options for From and To date
	for ($i = $currYear; $i >= $currYear-2; $i--) {
		
		if ($i == $yearTo) {
			$toSel = "selected";
		} else {
			$toSel ="";
		}
		$yearToOptions .= "<option value='$i' $toSel>$i";
	}

	// To display column headings as the dates of specified date range

$dateHeading = $dateTo;
$newDay = $dayTo;
$d = 0;
$headingRow = "<TR><TD valign=middle rowspan=2><font face=verdana size=1 width=35%><b>Newsletter Title</b></font></td><TD rowspan=2 width=9%><font face=verdana size=1><b>Release Time</b></font></td>";
$dateHeadingRow ="<tr>";
// traverse $d variable from 0 to 6 (for 7 days back)
while ($d < 7) {		
	
	$yearHeading = substr($dateHeading, 0, 4);
	$monthHeading = substr($dateHeading, 5, 2);
	$dayHeading = substr($dateHeading, 8, 2);
	// Store column date in array, in mysql format for date comparison in query
	$dateHeadingArray[$d] = $yearHeading."-".$monthHeading."-".$dayHeading;
	
	//get the weekday to display
	$weekDay = date("l", mktime(0, 0, 0, $monthTo, $newDay, $yearTo));
	$weekDayHeadingArray[$d] = substr($weekDay,0,3);
	
	$d++;
	$dayHeadingRow .= "\n<td valign=top width=9%><font face=verdana size=1><b>$weekDay</b></font></td>";
	$dateHeadingRow .= "\n<td  valign=top><font face=verdana size=1><b>$monthHeading-$dayHeading</b></font></td>";
	//$dateHeading = DateAdd("d",-1,$yearTo."-".$monthTo."-".$dayTo);
	$dateHeading = date("Y-m-d", strtotime("$dateHeading -1 days")); 
	//echo "<BR>$dateHeading";
	//print "<BR>".date("y-m-d", strtotime($yearTo."-".$monthTo."-".$dayTo." - 1 days")); 
	//$dayTo--;
	$newDay = $newDay -1;
	
}
$headingRow = $headingRow . $dayHeadingRow . "</tr>" .$dateHeadingRow . "</tr>";

// Select Query to display newsletter records
$soloColor = "#FFFF66";
$standardColor = "#FFFFFF";
$noScheduleColor = "#99CCFF";
$reportData ="<html><body><table width=80% align=center border=1 cellpaddiing=0 cellspacing=0 bordercolorlight=#0066FF>";
$reportData .= $headingRow;
$pubQuery = "SELECT *
			 FROM   publications
			 ORDER BY  releasePrevNight DESC, releaseTime, publicationName";

	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Report: $pubQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles


$pubResult = mysql_query($pubQuery);
while ($pubRow = mysql_fetch_object($pubResult)) {
	// start new row for new publication code
	$publicationCode = $pubRow->publicationCode;
	$publicationName = $pubRow->publicationName;
	$standardSchedule = $pubRow->standardSchedule;
	$soloSchedule = $pubRow->soloSchedule;
	// set default bgcolor
	$tdBgcolor = $noScheduleColor;
	// Following is to avoid the error and not to use stristr() function on empty string
	if (!($standardSchedule)) {
		$standardSchedule = "sss";
	}
	if (!($soloSchedule)) {
		$soloSchedule = "ooo";
	}
	// reset to count <TD> to 0 for new row
	$c = 0;
	
	$reportData .= "\n<tr bgcolor=white><font face=verdana size=1><Td><font face=verdana size=1>$pubRow->publicationName</font></td><td><font face=verdana size=1>$pubRow->releaseTime</font></td>";		
	$selectQuery = "SELECT  sentDate, date_format(receivedDate, \"%m-%d\") as receivedDate,
								ISPCode
						FROM    newsLetters, publications
						WHERE   newsLetters.publicationCode = '$publicationCode'
						AND   publications.publicationCode = newsLetters.publicationCode
						AND   sentDate between '$dateFrom' and '$dateTo'
						ORDER BY releasePrevNight DESC, releaseTime, publications.publicationName,  sentDate DESC, ISPCode, receivedDate";	
	$result = mysql_query($selectQuery);
	
	if ($result) {
		
		$numRecords = mysql_num_rows($result);
		
		$soloEmail = false;
		//set SoloSchedule color if it's solo email
		if (stristr($publicationName, "p500"))
		{
			$soloEmail = true;
			$tdBgcolor = $soloColor;
		}
		
		if ($numRecords > 0) {

			if (stristr($standardSchedule, $weekDayHeadingArray[$c] ) ||
						stristr($soloSchedule, $weekDayHeadingArray[$c] )) {
				$tdBgcolor = $standardColor;
			}
			
			if ($c == 0) {
				$reportData .= "\n<td align=center bgcolor=$tdBgcolor bordercolordark=$tdBgcolor bordercolorlight=#0066FF><font face=verdana size=1>&nbsp;";
			}
			
			while ($row = mysql_fetch_object($result)) {
				// Loop through remaining sentDate TD in the row
				// Start loop from current TD (sentDate)
				
				for ($d = $c; $d < count($dateHeadingArray); $d++) {
					// If sent date is same as current dateHeading, display ISPCode
					// Otherwise increment TD counter (sentDate counter) and check again through for loop
					if ($dateHeadingArray[$d] == $row->sentDate) {
						$reportData .= $row->ISPCode;
						break;
					} else {
						
						
						$reportData .= "</font></td>";
						if (++$c < count($dateHeadingArray)) {
							
							if ($soloEmail == 'true') {
								$tdBgcolor = $soloColor;
							} else {
								$tdBgcolor = $noScheduleColor;
							}
							
							if (stristr($standardSchedule, $weekDayHeadingArray[$c] ) ||
								stristr($soloSchedule, $weekDayHeadingArray[$c] )) {
								$tdBgcolor = $standardColor;
								
							}
												
								$reportData .= "\n<td align=center bgcolor=$tdBgcolor bordercolordark=$tdBgcolor bordercolorlight=#0066FF><font face=verdana size=1>&nbsp;";							
						}
						
					}
				}
			} // End of while loop
			$c++;
			
		} 
		mysql_free_result($result);
		
	} else {
		echo mysql_error();
	}
	// Fill out remaining <TD> in the row if records over for this particular publication code
	
	for (; $c < count($dateHeadingArray); $c++) {
		if ($soloEmail == 'true') {
			$tdBgcolor = $soloColor;
		} else {
			$tdBgcolor = $noScheduleColor;
		}
		//$tdBgcolor = $noScheduleColor;
		
		if ( stristr($standardSchedule, $weekDayHeadingArray[$c]  ) ||
				(stristr($soloSchedule, $weekDayHeadingArray[$c] ))) {
			$tdBgcolor = $standardColor;
		} 
		
			$reportData .="\n<td align=center bgcolor=$tdBgcolor bordercolordark=$tdBgcolor bordercolorlight=#0066FF><font face=verdana size=1>&nbsp;</font></td>";		
	}
	$reportData .= "</tr>\n";
} // end of while pubRow
$reportData .="</table></body></html>";


// put Legends
if ($codeLegendRow == '') {
	$accountQuery = "SELECT *
						 FROM   seedEmailAccounts
						 WHERE  ISPType = 'pop3'
						 ORDER BY ISPCode";
	$accountResult = mysql_query($accountQuery);
	
	while ($accountRow = mysql_fetch_object($accountResult)) {
		$ISPName = $accountRow->ISPName;
		$ISPCode = $accountRow->ISPCode;
		
		$codeLegendRow .= "<b>".$ISPCode . "</b> - ". $ISPName." &nbsp; &nbsp; &nbsp; ";
	}
}

$reportData .="<br><table width=85% align=center border=0 cellpadding=0>
					<tr><td align=center colspan=2><font face=verdana size=1>$codeLegendRow</font><br><br></td></tr>
					<tr><td width=32%></td><td><font face=verdana size=1><b>White</b> - Scheduled For The Day</font></td></tr>
					<tr><td></td><td><font face=verdana size=1><b>Yellow</b> - Solo Newsletter</font></td></tr>
					<tr><td></td><td><font face=verdana size=1><b>Blue</b> - No Schedule For This Day</font></td></tr>
					</table>";		
	
	$complianceLink = "index.php?iMenuId=$iMenuId";
	$sortLink = "$PHP_SELF?iMenuId=$iMenuId&dayTo=$dayTo&yearTo=$yearTo&monthTo=$monthTo&sSave=View+Report";
	
	// Hidden variables to be passed with Form Submission
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=messageId value='$messageId'>";
		
	include("../../includes/adminHeader.php");	
	
	
	?>
	
	<script language=JavaScript>
				function confirmDelete(form1, mes)
				{
					if(confirm('Are you sure to delete this record ?'))
					{							
						document.form1.elements['delete'].value='Delete';						
						document.form1.elements['messageId'].value=mes;
						document.form1.submit();								
					}
				}						
</script>
	
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $hidden;?>
<input type=hidden name=delete>
<table width=95% cellpadding=5 cellspacing=0 align=center>
<tr><td class=message><?php echo $mailMessage;?></td></tr>
</table>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=5><a href='<?php echo $complianceLink;?>'>Back to Compliance</a></td></tr>
<tr>
<td>7 Days report up to</td>
<td><select name=monthTo><?php echo $monthToOptions;?>
	</select> &nbsp; <select name=dayTo><?php echo $dayToOptions;?>
	</select> &nbsp; <select name=yearTo><?php echo $yearToOptions;?>
	</select> &nbsp; &nbsp; <input type=submit name=save value='View Report'></td>
	<td><a href='<?php echo $sortLink;?>&accessMailBoxes=access'>Access Mailboxes For Latest Report</a></td>
</tr>

</table>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td><?php echo $reportData;?></td></tr>
<tr><td colspan=5><a href='<?php echo $complianceLink;?>'>Back to Compliance</a></td></tr>
</table>


</form>

<?php

	include("../../includes/adminFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}

?>