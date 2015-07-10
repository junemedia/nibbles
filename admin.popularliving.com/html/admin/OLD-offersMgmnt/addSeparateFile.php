<?php

/*********

Script to Add/Edit Offer

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblLibsPath/dateFunctions.php");

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
$sPageTitle = "Nibbles Offers - Separate Lead File";


if ($sSaveClose || $sSaveNew || $sSaveContinue) { 
						
					// update lead spec entry
					
					$sLeadSpecUpdateQuery = "UPDATE offerLeadSpec
											 SET    deliveryMethodId2 = '$iDeliveryMethodId',													
													postingUrl2 = \"$sPostingUrl\", 
													httpPostString2 = \"$sHttpPostString\",
													ftpSiteUrl2 = '$sFtpSiteUrl', 
													initialFtpDirectory2 = \"$sInitialFtpDirectory\",
													isSecured2 = '$iIsSecured', 
													userId2 = '$sUserId', 
													passwd2 = '$sPasswd', 
													leadFileName2 ='$sLeadFileName',
													isEncrypted2 = '$iIsEncrypted', 
													encMethod2 = '$sEncMethod', 
													encType2 = '$sEncType',
													encKey2 = \"$sEncKey\", 
													headerText2 = \"$sHeaderText\", 
													footerText2 =  \"$sFooterText\", 
													leadsQuery2 = \"$sLeadsQuery\",
													fieldDelimiter2 = \"$sFieldDelimiter\", 
													fieldSeparater2 = \"$sFieldSeparater\", 
													endOfLine2 = \"$sEndOfLine\", 
													leadsEmailSubject2 = \"$sLeadsEmailSubject\", 
													leadsEmailFromAddr2 = \"$sLeadsEmailFromAddr\",
													leadsEmailBody2 = \"$sLeadsEmailBody\",																	
													testEmailRecipients2 = \"$sTestEmailRecipients\",
													countEmailRecipients2 = \"$sCountEmailRecipients\", 
													leadsEmailRecipients2 = \"$sLeadsEmailRecipients\"													
											 WHERE  offerCode = '$sOfferCode'";
					
					$rLeadSpecUpdateResult = dbQuery($sLeadSpecUpdateQuery);
					echo dbError();
					if (!($rLeadSpecUpdateResult)) {
						$bKeepValues = true;
					}

		 if ($sSaveClose) {
			if ($bKeepValues != true) {
				echo "<script language=JavaScript>
					//window.opener.location.href = '".$sPageReloadUrl."';
					self.close();
					</script>";			
				// exit from this script
				exit();
			}

		}
		$iId = '';
	}

if (($iId != ''  || $sOfferCode != '') && $bKeepValues != "true") {
	// If Clicked Edit, display values in fields and
	// buttons to edit/Reset...
	
	
		// get lead spec data
		
		$sLeadSpecQuery = "SELECT *
						   FROM   offerLeadSpec
						   WHERE  offerCode = '$sOfferCode'";
		$rLeadSpecResult = dbQuery($sLeadSpecQuery);
		while ($oLeadSpecRow = dbFetchObject($rLeadSpecResult)) {
			
			$iDeliveryMethodId = $oLeadSpecRow->deliveryMethodId2;		
			$sPostingUrl = $oLeadSpecRow->postingUrl2;
			$sHttpPostString = $oLeadSpecRow->httpPostString2;
			$sFtpSiteUrl = $oLeadSpecRow->ftpSiteUrl2;
			$sInitialFtpDirectory = $oLeadSpecRow->initialFtpDirectory2;
			$iIsSecured = $oLeadSpecRow->isSecured2;
			$sUserId = $oLeadSpecRow->userId2;
			$sPasswd = $oLeadSpecRow->passwd2;
			$sLeadFileName = $oLeadSpecRow->leadFileName2;
			$iIsEncrypted = $oLeadSpecRow->isEncrypted2;
			$sEncMethod = $oLeadSpecRow->encMethod2;
			$sEncKey = $oLeadSpecRow->encKey2;
			$sHeaderText = $oLeadSpecRow->headerText2;
			$sFooterText = $oLeadSpecRow->footerText2;
			$sLeadsQuery = $oLeadSpecRow->leadsQuery2;
			$sFieldDelimiter = $oLeadSpecRow->fieldDelimiter2;
			$sFieldSeparater = $oLeadSpecRow->fieldSeparater2;
			$sEndOfLine = $oLeadSpecRow->endOfLine2;
			$sLeadsEmailSubject = $oLeadSpecRow->leadsEmailSubject2;
			$sLeadsEmailFromAddr = $oLeadSpecRow->leadsEmailFromAddr2;
			$sLeadsEmailBody = $oLeadSpecRow->leadsEmailBody2;			
			$sTestEmailRecipients = $oLeadSpecRow->testEmailRecipients2;
			$sCountEmailRecipients = $oLeadSpecRow->countEmailRecipients2;
			$sLeadsEmailRecipients = $oLeadSpecRow->leadsEmailRecipients2;			
		}		
		
} else {
	if ($sAutoRespEmailFromAddr == '') {
		$sAutoRespEmailFromAddr = "support@amperemedia.com";
	}
	
		// set default values
	if ($sLeadFileName == '') {
		$sLeadFileName = "[offerCode]_[mm]_[dd]_[yyyy]_Ampere.csv";
	}
	if ($sTestEmailRecipients == '') {
		$sTestEmailRecipients = $sSesEmail;
	}
	if ($sLeadsEmailSubject == '') {
		$sLeadsEmailSubject = "Ampere Media - [offerCode], [count] [mm]-[dd]-[yyyy]";	
	}
	
	if ($sLeadsEmailFromAddr == '') {
		$sLeadsEmailFromAddr = 'Ampere Media Leads <leads@AmpereMedia.com>';
	}
	if ($sSingleEmailFromAddr == '') {
		$sSingleEmailFromAddr = 'Ampere Media Lead <leads@AmpereMedia.com>';
	}
	if ($sLeadsEmailBody == '') {
		$sLeadsEmailBody = "[offerCode] - [count]";
	}
	
		
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}



// prepare options for delivery methods
$sMethodQuery = "SELECT *
				 FROM   deliveryMethods
				 ORDER BY method";
$rMethodResult = dbQuery($sMethodQuery);
while ($oMethodRow= dbFetchObject($rMethodResult)) {
	if ($oMethodRow->id == $iDeliveryMethodId) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sDeliveryMethodOptions .= "<option value=$oMethodRow->id $sSelected>$oMethodRow->method";
	
}

if ($iIsSecured) {
	$sIsSecuredChecked = "checked";
} else {
	$sIsSecuredChecked = "";
}

if ($iIsEncrypted) {
	$sIsEncryptedChecked = "checked";
} else {
	$sIsEncryptedChecked = "";
}

// prepare field delimiter options
$sDblQteSelected = "";
$sNoDelimiterSelected = "";

switch ($sFieldDelimiter) {
	case "\"":
	$sDblQteSelected = "selected";
	break;
	default:
	$sNoDelimiterSelected = "selected";
}

$sFieldDelimiterOptions = "<option value='' $sNoDelimiterSelected>No Delimiter
							<option value='\"' $sDblQteSelected>\"";

$sTabSelected = "";
$sCommaSelected = "";
$sPipeSelected = "";
$sTildSelected = "";
$sNLSelected = "";
$sNoSeparaterSelected = "";
// prepare field separater options
switch ($sFieldSeparater) {
	case "\\t":
	$sTabSelected = "selected";
	break;
	case "|":
	$sPipeSelected = "selected";
	break;
	case "\\n":
	$sNLSelected = "selected";
	break;
	case "~":
	$sTildSelected = "selected";
	break;
	case "":
	$sNoSeparaterSelected = "selected";
	break;
	case ",":	
	default:
	$sCommaSelected = "selected";
}

$sFieldSeparaterOptions = "<option value=',' $sCommaSelected>Comma
  							<option value='|' $sPipeSelected>|  							
							<option value='\\t' $sTabSelected>Tab
							<option value='~' $sTildSelected>~  	
							<option value='\\n' $sNLSelected>\\n
							<option value='' $sNoSeparaterSelected>No Separater";

// prepare end of line options
$sNLCRSelected = "";
$sNLSelected = "";
$sNoneSelected = "";
switch ($sEndOfLine) {
	case "":
	$sNoneSelected = "selected";
	break;
	case "\\n":
	$sNLSelected = "selected";
	break;		
	default:
	$sNLCRSelected = "selected";
}
$sEndOfLineOptions = "<option value='\\r\\n' $sNLCRSelected>\\r\\n
					  <option value='\\n' $sNLSelected>\\n
					  <option value='' $sNoneSelected>";


// prepare enc method options
$sGpgSelected = "";
switch ($sEncMethod) {
	case "gpg":
		$sGpgSelected = "selected";
		break;
		
}

$sEncMethodOptions .= "<option value=''>
					   <option value='gpg' $sGpgSelected>GPG";

// prepare enc type options
$sTextEncTypeSelected = "";
$sBinaryEncTypeSelected = "";
switch ($sEncType) {
	case "text":
	$sTextEncTypeSelected = "selected";
	break;
	case "binary":
	$sBinaryEncTypeSelected = "";	
	break;
}
$sEncTypeOptions .= "<option value=''>
					<option value='text' $sTextEncTypeSelected>Text
					<option value='binary' $sBinaryEncTypeSelected>Binary";


// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>
			<input type=hidden name=sOfferCode value='$sOfferCode'>
			";

//include("../../includes/adminAddHeader.php");

?>

<html>

<head>
<title><?php echo $sPageTitle;?></title>
<LINK rel="stylesheet" href="<?php echo $sGblAdminSiteRoot;?>/styles.css" type="text/css" >
</head>

<script lanugage=JavaScript>

function checkForm() {
document.form1.submitClicked.value=1;
return true;
}
</script>

<body>

<table width=85% align=center>
<tr><Td class=message align=center colspan=2><?php echo $sMessage;?>
</td></tr></table>	


<script language=JavaScript>


function openWin(winUrl) {
	checkForm();
	var temp = window.open(winUrl,'','');		
}



function testQuery() {

	checkForm();

var query = document.form1.sLeadsQuery.value;
var testQueryLink = "testQuery.php?sQuery=" + query;
var winOpen = window.open(testQueryLink,"testQuery","height=450, width=600, scrollbars=yes, resizable=yes, status=yes")

}

</script>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post enctype=multipart/form-data onSubmit="return checkForm();">
<input type=hidden name=submitClicked>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>	
	
	<tr><td>Offer Code</td><td colspan=3><?php echo $sOfferCode;?></td></tr>
	
		
	<tr><td align=right>Delivery Method *</td>
		<td colspan=3><select name=iDeliveryMethodId>
		<?php echo $sDeliveryMethodOptions;?>
		</select></td>		
	</tr>
	
	<tr><td align=right>Posting URL *</td>
		<td colspan=3><input type=text name=sPostingUrl value='<?php echo $sPostingUrl;?>' size=60>
		<BR>Include http:// or https://</td>
	</tr>
	<tr><td align=right>HTTP Post String</td>
		<td colspan=3><input type=text name=sHttpPostString value='<?php echo $sHttpPostString;?>' size=60></td>
	</tr>
	<tr><td align=right>FTP Site URL *</td>
		<td colspan=3><input type=text name=sFtpSiteUrl value='<?php echo $sFtpSiteUrl;?>' size=40>
			<BR>Don't include http:// or https://</td>
	</tr>
	
	<tr>
		<td align=right>Initial FTP Directory *</td>
		<td><input type=text name=sInitialFtpDirectory value='<?php echo $sInitialFtpDirectory;?>'></td>
	<td align=right>Is Secured *</td>
		<td><input type=checkbox name=iIsSecured value='1' <?php echo $sIsSecuredChecked;?>></td>
	</tr>		
	<tr><td align=right>User Id *</td>
		<td><input type=text name=sUserId value='<?php echo $sUserId;?>'></td>
	<td align=right>Password *</td>
		<td><input type=text name=sPasswd value='<?php echo $sPasswd;?>'></td>
	</tr>
	<tr><td align=right>Lead File Name *</td>
		<td colspan=3><input type=text name=sLeadFileName value='<?php echo $sLeadFileName;?>' size=45>				
		 <BR>
			[offerCode], [dd], [mm], [yy], [yyyy], [count] will be replaced with its value.<BR>
			[d-n] or [d+n] used anywhere in the file name will be applied to the date to add or subtract days.(n = Any integer value)</td>
	</tr>
	<tr><td align=right>Is Encrypted *</td>
		<td><input type=checkbox name=iIsEncrypted value='1' <?php echo $sIsEncryptedChecked;?>>
			&nbsp; &nbsp; Enc. Type * <select name=sEncType>
					<?php echo $sEncTypeOptions;?>
					</select>
		</td>
	<td align=right>Encryption Method *</td>
		<td><select name=sEncMethod>
			<?php echo $sEncMethodOptions;?>
			</select>
		</td>
	</tr>
	<tr><td align=right>Encryption Key *</td>
		<td colspan=3><input type=text name=sEncKey value='<?php echo $sEncKey;?>' size=80></td>
	</tr>
	<tr><td align=right>Header Text *</td>
		<td colspan=3><textarea name=sHeaderText rows=3 cols=80><?php echo $sHeaderText;?></textarea></td>
	</tr>
	<tr><td align=right>Footer Text *</td>
		<td colspan=3><textarea name=sFooterText rows=3 cols=80><?php echo $sFooterText;?></textarea></td>
	</tr>
	<tr><td align=right>Leads Query</td>
		<td colspan=3><textarea name=sLeadsQuery rows=10 cols=80><?php echo $sLeadsQuery;?></textarea>
			&nbsp; <a href='JavaScript:testQuery();'>Test Query</a><BR>
	<b> Sample Leads Query:</b> SELECT userDataHistory.email, first, last, address, address2, city, state, zip, phoneNo FROM userDataHistory, otDataHistory, offerLeadSpec WHERE offerLeadSpec.offerCode = otDataHistory.offerCode AND userDataHistory.email = otDataHistory.email AND otDataHistory.offerCode = '[offerCode]' AND postalVerified = 'V'  AND   DATE_ADD(date_format(otDataHistory.dateTimeAdded,"%Y-%m-%d"), INTERVAL maxAgeOfLeads DAY) >= CURRENT_DATE  AND address NOT LIKE '3401 DUNDEE%' <BR><BR>
	<b>Sample Leads Query With Page2 Data: </b>SELECT userDataHistory.email, first, last, address, address2, city, state, zip, phoneNo, <b>TRIM( BOTH '"' FROM substring_index( substring_index( page2Data, "|", <i>n</i> ) , "|", - 1 ) ) AS FIELDn</b>  FROM userDataHistory, otDataHistory, offerLeadSpec WHERE offerLeadSpec.offerCode = otDataHistory.offerCode AND userDataHistory.email = otDataHistory.email AND otDataHistory.offerCode = '[offerCode]' AND postalVerified = 'V'  AND   DATE_ADD(date_format(otDataHistory.dateTimeAdded,"%Y-%m-%d"), INTERVAL maxAgeOfLeads DAY) >= CURRENT_DATE  AND address NOT LIKE '3401 DUNDEE%'
	<BR><BR>
<b>Note:</b>Replace <i>n</i> with page2 field storage order.
	</td>
	</tr>
	<tr><td align=right>Field Delimiter</td>
		<td><select name=sFieldDelimiter>
			<?php echo $sFieldDelimiterOptions;?>
			</select>
		</td>
	<td align=right>Field Separater</td>
		<td><select name=sFieldSeparater>
			<?php echo $sFieldSeparaterOptions;?>
			</select>
		</td>
	</tr>
	<tr><td align=right>End Of Line</td>
		<td><select name=sEndOfLine>
			<?php echo $sEndOfLineOptions;?>
			</select>
		</td>
	<td align=right>Leads Subject *</td>
		<td><input type=text name=sLeadsEmailSubject value='<?php echo $sLeadsEmailSubject;?>' size=60><BR>
		[offerCode], [dd], [mm], [yy], [yyyy], [count]s will be replaced with its value.</td>
	</tr>
	<tr><td align=right>Leads Email From Address *</td>
		<td colspan=3><input type=text name=sLeadsEmailFromAddr value='<?php echo $sLeadsEmailFromAddr;?>' size=70></td>
	</tr>
	<tr><td align=right>Leads Email Body *</td>
		<td colspan=3><textarea name=sLeadsEmailBody  rows=4 cols=70><?php echo $sLeadsEmailBody;?></textarea></td>
	</tr>
	<tr><td></td><td colspan=3>Single email info can be used for Real Time Email  or  Daily Batch Email - One Per Lead</td></tr>
	<tr><td align=right>Single Email Subject *</td>
		<td colspan=3><input type=text name=sSingleEmailSubject value='<?php echo $sSingleEmailSubject;?>' size=60><BR>
		[offerCode], [dd], [mm], [yy], [yyyy], [count]s will be replaced with its value.</td>
	</tr>
	<tr><td align=right>Single Email From Address *</td>
		<td colspan=3><input type=text name=sSingleEmailFromAddr value='<?php echo $sSingleEmailFromAddr;?>' size=70></td>
	</tr>
	<tr><td align=right>Single Email Body *</td>
		<td colspan=3><textarea rows=5 cols=70 name=sSingleEmailBody><?php echo $sSingleEmailBody;?></textarea>
				<BR>[FIELD1], [FIELD2], [FIELD3]... will be replaced with the respective page2 fields.</td>
	</tr>
	
	<tr><td align=right>Test Mail Recipients *</td>
		<td colspan=3><input type=text name=sTestEmailRecipients value='<?php echo $sTestEmailRecipients;?>' size=70></td>
	</tr>
	<tr><td align=right>Count Mail Recipients *</td>
		<td colspan=3><input type=text name=sCountEmailRecipients value='<?php echo $sCountEmailRecipients;?>' size=70></td>
	</tr>
	<tr><td align=right>Leads Mail Recipients *</td>
		<td colspan=3><input type=text name=sLeadsEmailRecipients value='<?php echo $sLeadsEmailRecipients;?>' size=70></td>
	</tr>
	
	<!--<tr><td></td><td colspan=2>Fields marked with * will be disabled if lead group is selected.<BR>
						Values from the group record will be used instead.</td></tr>-->
	</table>
	

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD colspan=2 align=center >
		<input type=submit name=sSaveContinue value='Save & Continue'> &nbsp; &nbsp; 
		</td><td></td>
	</tr>	
	</table>
	
	
	

<SCRIPT LANGUAGE=JavaScript FOR=window EVENT=onbeforeunload>
<!-- Beginning of JavaScript --------

var strMsg = "All the changes you didn't saved, will be lost.";
if (document.form1.submitClicked.value==0) {
window.event.returnValue = strMsg;
document.form1.sSaveClose.focus()
} 

// -- End of JavaScript code -------------- -->
</SCRIPT>



<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>

