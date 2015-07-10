<?php


include( "/home/scripts/includes/cssLogFunctions.php" );
$iScriptId = cssLogStart( "fraudAddressAlert.php" );

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

mysql_connect ($reportingHost, $reportingUser, $reportingPass);
mysql_select_db ($reportingDbase);

$sFrom = DateAdd("d", -8, date('Y')."-".date('m')."-".date('d'));
$sTo = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));

$sToday = date('Y')."-".date('m')."-".date('d');

// init reports
$sInternalReport = "
	<html><head>
	<style =\"text/css\">
	TD.small { 
		FONT-FAMILY: Arial, Helvetica, \"Sans Serif\" ; FONT-SIZE: 9px; COLOR: #000000;
	}
	TD.big { 
		FONT-FAMILY: Arial, Helvetica, \"Sans Serif\" ; FONT-SIZE: 12px; COLOR: #000000;
	}
	TD.header {
	FONT-WEIGHT: bold; FONT-SIZE: 10pt; COLOR: #000000; FONT-FAMILY: Arial, Helvetica, \"Sans Serif\"; 
	}
	</style>
	</head>
"; // internal report
$clientReports = array(); // reports for clients

// Read script configuration from database
$configSql = "SELECT alertEmailName, fraudTriggerGroupSize, alertTriggerPercent, enabledStatus 
		FROM alertEmails 
		WHERE alertEmailName='fraudAddressAlert'";
$rConfigResult = dbQuery( $configSql );
$oConfigRow = dbFetchObject( $rConfigResult );

if( strtolower($oConfigRow->enabledStatus) == 'd' ) {
	cssLogFinish( $iScriptId );
	die();
}

// ****************************** Get totals *********************************

// get total leads for each source code
$sSourceQuery = "SELECT sourceCode, subSourceCode, count(email) as count
		FROM otDataHistory
	 	WHERE dateTimeAdded BETWEEN '$sFrom 00:00:00' AND '$sTo 23:59:59'
	 	GROUP BY sourceCode, subSourceCode";
$rSourceResult = dbQuery($sSourceQuery);
while( $oRow = dbFetchObject( $rSourceResult ) ) {
	$grandTotal += $oRow->count;
	$sourceCodeTotals[$oRow->sourceCode] += $oRow->count;
	$subSourceCodeTotals[$oRow->subSourceCode] += $oRow->count;
}

$sInternalReport .= "<table><tr><td class=big>Report Date:  $sFrom - $sTo</td></tr>";
$sInternalReport .= "<tr><td class=big>Total leads:  $grandTotal</td></tr>";


// *****************************   Leads per Address   ****************************************

// group userData that share same value for given field(s)
// determine which values appears to have characteristics of fraud
// for each potentially fraudulent value, determine how many leads are potentially fraudulent
// count the potentially fraudulent leads with each source code and keep a running total
// update the grand total potential fraud

$addressFlagged = 0;
$addressSourceFlagged = array();

// locate all potentially fraudulent addresses
$sAddressAllQuery = "SELECT address, zip, count(*) as count
		FROM userDataHistory
	 	WHERE dateTimeAdded BETWEEN '$sFrom 00:00:00' AND '$sTo 23:59:59'
		GROUP BY address, zip
		HAVING count >= " . $oConfigRow->fraudTriggerGroupSize . "
		ORDER BY count desc";

// Count total leads with (p)fraud addresses
// Count subtotals broken down by sourcecode
$rAddressAllResult = dbQuery($sAddressAllQuery);
while( $oAllRow = dbFetchObject( $rAddressAllResult ) ) {
	// For each address & zip, find the associated email in userDataHistory
	// group by source code and count the number of times the email appears
	$leadSourceQuery = "SELECT sourceCode, subSourceCode, count(o.email) as count from otDataHistory o, userDataHistory u
		WHERE o.dateTimeAdded BETWEEN '$sFrom 00:00:00' AND '$sTo 23:59:59'
		AND u.address = '" . $oAllRow->address . "' AND u.zip='" . $oAllRow->zip . "'
		AND o.email = u.email
		GROUP BY sourceCode, subSourceCode";
	// get total of flagged user records for each source code
	$rLeadSourceResult = dbQuery( $leadSourceQuery );
	while( $oSourceRow = dbFetchObject( $rLeadSourceResult )) {
		// increment the flagged user count for the source and subsource
		$addressSourceFlagged[$oSourceRow->sourceCode][flagged] += $oSourceRow->count;
		$addressSourceFlagged[$oSourceRow->sourceCode][subSourceCodes][$oSourceRow->subSourceCode] += $oSourceRow->count;
		// keep a running total of flagged users across all source codes
		$addressFlagged += $oSourceRow->count;
	}
}

// loop through source data and determine the pct for each

// total pct flagged = flagged / total
$sInternalReport .= "<tr><td class=big>Total leads with fraudulent Address characteristics:  $addressFlagged / $grandTotal = " . 100 * number_format( $addressFlagged / $grandTotal, 4 ). "</td></tr>";
$sInternalReport .= "<tr><td class=big>Source codes flagged due to high percentage of leads with like Address:</td></tr></table><br><br>";

// sort by source code
$sortedAddressSourceFlagged = ksort($addressSourceFlagged);


$sInternalReport .= "<table border=1><tr>
			<td class=header><b>&nbsp;&nbsp;Source Code&nbsp;&nbsp;</b></td>
			<td class=header><b>&nbsp;&nbsp;Fraud/Total&nbsp;&nbsp;</b></td>
			<td class=header><b>&nbsp;&nbsp;Percent&nbsp;&nbsp;</b></td></tr>";


// For each source code, pct. flagged = flagged divided by total
// add to our report
// if pct is greater than trigger pct: determine the client and add to the client's report
foreach( $addressSourceFlagged as $source => $sourceData ) {
	if ($sourceData[flagged] >= $oConfigRow->fraudTriggerGroupSize) {
		$sInternalReport .= "<tr>";
		$fraudPct = 100 * number_format( $sourceData[flagged] / $sourceCodeTotals[$source], 4 );
		if( $fraudPct > $oConfigRow->alertTriggerPercent ) {
			if( $source == '' ) {
				$sInternalReport .= '<td class=big>(none)</td>' . "<td class=big>" . "$sourceData[flagged] / $sourceCodeTotals[$source]" . "</td><td class=big>" . 100 * number_format( $sourceData[flagged] / $sourceCodeTotals[$source], 4 ) . "</td>";
			} else {
				$sInternalReport .= "<td class=big>".$source . "</td><td class=big>" . "$sourceData[flagged] / $sourceCodeTotals[$source]" . "</td><td class=big>" . 100 * number_format( $sourceData[flagged] / $sourceCodeTotals[$source], 4 ) . "</td>";
			}
			// source code and partner ID are in links table
			// also first 3 from source code are the "code" in partnerCompanies
			// id in partnerCompanies is partnerId in partnerContacts
			// group all partners' reports together
			
			$code = substr($source, 0, 3);
		
			// get partner company name
			$partnerSql = "SELECT pcm.companyName, pcn.partnerId
				FROM partnerCompanies pcm, partnerContacts pcn
				WHERE pcm.code = '$code' and pcn.partnerId = pcm.id";
	
			$rPartnerResult = dbQuery( $partnerSql );
			$oRow = dbFetchObject( $rPartnerResult );
			$clientReports[$oRow->partnerId][companyName] = $oRow->companyName;
			
			// get partner contact emails
			$partnerSql = "SELECT pcn.email
				FROM partnerCompanies pcm, partnerContacts pcn
				WHERE pcm.code = '$code' and pcn.partnerId = pcm.id";
		
			$rPartnerResult = dbQuery( $partnerSql );
			while($oEmailRow = dbFetchObject( $rPartnerResult ) ) {
				$clientReports[$oRow->partnerId][emails][] = $oEmailRow->email;
			}
		
			// append to report for the given partner
			if( $source == '' ) {
				$clientReports[$oRow->partnerId][report] .= '(none)' . "\t\t" . "$sourceData[flagged] / $sourceCodeTotals[$source]" . "\t\t" . 100 * number_format( $sourceData[flagged] / $sourceCodeTotals[$source], 4 ) . " %\n";
			} else {
				$clientReports[$oRow->partnerId][report] .= $source . "\t\t" . "$sourceData[flagged] / $sourceCodeTotals[$source]" . "\t\t" . 100 * number_format( $sourceData[flagged] / $sourceCodeTotals[$source], 4 ) . " %\n";
			}
			
			// append subSourceCode data to client report
			$subSourceData = $sourceData[subSourceCodes];
			if( sizeof($subSourceData) > 0 ) {
				if( implode( '', array_keys($subSourceData)) != '' ) {
					$clientReports[$oRow->partnerId][report] .= "subSourceCode Data\n";
					foreach( $subSourceData as $subSourceCode => $flagged ) {
						// append to report for the given partner
						if( $subSourceCode != '' ) {
							$clientReports[$oRow->partnerId][report] .= "\t" . $subSourceCode . "\t" . $flagged . " / $subSourceCodeTotals[$subSourceCode]" . "\t" . 100 * number_format( $flagged / $subSourceCodeTotals[$subSourceCode], 4 ) . " %\n";
						}
					}
				}
			}
		}
		$sInternalReport .= "</tr>";
	}
}

if ($addressFlagged == 0) {
	$sInternalReport .= "<tr><td colspan=3 class=big align=center>NONE</td></tr>";
}



// Loop through report data, format into an email, and send out.
if( strtolower($oConfigRow->enabledStatus) == 'c' || strtolower($oConfigRow->enabledStatus) == 'b') {
	foreach( $clientReports as $id => $clientReport ) {
		$sHeaders  = "MIME-Version: 1.0\r\n";
		$sHeaders .= "Content-type: text/plain; charset=iso-8859-1\r\n";
		$sHeaders .= "From:nibbles@amperemedia.com\r\n";
		$sHeaders .= "cc: ";
		
		$emailTo = '';
		$clientReportBody = '';
		// if source code doesnt have any emails associated, don't try to send
		if( $clientReport[emails] ) {
			$sReportTitle = $clientReport[companyName] . " Potentially Fraudulent Address Stats " . $sToday;
			
			$emailTo = implode(',', array_unique($clientReport[emails]));
			$clientReportBody .= "\n";
			$clientReportBody .= "$sReportTitle\n";
			$clientReportBody .= "Sent to: " . $emailTo . "\n\n";
			$clientReportBody .= "Source code\t\tFraud/Total\t\tPercent\n";
			$clientReportBody .= $clientReport[report] . "\n";
			$clientReportBody .= "---------------------------------------------------------\n";
			
			$clientReportsForUs .= $clientReportBody . "\n";
			$sSubject = $sReportTitle;
			
			//$emailTo = 'spatel@amperemedia.com';
			mail($emailTo, $sSubject, $clientReportBody, $sHeaders);
		}
	}
	
	$sInternalReport .= "<br><br><br><br>\r\n\r\n\r\n::::: Partner reports :::::\r\n<br><br>";
	$sInternalReport .= $clientReportsForUs;
}



//Add Notes:
$sNotes = "<tr><td class=small>
This script runs every Monday at 7AM for the past 7 days.  
First, it runs query # 1 and get list of addresses and zip codes.  Then for
each address and zip code, it runs query # 2 to find out how many 
leads were collected with address/zip by source code.
Then if number of leads collected is at least $oConfigRow->fraudTriggerGroupSize 
then it adds source code to fraud report.  
Also if percent flagged is greater than $oConfigRow->alertTriggerPercent, then it
add the source code to the report.  Total is the number of gross leads collected within past 
7 days with that source code.
<br><br>
<b>Query 1:</b>
SELECT address, zip, count(*) as count
FROM userDataHistory
WHERE dateTimeAdded BETWEEN '$sFrom 00:00:00' AND '$sTo 23:59:59'
GROUP BY address, zip
HAVING count >= $oConfigRow->fraudTriggerGroupSize
ORDER BY count desc
<br><br>
<b>Query 2:</b>
SELECT sourceCode, subSourceCode, count(o.email) as count from otDataHistory o, userDataHistory u
WHERE o.dateTimeAdded BETWEEN '$sFrom 00:00:00' AND '$sTo 23:59:59'
AND u.address = '[EACH ADDRESS]' AND u.zip='[EACH ZIP]'
AND o.email = u.email
GROUP BY sourceCode, subSourceCode
<td></tr>";

$sInternalReport = $sInternalReport."</table><br><br><table>
			<tr><td colspan=3 class=small><b>Notes:</b><br></td></tr>
			<tr><td colspan=3 class=small>Fraud: Number of leads collected with same address and zip</td></tr>
			<tr><td colspan=3 class=small>Total: Total number of leads collected with this source code.</td></tr>
			<tr><td colspan=3 class=small>Percent: Fraud Count divide by Total Count</td></tr>
			<tr><td colspan=3 class=small><br><td></tr>
			<tr><td colspan=3 class=small>Defined Fraud Group Size: $oConfigRow->fraudTriggerGroupSize<td></tr>
			<tr><td colspan=3 class=small>Defined Alert Trigger Percent: $oConfigRow->alertTriggerPercent<td></tr>
			<tr><td colspan=3 class=small><br><td></tr>
			$sNotes
			</table>";


if( strtolower($oConfigRow->enabledStatus) == 'i' || strtolower($oConfigRow->enabledStatus) == 'b') {
	$sHeaders  = "MIME-Version: 1.0\r\n";
	$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$sHeaders .= "From:nibbles@amperemedia.com";
	
	$sEmailQuery = "SELECT * FROM   emailRecipients WHERE  purpose = 'fraud report'";
	$rEmailResult = dbQuery($sEmailQuery);
	while ($oEmailRow = dbFetchObject($rEmailResult)) {
		$sRecipients = $oEmailRow->emailRecipients;
	}
	
	$sSubject = "ALL PARTNERS Potentially Fraudulent Address Stats " . $sToday;
	mail($sRecipients, $sSubject, $sInternalReport, $sHeaders);
}

cssLogFinish( $iScriptId );

?>
