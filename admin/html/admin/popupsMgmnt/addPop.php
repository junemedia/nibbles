<?php

/*********

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles - Popups Mgmnt";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($sSaveClose || $sSaveNew) {
		$sMessage = '';
		
		if ($sPopType == 'S') {
			if ($iFlowId == '') {
				$sMessage = "Standard Pop Triggered By Flow Id Required...";
				$bKeepValues = true;
			} else {
				if (!ctype_digit($iFlowId)) {
					$sMessage = "Standard Pop Triggered By Flow Id Must Be Numeric...";
					$bKeepValues = true;
				}
			}
			if ($iPageNo == '') {
				$sMessage = "Standard Pop Triggered By Page No Required...";
				$bKeepValues = true;
			} else {
				if (!ctype_digit($iPageNo)) {
					$sMessage = "Standard Pop Triggered By Page No Must Be Numeric...";
					$bKeepValues = true;
				}
			}
		}
		
		if ($sPopType == 'A' || $sPopType == 'W') {
			if ($iTimeDelayed == '') {
				$sMessage = "Time Delayed Required For Abandoned/Window Manager Popups.";
				$bKeepValues = true;
			} else {
				if (!(ctype_digit($iTimeDelayed))) {
					$sMessage = "Time Delayed Must Be Numeric.";
					$bKeepValues = true;
				}
			}
		}
		
		if(!($sPopupUrl)){
			$sMessage = "Pop-Up URL is required.";
			$bKeepValues = true;
		}
		
		if (!(checkdate($iMonthFrom, $iDayFrom, $iYearFrom) && checkdate($iMonthTo, $iDayTo, $iYearTo))) {
			$sMessage = "Invalid Start / End Date...";
			$bKeepValues = true;
		}
		
		$sStartTimeStamp = strtotime("$iYearFrom-$iMonthFrom-$iDayFrom");
		$sEndTimeStamp = strtotime("$iYearTo-$iMonthTo-$iDayTo");

		if ($sStartTimeStamp <= $sEndTimeStamp) {
			//echo "good";
		} else {
			$sMessage = "Start Date Must Be Earlier Than End Date...";
			$bKeepValues = true;
		}

		if ($sMessage == '') {
			$sStartDate = "$iYearFrom-$iMonthFrom-$iDayFrom";
			$sEndDate = "$iYearTo-$iMonthTo-$iDayTo";
			$sTriggerPop = '';
			if ($sPopType == 'S') {
				$sTriggerPop = "$iFlowId,$iPageNo";
			}
			
			$sPopName = addslashes($sPopName);
			
			if (!($id)) {
				$sAddQuery = "INSERT INTO popups (popType,popUpUnder,startDate,endDate,triggerPop,popupUrl,timeDelayed,popName,userName,dateTimeAdded) 
							VALUES('$sPopType','$sPopUpUnder','$sStartDate','$sEndDate','$sTriggerPop','$sPopupUrl','$iTimeDelayed',\"$sPopName\", '$sTrackingUser', NOW())";
				$rAddResult = dbQuery($sAddQuery);
			} elseif ($id) {
				$sAddQuery = "UPDATE popups 
								SET popType = '$sPopType',
								popUpUnder = '$sPopUpUnder',
								startDate = '$sStartDate',
								endDate = '$sEndDate',
								triggerPop = '$sTriggerPop',
								popupUrl = '$sPopupUrl',
								timeDelayed = '$iTimeDelayed',
								popName = \"$sPopName\"
							WHERE  id = '$id'";
				$result = mysql_query($sAddQuery);
			}
			
			// start of track users' activity in nibbles
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sAddQuery) . "\")";
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
	} else if ($sSaveNew) {
		if ($bKeepValues != true) {
			$sReloadWindowOpener = "<script language=JavaScript>
						window.opener.location.reload();
						</script>";			
			$sTriggerPop = '';
			$sPopType = '';
			$sPopUpUnder = '';
			$sStartDate = '';
			$sEndDate = '';
			$iTimeDelayed = '';
			$sPopName = '';
			$id = '';
		}
	}
	
	if ($id != '') {
		$selectQuery = "SELECT * FROM popups WHERE id = '$id'";
		$result = mysql_query($selectQuery);
		while ($row = mysql_fetch_object($result)) {
			$sTriggerPop = explode(',',$row->triggerPop);
			$sPopType = $row->popType;
			$sPopUpUnder = $row->popUpUnder;
			$sPopupUrl = $row->popupUrl;
			$sPopName = $row->popName;
			
			$iTimeDelayed = $row->timeDelayed;
			
			$sStartDate = explode('-',$row->startDate);
			$sEndDate = explode('-',$row->endDate);
			
			$iYearFrom = $sStartDate[0];
			$iMonthFrom = $sStartDate[1];
			$iDayFrom = $sStartDate[2];

			$iYearTo = $sEndDate[0];
			$iMonthTo = $sEndDate[1];
			$iDayTo = $sEndDate[2];

			$sDisableTimeDelayed = '';
			if ($row->popType == 'E' || $row->popType == 'S') {
				$sDisableTimeDelayed = ' disabled ';
			}

			$sDisableFlowPage = '';
			if ($row->popType != 'S') {
				$iFlowId = '';
				$iPageNo = '';
				$sTriggerPop = '';
				$sDisableFlowPage = ' disabled ';
			} else {
				$iFlowId = $sTriggerPop[0];
				$iPageNo = $sTriggerPop[1];
			}
		}
	}

	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=id value='$id'>";
	
	if ($sPopType == '') { $sPopType = 'S'; }
	if ($sPopUpUnder == '') { $sPopUpUnder = 'UP'; }
	
	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');
	
	// prepare month options for From and To date
	for ($i = 0; $i < count($aGblMonthsArray); $i++) {
		$iValue = $i+1;
		if ($iValue < 10) {
			$iValue = "0".$iValue;
		}
		if ($iValue == $iMonthFrom) {
			$sFromSel = "selected";
		} else {
			$sFromSel = "";
		}
		if ($iValue == $iMonthTo) {
			$sToSel = "selected";
		} else {
			$sToSel = "";
		}
		$sMonthFromOptions .= "<option value='$iValue' $sFromSel>$aGblMonthsArray[$i]";
		$sMonthToOptions .= "<option value='$iValue' $sToSel>$aGblMonthsArray[$i]";
	}

	// prepare day options for From and To date
	for ($i = 1; $i <= 31; $i++) {
		if ($i < 10) {
			$iValue = "0".$i;
		} else {
			$iValue = $i;
		}
		if ($iValue == $iDayFrom) {
			$sFromSel = "selected";
		} else {
			$sFromSel = "";
		}
		if ($iValue == $iDayTo) {
			$sToSel = "selected";
		} else {
			$sToSel = "";
		}
		$sDayFromOptions .= "<option value='$iValue' $sFromSel>$i";
		$sDayToOptions .= "<option value='$iValue' $sToSel>$i";
	}

	// prepare year options
	for ($i = $iCurrYear; $i <= $iCurrYear+5; $i++) {
		if ($i == $iYearFrom) {
			$sFromSel = "selected";
		} else {
			$sFromSel ="";
		}
		if ($i == $iYearTo) {
			$sToSel = "selected";
		} else {
			$sToSel ="";
		}
		$sYearFromOptions .= "<option value='$i' $sFromSel>$i";
		$sYearToOptions .= "<option value='$i' $sToSel>$i";
	}
	
	//flow names/ids for the dropdown.
	$sFlowNamesIds = "SELECT id, flowName FROM flows";
	$result1 = mysql_query($sFlowNamesIds);
	$sFlowNamesOptions = "<option value=''>";
	while ($row1 = mysql_fetch_object($result1)) {
		if($row1->id == $iFlowId) {
			$selected = "selected";
		} else {
			$selected = "";
		}
		
		$sFlowNamesOptions .= "<option value='$row1->id' $selected>$row1->flowName";
	}
	
	
	include("../../includes/adminAddHeader.php");
	?>
	
<script language=JavaScript>
function enableTrigger() {
	document.form1.iFlowId.disabled = false;
	document.form1.iPageNo.disabled = false;
	if (document.form1.sPopType[0].checked == true || document.form1.sPopType[1].checked == true) {
		document.form1.iTimeDelayed.disabled = true;
	} else {
		document.form1.iTimeDelayed.disabled = false;
	}
	
	if (document.form1.sPopType[0].checked == false) {
		document.form1.iFlowId.disabled = true;
		document.form1.iPageNo.disabled = true;
	}
}
function disableTrigger() {
	document.form1.iFlowId.disabled = true;
	document.form1.iPageNo.disabled = true;
	document.form1.iFlowId.value = '';
	document.form1.iPageNo.value = '';
	if (document.form1.sPopType[0].checked == true || document.form1.sPopType[1].checked == true) {
		document.form1.iTimeDelayed.disabled = true;
	} else {
		document.form1.iTimeDelayed.disabled = false;
	}
	
	if (document.form1.sPopType[0].checked == false) {
		document.form1.iFlowId.disabled = true;
		document.form1.iPageNo.disabled = true;
	}
}
</script>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
	
	<tr><td>Popup Type: </td>
		<td><input type="radio" name="sPopType" onclick="enableTrigger();" value="S" <?php if ($sPopType=='S') { echo 'checked'; } ?>> Standard
		&nbsp;&nbsp;&nbsp;<br>
		<input type="radio" name="sPopType" onclick="disableTrigger();" value="E" <?php if ($sPopType=='E') { echo 'checked'; } ?>> Exit
		&nbsp;&nbsp;&nbsp;<br>
		<input type="radio" name="sPopType" onclick="disableTrigger();" value="A" <?php if ($sPopType=='A') { echo 'checked'; } ?>> Abandoned
		&nbsp;&nbsp;&nbsp;<br>
		<input type="radio" name="sPopType" onclick="disableTrigger();" value="W" <?php if ($sPopType=='W') { echo 'checked'; } ?>> Window Manager
		</td>
	</tr>
	
	<tr><td colspan="2">&nbsp;</td></tr>
		
	<tr><td>Pop Up/Under:</td>
	<td><input type="radio" name="sPopUpUnder" value="UP" <?php if ($sPopUpUnder=='UP') { echo 'checked'; } ?>> Pop Up
		&nbsp;&nbsp;&nbsp;
		<input type="radio" name="sPopUpUnder" value="UNDER" <?php if ($sPopUpUnder=='UNDER') { echo 'checked'; } ?>> Pop Under
	</td>
	</tr>
	
	<tr>
	<td>URL:</td>
	<td><input type='text' name='sPopupUrl' value='<?php echo $sPopupUrl;?>' size=50></td>
	</tr>
	
	
	<tr><td>Start Date</td><td><select name=iMonthFrom><?php echo $sMonthFromOptions;?>
	</select> &nbsp;<select name=iDayFrom><?php echo $sDayFromOptions;?>
	</select> &nbsp;<select name=iYearFrom><?php echo $sYearFromOptions;?>
	</select></td></tr>
	
	<tr><td>End Date</td>
	<td><select name=iMonthTo><?php echo $sMonthToOptions;?>
	</select> &nbsp;<select name=iDayTo><?php echo $sDayToOptions;?>
	</select> &nbsp;<select name=iYearTo><?php echo $sYearToOptions;?></select></td></tr>
	
	<tr>
	<td>Standard Pop Triggered By:</td>
	<td>
		<select name=iFlowId <?php echo $sDisableFlowPage; ?>>
			<?php echo $sFlowNamesOptions; ?>
		</select>
		
		<!--<input type="text" size="3" maxlength="3" name="iFlowId" value="<?php //echo $iFlowId; ?>" <?php //echo $sDisableFlowPage; ?>>-->,
		<input type="text" size="3" maxlength="3" name=iPageNo value='<?php echo $iPageNo; ?>' <?php echo $sDisableFlowPage; ?>>
		&nbsp;&nbsp;&nbsp;( FlowId , Page No )
	</td></tr>
	
	
	
	<tr>
	<td>Time Delayed:</td>
	<td><input type="text" size="3" maxlength="10" name="iTimeDelayed" value="<?php echo $iTimeDelayed; ?>" <?php echo $sDisableTimeDelayed; ?>>
		&nbsp;&nbsp;&nbsp;( Number of seconds )
	</td></tr>
	
	<tr><td width=35%>Popup Name or Description: </td>
		<td><textarea name=sPopName rows=3 cols=50><?php echo $sPopName;?></textarea></td>
	</tr>
	
	
	<tr><td colspan="2">&nbsp;</td></tr>
</table>	
<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>