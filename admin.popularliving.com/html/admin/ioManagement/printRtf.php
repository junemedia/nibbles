<?php


include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
include("../../libs/rtfFunctions.php");
include("$sGblIncludePath/reportInclude.php");

session_start();

$sPageTitle = "Nibbles IO Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];


	//if($iId) {
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
		
		$text = file($sMediaType.'.rtf');
		
		$rtf = new rtf("../../libs/rtfConfig.php");
		$rtf->setPaperSize(1);
		$rtf->setPaperOrientation(1);
		$rtf->setDefaultFontFace(0);
		$rtf->setDefaultFontSize(24);
		$rtf->setAuthor("ampere");
		$rtf->setOperator("it@amperemedia.com");
		$rtf->setTitle("RTF Document");
		$rtf->addColour("#000000");
		
		for ($i=0; $i<count($text); $i++) {
			if (strstr($text[$i],"[CAMP_TYPE]")) {
				$text[$i] = str_replace("[CAMP_TYPE]", $sRateStructure, $text[$i]);
			}
			if (strstr($text[$i],"[TYPE_OF_SALE]")) {
				$text[$i] = str_replace("[TYPE_OF_SALE]", $sCampaignType, $text[$i]);
			}
			if (strstr($text[$i],"[ST_DATE]")) {
				$text[$i] = str_replace("[ST_DATE]", $sStartDate, $text[$i]);
			}
			if (strstr($text[$i],"[ED_DATE]")) {
				$text[$i] = str_replace("[ED_DATE]", $sEndDate, $text[$i]);
			}
			if (strstr($text[$i],"[VOL]")) {
				$text[$i] = str_replace("[VOL]", $iVolume, $text[$i]);
			}
			if (strstr($text[$i],"[COST]")) {
				$text[$i] = str_replace("[COST]", '$'.$fUnitCost, $text[$i]);
			}
			if (strstr($text[$i],"[TOTAL_COST]")) {
				$text[$i] = str_replace("[TOTAL_COST]", $fRowCost, $text[$i]);
			}
			if (strstr($text[$i],"[DUE_SIGNING]")) {
				$text[$i] = str_replace("[DUE_SIGNING]", $fAmountDue, $text[$i]);
			}
			if (strstr($text[$i],"[BALANCE_DUE]")) {
				$text[$i] = str_replace("[BALANCE_DUE]", $fBalanceDue, $text[$i]);
			}
			if ($text[$i] == "[REP_COMPANY_NAME] TECH CONTACT:\n") {
				if ($sRepCompanyName == 'Ampere Media LLC') {
					$sCompanyName = "AMPERE MEDIA";
				} else {
					$sCompanyName = "SILVERCARROT";
				}
				$text[$i] = str_replace("[REP_COMPANY_NAME]", $sCompanyName, $text[$i]);
			}
			if (strstr($text[$i],"Accepted: [REP_COMPANY_NAME]")) {
				$text[$i] = str_replace("[REP_COMPANY_NAME]", $sRepCompanyName, $text[$i]);
			}
			if ($text[$i] == "[ADDITIONAL_TERMS]\n") {
				$text[$i] = str_replace("[ADDITIONAL_TERMS]", $sAdditionalTerms, $text[$i]);
			}
			
			
			if ($text[$i] == "[FIRST_ADDRESS]\n") {
				$text[$i] = str_replace("[FIRST_ADDRESS]", '', $text[$i]);
				$aFirstAddr = explode("\n",$sFirstAddress);
				foreach ($aFirstAddr as $line) {
					$rtf->addText($line);
					$rtf->addText("\n");
				}
			} else if ($text[$i] == "[IO_NUMBER]\n") {
				$text[$i] = str_replace("[IO_NUMBER]", '', $text[$i]);
				$rtf->addText("Insertion Order#: $sIoNum");
				$rtf->addText("\n");
			} else if ($text[$i] == "[TODAYS_DATE]\n") {
				$text[$i] = str_replace("[TODAYS_DATE]", '', $text[$i]);
				$rtf->addText("Date: ".date('m').'/'.date('d').'/'.date('Y'));
				$rtf->addText("\n");
			} else if ($text[$i] == "[REP_COMPANY_NAME], Contact: [REP_NAME]\n") {
				$text[$i] = str_replace("[REP_COMPANY_NAME], Contact: [REP_NAME]", '', $text[$i]);
				$rtf->addText("$sRepCompanyName, Contact: $sRepName");
				$rtf->addText("\n");
			}  else if ($text[$i] == "[REP_EXT]\n") {
				$text[$i] = str_replace("[REP_EXT]", '', $text[$i]);
				$rtf->addText("Tel: $sRepExt");
				$rtf->addText("\n");
			} else if ($text[$i] == "[REP_EMAIL]\n") {
				$text[$i] = str_replace("[REP_EMAIL]", '', $text[$i]);
				$rtf->addText("Email: $sRepEmail");
				$rtf->addText("\n");
			} else if ($text[$i] == "[SECOND_ADDRESS]\t\t\t\t\t\t\t\t[THIRD_ADDRESS]\n") {
				$text[$i] = str_replace("[SECOND_ADDRESS]\t\t\t\t\t\t\t\t[THIRD_ADDRESS]\n", '', $text[$i]);
				$aSecondAddr = explode("\n",$sSecondAddress);
				$aThirdAddr = explode("\n",$sThirdAddress);
				$iMax = count($aThirdAddr);
				if (count($aSecondAddr) > count($aThirdAddr)) {
					$iMax = count($aSecondAddr);
				}
				for ($ii=0; $ii<$iMax; $ii++) {
					$s2ndTemp = $aSecondAddr[$ii];
					if ($ii < $iMax - 1) {
						$s2ndTemp = substr($s2ndTemp,0,strlen($s2ndTemp)-1);
					}
					while( strlen($s2ndTemp) < 36 ) {
						$s2ndTemp = $s2ndTemp." ";
					}
					
					$rtf->addText($s2ndTemp.$aThirdAddr[$ii]);
					$rtf->addText("\n");
				}
			} else if ($text[$i] == "[FOURTH_ADDRESS]\n") {
				$text[$i] = str_replace("[FOURTH_ADDRESS]", '', $text[$i]);
				$aFourthAddr = explode("\n",$sFourthAddress);
				$iCount = count($aFourthAddr) - 1;
				$iTemp = 0;
				foreach ($aFourthAddr as $line) {
					if ($iTemp != $iCount) {
						$line = substr($line,0,strlen($line)-1);
						$rtf->addText($line);
						$rtf->addText("\n");
					} else {
						$rtf->addText($line);
					}
					$iTemp++;
				}
			} else if ($text[$i] == "[MATERIALS_DUE]\n") {
				$text[$i] = str_replace("[MATERIALS_DUE]", '', $text[$i]);
				$rtf->addText("Materials Due: $sMaterialDue\n");
			} else if ($text[$i] == "[MATERIALS_DUE_DATE]\n") {
				$text[$i] = str_replace("[MATERIALS_DUE_DATE]", '', $text[$i]);
				$rtf->addText("Materials Due Date: $sMaterialDueDate\n");
			} else if ($text[$i] == "[MATERIALS_TO]\n") {
				$text[$i] = str_replace("[MATERIALS_TO]", '', $text[$i]);
				$rtf->addText("Deliver Materials To: $sMaterialTo\n");
			} else {
				$rtf->addText($text[$i]);
			}
		}
		
		$rtf->getDocument();
	//}
		

?>

