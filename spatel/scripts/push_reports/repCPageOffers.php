<?php

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

//get a list of offer codes that the 'USER_FORM_' tag AND have entries in the
//offerPage2Validation table 

$sOffersSQL = "
select offerCode, name , offerType
from offers 
where ((page2Template like '%USER_FORM_%'
and page2Template not like '%<span style=\"display:none\">\r\n<!--[USER_FORM_C_CENTER]-->\r\n</span>%')
or offerType = 'CR' or offerType = 'CRP')
and mode = 'A'
order by offerCode";

$rOffersResult = dbQuery($sOffersSQL);

echo (dbError() ? __line__.dbError() : '');

$aOffers = array();
$aTypes = array();
while($oOffers = dbFetchObject($rOffersResult)){
	$aOffers[$oOffers->offerCode] = $oOffers->name;
	switch($oOffers->offerType){	
	case 'CR':
	case 'CRP':
	case 'CWH':	
		$aTypes[$oOffers->offerCode] = $oOffers->offerType;
		break;
	case '':
	default:
		$aTypes[$oOffers->offerCode] = '&nbsp;';
		break;
	}
}

print_r($aOffers);

$moreSQL = "SELECT distinct offerCode FROM offerPage2Validation UNION SELECT distinct offerCode FROM offerPage2Options";
$rMoreResults = dbQuery($moreSQL);

$aValidations = array();
while($oMoreOfferCodes = dbFetchObject($rMoreResults)){
	array_push($aValidations, $oMoreOfferCodes->offerCode);

}

//then, format, and send as an email

//exit();

$out = "Offers - C Page Compliant<br><table border=1>";
foreach($aOffers as $key => $value){
	$out .= "<tr><td>$key</td><td>$value</td><td>".$aTypes[$key]."</td>";
	if(!in_array($key, $aValidations)){
		$out .= "<td>*</td>";
	} else {
		$out .= "<td>&nbsp;</td>";
	}
	$out .= "</tr>\n";
}
$out .= "</table><br> <b>*</b> indicates that the offer has no validation checks on offerPage2Validation or offerPage2Options. ";

$sHeaders  = "MIME-Version: 1.0\r\n";
$sHeaders .= "Content-type: text/html; charset=iso-8859-1\r\n";
$sHeaders .= "From:nibbles@amperemedia.com\r\n";

mail('sales@amperemedia.com, skaplan@amperemedia.com, acorrea@silvercarrot.com', 'C Page Compliant Offers Report', $out,$sHeaders);

?>
