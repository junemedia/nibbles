<?php

ini_set('max_execution_time', 5000);

/*********
Script to Process and Send Leads
**********/

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
include_once("$sGblLibsPath/stringFunctions.php");
include_once( "/home/scripts/includes/cssLogFunctions.php" );
$iScriptId = cssLogStart( "resendReport.php" );

mysql_select_db('nibbles');

$iRealTimeDaysBack = "1";

$iPvThreshold = 90;
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

session_start();

$sPageTitle = "Nibbles - Process Leads";
$sendmailtobill = 'spatel@amperemedia.com';

	//$sOtDataTable = "process_leads.otDataHistoryWorking";
	//$sUserDataTable = "process_leads.userDataHistoryWorking";
	$sOtDataTable = "otDataHistory";
	$sUserDataTable = "userDataHistory";
	
	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');
	
	$sRunDate = "$iCurrMonth-$iCurrDay-$iCurrYear";

	// get today's date for leads folder name
	$sToday = $iCurrYear.$iCurrMonth.$iCurrDay;
	$iJulianDays = date('z',strtotime($sToday)) + 1;

	// get today's leads folder
	$sTodaysLeadsFolder = "$sGblLeadFilesPath/$sToday";

	// set the reRun folder
	$sRerunFolder = "$sGblLeadFilesPath/reRun";

	// set today's reRun folder
	$sTodaysRerunFolder = "$sRerunFolder/$sToday";

	
	$aCountsArray = array('offerCode'=>array(),'counts'=>array(),'offerName'=>array());
	
	
	// Start of getting count for they host - 4/13/06 - samir
	$sThContent = "<table width=30% align=left border=1 cellpaddiing=0 cellspacing=0 bordercolorlight=#0066FF>
							<tr><td><font face=verdana size=1><b>They Host Offer</b></font></td>
							<td><font face=verdana size=1><b>Offer Name</b></font></td>
							<td align=right><font face=verdana size=1><b>Count</b></font></td></tr>";
	$iThTotalLeads = 0;
	$sThCountQuery = "SELECT offerCode, count(email) AS counts
					FROM   $sOtDataTable
					WHERE  date_format(dateTimeSent, '%Y-%m-%d') BETWEEN date_add(CURRENT_DATE, INTERVAL -$iRealTimeDaysBack DAY)
	 					AND date_add(CURRENT_DATE, INTERVAL -$iRealTimeDaysBack DAY)
					AND    isOpenTheyHost = 'Y'
					GROUP BY offerCode";
	$rThCountResult = dbQuery($sThCountQuery);
	while ($oThRow = dbFetchObject($rThCountResult)) {
		$sThOfferName = '';
		$sGetOfferName = "SELECT name FROM offers WHERE offerCode='$oThRow->offerCode'";
		$rGetOfferName = dbQuery($sGetOfferName);
		while ($oThNameRow = dbFetchObject($rGetOfferName)) {
			$sThOfferName = $oThNameRow->name;
		}

		$sThContent .= "<tr><td><font face=verdana size=1>".$oThRow->offerCode."</font></td>
			<td><font face=verdana size=1>$sThOfferName</font></td>
			<td align=right><font face=verdana size=1>".$oThRow->counts."</font></td></tr>";
		$iThTotalLeads += $oThRow->counts;
	}
	$sThContent .= "<tr><td><font face=verdana size=1><b>Total</b></font></td><td>&nbsp;</td>
					<td align=right><font face=verdana size=1><b>$iThTotalLeads</b></font></td></tr>
					</table>";
	// End of getting count for they host - 4/13/06 - samir
	

	

	$sCountsEmailContent = "<html><body>
							<table><tr><td>
							<table width=30% align=left border=1 cellpaddiing=0 cellspacing=0 bordercolorlight=#0066FF>
							<tr><td><font face=verdana size=1><b>Offer Code</b></font></td>
							<td><font face=verdana size=1><b>Offer Name</b></font></td>
							<td align=right><font face=verdana size=1><b>Leads Count</b></font></td></tr>";

	$sHeaders  = "MIME-Version: 1.0\r\n";
	$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$sHeaders .= "From:nibbles@amperemedia.com\r\n";
	$sHeaders .= "cc: ";

	$sEmailQuery = "SELECT *
		   FROM   emailRecipients
			WHERE  purpose = 'lead counts'";
	$rEmailResult = dbQuery($sEmailQuery);
	echo dbError();
	while ($oEmailRow = dbFetchObject($rEmailResult)) {
		$sRecipients = $oEmailRow->emailRecipients;
		//$sRecipients = 'spatel@amperemedia.com';
	}
		
	echo '.';
	flush();
	ob_flush();

	if (!($sEmailTo)) {
		$sLeadCountsEmailTo = substr($sRecipients,0,strlen($sRecipients)-strrpos(strrev($sRecipients),","));
	}

	$sCcTo = substr($sRecipients,strlen($sLeadCountsEmailTo));
	$sHeaders .= " $sCcTo";
	$sHeaders .= "\r\n";

	// get lead counts of all the offers except real time and form post
	$sLeadCountsQuery = "SELECT offerCode, count(dateTimeSent) AS counts
						 FROM   $sOtDataTable 
						 WHERE  date_format(dateTimeSent, '%m-%d-%Y') = '$sRunDate'
						 AND	sendStatus = 'S'
						 AND	howSent NOT IN ('rtfpp', 'rtfpg', 'rte', 'dbfpg', 'dbfpp')
						 AND    offerCode NOT LIKE 'SAMPLE%'
						 GROUP BY offerCode";
						//echo "process start time : "
	$startTime = date("U");
	echo "leads counts query1:<br>".__LINE__."&nbsp; &nbsp; start time : ".$startTime;
	$rLeadCountsResult = dbQuery($sLeadCountsQuery);
	$endTime = date("U");
	echo "<br> &nbsp; &nbsp; query: ".$sLeadCountsQuery."<br>";
	echo "<br>".__LINE__."&nbsp; &nbsp; end time: ".$endTime. " (".($endTime - $startTime).")<br><br>";
	flush();
	ob_flush();
						//echo "process end time : "
	echo dbError();
	$iTotalLeads = 0;
	$i = 0;
		
	echo '.';
	flush();
	ob_flush();

	echo "\n".__line__."hey hey";
	
	while ($oLeadCountsRow = dbFetchObject($rLeadCountsResult)) {
		$sOfferName = '';
		$sGetOfferName = "SELECT name FROM offers WHERE offerCode='$oLeadCountsRow->offerCode'";
		$rGetOfferName = dbQuery($sGetOfferName);
		while ($oNameRow = dbFetchObject($rGetOfferName)) {
			$sOfferName = $oNameRow->name;
		}
		
		$aCountsArray['offerCode'][$i] = $oLeadCountsRow->offerCode;
		$aCountsArray['offerName'][$i] = $sOfferName;
		$aCountsArray['counts'][$i] = $oLeadCountsRow->counts;
		echo $aCountsArray['offerCode'][$i]." ".$aCountsArray['offerName'][$i]." ".$aCountsArray['counts'][$i]."\n";
		$i++;
	}

	echo "\n".__line__."my my";
	
	// get real time offers counts
	$sRealTimeLeadCountsQuery = "SELECT offerCode, count(email) AS counts
								 FROM   $sOtDataTable
								 WHERE  date_format(dateTimeSent, '%Y-%m-%d') BETWEEN date_add(CURRENT_DATE, INTERVAL -$iRealTimeDaysBack DAY)
	 					 		 AND date_add(CURRENT_DATE, INTERVAL -$iRealTimeDaysBack DAY) 
								 AND    sendStatus = 'S'
								 AND    howSent IN ('rtfpp', 'rtfpg', 'rte')
								 GROUP BY offerCode";
						//echo "process start time : "
	$startTime = date("U");
	echo "real time lead counts query:<br>".__LINE__."&nbsp; &nbsp; start time : ".$startTime;
	$rRealTimeLeadCountsResult = dbQuery($sRealTimeLeadCountsQuery);
	$endTime = date("U");
	echo "<br> &nbsp; &nbsp; query: ".$sRealTimeLeadCountsQuery."<br>";
	echo "<br>".__LINE__."&nbsp; &nbsp; end time: ".$endTime. " (".($endTime - $startTime).")<br><br>";
	flush();
	ob_flush();
						//echo "process end time : "
	echo dbError();
	while ($oRealTimeLeadCountsRow = dbFetchObject($rRealTimeLeadCountsResult)) {
		$sOfferName = '';
		$sGetOfferName = "SELECT name FROM offers WHERE offerCode='$oRealTimeLeadCountsRow->offerCode'";
		$rGetOfferName = dbQuery($sGetOfferName);
		while ($oNameRow = dbFetchObject($rGetOfferName)) {
			$sOfferName = $oNameRow->name;
		}
		
		$aCountsArray['offerCode'][$i] = $oRealTimeLeadCountsRow->offerCode;
		$aCountsArray['offerName'][$i] = $sOfferName;
		$aCountsArray['counts'][$i] = $oRealTimeLeadCountsRow->counts;
		$i++;
	}

	echo '.';
	flush();
	ob_flush();

	if ( count($aCountsArray) > 0) {
		array_multisort($aCountsArray['offerCode'],SORT_ASC, $aCountsArray['offerName'], $aCountsArray['counts']);
	}

	$iChicagoCount = 0;
	$iNewYorkCount = 0;
	$sChicagoContent = '';
	$sNewYorkContent = '';
	for ($i = 0; $i<count($aCountsArray['offerCode']);$i++) {
		
		$sCheckQuery = "SELECT offerCompanies.repDesignated
					FROM   offers, offerCompanies
					WHERE  offers.companyId = offerCompanies.id
					AND    offers.offerCode = '".$aCountsArray['offerCode'][$i]."' LIMIT 1";
		$rCheckResult = dbQuery($sCheckQuery);
		while ($oCheckRow = dbFetchObject($rCheckResult)) {
			$sLocQuery = "SELECT * FROM nbUsers WHERE id IN (".$oCheckRow->repDesignated.")
						AND officeLocation = 'NY'";
			$rLocResult = dbQuery($sLocQuery);
			if (dbNumRows($rLocResult) > 0) {
				// new york offer
				$sNewYorkContent .= "<tr><td><font face=verdana size=1>".$aCountsArray['offerCode'][$i]."</font></td>
								<td><font face=verdana size=1>".$aCountsArray['offerName'][$i]."</font></td>
								<td align=right><font face=verdana size=1>".$aCountsArray['counts'][$i]."</font></td></tr>";
				
				$iNewYorkCount += $aCountsArray['counts'][$i];
			} else {
				// northbrook offer
				$sChicagoContent .= "<tr><td><font face=verdana size=1>".$aCountsArray['offerCode'][$i]."</font></td>
								<td><font face=verdana size=1>".$aCountsArray['offerName'][$i]."</font></td>
								<td align=right><font face=verdana size=1>".$aCountsArray['counts'][$i]."</font></td></tr>";
				
				$iChicagoCount += $aCountsArray['counts'][$i];
			}
		}

		$iTotalLeads += $aCountsArray['counts'][$i];
		echo '.';
		flush();
		ob_flush();
	}
	
	
	$sCountsEmailContent .= "<tr><td colspan=3></td></tr><tr><td colspan=3></td></tr>
					<tr><td colspan=3 align=center><font face=verdana size=1><b>Northbrook Offers</b></td></tr>
					$sChicagoContent
					<tr><td><font face=verdana size=1><b>Northbrook Total</b></font></td><td>&nbsp;</td>
					<td align=right><font face=verdana size=1><b>$iChicagoCount</b></font></td></tr>
					
					
					<tr><td colspan=3></td></tr><tr><td colspan=3></td></tr>
					<tr><td colspan=3 align=center><font face=verdana size=1><b>New York Offers</b></td></tr>
					$sNewYorkContent
					<tr><td><font face=verdana size=1><b>New York Total</b></font></td><td>&nbsp;</td>
					<td align=right><font face=verdana size=1><b>$iNewYorkCount</b></font></td></tr>
					<tr><td colspan=3></td></tr><tr><td colspan=3></td></tr>";

	$sCountsEmailContent .= "<tr><td><font face=verdana size=1><b>Total</b></font></td><td>&nbsp;</td>
							<td align=right><font face=verdana size=1><b>$iTotalLeads</b></font></td></tr>";
	$sCountsEmailContent .= "</table></td></tr><tr><td>&nbsp;</td></tr><tr><td>$sThContent</td></tr></table></body></html>";
	

	$sLeadsCountEmailSubject = "Lead Counts - $sRunDate";
	mail($sLeadCountsEmailTo, $sLeadsCountEmailSubject, $sCountsEmailContent, $sHeaders);


	


?>
