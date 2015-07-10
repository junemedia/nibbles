<?php

include("../../includes/paths.php");
session_start();
$sPageTitle = "Nibbles - Scrub Rate Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	if (($sSaveClose || $sSaveNew)) {
		$sMessage = '';
		$fScrubRate = str_replace("%",'',$fScrubRate);
		
		if (!ereg("^[0-9\.]*$", $fScrubRate)) {
			$sMessage = "Scrub Rate Can Contain Only Numbers or .";
			$bKeepValues = true;
		} elseif (!checkdate($iMonth, $iDay, $iYear)) {
			$sMessage = "Please correct the date.";
			$bKeepValues = true;
		}
		
		if ($id && $sMessage == '') {
			$editQuery = "UPDATE offerScrubHistory 
						SET dateTimeAdded = '$iYear-$iMonth-$iDay $iHour:$iMin:$iSec',
						scrubRate = '$fScrubRate'
						WHERE  id = '$id' LIMIT 1";
			$result = mysql_query($editQuery);

			// start of track users' activity in nibbles
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($editQuery) . "\")";
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
			exit();
		}
	}
	

	if ($id != '') {
		$selectQuery = "SELECT * FROM offerScrubHistory WHERE  id = '$id'";
		$result = mysql_query($selectQuery);
		while ($row = mysql_fetch_object($result)) {
			$sOfferCode = $row->offerCode;
			$fScrubRate = $row->scrubRate;
			
			$iYear = substr($row->dateTimeAdded,0,4);
			$iMonth = substr($row->dateTimeAdded,5,2);
			$iDay = substr($row->dateTimeAdded,8,2);
			
			$iHour = substr($row->dateTimeAdded,11,2);
			$iMin = substr($row->dateTimeAdded,14,2);
			$iSec = substr($row->dateTimeAdded,17,2);
		}
	}

	$sHourOptions = "";
	for ($i = 0; $i < 24; $i++) {
		$iValue = $i;
		$sSelected = "";
		if ($iValue < 10) {
			$iValue = "0".$iValue;
		}

		if ($iValue == $iHour) {
			$sSelected = "selected";
		} else {
			if($iValue == '00') {
				$sSelected = "selected";
			}
		}
		$sHourOptions .= "<option value='$iValue' $sSelected>$iValue";
	}
	
	
	$sMinOptions = "";
	for ($i = 0; $i < 60; $i++) {
		$iValue = $i;
		if ($iValue < 10) {
			$iValue = "0".$iValue;
		}
		$sSelected = '';
		if ($iValue == $iMin) {
			$sSelected = "selected";
		} else {
			if($iValue == '00') {
				$sSelected = "selected";
			}
		}
		$sMinOptions .= "<option value='$iValue' $sSelected>$iValue";
	}
	
	
	$sSecOptions = "";
	for ($i = 0; $i < 60; $i++) {
		$iValue = $i;
		if ($iValue < 10) {
			$iValue = "0".$iValue;
		}
		$sSelected = '';
		if ($iValue == $iSec) {
			$sSelected = "selected";
		} else {
			if($iValue == '00') {
				$sSelected = "selected";
			}
		}
		$sSecOptions .= "<option value='$iValue' $sSelected>$iValue";
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
	<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
	<?php echo $sHidden;?>
	<?php echo $sReloadWindowOpener;?>
	<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><td width=35%>Offer Code: </td>
			<td><?php echo $sOfferCode;?>
			</td>
		</tr>
	
	
		<tr><td width=35%>Date: </td>
			<td><select name="iMonth"><?php echo $sMonthOptions; ?></select>
			<select name="iDay"><?php echo $sDayOptions; ?></select>
			<select name="iYear"><?php echo $sYearOptions; ?></select>
			&nbsp; &nbsp; &nbsp; &nbsp; 
			<select name="iHour"><?php echo $sHourOptions; ?></select>&nbsp; : &nbsp; 
			<select name="iMin"><?php echo $sMinOptions; ?></select>&nbsp; : &nbsp; 
			<select name="iSec"><?php echo $sSecOptions; ?></select>
			</td>
		</tr>
		
		
		<tr><td width=35%>Scrub Rate: </td>
			<td><input type="text" name=fScrubRate size='5' maxlength="11" value="<?php echo $fScrubRate; ?>">
			&nbsp;&nbsp;For example: if 30%, enter as 30 or 30.0. Do not include '%' sign.
			</td>
		</tr>
		
		<tr><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2">&nbsp;</td></tr>
		
		<tr><td colspan=2 align=center >
			<input type=submit name=sSaveClose value='Save & Close'> &nbsp; &nbsp; 
			<input type=button name=sAbandonClose value='Abandon & Close' onclick="self.close();" ></td><td></td>
		</tr>
	</table>
	<?php
} else {
	echo "You are not authorized to access this page...";
}
?>