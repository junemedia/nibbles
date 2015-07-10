<?php


// get inbound vars to rename incoming vars
// place this code here outside of the loop if ($isset($_SESSION['aSesInboundQueryString') loop
// because incoming variable will be needed every time when user comes to the page again in case of error.
// all incoming variables also are attached in the url when redirected again to the same page with error message
// so again rename all the incoming variables to the mapping variable names
// except the variables user may change. i.e. user's own info should not be changed what it was in incoming url
// but it should be remain whatever user submitted through the form
include_once("$sGblLibsPath/validationFunctions.php");

// get the referer page name to go back to that page if any error in data
$sRefererScriptFileName = $_SERVER['HTTP_REFERER'];
// remove any queryString variables...
if (strstr($sRefererScriptFileName,"?")) {
	$sRefererScriptFileName = substr($sRefererScriptFileName, 0, strpos($sRefererScriptFileName, "?"));
}


// set single offer page's pageId
$iPageId = '9195';
$_SESSION["iSesPageId"] = $iPageId;
	
// session varible is set in sop.php as per offer mode
// if offer is in test mode, make the page as test mode
if ($_SESSION['sSesMode'] != 'T') {
	$sPageMode = 'A';
} else {
	$sPageMode = 'T';
}


$aQueryStringArray = explode("&", $sGblQueryString);

//echo ;

// set incoming variable every time only when sopPage.php and not sopPageSubmit.php
if(!strstr($_SERVER['PHP_SELF'], "scFlowSopSubmit.php")) {

	// get inbound vars to rename incoming vars
	$aInboundVarMap = explode(",",$sInboundVarMap);
	
	$_SESSION["aSesInboundQueryString"] = array();
	
	$j=0;
	$aQueryStringArray = explode("&", $sGblQueryString);
	for ($i=0; $i<count($aQueryStringArray); $i++) {
		$aKeyValuePair = explode("=",$aQueryStringArray[$i]);
		$key = $aKeyValuePair[0];
		$value = $aKeyValuePair[1];
		
		if ($key == 'src') {
			$_SESSION['sSesPage1JavaScriptVars'] .= "\n var tempSrc = '".$value."';";
			$bSrcDefined = true;
		}
		
					
		if ($key != 'e' && $key != 'f' && $key != 'l' && $key != 'a1' && $key != 'a2' && $key != 'c' &&
		$key != 's' && $key != 'z' && $key != 'p' && $key != 'src' && $key != 'ss' &&
		 $key != 'g' && $key != 'iMenuId' && $key != 'iId' && $key != 'sMessage' && $key != 'PHPSESSID') {
			//echo "<BR>$key $value";
			$_SESSION['aSesInboundQueryString'][$j]['key'] = $key;
			$_SESSION['aSesInboundQueryString'][$j]['value'] = $value;
			
			$j++;
		} else 	if ($key != 'iMenuId' && $key != 'iId') {
			// g variable should be available on page2 as javascript variable
			
			if (ltrim((rtrim($key))) == 'g') {
				$_SESSION['sSesInboundJavaScriptVars'] .= "\n var $key = '".$value."';";
			}
			$sPrepopcodes .= "$key=$value&";		
		}
	}
	
	if ($sPrepopcodes != '') {
		$sPrepopcodes = substr($sPrepopcodes, 0, strlen($sPrepopcodes)-1);
	}
	
	if (!$bSrcDefined) {
		$_SESSION['sSesPage1JavaScriptVars'] .= "\n var tempSrc = '';";
	}
}



for ($j=0; $j<count($_SESSION["aSesInboundQueryString"]); $j++) {
	if ($_SESSION["aSesInboundQueryString"][$j]['key'] != '') {	
		$sOutboundQueryString .= $_SESSION["aSesInboundQueryString"][$j]['key']."=".$_SESSION["aSesInboundQueryString"][$j]['value']."&";
	}
}
			
if ($sOutboundQueryString != '') {
	$sOutboundQueryString = substr($sOutboundQueryString,0, strlen($sOutboundQueryString)-1);
			
}

//echo $sOutboundQueryString;

// Initialize session variables, if session is not yet created
if (!(session_id())) {
	$_SESSION["sSesSalutation"] = '';
	$_SESSION["sSesFirst"] = '';
	$_SESSION["sSesLast"] = '';
	$_SESSION["sSesEmail"] = '';
	$_SESSION["sSesAddress"] = '';
	$_SESSION["sSesAddress2"] = '';
	$_SESSION["sSesCity"] = '';
	$_SESSION["sSesState"] = '';
	$_SESSION["sSesZip"] = '';
	$_SESSION["sSesPhone"] = '';
	$_SESSION["sSesRemoteIp"] = '';
	$_SESSION["iSesJoinListId"] = '';
	$_SESSION["sSesSourceCode"] = '';
	$_SESSION["sSesSubSourceCode"] = '';
	$_SESSION["sSesPageMode"] = '';
	$_SESSION["sSesOffersTaken"] = '';
	$_SESSION["iSesBirthYear"] = '';
	$_SESSION["iSesBirthMonth"] = '';
	$_SESSION["iSesBirthDay"] = '';
	
	$_SESSION["sSesFirstNameAsterisk"] = '';
	$_SESSION["sSesLastNameAsterisk"] = '';
	$_SESSION["sSesAddressAsterisk"] = '';
	$_SESSION["sSesAddress2Asterisk"] = '';
	$_SESSION["sSesCityAsterisk"] = '';
	$_SESSION["sSesStateAsterisk"] = '';
	$_SESSION["sSesZipCodeAsterisk"] = '';
	$_SESSION["sSesPhoneNoAsterisk"] = '';
	$_SESSION["sSesEmailAsterisk"] = '';
	$_SESSION["sSesGoTo"] = '';
	
}

if ($g) {
	//session_register("sSesGoTo");
	$_SESSION["sSesGoTo"] = $g;
}


///************* replace user form variables now ********************/
	
	
// display values in case of errors if submitted before


if ($e) {
	$sEmail = $e;
}

if ($f) {
	$sFirst = $f;
}

if ($l) {
	$sLast = $l;
}

if ($a1) {
	$sAddress = $a1;
}

if ($a2) {
	$sAddress2 = $a2;
}

if ($c) {
	$sCity = $c;
}

if ($s) {
	$sState = $s;
}

if ($z) {
	$sZip = $z;
}

if ($p) {
	$sPhone = $p;
}

if ($src) {
	$sSourceCode = $src;
}
if ($ss) {
	$sSubSourceCode = $ss;
}


if ($gn) {
	$sGender = $gn;
}

if ($by) {
	$iBirthYear = $by;
}

if ($bd) {
	$iBirthDay = $bd;
}

if ($bm) {
	$iBirthMonth = $bm;
}

if ($pa) {
	$sPhone_areaCode = $pa;
}

if ($pe) {
	$sPhone_exchange = $pe;
}

if ($pnum) {
	$sPhone_number = $pnum;
}




// If values are already set in session variables, prepopulate it
if (session_id()) {
		
	
	
	if ($_SESSION["sSesSalutation"] && !(isset($sSalutation))) {
		$sSalutation = $_SESSION["sSesSalutation"];
	}
	
	if ($_SESSION["sSesEmail"] && $e =='' && !(isset($sEmail))) {
		$sEmail = $_SESSION["sSesEmail"];
	}
	
	if ($_SESSION["sSesFirst"] && $f =='' && !(isset($sFirst))) {
		$sFirst = $_SESSION["sSesFirst"];
	}
	
	if ($_SESSION["sSesLast"] && $l =='' && !(isset($sLast))) {
		$sLast = $_SESSION["sSesLast"];
	}
	
	if ($_SESSION["sSesAddress"] && $a1 =='' && !(isset($sAddress))) {
		$sAddress = $_SESSION["sSesAddress"];
	}
	
	if ($_SESSION["sSesAddress2"] && $a2 =='' && ! isset($sAddress2)) {
		$sAddress2 = $_SESSION["sSesAddress2"];
	}
	
	if ($_SESSION["sSesCity"] && $c =='' && !(isset($sCity))) {
		$sCity = $_SESSION["sSesCity"];
	}
	
	if ($_SESSION["sSesState"] && $s =='' && !(isset($sState))) {
		$sState = $_SESSION["sSesState"];
	}
	
	if ($_SESSION["sSesZip"] && $z =='' && !(isset($sZip))) {
		$sZip = $_SESSION["sSesZip"];
	}
	
	if ($_SESSION["sSesPhone"] && $p =='' && !(isset($sPhone))) {
		$sPhone = $_SESSION["sSesPhone"];
	}
	
		
	if ($_SESSION["sSesPhoneAreaCode"] && $pa =='' && !(isset($sPhone_areaCode))) {
		$sPhone_areaCode = $_SESSION["sSesPhoneAreaCode"];
	}
	
	if ($_SESSION["sSesPhoneExchange"] && $pe =='' && !(isset($sPhone_exchange))) {
		$sPhone_exchange = $_SESSION["sSesPhoneExchange"];
	}
	
	if ($_SESSION["sSesPhoneNumber"] && $pnum =='' && !(isset($sPhone_number))) {
		$sPhone_number = $_SESSION["sSesPhoneNumber"];
	}
	
	
	if ($_SESSION["iSesJoinListId"] && $iJoinListId == '') {
		$iJoinListId = $_SESSION["iSesJoinListId"];
	}
	
	if ($_SESSION["sSesSourceCode"] && $src =='' && !(isset($sSourceCode))) {
		$sSourceCode = $_SESSION["sSesSourceCode"];
	}
	
	if ($_SESSION["sSesSubSourceCode"] && $ss == '' && !(isset($sSubSourceCode))) {
		$sSubSourceCode = $_SESSION["sSesSubSourceCode"];
	}
	
	if ($_SESSION["sSesPageMode"] && $sPageMode = '' && !(isset($sPageMode))) {
		$sPageMode = $_SESSION["sSesPageMode"];
	}
	
	if ($_SESSION["iSesBirthYear"] && $iBirthYear = '' && !(isset($iBirthYear))) {
		$iBirthYear = $_SESSION["iSesBirthYear"];
	}
	
	if ($_SESSION["iSesBirthMonth"] && $iBirthMonth = '' && !(isset($iBirthMonth))) {
		$iBirthMonth = $_SESSION["iSesBirthMonth"];
	}
	
	if ($_SESSION["iSesBirthDay"] && $iBirthDay = '' && !(isset($iBirthDay))) {
		$iBirthDay = $_SESSION["iSesBirthDay"];
	}

	if ($iBirthDay == '' && $_SESSION["iSesBirthDay"] !='') {
		$iBirthDay = $_SESSION["iSesBirthDay"];
	}
	
	if ($iBirthMonth == '' && $_SESSION["iSesBirthMonth"] !='') {
		$iBirthMonth = $_SESSION["iSesBirthMonth"];
	}
	
	if ($iBirthYear == '' && $_SESSION["iSesBirthYear"] !='') {
		$iBirthYear = $_SESSION["iSesBirthYear"];
	}
	
	
	if ($_SESSION["sSesGender"] && $sGender = '' && !(isset($sGender))) {
		$sGender = $_SESSION["sSesGender"];
	}
	
	if ($sGender == '' && $_SESSION["sSesGender"] !='') {
		$sGender = $_SESSION["sSesGender"];
	}
	
}


if ($sPhone == '----' || $sPhone == '--') {
	$sPhone = '';
}
	
// get separate parts of the phone no for later use
if ($sPhone != '') {
	if (strlen($sPhone) == 10) {
		$sPhone_areaCode = substr($sPhone,0,3);
		$sPhone_exchange = substr($sPhone,3,3);
		$sPhone_number = substr($sPhone,6,4);
	} else {
		$sPhone_areaCode = substr($sPhone,0,3);
		$sPhone_exchange = substr($sPhone,4,3);
		$sPhone_number = substr($sPhone,8,4);
	}
}

$sFirst = stripslashes($sFirst);
$sLast = stripslashes($sLast);
$sAddress = stripslashes($sAddress);
$sAddress2 = stripslashes($sAddress2);

// prepare state options
$sStateQuery = "SELECT *
				FROM   states
				ORDER BY state";
$rStateResult = dbQuery($sStateQuery);
$sStateOptions = "<option value=''>";
while ($oStateRow = dbFetchObject($rStateResult)) {
	if ($sState == $oStateRow->stateId) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sStateOptions .= "<option value=$oStateRow->stateId $sSelected>$oStateRow->state";
}

if ($rStateResult) {
	dbFreeResult($rStateResult);
}

// prepare salutation options
$sMrSelected = "";
$sMrsSelected = "";
$sMsSelected = "";
$sDrSelected = "";
$sOtherSelected = "";

switch ($sSalutation) {
	case "Mr.":
	$sMrSelected = "selected";
	break;
	case "Mrs.":
	$sMrsSelected = "selected";
	break;
	case "Ms.":
	$sMsSelected = "selected";
	break;
	case "Dr.":
	$sDrSelected = "selected";
	break;
	case "Other":
	$sOtherSelected = "selected";
	break;
}

$sSalutationOptions = "<option value=''>
					   <option value='Mr.' $sMrSelected>Mr.
					   <option value='Mrs.' $sMrsSelected>Mrs.
					   <option value='Ms.' $sMsSelected>Ms.
					   <option value='Dr.' $sDrSelected>Dr.
					   <option value='Other' $sOtherSelected>Other";



/*********************************************/

// SC FLOW BIRTH DATE OPTIONS

$sYearQuery = "SELECT * FROM scFlowBirthDateOptions WHERE year !='' ORDER BY year DESC";
$rYearResult = dbQuery($sYearQuery);
$sBirthYearOptions = "<option value=''>Year";
if ($rYearResult) {
	while ($sYearTemp = dbFetchObject($rYearResult)) {
		if ($iBirthYear == $sYearTemp->year) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		$sBirthYearOptions .= "<option value=$sYearTemp->year $sSelected>$sYearTemp->year";
	}
}


$sMonthQuery = "SELECT * FROM scFlowBirthDateOptions WHERE month !='' ORDER BY month ASC";
$rMonthResult = dbQuery($sMonthQuery);
$sBirthMonthOptions = "<option value=''>Month";
if ($rMonthResult) {
	while ($sMonthTemp = dbFetchObject($rMonthResult)) {
		$sTempMonth = $sMonthTemp->month;
		
		if (strlen($sTempMonth) == 1) {
			$sTempMonth = '0'.$sTempMonth;
		}
		
		if ($iBirthMonth == $sTempMonth) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		$sBirthMonthOptions .= "<option value='$sTempMonth' $sSelected>$sTempMonth";
	}
}


$sDayQuery = "SELECT * FROM scFlowBirthDateOptions WHERE day !='' ORDER BY day ASC";
$rDayResult = dbQuery($sDayQuery);
$sBirthDayOptions = "<option value=''>Day";
if ($rMonthResult) {
	while ($sDayTemp = dbFetchObject($rDayResult)) {
		$sTempDay = $sDayTemp->day;
		if (strlen($sTempDay) == 1) {
			$sTempDay = '0'.$sTempDay;
		}
		
		if ($iBirthDay == $sTempDay) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		$sBirthDayOptions .= "<option value='$sTempDay' $sSelected>$sTempDay";
	}
}

/******************************************/
// Get State From ZipCode IF state is Not Blank
if ($sState == '' && $_SESSION["sSesState"] == '') {
	if ($sZip !='') {
		$sGetStateFromZip = "SELECT * FROM zipStateCity WHERE zip='$sZip'";
	}
	
	if ($_SESSION["sSesZip"] !='') {
		$sGetStateFromZip = "SELECT * FROM zipStateCity WHERE zip='".$_SESSION["sSesZip"]."'";
	}
	
	if ($sZip !='' || $_SESSION["sSesZip"]!='') {
		$rGetStateResult = dbQuery($sGetStateFromZip);
		while ($sStateRow = dbFetchObject($rGetStateResult)) {
			$sState = $sStateRow->state;
			$_SESSION["sSesState"] = $sState;
		}
		if ($rGetStateResult) {
			dbFreeResult($rGetStateResult);
		}
	}
}




	if ((validateBirthDate($iBirthYear, $iBirthMonth, $iBirthDay))) {
		if ($_SESSION["iSesBirthYear"] != '') { $sBirthYearDisable = 'disabled'; }
		if ($_SESSION["iSesBirthMonth"] != '') { $sBirthMonthDisable = 'disabled'; }
		if ($_SESSION["iSesBirthDay"] != '') { $sBirthDayDisable = 'disabled'; }
		if ($_SESSION["sSesGender"] != '') { $sGenderDisable = 'disabled'; }
	}

	if ($_SESSION["sSesGender"] == 'M') { $sMaleChecked = ' checked '; $sGenderDisable = 'disabled'; }
	if ($_SESSION["sSesGender"] == 'F') { $sFemaleChecked = ' checked '; $sGenderDisable = 'disabled'; }


	if ($_SESSION["sSesSalutation"] != '') { $sSalutationDisable = 'disabled'; }

	if ($_SESSION["sSesFirst"] != '') { $sFirstDisable = 'disabled'; }

		if ($_SESSION["sSesLast"] != '') {
			$sLastDisable = 'disabled';
		}

		if ($_SESSION["sSesEmail"] != '') {
			$sEmailDisable = 'disabled';
		}

		if ($_SESSION["sSesAddress"] != '') {
			$sAddressDisable = 'disabled';
		}

		if ($_SESSION["sSesAddress2"] != '') {
			$sAddress2Disable = 'disabled';
		}

		if ($_SESSION["sSesCity"] != '') {
			$sCityDisable = 'disabled';
		}

		if ($_SESSION["sSesState"] != '') {
			$sStateDisable = 'disabled';
		}

		if ($_SESSION["sSesZip"] != '') {
			$sZipDisable = 'disabled';
		}

		if ($_SESSION['sSesPhone'] != '') {
			$sPhoneDisable = 'disabled';
		}

		if ($_SESSION['sSesPhoneNoDash'] != '') {
			$sPhoneDisable = 'disabled';
		}
		
		if ($_SESSION['sSesPhoneExt'] != '') {
			$sPhoneDisable = 'disabled';
		}
		
		if (!(ctype_digit($sPhone_areaCode) && ctype_digit($sPhone_exchange) && ctype_digit($sPhone_number))) {
			$sPhoneDisable = '';
		}

		if ($_SESSION['sSesScFlowAllowEditUserForm']) {
			$sBirthYearDisable = '';
			$sBirthMonthDisable = '';
			$sBirthDayDisable = '';
			$sGenderDisable = '';
			$sSalutationDisable = '';
			$sFirstDisable = '';
			$sLastDisable = '';
			$sEmailDisable = '';
			$sAddressDisable = '';
			$sAddress2Disable = '';
			$sCityDisable = '';
			$sStateDisable = '';
			$sZipDisable = '';
			$sPhoneDisable = '';
		}

		$sTempDobGenderFormFromAbove = "<tr>
		<td align='right' nowrap='nowrap'>
		<font size='2' face='Arial,Helvetica'><b>Birth Date </b></font>
		</td>
		<td><select name='iBirthMonth' $sBirthMonthDisable>$sBirthMonthOptions</select>
		<select name='iBirthDay' $sBirthDayDisable>$sBirthDayOptions</select>
		<select name='iBirthYear' $sBirthYearDisable>$sBirthYearOptions</select>
		<span class='required'>required</span>
		</td></tr>
		
        
	    <tr><td align='right' nowrap='nowrap'><font size='2' face='Arial,Helvetica'><b>Gender </b></font>
	    </td><td>
	    	<input type='radio' name='sGender' value='M' $sGenderDisable $sMaleChecked >Male&nbsp;&nbsp;&nbsp;
			&nbsp;&nbsp;<input type='radio' name='sGender' value='F' $sGenderDisable $sFemaleChecked >Female
			<span class='required'>required</span>
		</td></tr>";

		if ($_SESSION['TEMPTEMP'] == 'WNWM_EML' || $_SESSION['TEMPTEMP'] == 'WNWM_VML') {
			$sTempDobGenderFormFromAbove = '';
		}
$prot = ($_SERVER['HTTPS'] ? 'https://' : 'http://');
$sUserFormPage2Left = "<br /> 
<img src='".$prot."www.popularliving.com/images/page2Spacer.gif' height='2' vspace='4' width='100%'>
<br />
<table align='left' border='0' cellpadding='2' cellspacing='0'>
	<tr>
		<td align='right' nowrap='nowrap'>
			<font size='2' face='Arial,Helvetica'><b>First Name </b></font>
		</td>
		<td>
			<input type='text' name='sFirst' value='$sFirst' size='30' $sFirstDisable>
			<span class='required'>required</span>
		</td>
	</tr>
	<tr>
		<td align='right' nowrap='nowrap'>
			<font size='2' face='Arial,Helvetica'><b>Last Name </b></font>
		</td>
		<td>
			<input type='text' name='sLast' value='$sLast' size='30' $sLastDisable>
			<span class='required'>required</span>
		</td>
	</tr>
	<tr>
		<td align='right' nowrap='nowrap'>
			<font size='2' face='Arial,Helvetica'><b>Address </b></font>
		</td>
		<td>
			<input type='text' name='sAddress' value='$sAddress' size='30' $sAddressDisable>
			<span class='required'>required</span>
		</td>
	</tr>
	<tr>
		<td align='right' nowrap='nowrap'>
			<font size='2' face='Arial,Helvetica'><b></b></font>
		</td>
		<td>
			<input type='text' name='sAddress2' value='$sAddress2' size='30' $sAddress2Disable>
		</td>
	</tr>
	<tr>
		<td align='right' nowrap='nowrap'>
			<font size='2' face='Arial,Helvetica'><b>City </b></font>
		</td>
		<td>
			<input type='text' name='sCity' value='$sCity' size='20' $sCityDisable>
			<span class='required'>required</span>
		</td>
	</tr>
	<tr>
		<td align='right' nowrap='nowrap'>
			<font size='2' face='Arial,Helvetica'><b>State </b></font>
		</td>
		<td>
			<select name=sState size='1' $sStateDisable> 
			$sStateOptions</select>
			<span class='required'>required</span>
		</td>
	</tr>
	<tr>
		<td align='right' nowrap='nowrap'>
			<font size='2' face='Arial,Helvetica'><b>Zip Code </b></font>
		</td>
		<td>
			<input type='text' name='sZip' value='$sZip' size='7' maxlength='5' $sZipDisable>
			<span class='required'>required</span>
		</td>
	</tr>
	<tr>
		<td align='right' nowrap='nowrap'>
			<font size='2' face='Arial,Helvetica'><b>Phone Number </b></font>
		</td>
		<td>
			<input type='text' name='sPhone_areaCode' value='$sPhone_areaCode' size='3' maxlength='3' $sPhoneDisable>&nbsp;-&nbsp;
			<input type='text' name='sPhone_exchange' value='$sPhone_exchange' size='3' maxlength='3' $sPhoneDisable>&nbsp;-&nbsp;
			<input type='text' name='sPhone_number' value='$sPhone_number' size='4' maxlength='4' $sPhoneDisable>
			<span class='required'>required</span>
		</td>
	</tr>
		<tr>
		<td align='right' nowrap='nowrap'>
			<font size='2' face='Arial,Helvetica'><b>E-Mail Address </b></font>
		</td>
		<td>
			<input type='text' name='sEmail' value='$sEmail' size='30' $sEmailDisable>
			<span class='required'>required</span>
		</td>
	</tr>
	$sTempDobGenderFormFromAbove
</table>";
$prot = ($_SERVER['HTTPS'] ? 'https://' : 'http://');
$sUserFormPage2Center = "<br /> 
<img src='".$prot."www.popularliving.com/images/page2Spacer.gif' height='2' vspace='4' width='100%'>
<br />
<table align='center' border='0' cellpadding='2' cellspacing='0'>
	<tr>
		<td align='right' nowrap='nowrap'>
			<font size='2' face='Arial,Helvetica'><b>First Name </b></font>
		</td>
		<td>
			<input type='text' name='sFirst' value='$sFirst' size='30' $sFirstDisable>
			<span class='required'>required</span>
		</td>
	</tr>
	<tr>
		<td align='right' nowrap='nowrap'>
			<font size='2' face='Arial,Helvetica'><b>Last Name </b></font>
		</td>
		<td>
			<input type='text' name='sLast' value='$sLast' size='30' $sLastDisable>
			<span class='required'>required</span>
		</td>
	</tr>
	<tr>
		<td align='right' nowrap='nowrap'>
			<font size='2' face='Arial,Helvetica'><b>Address </b></font>
		</td>
		<td>
			<input type='text' name='sAddress' value='$sAddress' size='30' $sAddressDisable>
			<span class='required'>required</span>
		</td>
	</tr>
	<tr>
		<td align='right' nowrap='nowrap'>
			<font size='2' face='Arial,Helvetica'><b></b></font>
		</td>
		<td>
			<input type='text' name='sAddress2' value='$sAddress2' size='30' $sAddress2Disable>
		</td>
	</tr>
	<tr>
		<td align='right' nowrap='nowrap'>
			<font size='2' face='Arial,Helvetica'><b>City </b></font>
		</td>
		<td>
			<input type='text' name='sCity' value='$sCity' size='20' $sCityDisable>
			<span class='required'>required</span>
		</td>
	</tr>
	<tr>
		<td align='right' nowrap='nowrap'>
			<font size='2' face='Arial,Helvetica'><b>State </b></font>
		</td>
		<td>
			<select name=sState size='1' $sStateDisable> 
			$sStateOptions</select>
			<span class='required'>required</span>
		</td>
	</tr>
	<tr>
		<td align='right' nowrap='nowrap'>
			<font size='2' face='Arial,Helvetica'><b>Zip Code </b></font>
		</td>
		<td>
			<input type='text' name='sZip' value='$sZip' size='7' maxlength='5' $sZipDisable>
			<span class='required'>required</span>
		</td>
	</tr>
	<tr>
		<td align='right' nowrap='nowrap'>
			<font size='2' face='Arial,Helvetica'><b>Phone Number </b></font>
		</td>
		<td>
			<input type='text' name='sPhone_areaCode' value='$sPhone_areaCode' size='3' maxlength='3' $sPhoneDisable>&nbsp;-&nbsp;
			<input type='text' name='sPhone_exchange' value='$sPhone_exchange' size='3' maxlength='3' $sPhoneDisable>&nbsp;-&nbsp;
			<input type='text' name='sPhone_number' value='$sPhone_number' size='4' maxlength='4' $sPhoneDisable>
			<span class='required'>required</span>
		</td>
	</tr>
		<tr>
		<td align='right' nowrap='nowrap'>
			<font size='2' face='Arial,Helvetica'><b>E-Mail Address </b></font>
		</td>
		<td>
			<input type='text' name='sEmail' value='$sEmail' size='30' $sEmailDisable>
			<span class='required'>required</span>
		</td>
	</tr>
	
	$sTempDobGenderFormFromAbove
		
</table>";










?>
