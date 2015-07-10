<?php


include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblLibsPath/pdfFunctions.php");
include("$sGblIncludePath/reportInclude.php");

session_start();

$sPageTitle = "Nibbles IO Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];


	if($iId) {
		$sGetIo = "SELECT * FROM io WHERE id = '$iId'";
		$rResult = dbQuery($sGetIo);
		while ($oRow = dbFetchObject($rResult)) {
			$iId = $oRow->id;
			$sIoNum = $oRow->ioNum;
			$sMediaType =$oRow->mediaType;
			$iRepId = $oRow->repId;
			$iPublisherId = $oRow->publisherId;
			$iAdvertiserId = $oRow->advertiserId;
			$sAgencyCompanyName = $oRow->agencyName;
			$sContactInfo = $oRow->contactInfo;
			$sSecondAddress = $sContactInfo;
			$sBilling = $oRow->billing;
			$sFourthAddress = $sBilling;
			$iCampaignId = $oRow->campaignId;
			$iCampaignTypeId = $oRow->campaignType;
			$iRateStructureId = $oRow->rateStructureId;
			$iVolume = $oRow->volume;
			$fUnitCost = $oRow->cost;
			$sMaterialDue = $oRow->materialsDue;
			$sMaterialDueDate = $oRow->materialsDueDate;
			$sMaterialTo = $oRow->materialsTo;
			$fAmountDue = $oRow->amountDue;
			$sAdditionalTerms = $oRow->additionalTerms;
			$iPartnerId = $oRow->partnerId;
			$sDateTimeAdded = $oRow->dateGenerated;
			
			
			$sStartDate = substr($oRow->startDate,5,2).'/'.substr($oRow->startDate,8,2).'/'.substr($oRow->startDate,0,4);
			$sEndDate = $oRow->endDate;
			
			
			
			
			// remove headers
			$sSecondAddress = str_replace('Name: ','',$sSecondAddress);
			$sSecondAddress = str_replace('Company: ','',$sSecondAddress);
			$sSecondAddress = str_replace('Address: ','',$sSecondAddress);
			$sSecondAddress = str_replace('Address2: ','',$sSecondAddress);
			$sSecondAddress = str_replace('City/State/Zip: ','',$sSecondAddress);
			$sSecondAddress = str_replace('Phone: ','P: ',$sSecondAddress);
			$sSecondAddress = str_replace('Fax: ','F: ',$sSecondAddress);
			$sSecondAddress = str_replace('Email: ','',$sSecondAddress);
			
			$sFourthAddress = str_replace('Name: ','',$sFourthAddress);
			$sFourthAddress = str_replace('Company: ','',$sFourthAddress);
			$sFourthAddress = str_replace('Address: ','',$sFourthAddress);
			$sFourthAddress = str_replace('Address2: ','',$sFourthAddress);
			$sFourthAddress = str_replace('City/State/Zip: ','',$sFourthAddress);
			$sFourthAddress = str_replace('Phone: ','P: ',$sFourthAddress);
			$sFourthAddress = str_replace('Fax: ','F: ',$sFourthAddress);
			$sFourthAddress = str_replace('Email: ','',$sFourthAddress);
		}
			
		$qSelectUser = "SELECT * from nbUsers WHERE id = '$iRepId'";
		$rSelectUser = dbQuery($qSelectUser);
		while ($oRepRow = dbFetchObject($rSelectUser)) {
			$sRepName = $oRepRow->firstName . " " . $oRepRow->lastName;
			$sRepEmail = $oRepRow->email;
			$sRepExt = 'Ext '.$oRepRow->extension;
			$sRepOfficeLocation = $oRepRow->officeLocation;
			
			if ($sRepOfficeLocation == 'NY') {
				$sFirstAddress = "SilverCarrot, Inc.
132 West 36th St
9th Floor
NY, NY 10018
P: (212) 630-0234
F: (212) 630-0210";
				$sRepCompanyName = "SilverCarrot, Inc.";
			} else {
				$sFirstAddress = "Ampere Media LLC
3400 Dundee Road
Suite 236
Northbrook, IL 60062
P: (847) 205-9320
F: (847) 205-9340";
				$sRepCompanyName = "Ampere Media LLC";
			}
		}
		

		$sThirdAddress = "None";
		$sPartnerQuery = "SELECT partnerContacts.*, partnerCompanies.companyName 
						FROM partnerCompanies,partnerContacts 
						WHERE partnerCompanies.id = partnerContacts.partnerId
						AND companyName = \"$sAgencyCompanyName\"
						LIMIT 1";
		$rPartnerResult = mysql_query($sPartnerQuery);
		if (dbNumRows($rPartnerResult) == 1) {
			while ($oRow = mysql_fetch_object($rPartnerResult)) {
				$sThirdAddress = "$oRow->contact
$oRow->companyName
$oRow->address1, $oRow->address2
$oRow->city, $oRow->state $oRow->zip
P: $oRow->phoneNo
F: $oRow->faxNo
$oRow->email";
			}
		} else {
			$sOfferQuery = "SELECT offerCompanyContacts.*, offerCompanies.companyName
					FROM offerCompanies,offerCompanyContacts 
					WHERE offerCompanies.id = offerCompanyContacts.companyId
					AND companyName = \"$sAgencyCompanyName\"
					LIMIT 1";
			$rOfferResult = mysql_query($sOfferQuery);
			if (dbNumRows($rOfferResult) == 1) {
				while ($oRow = mysql_fetch_object($rOfferResult)) {
					$sThirdAddress = "$oRow->contact
$oRow->companyName
$oRow->address, $oRow->address2
$oRow->city, $oRow->state $oRow->zip
P: $oRow->phoneNo
F: $oRow->faxNo
$oRow->email";
				}
			}
		}
		
		$rCampTypeResult = mysql_query("SELECT campaignType FROM campaignTypes WHERE id ='$iCampaignTypeId'");
		while ($oCampTypeRow = mysql_fetch_object($rCampTypeResult)) {
			$sCampaignType = $oCampTypeRow->campaignType;
		}
		
		// get rate structure
		$rCampRateTypeResult = mysql_query("SELECT rateType FROM campaignRateStructure WHERE id = '$iRateStructureId'");
		while ($oCampRateTypeRow = mysql_fetch_object($rCampRateTypeResult)) {
			$sRateStructure = $oCampRateTypeRow->rateType;
		}
		
		// code to create pdf file goes here...
	}
		

	$oPdf = new FPDF();
	$oPdf->AddPage();
	$oPdf->SetMargins(20, 20,20);
	$oPdf->SetFont('Arial', '', '10');
	$text = file($sMediaType.'.txt');
	
	if ($iVolume == 0) {
		$iVolume = 'OPEN';
	}
	$fRowCost = "TBD";
	if ($sRateStructure == 'CPA' || $sRateStructure == 'CPC') {
		if ($iVolume > 0) {
			$fRowCost = ($iVolume * $fUnitCost);
			$fRowCost = money_format(number_format($fRowCost,2), 2);
		}
	} elseif ($sRateStructure == 'CPM') {
		if ($iVolume > 0) {
			$fRowCost = ($iVolume / 1000) * $fUnitCost;
			$fRowCost = money_format(number_format($fRowCost,2), 2);
		}
	}
	
	$fCampaignTotal = $fRowCost;
	if ($fCampaignTotal > 0) {
		$fBalanceDue = $fCampaignTotal - $fAmountDue;
		$fBalanceDue = money_format(number_format($fBalanceDue,2), 2);
	} else {
		$fBalanceDue = 'TBD';
	}
	
	if ($fRowCost !='TBD') {
		$fRowCost = '$'.$fRowCost;
	}
	if ($fCampaignTotal !='TBD') {
		$fCampaignTotal = '$'.$fCampaignTotal;
	}
	if ($fBalanceDue !='TBD') {
		$fBalanceDue = '$'.$fBalanceDue;
	}
	
	
	
	for ($i=0; $i<count($text); $i++) {
		if ($text[$i] == "Campaign\t\t\t\t\tStart\t\tEnd\t\t\t\t\t\tUnit\t\tTotal\n") {
			$text[$i] = str_replace("Campaign\t\t\t\t\tStart\t\tEnd\t\t\t\t\t\tUnit\t\tTotal\n", '', $text[$i]);
			$oPdf->ln();
			$oPdf->cell(200,4,"Campaign");
			$oPdf->setX(80);
			$oPdf->cell(200,4,"Start");
			$oPdf->setX(110);
			$oPdf->cell(200,4,"End");
			$oPdf->setX(160);
			$oPdf->cell(200,4,"Unit");
			$oPdf->setX(180);
			$oPdf->cell(200,4,"Total");
		} else if ($text[$i] == "Type:\t\t\tPlacement:\t\tDate:\t\tDate:\tVolume:\t\tCost:\t\tCost:\n") {
			$text[$i] = str_replace("Type:\t\t\tPlacement:\t\tDate:\t\tDate:\tVolume:\t\tCost:\t\tCost:\n", '', $text[$i]);
			$oPdf->ln();
			$oPdf->cell(200,4,"Type:");
			$oPdf->setX(50);
			$oPdf->cell(200,4,"Placement:");
			$oPdf->setX(80);
			$oPdf->cell(200,4,"Date:");
			$oPdf->setX(110);
			$oPdf->cell(200,4,"Date:");
			$oPdf->setX(140);
			$oPdf->cell(200,4,"Volume:");
			$oPdf->setX(160);
			$oPdf->cell(200,4,"Cost:");
			$oPdf->setX(180);
			$oPdf->cell(200,4,"Cost:");
		} else if ($text[$i] == "[CAMP_TYPE]\t\t[TYPE_OF_SALE]\t[ST_DATE]\t[ED_DATE]\t[VOL]\t[COST]\t\t[TOTAL_COST]\n") {
			$text[$i] = str_replace("[CAMP_TYPE]\t\t[TYPE_OF_SALE]\t[ST_DATE]\t[ED_DATE]\t[VOL]\t[COST]\t\t[TOTAL_COST]\n", '', $text[$i]);
			$oPdf->ln();
			$oPdf->cell(200,4,$sRateStructure);
			$oPdf->setX(50);
			$oPdf->cell(200,4,$sCampaignType);
			$oPdf->setX(80);
			$oPdf->cell(200,4,$sStartDate);
			$oPdf->setX(110);
			$oPdf->cell(200,4,$sEndDate);
			$oPdf->setX(140);
			$oPdf->cell(200,4,$iVolume);
			$oPdf->setX(160);
			$oPdf->cell(200,4,'$'.$fUnitCost);
			$oPdf->setX(180);
			$oPdf->cell(200,4,$fRowCost);
		} else if ($text[$i] == "-----------------------------------------------------------------------------------------\n") {
			$text[$i] = str_replace("-----------------------------------------------------------------------------------------\n", '', $text[$i]);
			$oPdf->ln();
			$oPdf->cell(200,4,"----------------------------------------------------------------------------------------------------------------------------------------------------");
		} else if ($text[$i] == "[TOTAL_COST]\n") {
			$text[$i] = str_replace("[TOTAL_COST]\n", '', $text[$i]);
			$oPdf->ln();
			$oPdf->setX(142);
			$oPdf->cell(200,4,"Campaign Total: ");
			$oPdf->setX(180);
			$oPdf->cell(200,4,$fCampaignTotal);
		} else if ($text[$i] == "-----\n") {
			$text[$i] = str_replace("-----\n", '', $text[$i]);
			$oPdf->ln();
			$oPdf->setX(170);
			$oPdf->cell(200,4,"---------------------");
		} else if ($text[$i] == "[DUE_SIGNING]\n") {
			$text[$i] = str_replace("[DUE_SIGNING]\n", '', $text[$i]);
			$oPdf->ln();
			$oPdf->setX(138);
			$oPdf->cell(200,4,"Due Upon Signing: ");
			$oPdf->setX(180);
			$oPdf->cell(200,4,'$'.$fAmountDue);
		} else if ($text[$i] == "[BALANCE_DUE]\n") {
			$text[$i] = str_replace("[BALANCE_DUE]\n", '', $text[$i]);
			$oPdf->ln();
			$oPdf->setX(147);
			$oPdf->cell(200,4,"Balance Due: ");
			$oPdf->setX(180);
			$oPdf->cell(200,4,$fBalanceDue);
		} else if ($text[$i] == "[REP_COMPANY_NAME] TECH CONTACT:\n") {
			if ($sRepCompanyName == 'Ampere Media LLC') {
				$sCompanyName = "AMPERE MEDIA";
			} else {
				$sCompanyName = "SILVERCARROT";
			}
			$text[$i] = str_replace("[REP_COMPANY_NAME] TECH CONTACT:\n", '', $text[$i]);
			$oPdf->ln();
			$oPdf->cell(200,4,"$sCompanyName TECH CONTACT:");
		} else if ($text[$i] == "[SIGNATURE_BOX]\n") {
			$text[$i] = str_replace("[SIGNATURE_BOX]\n", '', $text[$i]);
			$oPdf->ln();
			$oPdf->setX(22);
			$oPdf->cell(200,4,"Accepted: $sRepCompanyName");
			$oPdf->setX(105.5);
			$oPdf->cell(200,4,"Accepted: ______________________________");
			
			$oPdf->ln();
			$oPdf->ln();
			$oPdf->setX(21.5);
			$oPdf->cell(200,4,"Signature: ______________________________");
			$oPdf->setX(105);
			$oPdf->cell(200,4,"Signature: ______________________________");
			
			$oPdf->ln();
			$oPdf->ln();
			$oPdf->setX(30);
			$oPdf->cell(200,4,"Print: ______________________________");
			$oPdf->setX(112.5);
			$oPdf->cell(200,4,"Print: ______________________________");
			
			$oPdf->ln();
			$oPdf->ln();
			$oPdf->setX(30.5);
			$oPdf->cell(200,4,"Title: ______________________________");
			$oPdf->setX(113);
			$oPdf->cell(200,4,"Title: ______________________________");
			
			
			$oPdf->ln();
			$oPdf->ln();
			$oPdf->setX(29.5);
			$oPdf->cell(200,4,"Date: ______________________________");
			$oPdf->setX(112.5);
			$oPdf->cell(200,4,"Date: ______________________________");
		} else if ($text[$i] == "ADDITIONAL TERMS: [ADDITIONAL_TERMS]\n") {
			$text[$i] = str_replace("ADDITIONAL TERMS: [ADDITIONAL_TERMS]\n", '', $text[$i]);
			if ($sAdditionalTerms !='') {
				$oPdf->ln();
				$oPdf->cell(200,4,"ADDITIONAL TERMS: ");
				$oPdf->ln();
				$oPdf->cell(200,4,$sAdditionalTerms);
			} else {
				$oPdf->ln();
				$oPdf->cell(200,4,$text[$i]);
			}
		} else if ($text[$i] == "[MATERIALS_DUE]\n") {
			$text[$i] = str_replace("[MATERIALS_DUE]", '', $text[$i]);
			$oPdf->ln();
			$oPdf->cell(200,4,"Materials Due: ");
			$oPdf->setX(60);
			$oPdf->cell(200,4,$sMaterialDue);
		} else if ($text[$i] == "[MATERIALS_DUE_DATE]\n") {
			$text[$i] = str_replace("[MATERIALS_DUE_DATE]", '', $text[$i]);
			$oPdf->ln();
			$oPdf->cell(200,4,"Materials Due Date: ");
			$oPdf->setX(60);
			$oPdf->cell(200,4,$sMaterialDueDate);
		} else if ($text[$i] == "[MATERIALS_TO]\n") {
			$text[$i] = str_replace("[MATERIALS_TO]", '', $text[$i]);
			$oPdf->ln();
			$oPdf->cell(200,4,"Deliver Materials To: ");
			$oPdf->setX(60);
			$oPdf->cell(200,4,$sMaterialTo);
		} else if ($text[$i] == "[IO_NUMBER]\n") {
			$text[$i] = str_replace("[IO_NUMBER]", '', $text[$i]);
			$oPdf->ln();
			$oPdf->cell(200,4,"Insertion Order#: ");
			$oPdf->setX(60);
			$oPdf->cell(200,4,$sIoNum);
		} else if ($text[$i] == "[REP_EMAIL]\n") {
			$text[$i] = str_replace("[REP_EMAIL]", '', $text[$i]);
			$oPdf->ln();
			$oPdf->cell(200,4,"Email: ");
			$oPdf->setX(60);
			$oPdf->cell(200,4,$sRepEmail);
		} else if ($text[$i] == "[REP_EXT]\n") {
			$text[$i] = str_replace("[REP_EXT]", '', $text[$i]);
			$oPdf->ln();
			$oPdf->cell(200,4,"Tel: ");
			$oPdf->setX(60);
			$oPdf->cell(200,4,$sRepExt);
		} else if ($text[$i] == "[TODAYS_DATE]\n") {
			$text[$i] = str_replace("[TODAYS_DATE]", '', $text[$i]);
			$oPdf->ln();
			$oPdf->cell(200,4,"Date: ");
			$oPdf->setX(60);
			$oPdf->cell(200,4,date('m').'/'.date('d').'/'.date('Y'));
		} else if ($text[$i] == "[REP_COMPANY_NAME], Contact: [REP_NAME]\n") {
			$text[$i] = str_replace("[REP_COMPANY_NAME], Contact: [REP_NAME]", '', $text[$i]);
			$oPdf->ln();
			$oPdf->cell(200,4,"$sRepCompanyName, Contact: ");
			$oPdf->setX(70);
			$oPdf->cell(200,4,$sRepName);
		} else if ($text[$i] == "[NEW_PAGE]\n") {
			$oPdf->AddPage();
		} else if ($text[$i] == "[FIRST_ADDRESS]\n") {
			$text[$i] = str_replace("[FIRST_ADDRESS]", '', $text[$i]);
			$aFirstAddr = explode("\n",$sFirstAddress);
			foreach ($aFirstAddr as $line) {
				$oPdf->ln();
				$oPdf->cell(200,4,$line);
			}
		} else if ($text[$i] == "[FOURTH_ADDRESS]\n") {
			$text[$i] = str_replace("[FOURTH_ADDRESS]", '', $text[$i]);
			$aFourthAddr = explode("\n",$sFourthAddress);
			foreach ($aFourthAddr as $line) {
				$oPdf->ln();
				$oPdf->cell(200,4,$line);
			}
		} else if ($text[$i] == "Advertiser:\t\t\t\t\t\t\t\t\t\t\t\t\t\t\tAgency:\n") {
			$text[$i] = str_replace("Advertiser:\t\t\t\t\t\t\t\t\t\t\t\t\t\t\tAgency:\n", '', $text[$i]);
			$oPdf->ln();
			$oPdf->cell(200,4,"Advertiser:");
			$oPdf->setX(100);
			$oPdf->cell(200,4,"Agency:");
		} else if ($text[$i] == "Publisher:\t\t\t\t\t\t\t\t\t\t\t\t\t\t\tAgency:\n") {
			$text[$i] = str_replace("Publisher:\t\t\t\t\t\t\t\t\t\t\t\t\t\t\tAgency:\n", '', $text[$i]);
			$oPdf->ln();
			$oPdf->cell(200,4,"Publisher:");
			$oPdf->setX(100);
			$oPdf->cell(200,4,"Agency:");
		} else if ($text[$i] == "[SECOND_ADDRESS]\t\t\t\t\t\t\t\t\t\t\t\t\t[THIRD_ADDRESS]\n") {
			$text[$i] = str_replace("[SECOND_ADDRESS]\t\t\t\t\t\t\t\t\t\t\t\t\t[THIRD_ADDRESS]\n", '', $text[$i]);
			$aSecondAddr = explode("\n",$sSecondAddress);
			$aThirdAddr = explode("\n",$sThirdAddress);
			$iMax = count($aThirdAddr);
			if (count($aSecondAddr) > count($aThirdAddr)) {
				$iMax = count($aSecondAddr);
			}
			for ($ii=0; $ii<=$iMax; $ii++) {
				$oPdf->ln();
				$oPdf->cell(200,4,$aSecondAddr[$ii]);
				$oPdf->setX(100);
				$oPdf->cell(200,4,$aThirdAddr[$ii]);
			}
		} else {
			$oPdf->ln();
			$oPdf->cell(200,4,$text[$i]);
		}
	}
	$oPdf->AliasNbPages();
	
	$sFileName = $sIoNum.".pdf";
	$oPdf->Output("$sGblWebRoot/temp/$sFileName","F");
	$oPdf->Close();
	echo "<script language=JavaScript>
				void(window.open(\"../../download.php?sFile=$sFileName\",\"_blank\",\"height=150, width=300, scrollbars=yes, resizable=yes, status=yes\"));				
				self.close();
			  </script>";

?>

