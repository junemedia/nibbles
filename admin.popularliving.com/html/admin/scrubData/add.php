<?php

include("../../includes/paths.php");
session_start();
$sPageTitle = "Nibbles - Reported Revenue Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	if (($sSaveClose || $sSaveNew)) {
		$sMessage = '';
		
		if ($sOfferCode == '') {
			$sMessage = "Offer Code Is Required.";
			$bKeepValues = true;
		} elseif ($iNoOfLeads !='' && !ctype_digit($iNoOfLeads)) {
			$sMessage = "No Of Leads Must Be Numeric.";
			$bKeepValues = true;
		} else if (!ereg("^[0-9\.]*$", $fRevenue)) {
			$sMessage = "Revenue Can Contain Only Numbers or .";
			$bKeepValues = true;
		} elseif (!checkdate($iMonth, $iDay, $iYear)) {
			$sMessage = "Please correct the date.";
			$bKeepValues = true;
		}
		
		if ($sMessage == '') {
			$fRevPerLead = 0;
			$sGetRate = "SELECT revPerLead FROM offers WHERE offerCode='$sOfferCode'";
			$rRateResult = mysql_query($sGetRate);
			while ($oRateRow = mysql_fetch_object($rRateResult)) {
				$fRevPerLead = $oRateRow->revPerLead;
			}
			
			if ($iNoOfLeads == '') {
				$sWhatEntered = 'revenue';
			} else {
				$sWhatEntered = 'noOfLeads';
			}
			
			if ($iNoOfLeads == '') {
				$iNoOfLeads = round($fRevenue / $fRevPerLead);
			} else if ($fRevenue == '') {
				$fRevenue = number_format($iNoOfLeads * $fRevPerLead, 2);
			}
		}
		
		if (!$id && $sMessage == '') {
			// check if already exists
			$sCheckQuery = "SELECT *
							FROM   actualScrubData
							WHERE  offerCode = \"$sOfferCode\"
							AND dateAdded = '$iYear-$iMonth-$iDay'";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult) > 0) {
				$sMessage = "Offer Code And Date Already Exists...";
				$bKeepValues = true;
			} else {
				$sAddQuery = "INSERT INTO actualScrubData (offerCode,dateAdded,noOfLeads,revenue,whatEntered,revPerLead) 
								VALUES('$sOfferCode','$iYear-$iMonth-$iDay','$iNoOfLeads','$fRevenue', '$sWhatEntered', '$fRevPerLead')";
				$rAddResult = dbQuery($sAddQuery);
				
				
				$sAddQuery = "INSERT INTO actualScrubDataHistory (offerCode,dateAdded,noOfLeads,revenue,dateTimeAdded,whatEntered,revPerLead) 
								VALUES('$sOfferCode','$iYear-$iMonth-$iDay','$iNoOfLeads','$fRevenue', NOW(), '$sWhatEntered', '$fRevPerLead')";
				$rAddResult = dbQuery($sAddQuery);
					
				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sAddQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
			}
		} elseif ($id && $sMessage == '') {
			$sCheckQuery = "SELECT *
							FROM   actualScrubData
							WHERE  offerCode = \"$sOfferCode\"
							AND dateAdded = '$iYear-$iMonth-$iDay'
							AND id !='$id'";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult) > 0) {
				$sMessage = "Offer Code And Date Already Exists...";
				$bKeepValues = true;
			} else {
				$sTempQuery = "SELECT * FROM actualScrubData WHERE id = '$id'";
				$rTempResult = dbQuery($sTempQuery);
				while ($oTempRow = mysql_fetch_object($rTempResult)) {
					$iOldNoOfLeads = $oTempRow->noOfLeads;
					$fOldRevenue = $oTempRow->revenue;
					if ($iOldNoOfLeads == 0) { $iOldNoOfLeads = ''; }
					if ($fOldRevenue == 0.0) { $fOldRevenue = ''; }
				}
				if (($iNoOfLeads != $iOldNoOfLeads) || ($fRevenue != $fOldRevenue)) {
					$sAddQuery = "INSERT INTO actualScrubDataHistory (offerCode,dateAdded,noOfLeads,revenue,dateTimeAdded,whatEntered,revPerLead) 
								VALUES('$sOfferCode','$iYear-$iMonth-$iDay','$iNoOfLeads','$fRevenue', NOW(), '$sWhatEntered', '$fRevPerLead')";
					$rAddResult = dbQuery($sAddQuery);
				}
				
				$editQuery = "UPDATE actualScrubData 
								SET offerCode = '$sOfferCode',
								dateAdded = '$iYear-$iMonth-$iDay',
								noOfLeads = '$iNoOfLeads',
								revenue = '$fRevenue',
								whatEntered = '$sWhatEntered',
								revPerLead = '$fRevPerLead'
								WHERE  id = '$id'";
				$result = mysql_query($editQuery);
					
				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($editQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
			}
		}
	}
	
	
	if ($sSaveClose) {
		if ($bKeepValues != true) {
			echo "<script language=JavaScript>
				window.opener.location.reload();
				self.close();
				</script>";			
			exit();
		}
	} else if ($sSaveNew) {
		if ($bKeepValues != true) {
			$sReloadWindowOpener = "<script language=JavaScript>
						window.opener.location.reload();
						</script>";			
			$sOfferCode = '';
			$sDateAdded = '';
			$iNoOfLeads = '';
			$fRevenue = '';
			$id = '';
		}
	}
	
	$sDisableNoOfLeads = '';
	$sDisableRev = '';
	if ($id != '') {
		$selectQuery = "SELECT * FROM actualScrubData WHERE  id = '$id'";
		$result = mysql_query($selectQuery);
		while ($row = mysql_fetch_object($result)) {
			$sOfferCode = $row->offerCode;
			$iNoOfLeads = $row->noOfLeads;
			$fRevenue = $row->revenue;
			if ($iNoOfLeads == 0) { $iNoOfLeads = ''; }
			if ($fRevenue == 0.0) { $fRevenue = ''; }
			$iYear = substr($row->dateAdded,0,4);
			$iMonth = substr($row->dateAdded,5,2);
			$iDay = substr($row->dateAdded,8,2);
			
			$fRevPerLead = $row->revPerLead;
			
			if ($row->whatEntered == 'revenue') {
				$sDisableNoOfLeads = 'disabled';
				$iNoOfLeads = '';
			} else {
				$sDisableRev = 'disabled';
				$fRevenue = '';
			}
		}
	}

	
	
	// Get all offer code
	$rGetOfferCode = mysql_query("SELECT offerCode FROM offers ORDER BY offerCode ASC");
	$sOfferCodeOption = "<option value=''>";
	while ($oOfferRow = mysql_fetch_object($rGetOfferCode)) {
		$sOfferCodeSelected = '';
		if ($oOfferRow->offerCode == $sOfferCode) {
			$sOfferCodeSelected = "selected";
		}
		$sOfferCodeOption .= "<option value='$oOfferRow->offerCode' $sOfferCodeSelected>$oOfferRow->offerCode";
	}

	
	
	// prepare month options for From and To date
	if ($iMonth == '') { $iMonth = date('m'); }
	$sMonthOptions = '';
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		$iValue = $i+1;
		if ($iValue < 10) {
			$iValue = "0".$iValue;
		}
		if ($iValue == $iMonth) {
			$sMonthSel = "selected";
		} else {
			$sMonthSel = "";
		}
		$sMonthOptions .= "<option value='$iValue' $sMonthSel>$aGblMonthsArray[$i]";
	}
	
	
	// prepare day options for From and To date
	if ($iDay == '') { $iDay=date('d'); }
	$sDayOptions = '';
	for ($i = 1; $i <= 31; $i++) {
		if ($i < 10) {
			$iValue = "0".$i;
		} else {
			$iValue = $i;
		}
		
		if ($iValue == $iDay) {
			$sDaySel = "selected";
		} else {
			$sDaySel = "";
		}
		$sDayOptions .= "<option value='$iValue' $sDaySel>$i";
	}
	

	// prepare year options for From and To date
	$iCurrYear = date('Y');
	if ($iYear =='') { $iYear=date('Y'); }
	$sYearOptions = '';
	for ($i = $iCurrYear-1; $i <= $iCurrYear+15; $i++) {
		if ($i == $iYear) {
			$sYearSel = "selected";
		} else {
			$sYearSel = '';
		}
	
		$sYearOptions .= "<option value='$i' $sYearSel>$i";
	}
	
	
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=id value='$id'>";
	
	include("../../includes/adminAddHeader.php");
	?>
	<script language='javascript' src='../../libs/ajax.js'></script>
	<script language='javascript'>
	
	function getRate(oc) {
		if (oc !='') {
			asdf = new AmpereMedia();
			document.getElementById('rateDiv').innerHTML = "&nbsp;Rev Per Lead: $" + asdf.send('getRate.php?oc='+oc,'');
		} else {
			document.getElementById('rateDiv').innerHTML = '';
		}
	}

	function disableRev(val) {
		if (val == '') {
			//enable rev
			document.form1.fRevenue.disabled = false;
		} else {
			//disable rev
			document.form1.fRevenue.disabled = true;
		}
	}
	
	function disableNoOfLeads(val) {
		if (val == '') {
			//enable iNoOfLeads
			document.form1.iNoOfLeads.disabled = false;
		} else {
			//disable iNoOfLeads
			document.form1.iNoOfLeads.disabled = true;
		}
	}
	</script>
	<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
	<?php echo $sHidden;?>
	<?php echo $sReloadWindowOpener;?>
	<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><td width=35%>Offer Code: </td>
			<td><select name='sOfferCode' onchange="getRate(this.value);">
				<?php echo $sOfferCodeOption;?>
				</select><div id='rateDiv'></div>
			</td>
		</tr>
	
	
		<tr><td width=35%>Date: </td>
			<td><select name="iMonth"><?php echo $sMonthOptions; ?></select>
			<select name="iDay"><?php echo $sDayOptions; ?></select>
			<select name="iYear"><?php echo $sYearOptions; ?></select>
			</td>
		</tr>
		
		
		<tr><td width=35%>No of Leads: </td>
			<td><input type="text" name=iNoOfLeads size='5' onkeyup="disableRev(this.value);" maxlength="11" value="<?php echo $iNoOfLeads; ?>" <?php echo $sDisableNoOfLeads; ?>>
			&nbsp;&nbsp;Must be numeric [0-9].
			</td>
		</tr>
		
		
		<tr><td width=35%>Revenue: </td>
			<td><input type="text" name=fRevenue size='10' onkeyup="disableNoOfLeads(this.value);" maxlength="11" value="<?php echo $fRevenue; ?>" <?php echo $sDisableRev; ?>> $
			</td>
		</tr>


		<tr><td colspan="2">Notes:<br>
			- Select Offer Code from the drop down menu to see revenue per lead for that offer.
			<br>
			- If "No Of Leads" entered, "Revenue" is calculated as follow: Revenue = No Of Leads * nibbles.offers.revPerLead
			<br>
			- If "Revenue" entered, "No Of Leads" is calculated as follow: NoOfLeads = Revenue / nibbles.offers.revPerLead
			<br>
			</td>
		</tr>
	</table>

	<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>