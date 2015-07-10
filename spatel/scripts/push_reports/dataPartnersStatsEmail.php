<?php

	include( "/home/scripts/includes/cssLogFunctions.php" );
	$iScriptId = cssLogStart( "dataPartnersStatsEmail.php" );
	
	include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
	include("$sGblLibsPath/dateFunctions.php");
	include("$sGblLibsPath/stringFunctions.php");
	
	
	$s31DaysBack = strftime ("%Y-%m-%d", strtotime("-31 day"));
	$s31DaysBack = str_replace('/','-',$s31DaysBack);
	$sFrom = $s31DaysBack." 00:00:00";
	$sTo = $s31DaysBack." 23:59:59";

	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');
	$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear";
	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	$sFirstDate = date('Y')."-".date('m')."-01";
	$sReportContent = "<html><body><table width=50% border=1 cellpadding=0 cellspacing=0 bordercolorlight=#0066FF>";
	$sReportContent .= "<tr><td><font face=verdana size=1><b>Date</b></font></td>
						<td><font face=verdana size=1><b>Vendor</b></font></td>
						<td><font face=verdana size=1><b>Daily Count</b></font></td>
						<td><font face=verdana size=1><b>Monthly Count</b></font></td></tr>";
	
	$sReportQuery = "SELECT date, script, count
					FROM nibbles_datafeed.dataSentStats
   				 	WHERE date BETWEEN '$sYesterday' AND '$sYesterday'
   				 	ORDER BY date";
	$rReportResult = dbQuery($sReportQuery);
	echo dbError();
	while ($oReportRow = dbFetchObject($rReportResult)) {	
		$sEndDate = $oReportRow->date;
		$sFirstDate = substr($sEndDate,0,8) . "01";
		$sMonthlyQuery = "SELECT sum(count) as mcount
					FROM nibbles_datafeed.dataSentStats
					WHERE date BETWEEN '$sFirstDate' AND '$sEndDate'
					AND script = '$oReportRow->script'";
		$rMonthlyResult = mysql_query($sMonthlyQuery);
		while ($oDataRow = dbFetchObject($rMonthlyResult)) {
			$sReportContent .= "\n<tr bgcolor=white><td><font face=verdana size=1>".$oReportRow->date."</font></td>
								<td><font face=verdana size=1>".$oReportRow->script."</font></td>
								<td><font face=verdana size=1>".$oReportRow->count."</font></td>
								<td><font face=verdana size=1>".$oDataRow->mcount."</font></td></tr>";
			$iTotalCount += $oReportRow->count;
		}
	}
	$sReportContent .= "\n<tr bgcolor=white><td><font face=verdana size=1><b>Total:</b></font></td><td>&nbsp;</td><td><font face=verdana size=1><b>$iTotalCount</b></font></td><td>&nbsp;</td><td></tr>";

	
	$sReportContent .= "</table><BR><BR><table width=80% align=center><tr><Td><font face=verdana size=1><b>Notes:</b> 
	The data is as of midnight prior day.<br>
	Daily and Monthly count for Datran and Grid should be same.<br>
	Daily and Monthly count for Ennovate and Hydra should be same.<br>
	Monthly Count:  Monthly Count includes from $sFirstDate to $sEndDate.<br>
	<br><br>
	
	
	<b>Datran:</b>  This script runs every 15 minutes.  It collects data from current tables (userData and otData).  
	It collects first, last, email, address, address2, city, state, zip, phone, salutation, and ip address from 
	current tables.  It filters out excluded source codes, excluded domains, excluded email addresses, excluded TLDs, and API leads.  
	It also collects email and remoteIp from joinEmailSub table and for each email it gets data from userData table.
	If no userData found, then it only sends email and remoteIp to client.
	It sends data to client using Real Time Form Post method to ampmed.superautoresponders.com/c.aspx.<br>
		<b>Query 1: </b>SELECT userData.*, otData.remoteIp
			FROM userData, otData 
			WHERE userData.dateTimeAdded > 'DateTimeFromLogFile'
			AND userData.email = otData.email
			AND otData.pageId != '238'
			sUserDataExcludeSourceCode
			sUserDataExcludeDomain
			sUserDataExcludeEmail
			sExcludeTLDs
			ORDER BY userData.dateTimeAdded ASC
	<br><b>Query 2: </b>SELECT email, remoteIp
			FROM joinEmailSub
			WHERE dateTimeAdded > 'DateTimeFromLogFile'
			sUserDataExcludeSourceCode
			sUserDataExcludeDomain
			sUserDataExcludeEmail
			sExcludeTLDsJoin
	<br>Replace Following In Above Queries: sUserDataExcludeSourceCode, sUserDataExcludeDomain, 
	sUserDataExcludeEmail, DateTimeFromLogFile, sExcludeTLDs, sExcludeTLDsJoin with actual value.<br><br>

	
	<b>Elysium:</b>  This script runs everyday at 5:30 am.  It collects yesterday's data from history tables (userDataHistory
	 and otDataHistory).  It collects email, dateTimeAdded, and ip address.  
	 It filters out excluded source codes, excluded domains, excluded email addresses, excluded TLDs, and API leads.  
	 It also collects data from joinEmailSub table.  
	 It creates a .csv file in /home/elysium/ folder.  Then it also ftp copy of that file to host: 198.65.133.49.<br>
	<b>Query 1: </b>SELECT userDataHistory.email, userDataHistory.dateTimeAdded, otDataHistory.remoteIp, otDataHistory.sourceCode
			FROM userDataHistory, otDataHistory 
			WHERE userDataHistory.dateTimeAdded BETWEEN '$sYesterday 00:00:00' AND '$sYesterday 23:59:59'
			AND userDataHistory.email = otDataHistory.email
			AND otDataHistory.excludeDataSale != '1'
			AND otDataHistory.pageId != '238'
 	<br><b>Query 2: </b>SELECT email,dateTimeAdded,remoteIp,sourceCode
			FROM joinEmailSub
			WHERE dateTimeAdded BETWEEN '$sYesterday 00:00:00' AND '$sYesterday 23:59:59'
			sFilter
	<br><br>
	
	
	<b>FrontLine:</b>  This script runs everyday at 6:30 am.  It collects data from history tables 
	(userDataHistory and otDataHistory) that are 31 days old.  It collects email, first, last, address, address2,
	city, state, zip, gender, birthdate, phone, ip, and dateTimeAdded.  
	It also collects email, sourceCode, remoteIp, and dateTimeAdded from joinEmailSub table and then gets user data from history table.  
	If no userData found, then it only sends email, dateTimeAdded, and remoteIp to client.
	It creates a .txt file in /home/frontLine/ folder.  Then it also ftp copy of that file to server: frontlinedirectinc.com.  
	It filters out excluded source codes, excluded domains, excluded email addresses, excluded TLD's, and API leads.  
	Also we do not send any data without ip address.<br>
		<b>Query 1: </b>SELECT userDataHistory.*, otDataHistory.remoteIp ,otDataHistory.sourceCode
			FROM userDataHistory, otDataHistory 
			WHERE userDataHistory.dateTimeAdded BETWEEN '$sFrom' AND '$sTo'
			AND userDataHistory.email = otDataHistory.email
			AND otDataHistory.excludeDataSale != '1'
			AND otDataHistory.pageId != '238'
 	<br><b>Query 2: </b>SELECT email
			FROM joinEmailSub
			WHERE dateTimeAdded BETWEEN '$sFrom' AND '$sTo'
			sFilter
	<br><br>
	
	
	<b>18th Story:</b>  This script runs everyday at 6:25 am.  It collects data from history tables 
	(userDataHistory and otDataHistory) that are 31 days old.  It collects email, first, last, address, address2,
	city, state, zip, gender, ip, and dateTimeAdded.  We only send aol.com, cs.com, wmconnect.com, netscape.net, and netscape.com.  
	It also collects email, remoteIp, and dateTimeAdded from joinEmailSub table and then gets user data from history table.  
	If no userData found, then it only sends email, dateTimeAdded, and remoteIp to client.
	It creates a .csv file in /home/18thStory/ folder.  Then it also ftp copy of that file to server: largf.fatcow.com.  
	It filters out excluded source codes, excluded domains, excluded email addresses, excluded TLD's, and API leads.  
	Also we do not send any data without ip address.<br>
		<b>Query 1: </b>SELECT userDataHistory.*, otDataHistory.remoteIp as tempRemoteIp, otDataHistory.sourceCode
			FROM userDataHistory, otDataHistory 
			WHERE userDataHistory.dateTimeAdded BETWEEN '$sFrom' AND '$sTo'
			AND userDataHistory.email = otDataHistory.email
			AND (userDataHistory.email LIKE '%@aol.com' 
				OR userDataHistory.email LIKE '%@cs.com' 
				OR userDataHistory.email LIKE '%@wmconnect.com' 
				OR userDataHistory.email LIKE '%@netscape.net' 
				OR userDataHistory.email LIKE '%@netscape.com' )
			AND otDataHistory.excludeDataSale != '1'
			AND otDataHistory.pageId != '238'
 	<br><b>Query 2: </b>SELECT email,sourceCode,remoteIp,dateTimeAdded
		FROM joinEmailSub WHERE dateTimeAdded BETWEEN '$sFrom' AND '$sTo'
		AND (email LIKE '%@aol.com' 
				OR email LIKE '%@cs.com' 
				OR email LIKE '%@wmconnect.com' 
				OR email LIKE '%@netscape.net' 
				OR email LIKE '%@netscape.com' )
	<br><br>
	
	
	
	<b>Tranzact:</b>  This script runs everyday at 6:15 am.  It collects data from history tables 
	(userDataHistory and otDataHistory) that are 31 days old.  It collects email, first, last, address, address2,
	city, state, zip, ip, and dateTimeAdded.  We only send aol.com entries.  
	It also collects email, remoteIp, and dateTimeAdded from joinEmailSub table and then gets user data from history table.  
	If no userData found, then it only sends email, dateTimeAdded, and remoteIp to client.
	It creates a .csv file in /home/tranzact/ folder.  Then it also ftp copy of that file to server: 66.111.219.170.  
	It filters out excluded source codes, excluded domains, excluded email addresses, excluded TLD's, and API leads.  
	Also we do not send any data without ip address.<br>
		<b>Query 1: </b>SELECT userDataHistory.*, otDataHistory.remoteIp as tempRemoteIp, otDataHistory.sourceCode
			FROM userDataHistory, otDataHistory 
			WHERE userDataHistory.dateTimeAdded BETWEEN '$sFrom' AND '$sTo'
			AND userDataHistory.email = otDataHistory.email
			AND userDataHistory.email LIKE '%@aol.com' 
			AND otDataHistory.excludeDataSale != '1'
			AND otDataHistory.pageId != '238'
 	<br><b>Query 2: </b>SELECT email,sourceCode,remoteIp,dateTimeAdded
		FROM joinEmailSub WHERE dateTimeAdded BETWEEN '$sFrom' AND '$sTo'
		AND email LIKE '%@aol.com'
	<br><br>
	
	
	
	
	<b>Grid:</b>  This script runs every 15 minutes.  It collects data from current tables (userData and otData).  
	It collects email and dateTimeAdded from current tables.  It filters out excluded source codes, excluded domains, excluded email addresses, excluded TLDs, and API leads.  
	It also collects email and dateTimeAdded from joinEmailSub table. It sends data to client using SOAP Call.<br>
		<b>Query 1: </b>SELECT userData.*, otData.remoteIp
			FROM userData, otData 
			WHERE userData.dateTimeAdded > 'DateTimeFromLogFile'
			AND userData.email = otData.email
			AND otData.pageId != '238'
			sUserDataExcludeSourceCode
			sUserDataExcludeDomain
			sUserDataExcludeEmail
			sExcludeTLDs
			ORDER BY userData.dateTimeAdded ASC
	<br><b>Query 2: </b>SELECT email, remoteIp
			FROM joinEmailSub
			WHERE dateTimeAdded > 'DateTimeFromLogFile'
			sUserDataExcludeSourceCode
			sUserDataExcludeDomain
			sUserDataExcludeEmail
			sExcludeTLDsJoin
	<br>Replace Following In Above Queries: sUserDataExcludeSourceCode, sUserDataExcludeDomain, 
	sUserDataExcludeEmail, DateTimeFromLogFile, sExcludeTLDs, sExcludeTLDsJoin with actual value.<br><br>
	
	
	
	<b>Partner Data:</b>  This script runs everyday at 6:33 am.  It collects data from history tables (userDataHistory and otDataHistory) that are 31 days old.  
	It collects email, first, last, address, city, state, zip, ip, and dateTimeAdded. 
	It also collects email, remoteIp, and dateTimeAdded from joinEmailSub table and then gets user data from history table.  
	If no userData found, then it only sends email, dateTimeAdded, and remoteIp to client.
	It creates a .txt file in /home/partnerData/ folder.  Then it also ftp copy of that file to server: 71.5.84.3.  
	It filters out excluded source codes, excluded domains, excluded email addresses, excluded TLD's, and API leads.<br>
		<b>Query 1: </b>SELECT userDataHistory.email, first, last, address, city, state, zip, otDataHistory.remoteIp, otDataHistory.dateTimeAdded, sourceCode
			FROM userDataHistory, otDataHistory 
			WHERE userDataHistory.dateTimeAdded BETWEEN '$sFrom 00:00:00' AND '$sTo 23:59:59'
			AND userDataHistory.email = otDataHistory.email
			AND otDataHistory.excludeDataSale != '1'
			AND otDataHistory.pageId != '238'
 	<br><b>Query 2: </b>SELECT email,sourceCode,remoteIp,dateTimeAdded
		FROM joinEmailSub WHERE dateTimeAdded BETWEEN '$sFrom' AND '$sTo'
	<br><br>
	
	
	
	<b>RPM (Rocket Power):</b>  This script runs everyday at 6:10 am.  It collects data from history tables (userDataHistory and otDataHistory) that are 31 days old.  
	It collects email, first, last, address, city, state, zip, ip, phoneNo, and dateTimeAdded. 
	It also collects email, remoteIp, and dateTimeAdded from joinEmailSub table and then gets user data from history table.  
	If no userData found, then it only sends email, dateTimeAdded, and remoteIp to client.
	It creates a .txt file in /home/rpmLiveFeed/ folder.  Then it also ftp copy of that file to server: ftp.rpmlivefeed.com.  
	It filters out excluded source codes, excluded domains, excluded email addresses, excluded TLD's, and API leads.
	Also it filters out if following fields are blank: email, ip, and dateTimeAdded.<br>
		<b>Query 1: </b>SELECT userDataHistory.email, userDataHistory.dateTimeAdded, otDataHistory.remoteIp,
			first,last,address,city,state,zip,phoneNo,sourceCode
			FROM userDataHistory, otDataHistory 
			WHERE userDataHistory.dateTimeAdded BETWEEN '$sFrom' AND '$sTo'
			AND userDataHistory.email = otDataHistory.email
			AND otDataHistory.excludeDataSale != '1'
			AND otDataHistory.pageId != '238'
 	<br><b>Query 2: </b>SELECT email,sourceCode,remoteIp,dateTimeAdded
		FROM joinEmailSub WHERE dateTimeAdded BETWEEN '$sFrom' AND '$sTo'
	<br><br>
	
	
	
	<b>Seguai:</b>  This script runs everyday at 6:40 am.  It collects data from history tables (userDataHistory and otDataHistory) that are 31 days old.  
	It collects email, first, last, ip, and dateTimeAdded. 
	It also collects email, remoteIp, and dateTimeAdded from joinEmailSub table and then gets user data from history table.  
	If no userData found, then it only sends email, dateTimeAdded, and remoteIp to client.
	It creates a .txt file in /home/seguai/ folder.  Then it also ftp copy of that file to server: 68.165.241.166.  
	It filters out excluded source codes, excluded domains, excluded email addresses, excluded TLD's, API leads, and blank ip.
	It only sends hotmail and msn data.<br>
		<b>Query 1: </b>SELECT userDataHistory.email, first,last,'popularliving.com',
			otDataHistory.remoteIp, userDataHistory.dateTimeAdded,otDataHistory.sourceCode
			FROM userDataHistory, otDataHistory 
			WHERE userDataHistory.dateTimeAdded BETWEEN '$sFrom' AND '$sTo'
			AND userDataHistory.email = otDataHistory.email
			AND (userDataHistory.email LIKE '%@hotmail.com' 
				OR userDataHistory.email LIKE '%@msn.com')
			AND otDataHistory.excludeDataSale != '1'
			AND otDataHistory.pageId != '238'
 	<br><b>Query 2: </b>SELECT * FROM joinEmailSub WHERE dateTimeAdded BETWEEN '$sFrom' AND '$sTo'
				AND (email LIKE '%@hotmail.com' OR email LIKE '%@msn.com')
	<br><br>
	
	</font></td></tr></table>";
	
	
	$sHeaders  = "MIME-Version: 1.0\r\n";
	$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$sHeaders .= "From:nibbles@amperemedia.com\r\n";
	
	$sEmailQuery = "SELECT *
				   FROM   emailRecipients
				   WHERE  purpose = 'data partners stats'";
	$rEmailResult = dbQuery($sEmailQuery);
	echo dbError();
	while ($oEmailRow = dbFetchObject($rEmailResult)) {
		$sRecipients = $oEmailRow->emailRecipients;
	}
	
	//$sRecipients = 'spatel@amperemedia.com';
	$sSubject = "Data Partners Stats Report - $sRunDateAndTime";
	mail($sRecipients, $sSubject, $sReportContent, $sHeaders);

	cssLogFinish( $iScriptId );

?>
