<?php


include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");
include("$sGblLibsPath/stringFunctions.php");

$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
$sFirstDate = substr($sYesterday,0,8).'01';

$sNotes = '';

// ******************************
// Get Daily Email Count		*
// ******************************
$sReportQuery1 = "SELECT DISTINCT email FROM joinEmailSub 
WHERE dateTimeAdded between '$sYesterday 00:00:00' and '$sYesterday 23:59:59'
UNION
SELECT DISTINCT email FROM otDataHistory
WHERE dateTimeAdded between '$sYesterday 00:00:00' and '$sYesterday 23:59:59'";
$rResult = dbQuery($sReportQuery1);
echo dbError();
$iDailyEmailCount = mysql_num_rows($rResult);

$sNotes .= "<b>Daily Email Capture Count Queries:</b><br>$sReportQuery1<br><br>";




// ******************************
// Get Monthly Email Count		*
// ******************************
$sReportQuery2 = "SELECT DISTINCT email FROM joinEmailSub 
WHERE dateTimeAdded between '$sFirstDate 00:00:00' and '$sYesterday 23:59:59'
UNION
SELECT DISTINCT email FROM otDataHistory
WHERE dateTimeAdded between '$sFirstDate 00:00:00' and '$sYesterday 23:59:59'";
$rResult1 = dbQuery($sReportQuery2);
echo dbError();
$iMonthlyEmailCount = mysql_num_rows($rResult1);

$sNotes .= "<b>Monthly Email Capture Count Queries:</b><br>$sReportQuery2<br><br>";




// *******************************
// Get Daily Email Count Exc API *
// *******************************
$sReportQuery3 = "SELECT DISTINCT email FROM joinEmailSub 
WHERE dateTimeAdded between '$sYesterday 00:00:00' and '$sYesterday 23:59:59'
UNION
SELECT DISTINCT email FROM otDataHistory
WHERE dateTimeAdded between '$sYesterday 00:00:00' and '$sYesterday 23:59:59'
AND pageId != '238'";
$rResult3 = dbQuery($sReportQuery3);
echo dbError();
$iExcApiDailyEmailCount = mysql_num_rows($rResult3);

$sNotes .= "<b>Daily Email Capture Count Queries (API Excluded):</b><br>$sReportQuery3<br><br>";


/**

New additions, 2008-08-22, 

TLD
email
domains
src

*/

//select the count frome above, with the TLD table, where email like '%'.TLD
$sReportQuery3 = "SELECT DISTINCT email FROM joinEmailSub, excludeTLDsDataSales
WHERE dateTimeAdded between '$sYesterday 00:00:00' and '$sYesterday 23:59:59'
AND upper(email) like upper(concat('%',TLDs))
UNION
SELECT DISTINCT email FROM otDataHistory, excludeTLDsDataSales
WHERE dateTimeAdded between '$sYesterday 00:00:00' and '$sYesterday 23:59:59'
AND upper(email) like upper(concat('%',TLDs))
AND pageId != '238'";
$rResult3 = dbQuery($sReportQuery3);
echo dbError();
$iExcApiTLDScrub = mysql_num_rows($rResult3);

$aExcludeEmails = array();
while($oResult = dbFetchObject($rResult3)){
	array_push($aExcludeEmails, $oResult->email);
}

$sNotes .= "<b>Daily Email Capture, API Excluded, banned TLD scrub count:</b><br>$sReportQuery3<br><br>";

//email
$sReportQuery3 = "SELECT DISTINCT email FROM joinEmailSub, excludeDomainsDataSales
WHERE dateTimeAdded between '$sYesterday 00:00:00' and '$sYesterday 23:59:59'
AND upper(email) like upper(concat('%',excludeDomainsDataSales.domain))
AND email NOT IN ('".join("','",$aExcludeEmails)."')
UNION
SELECT DISTINCT email FROM otDataHistory, excludeDomainsDataSales
WHERE dateTimeAdded between '$sYesterday 00:00:00' and '$sYesterday 23:59:59'
AND upper(email) like upper(concat('%',excludeDomainsDataSales.domain))
AND email NOT IN ('".join("','",$aExcludeEmails)."')
AND pageId != '238'";
$rResult3 = dbQuery($sReportQuery3);
echo dbError();
$iExcApiDomainScrub = mysql_num_rows($rResult3);

while($oResult = dbFetchObject($rResult3)){
	if(!in_array($oResult->email, $aExcludeEmails)){
		array_push($aExcludeEmails, $oResult->email);
	}
}

$sNotes .= "<b>Daily Email Capture, API Excluded, banned domain scrub count:</b><br>$sReportQuery3<br><br>";


//email
$sReportQuery3 = "SELECT DISTINCT joinEmailSub.email FROM joinEmailSub, excludeEmailDataSales
WHERE dateTimeAdded between '$sYesterday 00:00:00' and '$sYesterday 23:59:59'
AND joinEmailSub.email = excludeEmailDataSales.email
AND joinEmailSub.email NOT IN ('".join("','",$aExcludeEmails)."')
UNION
SELECT DISTINCT otDataHistory.email FROM otDataHistory, excludeEmailDataSales
WHERE dateTimeAdded between '$sYesterday 00:00:00' and '$sYesterday 23:59:59'
AND otDataHistory.email = excludeEmailDataSales.email
AND otDataHistory.email NOT IN ('".join("','",$aExcludeEmails)."')
AND pageId != '238'";
$rResult3 = dbQuery($sReportQuery3);
echo dbError();
$iExcApiEmailScrub = mysql_num_rows($rResult3);

while($oResult = dbFetchObject($rResult3)){
	if(!in_array($oResult->email, $aExcludeEmails)){
		array_push($aExcludeEmails, $oResult->email);
	}
}

$sNotes .= "<b>Daily Email Capture, API Excluded, banned email scrub count:</b><br>$sReportQuery3<br><br>";


//email
$sReportQuery3 = "SELECT DISTINCT email FROM joinEmailSub, links, partnerCompanies
WHERE dateTimeAdded between '$sYesterday 00:00:00' and '$sYesterday 23:59:59'
AND links.partnerId = partnerCompanies.id 
AND partnerCompanies.excludeDataSale = '1'
AND joinEmailSub.sourceCode = links.sourceCode
AND joinEmailSub.email NOT IN ('".join("','",$aExcludeEmails)."')
UNION
SELECT DISTINCT email FROM otDataHistory, links, partnerCompanies
WHERE dateTimeAdded between '$sYesterday 00:00:00' and '$sYesterday 23:59:59'
AND links.partnerId = partnerCompanies.id 
AND partnerCompanies.excludeDataSale = '1'
AND otDataHistory.sourceCode = links.sourceCode
AND otDataHistory.email NOT IN ('".join("','",$aExcludeEmails)."')
AND pageId != '238'";
$rResult3 = dbQuery($sReportQuery3);
echo dbError();
$iExcApiSrcScrub = mysql_num_rows($rResult3);

while($oResult = dbFetchObject($rResult3)){
	if(!in_array($oResult->email, $aExcludeEmails)){
		array_push($aExcludeEmails, $oResult->email);
	}
}

$sNotes .= "<b>Daily Email Capture, API Excluded, banned domain scrub count:</b><br>$sReportQuery3<br><br>";

//top domain breakdowns
$sDropQuery = "DROP TABLE IF EXISTS tempDomains";
$rResult3 = dbQuery($sDropQuery);

$sCreateQuery = "CREATE TABLE tempDomains 
SELECT DISTINCT email FROM joinEmailSub
WHERE dateTimeAdded between '$sYesterday 00:00:00' and '$sYesterday 23:59:59'
AND joinEmailSub.email NOT IN ('".join("','",$aExcludeEmails)."')
UNION
SELECT DISTINCT email FROM otDataHistory
WHERE dateTimeAdded between '$sYesterday 00:00:00' and '$sYesterday 23:59:59'
AND otDataHistory.email NOT IN ('".join("','",$aExcludeEmails)."')
AND pageId != '238'";
$rResult3 = dbQuery($sCreateQuery);

$sReportQuery3 = "SELECT substring(email, (locate('@',email)+1)) as domain, count(email) as count FROM tempDomains
GROUP BY domain
ORDER BY count DESC
LIMIT 10";
$rResult3 = dbQuery($sReportQuery3);
echo dbError();
$aDomainCounts = array();
while($oResult = dbFetchObject($rResult3)){
	$aDomainCounts[$oResult->domain] = $oResult->count;
}

$sDropQuery = "DROP TABLE IF EXISTS tempDomains";
$rResult3 = dbQuery($sDropQuery);


$sNotes .= "<b>Daily Email Capture, API Excluded, banned domain scrub count:</b><br>$sReportQuery3<br><br>";




// ******************************
// Get Daily Members Count		*
// ******************************
$sReportQuery5 = "SELECT count(DISTINCT email) AS count
				FROM nibbles.otDataHistory
				WHERE dateTimeAdded BETWEEN '$sYesterday 00:00:00' AND '$sYesterday 23:59:59'";
$rReportResult = dbQuery($sReportQuery5);
echo dbError();
while ($oRow = dbFetchObject($rReportResult)) {
	$iDailyMembersCount = $oRow->count;
}

$sNotes .= "<b>Daily Members Count Queries:</b><br>$sReportQuery5<br><br>";

// ******************************
// Get Monthly Members Count	*
// ******************************
$sReportQuery6 = "SELECT count(DISTINCT email) AS count
				FROM nibbles.otDataHistory
				WHERE dateTimeAdded BETWEEN '$sFirstDate 00:00:00' AND '$sYesterday 23:59:59'";
$rReportResult = dbQuery($sReportQuery6);
echo dbError();
while ($oRow = dbFetchObject($rReportResult)) {
	$iMonthlyMembersCount = $oRow->count;
}


$sNotes .= "<b>Monthly Members Count Queries:</b><br>$sReportQuery6<br><br>";


$sReportContact = "<table width=80% align=center>
<tr><td><font face=verdana size=2>Nibbles EmailCaptures for $sYesterday: </font></td>
	<td align=left><font face=verdana size=2>$iDailyEmailCount</font></td></tr>
<tr><td><font face=verdana size=2>Nibbles EmailCaptures for $sFirstDate - $sYesterday: </font></td>
	<td align=left><font face=verdana size=2>$iMonthlyEmailCount</font></td></tr>
	
<tr><td colspan=2>&nbsp;</td></tr>
<tr><td><font face=verdana size=2>Nibbles EmailCaptures for $sYesterday (API Excluded): </font></td>
	<td align=left><font face=verdana size=2>$iExcApiDailyEmailCount</font></td></tr>
<tr><td><font face=verdana size=2>TLD Scrub for $sYesterday (API Excluded): </font></td>
	<td align=left><font face=verdana size=2>$iExcApiTLDScrub</font></td></tr>
<tr><td><font face=verdana size=2>Domain Scrub for $sYesterday (API Excluded): </font></td>
	<td align=left><font face=verdana size=2>$iExcApiDomainScrub</font></td></tr>
<tr><td><font face=verdana size=2>Email Scrub for $sYesterday (API Excluded): </font></td>
	<td align=left><font face=verdana size=2>$iExcApiEmailScrub</font></td></tr>
<tr><td><font face=verdana size=2>Source Code Scrub for $sYesterday (API Excluded): </font></td>
	<td align=left><font face=verdana size=2>$iExcApiSrcScrub</font></td></tr>
<tr><td><font face=verdana size=2>Net EmailCaptures for $sYesterday (API Excluded): </font></td>
	<td align=left><font face=verdana size=2>".($iExcApiDailyEmailCount - ($iExcApiTLDScrub + $iExcApiDomainScrub + $iExcApiEmailScrub + $iExcApiSrcScrub))."</font></td></tr>
<tr><td colspan=2>&nbsp;</td></tr>

<tr></tr>

<tr><td><font face=verdana size=2>Top 10 Email Domains:</font></td></tr>
";

foreach($aDomainCounts as $sDomain => $iCount){
	$sReportContact .= "
	<tr><td><font face=verdana size=2>$sDomain</font></td>
		<td align=left><font face=verdana size=2>$iCount</font></td></tr>";
}

$sReportContact .= "<tr><td colspan=2>&nbsp;</td></tr>

<tr><td><font face=verdana size=2>Nibbles Members for $sYesterday: </font></td>
	<td align=left><font face=verdana size=2>$iDailyMembersCount</font></td></tr>
<tr><td><font face=verdana size=2>Nibbles Members for $sFirstDate - $sYesterday: </font></td>
	<td align=left><font face=verdana size=2>$iMonthlyMembersCount</font></td></tr>
</table>

<br><br>
<table width=80% align=center>
<tr><td><font face=verdana size=1>
Notes:<br>
<b>Nibbles EmailCaptures for $sYesterday: </b>Count of distinct emails from otDataHistory and joinEmailSub for $sYesterday.<br>
<b>Nibbles EmailCaptures for $sFirstDate - $sYesterday: </b>Count of distinct emails from otDataHistory and joinEmailSub for $sFirstDate - $sYesterday.<br>

<b>Nibbles Members for $sYesterday: </b>Count of distinct emails from otDataHistory for $sYesterday.<br>
<b>Nibbles Members for $sFirstDate - $sYesterday: </b>Count of distinct emails from otDataHistory for $sFirstDate - $sYesterday.<br>
<br>
<br>Queries: <br>
$sNotes
</font></td></tr>
</table>";




$sHeaders = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$sHeaders .= "From:nibbles@amperemedia.com\r\n";


$sEmailQuery = "SELECT *  FROM   emailRecipients
			   WHERE  purpose = 'email captures and members report' LIMIT 1";
$rEmailResult = dbQuery($sEmailQuery);
while ($oEmailRow = dbFetchObject($rEmailResult)) {
	$sSubject = "Email Captures & Members Report - ".date('Y-m-d H:i:s');
	mail($oEmailRow->emailRecipients, $sSubject, $sReportContact, $sHeaders);
}



?>
