<?php

include("../../includes/paths.php");
session_start();

$iId = trim($_GET['id']);
$sCompanyName = trim($_GET['cname']);
if (!(ctype_digit($iId))) { $iId = ''; }
$sContent = '';

if (trim($_GET['type']) == 'pub' && $iId !='') {
	// get partner companies info
	$sQuery = "SELECT partnerContacts.*, partnerCompanies.companyName
				FROM partnerCompanies,partnerContacts 
				WHERE partnerCompanies.id = partnerContacts.partnerId
				AND partnerContacts.partnerId='$iId' LIMIT 1";
	$rResult = mysql_query($sQuery);
	while ($oRow = mysql_fetch_object($rResult)) {
		$sContent .= "Name: $oRow->contact
Company: $oRow->companyName
Address: $oRow->address1, $oRow->address2
City/State/Zip: $oRow->city, $oRow->state $oRow->zip
Phone: $oRow->phoneNo
Fax: $oRow->faxNo
Email: $oRow->email";
	}
}


if (trim($_GET['type']) == 'deliverTo' && $iId !='') {
	$sQuery = "SELECT email FROM nbUsers WHERE id='$iId' LIMIT 1";
	$rResult = mysql_query($sQuery);
	while ($oRow = mysql_fetch_object($rResult)) {
		$sContent = $oRow->email;
	}
}



if (trim($_GET['type']) == 'adv' && $iId !='') {
	// get offer companies info
	$sQuery = "SELECT offerCompanyContacts.*, offerCompanies.companyName 
				FROM offerCompanies,offerCompanyContacts 
				WHERE offerCompanies.id = offerCompanyContacts.companyId
				AND offerCompanyContacts.companyId='$iId' LIMIT 1";
	$rResult = mysql_query($sQuery);
	while ($oRow = mysql_fetch_object($rResult)) {
		$sContent .= "Name: $oRow->contact
Company: $oRow->companyName
Address: $oRow->address, $oRow->address2
City/State/Zip: $oRow->city, $oRow->state $oRow->zip
Phone: $oRow->phoneNo
Fax: $oRow->faxNo
Email: $oRow->email";
	}
}


if (trim($_GET['type']) == 'cn' && $sCompanyName != '') {
	$sPartnerQuery = "SELECT partnerContacts.*, partnerCompanies.companyName
				FROM partnerCompanies,partnerContacts 
				WHERE partnerCompanies.id = partnerContacts.partnerId
				AND companyName = \"$sCompanyName\"
				LIMIT 1";
	$rPartnerResult = mysql_query($sPartnerQuery);
	if (dbNumRows($rPartnerResult) == 1) {
		while ($oRow = mysql_fetch_object($rPartnerResult)) {
		$sContent .= "Name: $oRow->contact
Company: $oRow->companyName
Address: $oRow->address1, $oRow->address2
City/State/Zip: $oRow->city, $oRow->state $oRow->zip
Phone: $oRow->phoneNo
Fax: $oRow->faxNo
Email: $oRow->email";
		}
	} else {
		$sOfferQuery = "SELECT offerCompanyContacts.*, offerCompanies.companyName 
				FROM offerCompanies,offerCompanyContacts 
				WHERE offerCompanies.id = offerCompanyContacts.companyId
				AND companyName = \"$sCompanyName\"
				LIMIT 1";
		$rOfferResult = mysql_query($sOfferQuery);
		if (dbNumRows($rOfferResult) == 1) {
			while ($oRow = mysql_fetch_object($rOfferResult)) {
		$sContent .= "Name: $oRow->contact
Company: $oRow->companyName
Address: $oRow->address, $oRow->address2
City/State/Zip: $oRow->city, $oRow->state $oRow->zip
Phone: $oRow->phoneNo
Fax: $oRow->faxNo
Email: $oRow->email";
			}
		}
	}
}



echo $sContent;

?>
