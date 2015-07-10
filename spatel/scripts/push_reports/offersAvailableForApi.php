<?php

	
include( "/home/scripts/includes/cssLogFunctions.php" );
$iScriptId = cssLogStart( "offersAvailableForApi.php" );
include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");



$sToday = date('Y')."-".date('m')."-".date('d');
$sListOffers = '';

$sListOffers = "
	<html><head>
	<style =\"text/css\">
	TD.small { 
		FONT-FAMILY: Arial, Helvetica, \"Sans Serif\" ; FONT-SIZE: 11px; COLOR: #000000;
	}
	TD.big { 
		FONT-FAMILY: Arial, Helvetica, \"Sans Serif\" ; FONT-SIZE: 13px; COLOR: #000000;
	}
	TD.header {
	FONT-WEIGHT: bold; FONT-SIZE: 10pt; COLOR: #000000; FONT-FAMILY: Arial, Helvetica, \"Sans Serif\"; 
	}
	</style>
	</head>
";

$sListOffers .= "<table border=1 cellpadding='3' align=center><tr><td class=header>Offer Code</td>
				<td class=header>Offer Name</td>
				<td class=header>Effective Rate</td>
				<td class=header>Pay Rate</td>
				<td class=header>Sales Rep</td>
				</tr>";

// Effective Rate - revPerLead
// Pay Rate - actualRevPerLead

$sSelectQuery = "SELECT offers.*, offerCompanies.repDesignated FROM offers LEFT JOIN offerCompanies ON offerCompanies.id = offers.companyId
				WHERE isAvailableForApi = 'Y' 
				AND (mode = 'P' || mode = 'A')
				AND isLive = '1'
				ORDER BY offerCode ASC";
$rSelectResult = dbQuery($sSelectQuery);
$iCount = 0;
while ($sTempRow = dbFetchObject($rSelectResult)) {
	$sUserQuery = "SELECT concat(nbUsers.firstName, ' ', nbUsers.lastName) as name FROM nbUsers where id in (".$sTempRow->repDesignated.")";
	$res = dbQuery($sUserQuery);
	$oUser = dbFetchObject($res);

	$sListOffers .= "<tr><td class=big>".$sTempRow->offerCode."</td>
					<td class=big>".$sTempRow->name."</td>
					<td class=big>".$sTempRow->revPerLead."</td>
					<td class=big>".$sTempRow->actualRevPerLead."</td>
					<td class=big>".$oUser->name."</td>
					</tr>";
	$iCount++;
}

if ($iCount == 0) {
	$sListOffers .= "<tr><td class=big colspan=4>NONE</td></tr>";
}

$sListOffers .= "</table><br><br>";


$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$sHeaders .= "From:nibbles@amperemedia.com";
	
$sEmailQuery = "SELECT * FROM   emailRecipients WHERE  purpose = 'offers available for api'";
$rEmailResult = dbQuery($sEmailQuery);
while ($oEmailRow = dbFetchObject($rEmailResult)) {
	$sRecipients = $oEmailRow->emailRecipients;
}

mail($sRecipients, "Offers Available For API - $sToday", $sListOffers, $sHeaders);
cssLogFinish( $iScriptId );
?>
