<?php

//include( "/home/scripts/includes/cssLogFunctions.php" );
//$iScriptId = cssLogStart( "sah.php" );
include("/home/sites/admin.popularliving.com/html/includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");


$sQuery = "select otDataHistory.offerCode, count(*) as count 
	from otDataHistory, userDataHistory
	where otDataHistory.email = userDataHistory.email
	and otDataHistory.offerCode like 'SAH%'
	and otDataHistory.dateTimeAdded > concat(DATE_ADD(curdate(), INTERVAL -4 DAY) , ' ',  '00:00:00')
	and otDataHistory.sendStatus is null
	and otDataHistory.reasonCode != 'npv'
	and otDataHistory.reasonCode != 'tst'
	and otDataHistory.postalVerified = 'V'
	group by offerCode";

$rResult = dbQuery($sQuery);
$sReportContent = "<table border=1><tr><td><b>OfferCode</b></td><td><b>Count</b></td></tr>";

while ($oOffersRow = dbFetchObject($rResult)) {
	$sReportContent .= "<tr><td>$oOffersRow->offerCode</td><td>$oOffersRow->count</td></tr>";
}

$sReportContent .= "</table>";
dbFreeResult($rResult);

$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$sHeaders .= "From:nibbles@amperemedia.com\r\n";

//mail('josh@myfree.com,pschechter@amperemedia.com', "SAH", $sReportContent, $sHeaders);
mail('spatel@amperemedia.com', "SAH", $sReportContent, $sHeaders);

//cssLogFinish( $iScriptId );

?>
