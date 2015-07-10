<?php


include("../../includes/paths.php");
include("$sGblLibsPath/dateFunctions.php");

session_start();

$sPageTitle = "Nibbles Offers - List/Delete Offers";

if (hasAccessRight($iMenuId) || isAdmin()) {

	$iCurrYear = date(Y);
	$iCurrMonth = date(m); //01 to 12
	$iCurrDay = date(d); // 01 to 31

	$sYesterday = DateAdd("d", -1, date('Y')."-".date('m')."-".date('d'));
	
	
$sTodaysLeads = stripslashes($sTodaysLeads);

if ($sTodaysLeads) {
	
	$iStartYear = date('Y');
	$iStartMonth = date('m');
	$iStartDay = date('d');
		
	$iEndMonth = $iStartMonth;
	$iEndDay = $iStartDay;
	$iEndYear = $iStartYear;
	
	$sOtDataTable = "otData";
	$sUserDataTable = "userData";
	
		
} else if ($sHistoryLeads) {
	
	
if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iEndMonth,$iEndDay,$iEndYear)) >= 0 || $iEndYear=='') {
			$iEndYear = substr( $sYesterday, 0, 4);
			$iEndMonth = substr( $sYesterday, 5, 2);
			$iEndDay = substr( $sYesterday, 8, 2);
		}
		
		if (DateDiff("d",mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,$iStartMonth,$iStartDay,$iStartYear)) >= 0 || $iStartYear=='') {
			$iStartYear = substr( $sYesterday, 0, 4);
			$iStartMonth = substr( $sYesterday, 5, 2);
			$iStartDay = "01";
		}	
		
	$sOtDataTable = "otDataHistory";
	$sUserDataTable = "userDataHistory";
	
}


if ($sTodaysLeads || $sHistoryLeads) {
	
		$sStartDate = 	$iStartYear."-".$iStartMonth."-".$iStartDay;
		$sEndDate = 	$iEndYear."-".$iEndMonth."-".$iEndDay;
		
		
		$sOffersQuery ="SELECT * 
				FROM    offerLeadSpec
				WHERE   offerCode = '$sOfferCode'";
		
$rOffersResult = dbQuery($sOffersQuery);
while ($oOfferRow = dbFetchObject($rOffersResult)) {
	$iDeliveryMethodId = $oOfferRow->deliveryMethodId;
	$sOfferCode = $oOfferRow->offerCode;
	$sHttpPostString = $oOfferRow->httpPostString;
	$sPostingUrl = $oOfferRow->postingUrl;
	
				$sHttpPostString = $oOfferRow->httpPostString;
				$sLeadsEmailRecipients = $oOfferRow->leadsEmailRecipients;
				$sSingleEmailFromAddr = $oOfferRow->singleEmailFromAddr;
				$sSingleEmailSubject = $oOfferRow->singleEmailSubject;
				$sSingleEmailBody = $oOfferRow->singleEmailBody;
				
				$sDeliveryMethodQuery = "SELECT *
								 FROM   deliveryMethods
								 WHERE  id = '$iDeliveryMethodId'";
				$rDeliveryMethodResult = dbQuery($sDeliveryMethodQuery);
				while ($oDeliveryMethodRow = dbFetchObject($rDeliveryMethodResult)) {
					$sHowSent = $oDeliveryMethodRow->shortMethod;
				}
				

$sSelectQuery = "SELECT $sOtDataTable.*, first, last, address, address2, city, state, zip, phoneNo
				 FROM	$sOtDataTable, $sUserDataTable
				 WHERE  $sOtDataTable.email = $sUserDataTable.email
				 AND    offerCode = '$sOfferCode'
				 AND 	date_format($sOtDataTable.dateTimeAdded, '%Y-%m-%d') BETWEEN '$sStartDate' AND '$sEndDate'";

if (!($iRerun)) {
	 $sSelectQuery .= "  AND    sendStatus IS NULL ";
}

$rSelectResult = dbQuery($sSelectQuery)  ;
echo dbError();
$iNumLeadsPosted = dbNumRows($rSelectResult);

while ($oSelectRow= dbFetchObject($rSelectResult)) {
	
	$iOtDataId = $oSelectRow->id;
	$sPage2Data = $oSelectRow->page2Data;
	$aPage2DataArray = explode("|",$sPage2Data);
	
	
	$sEmail = $oSelectRow->email;
	$sSalutation = $oSelectRow->salutation;
	$sFirst = addslashes($oSelectRow->first);
	$sLast = addslashes($oSelectRow->last);
	$sAddress = addslashes($oSelectRow->address);
	$sAddress2 = addslashes($oSelectRow->address2);
	$sCity = $oSelectRow->city;
	$sState = $oSelectRow->state;
	$sZip = $oSelectRow->zip;
	$sPhone = $oSelectRow->phoneNo;
	$sRemoteIp = $oSelectRow->remoteIp;
	
	if (strlen($sPhone) == 10) {
					$sPhone_areaCode = substr($sPhone,0,3);					
					$sPhone_exchange = substr($sPhone,3,3);					
					$sPhone_number = substr($sPhone,6,4);
				
				} else {
					$sPhone_areaCode = substr($sPhone,0,3);					
					$sPhone_exchange = substr($sPhone,4,3);					
					$sPhone_number = substr($sPhone,8,4);
				}
	
				$sRealTimeResponse = '';
	
if (!(substr(strtolower($sAddress),0,11) == '3401 dundee' && $sZip == '60062') ) {
					
				if ($iDeliveryMethodId == 2 || $iDeliveryMethodId == 3) {
					// 2 = real time form post - GET
					// 3 = real time form post - POST
					
					$aUrlArray = explode("//", $sPostingUrl);
					$sUrlPart = $aUrlArray[1];
					
					$sHttpPostString = ereg_replace("\[email\]",$sEmail, $sHttpPostString);
				$sHttpPostString = ereg_replace("\[first\]",$sFirst, $sHttpPostString);
				$sHttpPostString = ereg_replace("\[last\]",$sLast, $sHttpPostString);
				$sHttpPostString = ereg_replace("\[address\]",$sAddress, $sHttpPostString);
				$sHttpPostString = ereg_replace("\[address2\]",$sAddress2, $sHttpPostString);
				$sHttpPostString = ereg_replace("\[city\]",$sCity, $sHttpPostString);
				$sHttpPostString = ereg_replace("\[state\]",$sState, $sHttpPostString);
				$sHttpPostString = ereg_replace("\[zip\]",$sZip, $sHttpPostString);
				$sHttpPostString = ereg_replace("\[phone\]",$sPhone, $sHttpPostString);
				$sHttpPostString = ereg_replace("\[ipAddress\]",$sRemoteIp, $sHttpPostString);
				$sHttpPostString = ereg_replace("\[phone_areaCode\]", $sPhone_areaCode, $sHttpPostString);
				$sHttpPostString = ereg_replace("\[phone_exchange\]", $sPhone_exchange, $sHttpPostString);
				$sHttpPostString = ereg_replace("\[phone_number\]", $sPhone_number, $sHttpPostString);
				
				// replace page2 field values
				$sPage2MapQuery = "SELECT *
								   FROM   page2Map
		   						   WHERE offerCode = '$sOfferCode'
				   				   ORDER BY storageOrder ";
		
				$rPage2MapResult = dbQuery($sPage2MapQuery);
				$f = 1;
				$ff=0;
				while ($aPage2MapRow = dbFetchArray($rPage2MapResult)) {
																
						$sFieldVar = "FIELD".$f;
						$$sFieldVar = ereg_replace("\"","",$aPage2DataArray[$ff]);
						$sFieldVar2 = "field".$f;
						$$sFieldVar2 = ereg_replace("\"","",$aPage2DataArray[$ff]);
						$sHttpPostString = eregi_replace("\[$sFieldVar\]",urlencode($$sFieldVar), $sHttpPostString);
						$sSingleEmailBody = ereg_replace("\[$sFieldVar\]",urlencode($$sFieldVar), $sSingleEmailBody);
						$sHttpPostString = eregi_replace("\[$sFieldVar2\]",urlencode($$sFieldVar2), $sHttpPostString);
						$sSingleEmailBody = ereg_replace("\[$sFieldVar2\]",urlencode($$sFieldVar2), $sSingleEmailBody);
						//echo "<BR><BR> ".$sPage2Data." post string $sHttpPostString<BR>";
					
						$f++;
						$ff++;
				}
				
					
					// separate host part and script path
					
					$sHostPart = substr($sUrlPart,0,strlen($sUrlPart)-strrpos(strrev($sUrlPart),"/"));
					$sHostPart = ereg_replace("\/","",$sHostPart);
					
					$sScriptPath = substr($sUrlPart,strlen($sHostPart));
					
					
					//echo "<bR><BR>1 $sOfferCode - $sHttpPostString";
					//echo "<BR><BR>2 $sOfferCode - $sSingleEmailBody";
					
					if (strstr($sPostingUrl, "https:")) {
						$rSocketConnection = fsockopen("ssl://".$sHostPart, 443, $errno, $errstr, 30);
					} else {
						
						$rSocketConnection = fsockopen($sHostPart, 80, $errno, $errstr, 30);
						
					}
					echo "\n $sOfferCode - socket ".$rSocketConnection;
					
					if ($rSocketConnection) {
						if ($iDeliveryMethodId == '2') {
							// http form post - GET
							$sScriptPath  .= "?".$sHttpPostString;
							
							fputs($rSocketConnection, "GET $sScriptPath HTTP/1.1\r\n");
							fputs($rSocketConnection, "Host: $sHostPart\r\n");
							fputs($rSocketConnection, "User-Agent: MSIE\r\n");
							fputs($rSocketConnection, "Connection: close\r\n\r\n");
							
						} else if ($iDeliveryMethodId == '3') {
							// http form post - POST
							fputs($rSocketConnection, "POST $sScriptPath HTTP/1.1\r\n");
							fputs($rSocketConnection, "Host: $sHostPart\r\n");
							fputs($rSocketConnection, "Content-type: application/x-www-form-urlencoded \r\n");
							fputs($rSocketConnection, "Content-length: " . strlen($sHttpPostString) . "\r\n");
							fputs($rSocketConnection, "User-Agent: MSIE\r\n");
							fputs($rSocketConnection, "Connection: close\r\n\r\n");
							fputs($rSocketConnection, $sHttpPostString);
						}
						
						if ($sOfferCode != 'VIDEPROF') {
						
							while(!feof($rSocketConnection)) {
								$sRealTimeResponse .= fgets($rSocketConnection, 1024);
							}
						}
						
						fclose($rSocketConnection);
						
					} else {
						echo "$errstr ($errno)<br />\r\n";
					}
										
					$sUpdateStatusQuery = "UPDATE $sOtDataTable
									   SET    processStatus = 'P',
											  sendStatus = 'S',
											  howSent = '$sHowSent',
											  dateTimeProcessed = now(),
											  dateTimeSent = now(),
											  realTimeResponse = \"".addslashes($sRealTimeResponse)."\"
									   WHERE  id = '$iOtDataId'";
					$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
					
					
					echo dbError();
					
					
					
					
				} else if ($iDeliveryMethodId == 4) {
					// send lead email if lead delivery method set as real time email
					// only if mode is active
					
					$sSingleEmailHeaders = "From: $sSingleEmailFromAddr\r\n";
					$sSingleEmailHeaders .= "X-Mailer: MyFree.com\r\n";
					//if ($sOfferAutoEmailFormat == 'html') {
					//$sOfferAutoEmailHeaders .= "Content-Type: text/html; charset=iso-8859-1\r\n"; // Mime type
					//}
					
					$sSingleEmailSubject = ereg_replace("\[offerCode\]",$aOffersChecked[$i], $sSingleEmailSubject);
					
					
					if (strstr($sSingleEmailSubject,"[d-")) {
						
						//get date arithmetic number
						
						$iDateArithNum = substr($sSingleEmailSubject,strpos($sSingleEmailSubject,"[d-")+3,1);
						
						$sTempQuery = "SELECT DATE_ADD(CURRENT_DATE, INTERVAL -$iDateArithNum DAY) AS newDate";
						$rTempResult = dbQuery($sTempQuery);
						while ($oTempRow = dbFetchObject($rTempResult)) {
							$sNewDate = $oTempRow->newDate;
						}
						
						$sNewYY = substr($sNewDate, 0, 4);
						$sNewMM = substr($sNewDate, 5, 2);
						$sNewDD = substr($sNewDate, 8, 2);
						
						$sSingleEmailSubject = ereg_replace("\[dd\]", $sNewDD, $sSingleEmailSubject);
						$sSingleEmailSubject = ereg_replace("\[mm\]", $sNewMM, $sSingleEmailSubject);
						$sSingleEmailSubject = ereg_replace("\[yyyy\]", $sNewYY, $sSingleEmailSubject);
						
						$sDateArithString = substr($sSingleEmailSubject, strpos($sSingleEmailSubject,"[d-"),5);
						
						$sSingleEmailSubject = str_replace($sDateArithString, "", $sSingleEmailSubject);
						
					} else {
						
						$sSingleEmailSubject = ereg_replace("\[dd\]", date(d), $sSingleEmailSubject);
						$sSingleEmailSubject = ereg_replace("\[mm\]", date(m), $sSingleEmailSubject);
						$sSingleEmailSubject = ereg_replace("\[yyyy\]", date(Y), $sSingleEmailSubject);
					}
					
					$sSingleEmailBody = ereg_replace("\[email\]",$sEmail, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[salutation\]",$sSalutation, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[first\]",$sFirst, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[last\]",$sLast, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[address\]",$sAddress, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[address2\]",$sAddress2, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[city\]",$sCity, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[state\]",$sState, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[zip\]",$sZip, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[phone\]",$sPhone, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[ipAddress\]",$sRemoteIp, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[phone_areaCode\]", $sPhone_areaCode, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[phone_exchange\]", $sPhone_exchange, $sSingleEmailBody);
				$sSingleEmailBody = ereg_replace("\[phone_number\]", $sPhone_number, $sSingleEmailBody);
								
				
										
					$aSingleEmailBodyArray = explode("\\r\\n",$sSingleEmailBody);
					$sSingleEmailBody = "";
					
					for ( $x = 0; $x < count($aSingleEmailBodyArray); $x++ ) {
						$sSingleEmailBody .= $aSingleEmailBodyArray[$x]."\r\n";
					}
					
					
					
					mail($sLeadsEmailRecipients, $sSingleEmailSubject, $sSingleEmailBody, $sSingleEmailHeaders);
					
					$sUpdateStatusQuery = "UPDATE $sOtDataTable
									   		SET   processStatus = 'P',
												  sendStatus = 'S',
												  dateTimeProcessed = now(),
											  	  dateTimeSent = now(),
											 	  howSent = '$sHowSent'
									 	  WHERE   id = '$iOtDataId'";
					$rUpdateStatusResult = dbQuery($sUpdateStatusQuery);
					
					echo dbError();
					
					
				}

				}
				sleep(5);
} // end select
} // end offer select

$sMessage = "Total $iNumLeadsPosted Leads Attempted To Post";

	} /// end real time post
	
	
	
	// get the offers list which is not grouped
	$sOffersQuery = "SELECT *
				 	 FROM   offerLeadSpec
				 	 WHERE  deliveryMethodId IN ('2','3','4')				 	 
				 	 ORDER BY offerCode"; 
	$rOffersResult = dbQuery($sOffersQuery);
	echo dbError();
	$sOffersOptions .= "<option value=''>OfferCode";
	$sOffersOptions .= "<option value='allOffers'>All Offers";
	while ($oOffersRow = dbFetchObject($rOffersResult)) {
		if ($oOffersRow->offerCode == $sOfferCode)
		{
			$sOfferCodeSelected = "selected";
		} else {
			$sOfferCodeSelected = "";
		}
		
		$sOffersOptions .= "<option value='$oOffersRow->offerCode' $sOfferCodeSelected>$oOffersRow->offerCode";
	}
	
	
	
			
// prepare month options for From and To date

$sStartMonthOptions = "";
$sEndMonthOptions = "";

for ($i = 0; $i < count($aGblMonthsArray); $i++) {
	$iValue = $i+1;
	
	if ($iValue < 10) {
		$iValue = "0".$iValue;
	}
	
	if ($iValue == $iStartMonth) {
		$sStartMonthSel = "selected";
	} else {
		$sStartMonthSel = "";
	}
	if ($iValue == $iEndMonth) {
		$sEndMonthSel = "selected";
	} else {
		$sEndMonthSel = "";
	}
	
	
	$sStartMonthOptions .= "<option value='$iValue' $sStartMonthSel>$aGblMonthsArray[$i]";
	$sEndMonthOptions .= "<option value='$iValue' $sEndMonthSel>$aGblMonthsArray[$i]";
}


// prepare day options for From and To date
$sStartDayOptions = "";
$sEndDayOptions = "";

for ($i = 1; $i <= 31; $i++) {
	
	if ($i < 10) {
		$iValue = "0".$i;
	} else {
		$iValue = $i;
	}
	
	if ($iValue == $iStartDay) {
		$sStartDaySel = "selected";
	} else {
		$sStartDaySel = "";
	}
	
	if ($iValue == $iEndDay) {
		$sEndDaySel = "selected";
	} else {
		$sEndDaySel = "";
	}		
	
	$sStartDayOptions .= "<option value='$iValue' $sStartDaySel>$i";
	$sEndDayOptions .= "<option value='$iValue' $sEndDaySel>$i";			
}

// prepare year options for From and To date
$sStartYearOptions = "";
$sEndYearOptions = "";

for ($i = $iCurrYear-1; $i <= $iCurrYear+5; $i++) {
	
	if ($i == $iStartYear) {
		$sStartYearSel = "selected";
	} else {
		$sStartYearSel ="";
	}
		
	if ($i == $iEndYear) {
		$sEndYearSel = "selected";
	} else {
		$sEndYearSel = "";
	}
	
	$sStartYearOptions .= "<option value='$i' $sStartYearSel>$i";
	$sEndYearOptions .= "<option value='$i' $sEndYearSel>$i";	
}

if ($iRerun) {
	$sRerunChecked = "checked";
} else {
	$sRerunChecked = "";
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"> 
<html>

<head>
<title><?php echo $sPageTitle;?></title>
<LINK rel="stylesheet" href="<?php echo $sGblAdminSiteRoot;?>/styles.css" type="text/css" >
</head>

<body>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post enctype=multipart/form-data >
<input type=hidden name=iMenuId value='<?php echo $iMenuId;?>'>
<table cellpadding=3 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	<tr><Td class=message align=center colspan=2><?php echo $sMessage;?></td></tr>
</table>

<table cellpadding=3 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=2><BR>Only unprocessed real time leads will be posted unless Rerun is checked.<BR>Select date range on which leads were added.</td></tr>
<tr><td class=header>Offer Code</td><td>
	<select name=sOfferCode><?php echo $sOffersOptions;?>
	</select>
	</td>
</tr>
<tr><td class=header>Date Range</td><td>From: <select name=iStartMonth>
			<?php echo $sStartMonthOptions;?>
			</select> &nbsp;<select name=iStartDay>
			<?php echo $sStartDayOptions;?>
			</select> &nbsp;<select name=iStartYear>
			<?php echo $sStartYearOptions;?>
			</select>
			&nbsp; To: 
			<select name=iEndMonth>
			<?php echo $sEndMonthOptions;?>
			</select> &nbsp;<select name=iEndDay>
			<?php echo $sEndDayOptions;?>
			</select> &nbsp;<select name=iEndYear>
			<?php echo $sEndYearOptions;?>
			</select>
			</td></tr>
			<tr><td></td><td><input type=checkbox name=iRerun value='1' <?php echo $sRerunChecked;?>> Rerun Leads</td></tr>
<tr><td colspan=2 align=center><BR><BR><input type=submit name=sHistoryLeads value='Post History Leads'>  &nbsp; &nbsp; 
	<input type=submit name=sTodaysLeads value="Post Today's Leads"></td></tr>
</table>	
<?php
	
} else {
	echo "You are not authorized to access this page...";
}
?>