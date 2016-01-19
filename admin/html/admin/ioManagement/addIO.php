<?php

//Script to Add/Edit Insertion Order
include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
session_start();
$sPageTitle = "Nibbles IO Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
if ($sSaveClose || $sSaveNew) {
	$sStartTimeStamp = strtotime("$iStartYear-$iStartMonth-$iStartDay");
	$sEndTimeStamp = strtotime("$iEndYear-$iEndMonth-$iEndDay");
	$sMaterialDueDate = '';
	$sMessage = '';
	$sStartDate = '';
	$sEndDate = '';
	
	if ($sMediaType == 'selling' && $iAdvertiserId == '') {
		$sMessage = "Advertiser Is Required.";
		$bKeepValues = true;
	} elseif ($sMediaType == 'buying' && $iPublisherId == '') {
		$sMessage = "Publisher Is Required.";
		$bKeepValues = true;
	} elseif ($iVolume !='' && !ctype_digit($iVolume)) {
		$sMessage = "Volume Must Be Numeric.";
		$bKeepValues = true;
	} elseif (!(checkdate($iStartMonth,$iStartDay,$iStartYear) && checkdate($iEndMonth,$iEndDay,$iEndYear))) {
		$sMessage = "Invalid Start and End Date.";
		$bKeepValues = true;
	} elseif ($sMediaType == '') {
		$sMessage = "Please select either Media Buying or Media Selling.";
		$bKeepValues = true;
	} elseif ($iCampaignTypeId =='') {
		$sMessage = "Campaign Type Required.";
		$bKeepValues = true;
	} else if (!ereg("^[0-9\.]*$", $fUnitCost)) {
		$sMessage = "Unit Cost Can Contain Only Numbers or .";
		$bKeepValues = true;
	} elseif ($sMaterialsTo !='' && !eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", $sMaterialsTo)) {
		$sMessage = "Invalid Deliver Materials To Address";
		$bKeepValues = true;
	} else if (!ereg("^[0-9\.]*$", $fAmountDue)) {
		$sMessage = "Amount Due Upon Signing Can Contain Only Numbers or .";
		$bKeepValues = true;
	}

	if ($sStartTimeStamp <= $sEndTimeStamp) {
		$sEndDate = "$iEndYear-$iEndMonth-$iEndDay";
		$sStartDate = "$iStartYear-$iStartMonth-$iStartDay";
	} else {
		$sMessage = "Start Date Must Be Earlier Than End Date...";
		$bKeepValues = true;
	}
	
	
	if ($iMaterialDueYear !='' && $iMaterialDueMonth !='' && $iMaterialDueDay !='') {
		if (checkdate($iMaterialDueMonth,$iMaterialDueDay,$iMaterialDueYear)) {
			$sMaterialDueDate = "$iMaterialDueYear-$iMaterialDueMonth-$iMaterialDueDay";
		}
	}
	
	
	
	if ($sMessage == '') {
		$sBilling = addslashes($sBilling);
		$sContactInfo = addslashes($sContactInfo);
		$sAdditionalTerms = addslashes($sAdditionalTerms);
		
		$sGetCompany = "SELECT officeLocation FROM nbUsers WHERE id = '$repId'";
		$rCompanyResult = dbQuery($sGetCompany);
		while ($oRow = dbFetchObject($rCompanyResult)) {
			if ($oRow->officeLocation == 'NY') {
				$sOfficeLocation = 'SC';
			} else {
				$sOfficeLocation = 'AM';
			}
		}
		
		if (!($iId)) {
			$sInsertUpdateQuery = "INSERT IGNORE INTO io (mediaType,repId,publisherId,
							advertiserId,agencyName,contactInfo,billing,
							campaignId, campaignType, rateStructureId, volume,
							cost,startDate,endDate,materialsDue,materialsDueDate,materialsTo,
							amountDue,additionalTerms,partnerId,dateGenerated) 
						VALUES (\"$sMediaType\",\"$repId\",\"$iPublisherId\",
							\"$iAdvertiserId\", \"$sAgencyCompanyName\", \"$sContactInfo\", \"$sBilling\",
							\"$iCampaignId\", \"$iCampaignTypeId\", \"$iCampaignRateTypeId\",\"$iVolume\",
							\"$fUnitCost\", \"$sStartDate\", \"$sEndDate\", \"$sMaterialsDue\",\"$sMaterialDueDate\",\"$sMaterialsTo\", 
							\"$fAmountDue\",\"$sAdditionalTerms\",\"$iPublisherId\",NOW())";
			$rResult = dbQuery($sInsertUpdateQuery);
			$iLastInsertedId = mysql_insert_id();
			
			if (!($rResult)) {
				mail('spatel@amperemedia.com',"Insert Failed - File: ".__FILE__." Line: ".__LINE__, $sInsertUpdateQuery);
			}
			
			$iFiveDigitNum = $iLastInsertedId;
			while( strlen($iFiveDigitNum) < 5 ) {
				$iFiveDigitNum = "0".$iFiveDigitNum;
			}
			if ($sMediaType == 'buying') {
				$sIoId = "$sOfficeLocation-B-".date('y').date('m').date('d').'-'.$iFiveDigitNum;
			} else {
				$sIoId = "$sOfficeLocation-S-".date('y').date('m').date('d').'-'.$iFiveDigitNum;
			}
			
			$sUpdate = "UPDATE io
						SET ioNum = '$sIoId'
						WHERE id = '$iLastInsertedId'";
			$rResult = dbQuery($sUpdate);
			
			// start of track users' activity in nibbles
			$sLogAddQuery = "INSERT IGNORE INTO trackNibbleUse(userName, pageName, dateTimeLogged, action) 
					VALUES('$sTrackingUser', '$PHP_SELF', NOW(), \"" . addslashes($sUpdate) . "\")";
			$rLogResult = dbQuery($sLogAddQuery);
			// end of track users' activity in nibbles
			
			
		} else if ($iId) {
			if ($sOldMediaType != $sMediaType) {
				$iFiveDigitNum = $iId;
				while( strlen($iFiveDigitNum) < 5 ) {
					$iFiveDigitNum = "0".$iFiveDigitNum;
				}
				if ($sMediaType == 'buying') {
					$sIoId = "$sOfficeLocation-B-".date('y').date('m').date('d').'-'.$iFiveDigitNum;
				} else {
					$sIoId = "$sOfficeLocation-S-".date('y').date('m').date('d').'-'.$iFiveDigitNum;
				}
				
				$sUpdate = "UPDATE io
							SET ioNum = '$sIoId'
							WHERE id = '$iId'";
				$rResult = dbQuery($sUpdate);
			}
			
			$sInsertUpdateQuery = "UPDATE io SET 
				mediaType = '$sMediaType',
				repId = '$repId',
				publisherId = '$iPublisherId',
				partnerId = '$iPublisherId',
				advertiserId = '$iAdvertiserId',
				agencyName = \"$sAgencyCompanyName\",
				contactInfo = \"$sContactInfo\",
				billing = \"$sBilling\",
				campaignId = '$iCampaignId',
				campaignType = '$iCampaignTypeId',
				rateStructureId = '$iCampaignRateTypeId',
				volume = '$iVolume',
				cost = '$fUnitCost',
				startDate = '$sStartDate',
				endDate = '$sEndDate',
				materialsDue = '$sMaterialsDue',
				materialsDueDate = '$sMaterialDueDate',
				materialsTo = '$sMaterialsTo',
				amountDue = '$fAmountDue',
				additionalTerms = \"$sAdditionalTerms\"
				WHERE id = '$iId'";
			$rResult = dbQuery($sInsertUpdateQuery);
		}
		

		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT IGNORE INTO trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				VALUES('$sTrackingUser', '$PHP_SELF', NOW(), \"" . addslashes($sInsertUpdateQuery) . "\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
	}
}

if ($sSaveClose) {
	if ($bKeepValues != true) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";			
	}
} else if ($sSaveNew) {
	if ($bKeepValues != true) {
		$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";	
		$iId = '';
		$iLastInsertedId = '';
		$sDateGenerated = '';
		$iId = '';
		$repId = '';
		$type = '';
		$template = '';
		$iPartnerId = '';
		$sPartner = '';
		$iAdvertiserId = '';
		$iCampaignId = '';
		$iCampaignRateTypeId = '';
		$fUnitCost = '';
		$iVolume = '';
		$iCampaignTypeId = '';
	}
}

if ($iId && !($bKeepValues)) {
	// Get the data to display in HTML fields for the record to be edited
	$sSelectQuery = "SELECT * FROM io WHERE id='$iId'";
	$rResult = dbQuery($sSelectQuery);
	while ($oRow = dbFetchObject($rResult)) {
		$iId = $oRow->id;
		$repId = $oRow->repId;
		$type = $oRow->type;
		$template = $oRow->template;
		$iPartnerId = $oRow->partnerId;
		$iAdvertiserId = $oRow->advertiserId;
		$iCampaignId = $oRow->campaignId;
		$iCampaignRateTypeId = $oRow->rateStructureId;
		$fUnitCost = $oRow->cost;
		$iVolume = $oRow->volume;
		$iCampaignTypeId = $oRow->campaignType;
		$iEndYear = substr($oRow->endDate,0,4);
		$iEndMonth = substr($oRow->endDate,5,2);
		$iEndDay = substr($oRow->endDate,8,2);
		$iStartYear = substr($oRow->startDate,0,4);
		$iStartMonth = substr($oRow->startDate,5,2);
		$iStartDay = substr($oRow->startDate,8,2);
		
		$sPublisher = $oRow->publisher;
		$sBilling = $oRow->billing;
		
		$sMaterialsDue = $oRow->materialsDue;
		$sMaterialsTo = $oRow->materialsTo;
		
		$iMaterialDueYear = substr($oRow->materialsDueDate,0,4);
		$iMaterialDueMonth = substr($oRow->materialsDueDate,5,2);
		$iMaterialDueDay = substr($oRow->materialsDueDate,8,2);
		

		$m = substr($oRow->dateGenerated,4,2);
		$d = substr($oRow->dateGenerated,6,2);
		$y = substr($oRow->dateGenerated,0,4);
		$h = substr($oRow->dateGenerated,8,2);
		$min = substr($oRow->dateGenerated,10,2);
		$sec = substr($oRow->dateGenerated,12,2);
		$sDateGenerated = "$y/$m/$d $h:$min:$sec";
		
		
		$sMediaType = $oRow->mediaType;
		$sOldMediaType = $oRow->mediaType;
		$sAdditionalTerms = $oRow->additionalTerms;
		$fAmountDue = $oRow->amountDue;
		$sContactInfo = $oRow->contactInfo;
		$sAgencyCompanyName = $oRow->agencyName;
		$sIoId = $oRow->ioNum;
		$iPublisherId = $oRow->publisherId;
	}
}


$rUsers = dbQuery("SELECT userName, id FROM nbUsers ORDER BY userName ASC");
$sRepOption = '';
while ( $oUserRow = dbFetchObject($rUsers)) {
	$sSelected = '';
	if (($repId == $oUserRow->id) || ($iId=='' && ($oUserRow->userName == $sTrackingUser))) {
		$sSelected = 'selected';
	}
	$sRepOption  .= "<option value='$oUserRow->id' $sSelected>$oUserRow->userName</option>";	
}



// get rate structure
$sCampRateTypeQuery = "SELECT * FROM campaignRateStructure ORDER BY rateType";
$rCampRateTypeResult = mysql_query($sCampRateTypeQuery);
while ($oCampRateTypeRow = mysql_fetch_object($rCampRateTypeResult)) {
	if ($oCampRateTypeRow->id == $iCampaignRateTypeId) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sRateStructureOptions .= "<option value='$oCampRateTypeRow->id' $sSelected>$oCampRateTypeRow->rateType";
}


$sCallToFunc = '';
// set default start and end date if adding new entry.
if (!($iId)) {
	$iStartMonth = date('m');
	$iStartDay = date('d');
	$iStartYear = date('Y');
	$iEndMonth = date('m');
	$iEndDay = date('d');
	$iEndYear = date('Y')+1;
	$fUnitCost = 0;
	$iVolume = 0;
	$fAmountDue = 0;
	$sCallToFunc = "getMaterialsTo(document.form1.repId.value);";
}






// prepare month options for From and To date
$sStartMonthOptions = "<option value=''>Month";
$sEndMonthOptions = "<option value=''>Month";
$sMaterialMonthOptions = "<option value=''>Month";
for ($i = 0; $i < count($aGblMonthsArray); $i++) {
	$value = $i+1;
	if ($value < 10) {
		$value = "0".$value;
	}
	
	$sSelected = '';
	if ($value == $iStartMonth) {
		$sSelected = "selected";
	}
	$sStartMonthOptions .= "<option value='$value' $sSelected>$aGblMonthsArray[$i]";
	
	$sSelected = '';
	if ($value == $iEndMonth) {
		$sSelected = "selected";
	}
	$sEndMonthOptions .= "<option value='$value' $sSelected>$aGblMonthsArray[$i]";
	
	$sSelected = '';
	if ($value == $iMaterialDueMonth) {
		$sSelected = "selected";
	}
	$sMaterialMonthOptions .= "<option value='$value' $sSelected>$aGblMonthsArray[$i]";
}
	
// prepare day options for From and To date
$sStartDayOptions = "<option value=''>Day";
$sEndDayOptions = "<option value=''>Day";
$sMaterialDayOptions = "<option value=''>Day";
for ($i = 1; $i <= 31; $i++) {
	if ($i < 10) {
		$value = "0".$i;
	} else {
		$value = $i;
	}
	$sSelected = '';
	if ($value == $iStartDay) {
		$sSelected = "selected";
	}
	$sStartDayOptions .= "<option value='$value' $sSelected>$i";
	
	
	$sSelected = '';
	if ($value == $iEndDay) {
		$sSelected = "selected";
	}
	$sEndDayOptions .= "<option value='$value' $sSelected>$i";
	
	
	$sSelected = '';
	if ($value == $iMaterialDueDay) {
		$sSelected = "selected";
	}
	$sMaterialDayOptions .= "<option value='$value' $sSelected>$i";
}
	
// prepare year options
$sStartYearOptions = "<option value=''>Year";
$sEndYearOptions = "<option value=''>Year";
$sMaterialYearOptions = "<option value=''>Year";
$iCurrYear = date('Y');



for ($i = $iCurrYear; $i <= $iCurrYear+10; $i++) {
	$sSelected = '';
	if ($i == $iStartYear) {
		$sSelected = "selected";
	}
	$sStartYearOptions .= "<option value='$i' $sSelected>$i";
	
	$sSelected = '';
	if ($i == $iEndYear) {
		$sSelected = "selected";
	}
	$sEndYearOptions .= "<option value='$i' $sSelected>$i";
	
	
	$sSelected = '';
	if ($i == $iMaterialDueYear) {
		$sSelected = "selected";
	}
	$sMaterialYearOptions .= "<option value='$i' $sSelected>$i";
}




$sGetCampaignId = "SELECT id,campaignName FROM campaigns order by campaignName ASC";
$rGetCampaignIdResult = mysql_query($sGetCampaignId);
$sCampaignOptions = "<option value=''>";
while ($oCampRow = mysql_fetch_object($rGetCampaignIdResult)) {
	$sSelected = '';
	if ($oCampRow->id == $iCampaignId) {
		$sSelected = "selected";
	}
	$sCampaignOptions .= "<option value='$oCampRow->id' $sSelected>$oCampRow->campaignName";
}



$sCampTypeQuery = "SELECT * FROM campaignTypes ORDER BY campaignType";
$rCampTypeResult = mysql_query($sCampTypeQuery);
$sCampaignTypeOptions = "<option value=''>";
while ($oCampTypeRow = mysql_fetch_object($rCampTypeResult)) {
	$sSelected = '';
	if ($oCampTypeRow->id == $iCampaignTypeId) {
		$sSelected = "Selected";
	}
	$sCampaignTypeOptions .= "<option value='$oCampTypeRow->id' $sSelected >$oCampTypeRow->campaignType";
}

if ($sMediaType == '') {
	$sMediaType = 'buying';
}

switch ($sMediaType) {
	case "buying":
		$sMediaTypeOptions = "<option value='buying' selected>Media Buying</option>
				<option value='selling'>Media Selling</option>";
		break;
	case "selling":
		$sMediaTypeOptions = "<option value='buying'>Media Buying</option>
				<option value='selling' selected>Media Selling</option>";
		break;
}




// get partner companies drop down
$rCompanies = dbQuery("SELECT id, companyName FROM partnerCompanies ORDER BY companyName ASC");
$sBuyingPublisherPartnerCompaniesOptions = "<option value=''><option value='' onclick='addPartnersCompany();'>--Add Partner Company--<optgroup label='-------------------'>";
while( $oCompany = dbFetchObject($rCompanies)) {
	$sSelected = '';
	if ($iPublisherId == $oCompany->id) {
		$sSelected = 'selected';
	}
	$sBuyingPublisherPartnerCompaniesOptions .= "<option value='$oCompany->id' $sSelected>$oCompany->companyName";
}


// get offer companies drop down
$rCompanies = dbQuery("SELECT id, companyName FROM offerCompanies ORDER BY companyName ASC");
$sSellingAdvertiserOfferCompaniesOptions = "<option value=''><option value='' onclick='addOfferCompany();'>--Add Offer Company--<optgroup label='-------------------'>";
while( $oCompany = dbFetchObject($rCompanies)) {
	$sSelected = '';
	if ($iAdvertiserId == $oCompany->id) {
		$sSelected = 'selected';
	}
	$sSellingAdvertiserOfferCompaniesOptions .= "<option value='$oCompany->id' $sSelected>$oCompany->companyName";
}



// get agency offer/partner companies drop down
$rCompanies = dbQuery("SELECT companyName FROM offerCompanies
						UNION
						SELECT companyName FROM partnerCompanies
						ORDER BY companyName ASC");
$sAgencyCompaniesOptions = "<option value=''>";
while( $oCompany = dbFetchObject($rCompanies)) {
	$sSelected = '';
	if ($sAgencyCompanyName == $oCompany->companyName) {
		$sSelected = 'selected';
	}
	$sAgencyCompaniesOptions .= "<option value='$oCompany->companyName' $sSelected>$oCompany->companyName";
}




// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iId value='$iId'>";

include("../../includes/adminAddHeader.php");
?>
<SCRIPT LANGUAGE=JavaScript SRC="http://www.popularliving.com/nibbles2/libs/ajax.js" TYPE=text/javascript></script>

<script type="text/javascript">
function getObject(objectId) {
	// checkW3C DOM, then MSIE 4, then NN 4.
	//
	if(document.getElementById && document.getElementById(objectId)) {
		return document.getElementById(objectId);
	} else if (document.all && document.all(objectId)) {
		return document.all(objectId);
	} else if (document.layers && document.layers[objectId]) {
		return document.layers[objectId];
	} else {
		return false;
	}
}

function addPartnersCompany() {
	window.open('../partnersMgmnt/addCompany.php?iMenuId=14&PHPSESSID=<?php echo session_id(); ?>','','') ;
}

function addOfferCompany() {
	window.open('../offerCompanies/addCompany.php?iMenuId=15&PHPSESSID=<?php echo session_id(); ?>','','') ;
}

function fillInContactInfoPub(val) {
	// get partner companies info
	document.form1.sContactInfo.value = coRegPopup.send('getMoreData.php?id='+val+'&type=pub','');
}

function fillInContactInfoAdv(val) {
	// get offer companies info
	document.form1.sContactInfo.value = coRegPopup.send('getMoreData.php?id='+val+'&type=adv','');
}

function getPubOrAdv(val) {
	if (val == 'buying') {
		div = getObject('publisherOrAdvertiser');
		div.innerHTML = "<select name='iPublisherId' onchange='fillInContactInfoPub(this.value);'><?php echo $sBuyingPublisherPartnerCompaniesOptions;?></select>";
		
		div = getObject('publisherOrAdvertiserLabel');
		div.innerHTML = "Publisher: ";
	}
	if (val == 'selling') {
		// do nothing for now.
		div = getObject('publisherOrAdvertiserLabel');
		div.innerHTML = "Advertiser: ";
		
		div = getObject('publisherOrAdvertiser');
		div.innerHTML = "<select name='iAdvertiserId' onchange='fillInContactInfoAdv(this.value);getBillingInfo();'><?php echo $sSellingAdvertiserOfferCompaniesOptions;?></select>";
	}
}

function getMaterialsTo(val) {
	if (val !='') {
		document.form1.sMaterialsTo.value = coRegPopup.send('getMoreData.php?id='+val+'&type=deliverTo','');
	} else {
		document.form1.sMaterialsTo.value = '';
	}
}

function getBillingInfo () {
	advertiserId = document.form1.iAdvertiserId.value;
	agencyValue = document.form1.sAgencyCompanyName.value;
	if (agencyValue == '' && advertiserId !='') {
		// get advertiser data
		//alert('get-advertiser-data');
		document.form1.sBilling.value = coRegPopup.send('getMoreData.php?id='+advertiserId+'&type=adv','');
	}
	if (agencyValue !='') {
		// get agency data.
		//alert('get-agency-data');
		document.form1.sBilling.value = coRegPopup.send('getMoreData.php?cname='+agencyValue+'&type=cn','');
	}
}

</script>



<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<input type="hidden" name='sOldMediaType' value='<?php echo $sOldMediaType; ?>'>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>Media Buying or Selling</td>
		<td><select name='sMediaType' onchange="getPubOrAdv(this.value);">
				<?php echo $sMediaTypeOptions; ?>
			</select></td>
	</tr>
	<tr><td>Representative</td>
		<td>
		<select name='repId' onchange='getMaterialsTo(this.value);'>
			<?php echo $sRepOption;?>
		</select>
		</td>
	</tr>
	

	<tr><td><div id='publisherOrAdvertiserLabel'></div></td>
	<td><div id='publisherOrAdvertiser'></div></td></tr>
	
	
	
	<tr><td>Agency</td>
		<td>
		<select name='sAgencyCompanyName' onchange="getBillingInfo();">
			<?php echo $sAgencyCompaniesOptions;?>
		</select>
		</td>
	</tr>
	
	
	<tr><td>Contact Info</td><td>
		<textarea name=sContactInfo cols=40 rows=5 id='pub_id'><?php echo $sContactInfo;?></textarea></td>
	</tr>
	
	<tr><td>Billing</td><td>
		<textarea name=sBilling cols=40 rows=5 id='bil_id'><?php echo $sBilling;?></textarea></td>
	</tr>
	
	
	
	
	<tr><td>Campaign Name</td>
		<td><select name='iCampaignId'>
		<?php echo $sCampaignOptions;?>
		</select>
	</td></tr>
	
	
	<tr><td>Campaign Types</td>
		<td><select name='iCampaignTypeId'>
				<?php echo $sCampaignTypeOptions;?>
			</select>
	</td></tr>
		
		
	
	<tr><td>Rate Structure</td>
		<td><select name=iCampaignRateTypeId >
			<?php echo $sRateStructureOptions;?>
			</select>
		</td>
	</tr>

	
	<tr><td>Volume</td>
		<td>
			<input type="text" name="iVolume" value='<?php echo $iVolume; ?>' maxlength="10" size="5">&nbsp;&nbsp;Use "0" for OPEN.
		</td>
	</tr>
	
	
	<tr><td>Unit Cost</td>
		<td>
			$<input type="text" name="fUnitCost" value='<?php echo $fUnitCost; ?>' maxlength="10" size="5">
		</td>
	</tr>
	

	<tr><td>Date: </td>
		<td>Start:
			<select name=iStartMonth id=s_1 onchange="ender_date();"><?php echo $sStartMonthOptions;?></select>
			<select name=iStartDay id=s_2 onchange="ender_date();"><?php echo $sStartDayOptions;?></select>
			<select name=iStartYear  id=s_3 onchange="ender_date();"><?php echo $sStartYearOptions;?></select>
			<br>End:&nbsp;&nbsp;
			<select name=iEndMonth id=e_1><?php echo $sEndMonthOptions;?></select>
			<select name=iEndDay id=e_2><?php echo $sEndDayOptions;?></select>
			<select name=iEndYear id=e_3><?php echo $sEndYearOptions;?></select>
		</td>
	</tr>
	
	
	<tr><td class="header">Materials and Tracking:</td>
		<td></td>
	</tr>
	
	<tr><td>Materials Due</td>
		<td>
			<input type="text" name="sMaterialsDue" value='<?php echo $sMaterialsDue; ?>' size="50" maxlength="255">
		</td>
	</tr>
	
	
	<tr><td>Materials Due Date: </td>
		<td>
			<select name=iMaterialDueMonth id=d_1><?php echo $sMaterialMonthOptions;?></select>
			<select name=iMaterialDueDay id=d_2><?php echo $sMaterialDayOptions;?></select>
			<select name=iMaterialDueYear id=d_3><?php echo $sMaterialYearOptions;?></select>
		</td>
	</tr>
	
	
	<tr><td>Deliver Materials To</td>
		<td>
			<input type="text" name="sMaterialsTo" id='mat_id' value='<?php echo $sMaterialsTo; ?>' size="50" maxlength="255">
		</td>
	</tr>
	
	
	<tr><td>Amount Due Upon Signing</td>
		<td>
			$<input type="text" name="fAmountDue" value='<?php echo $fAmountDue; ?>' maxlength="10" size="5">
		</td>
	</tr>
	
	<tr><td>
	  Additional Terms </td>
	  <td> <textarea name="sAdditionalTerms" rows="5" cols="40"><?php echo $sAdditionalTerms; ?></textarea> 
	</td></tr>
	
</table>
<script>
getPubOrAdv(document.form1.sMediaType.value);
<?php echo $sCallToFunc; ?>
</script>
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>