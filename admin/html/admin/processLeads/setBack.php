<?php


include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles - Set Leads Back";

if (hasAccessRight($iMenuId) || isAdmin()) {

	if ($sSetBack) {
		
		$sStartDate = 	$iStartYear."-".$iStartMonth."-".$iStartDay;
		$sEndDate = 	$iEndYear."-".$iEndMonth."-".$iEndDay;
		
		if ($sOfferCode != '') {
			if ($sOfferCode == 'allOffers') {
				$sSetBackQuery = "UPDATE otDataHistory
								  SET    processStatus = NULL, 
										 sendStatus = NULL,
										 reasonCode = '',
										 howSent = '', 
										 realTimeResponse = ''
								  WHERE  date_format(dateTimeSent, '%Y-%m-%d') BETWEEN '$sStartDate' AND '$sEndDate'
								  AND    processStatus = 'P'";
				
			} else {
				$sSetBackQuery = "UPDATE otDataHistory
								  SET	 processStatus = NULL,
						  				 sendStatus = NULL,
										 reasonCode = '',
										 howSent = '', 
										 realTimeResponse = ''
								  WHERE  date_format(dateTimeSent, '%Y-%m-%d') BETWEEN '$sStartDate' AND '$sEndDate'
								  AND    offerCode = '$sOfferCode'
								  AND    processStatus = 'P'";
			}
			
		} else if ($iGroupId != '') {
			
			$sSetBackQuery = "UPDATE otDataHistory, offerLeadSpec
							  SET	 processStatus = NULL,
								 	 sendStatus = NULL,
									 reasonCode = '',
									 howSent = '', 
									 realTimeResponse = ''
							  WHERE  date_format(dateTimeSent, '%Y-%m-%d') BETWEEN '$sStartDate' AND '$sEndDate'
							  AND	 otDataHistory.offerCode = offerLeadSpec.offerCode
							  AND	 offerLeadSpec.leadsGroupId = '$iGroupId' 
							  AND    processStatus = 'P'";
		}
		
		if ($iGroupId || $sOfferCode != '') {			
			$rSetBackResult = dbQuery($sSetBackQuery);
			//echo $sSetBackQuery. mysql_error();
			$sSetBackQuery = ereg_replace("otDataHistory", "otData", $sSetBackQuery);
			$rSetBackResult = dbQuery($sSetBackQuery);
			//echo $sSetBackQuery. mysql_error();
			if ($rSetBackResult ) {
			// mark 3401 leads as rejected 	
						/*
				$sTestLeadsQuery = "SELECT *
	  							   FROM   userDataHistory
	  							   WHERE address like '3401 DUNDEE%' and zip = '60062'";
				
	  			$rTestLeadsResult = mysql_query($sTestLeadsQuery);
	  			while ($oTestLeadsRow = mysql_fetch_object($rTestLeadsResult)) {
	  				$sTestEmail = $oTestLeadsRow->email;
	  				
					
	  				if ($sOfferCode != '' &&  $sOfferCode != 'allOffers') {
	  					$sOtDataUpdateQuery = "UPDATE otDataHistory
							 SET    processStatus = 'R',
									reasonCode = 'tst',
									dateTimeProcessed = now()
							 WHERE  email = '$sTestEmail'
	  						 AND    processStatus IS NULL  
	  						 AND	date_format(dateTimeSent, '%Y-%m-%d') BETWEEN '$sStartDate' AND '$sEndDate
	  						 AND 	offerCode = '$sOfferCode'"; 
	  				} else if ($sOfferCode == 'allOffers') {
	  					$sOtDataUpdateQuery = "UPDATE otDataHistory
							 SET    processStatus = 'R',
									reasonCode = 'tst',
									dateTimeProcessed = now()
							 WHERE  email = '$sTestEmail'
	  						 AND    processStatus IS NULL  
	  						 AND	date_format(dateTimeSent, '%Y-%m-%d') BETWEEN '$sStartDate' AND '$sEndDate"; 
	  				} else if ($iGroupId != '') {
	  					$sOtDataUpdateQuery = "UPDATE otDataHistory, offerLeadSpec
							 SET    processStatus = 'R',
									reasonCode = 'tst',
									dateTimeProcessed = now()
							 WHERE  otDataHistory.offerCode = offerLeadSpec.offerCode
	  						 AND	offerLeadSpec.leadGroupId = '$iGroupId'
	  						 AND	email = '$sTestEmail'
	  						 AND    processStatus IS NULL  
	  						 AND	date_format(dateTimeSent, '%Y-%m-%d') BETWEEN '$sStartDate' AND '$sEndDate
	  						 AND 	offerCode = '$sOfferCode'"; 
	  					
	  				}
					//echo $sOtDataUpdateQuery;	  					
					$rOtDataUpdateResult = mysql_query($sOtDataUpdateQuery);
				
	  			}
	  			
	  			*/
	  			
			}
		}

	}
	
	
	// get the offers list which is not grouped
	$sOffersQuery = "SELECT offers.*
				 FROM   offers, offerLeadSpec
				 WHERE  offers.offerCode = offerLeadSpec.offerCode
				 AND    leadsGroupId = 0				 
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
	
	
	// get the groups list
	$sGroupsQuery = "SELECT *
				 FROM   leadGroups
				 ORDER BY name"; 
	$rGroupsResult = dbQuery($sGroupsQuery);
	$sGroupsOptions .= "<option value=''>Lead Group";
	while ($oGroupsRow = dbFetchObject($rGroupsResult)) {
		if ($oGroupsRow->id == $iGroupId)
		{
			$sGroupSelected = "selected";
		} else {
			$sGroupSelected = "";
		}
		$sGroupsOptions .= "<option value='$oGroupsRow->id' $sGroupSelected>$oGroupsRow->name";
	}
	

	$iCurrYear = date(Y);
	$iCurrMonth = date(m); //01 to 12
	$iCurrDay = date(d); // 01 to 31

	if (!($iStartMonth && $iStartDay && $iStartYear)) {
		$iStartMonth = $iCurrMonth;		
		$iStartDay = $iCurrDay;		
		$iStartYear = $iCurrYear;		
	} 
	
	if (!($iEndMonth && $iEndDay && $iEndYear)) {
		$iEndMonth = $iCurrMonth;		
		$iEndDay = $iCurrDay;		
		$iEndYear = $iCurrYear;		
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
<tr><td colspan=2><BR>Only successfully processed leads will be set back.
		<BR>Select date range on which leads were sent which you want to set back.</td></tr>
<tr><td colspan=2 class=header>Select Offer Code Or Group &nbsp; &nbsp; 
	<select name=sOfferCode><?php echo $sOffersOptions;?>
	</select>
	<select name=iGroupId><?php echo $sGroupsOptions;?>
	</select></td>
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
<tr><td colspan=2 align=center><BR><BR><input type=submit name=sSetBack value='Set Leads Back'></td></tr>
</table>	
<?php
	
} else {
	echo "You are not authorized to access this page...";
}
?>