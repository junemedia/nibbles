<?php

include("/home/sites/admin.popularliving.com/html/includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");


// make entry into cron script status table

$sEmailContent = '';

$iCurrYear = date('Y');
$iCurrMonth = date('m');
$iCurrDay = date('d');

$iCurrHH = date('H');
$iCurrMM = date('i');
$iCurrSS = date('s');

$sRunDateAndTime = "$iCurrMonth-$iCurrDay-$iCurrYear $iCurrHH:$iCurrMM:$iCurrSS";


// get the offers having inactive date withing a week
$sOffersQuery = "SELECT *
				 FROM   offers
				 WHERE  date_format(inactiveDateTime,'%Y-%m-%d') BETWEEN CURRENT_DATE AND DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)
			 	 AND    mode = 'A' 
				 AND	isLive = '1' ";
$rOffersResult = dbQuery($sOffersQuery);
echo dbError();
if ( dbNumRows($rOffersResult) > 0) {
	while ($oOffersRow = dbFetchObject($rOffersResult)) {
		$sOfferCode = $oOffersRow->offerCode;
		$sInactiveDateTime = $oOffersRow->inactiveDateTime;
		
		$sEmailContent .= "OfferCode: $sOfferCode   -   Inactive Date:$sInactiveDateTime\r\n";
		
		
	}
	
	
	$sEmailContent = "Following offers' inactive dates are within a week\r\n\r\n".$sEmailContent;
	
	
	$sHeaders  = "MIME-Version: 1.0\r\n";
	$sHeaders .= "Content-type: text/plain; charset=iso-8859-1\r\n";
	$sHeaders .= "From:nibbles@amperemedia.com\r\n";
	
	
	$sEmailQuery = "SELECT *
			   FROM   emailRecipients
			   WHERE  purpose = 'offers inactive alert'";
	$rEmailResult = dbQuery($sEmailQuery);
	echo dbError();
	while ($oEmailRow = dbFetchObject($rEmailResult)) {
		$sEmailTo = $oEmailRow->emailRecipients;
	}
	
		
	$sSubject = "Offers Inactive Alert - $sRunDateAndTime";
	mail($sEmailTo, $sSubject, $sEmailContent, $sHeaders);


}


?>
