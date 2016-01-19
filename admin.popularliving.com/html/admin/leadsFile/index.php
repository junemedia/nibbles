<?php

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Leads - Download Leads File";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];


if (hasAccessRight($iMenuId) || isAdmin()) {

$sGetEmailAddr = "SELECT email FROM nbUsers WHERE userName='$sTrackingUser'";
$rResult = dbQuery($sGetEmailAddr);
while ($oUserRow = dbFetchObject($rResult)) {
	$sEmail = $oUserRow->email;
}
	
if ($sViewReport != '') {
	if ($sOfferCode !='') {
		if ($sLeadsDate !='') {
			$bFileFound = false;
			$sLeadFileData = '';
			$sFileName = '';
			$sLeadsPath = "/home/sites/admin.popularliving.com/html/admin/leads";
			// get attachemnt file data
			if (is_dir("$sLeadsPath/$sLeadsDate/offers/$sOfferCode")) {
				if ($sDirectory = opendir("$sLeadsPath/$sLeadsDate/offers/$sOfferCode")) {
					while (false !== ($sLeadFile = readdir($sDirectory))) {
						if ($sLeadFile != '.' && $sLeadFile != '..') {
							$rFpLeadFile = fopen("$sLeadsPath/$sLeadsDate/offers/$sOfferCode/$sLeadFile","r");
							if ($rFpLeadFile) {
								$sFileName = $sLeadFile;
								while (!feof($rFpLeadFile)) {
									$sLeadFileData .= fread($rFpLeadFile, 1024);
								}
								$bFileFound = true;
								fclose($rFpLeadFile);
							} else {
								$sMessage = "Can't open lead file.  Please contact IT.";
							}
							break;
						}
					}
				}
			}
			
			if ($bFileFound) {
				$sBorderRandom = md5(time());
				$sMailBoundry = "==x{$sBorderRandom}x";
				$sHeaders = "From: leads@amperemedia.com\r\n";
				$sHeaders .= "Reply-To: leads@amperemedia.com\r\n";
				$sHeaders .= "cc: it@amperemedia.com\r\n";
				$sHeaders .= "X-Priority: 1\r\n";
				$sHeaders .= "X-MSMail-Priority: High\r\n";
				$sHeaders .= "X-Mailer: My PHP Mailer\r\n";
				$sHeaders .= "Content-Type: multipart/mixed;\n\tboundary=\"{$sMailBoundry}\"\t\r\n";
				$sHeaders .= "MIME-Version: 1.0\r\n";
				$sEmailMessage = "This is a multi-part message in MIME format.\r\n\r\n";
				$sEmailMessage .= "--{$sMailBoundry}\r\n";
				$sEmailMessage .= "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n";
				$sEmailMessage .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
				$sLeadFileData = base64_encode($sLeadFileData);
				$sLeadFileData = chunk_split($sLeadFileData);
				$sEmailMessage .= "--{$sMailBoundry}\r\n";
				$sEmailMessage .= "Content-type: text/plain; \r\n";
				$sEmailMessage .= "Content-Transfer-Encoding:base64\r\n";
				$sEmailMessage .= "Content-Disposition: attachment;\n\t filename=\"{$sFileName}\"\r\n\r\n";
				$sEmailMessage .= "$sLeadFileData\r\n";
				$sEmailMessage .= "--{$sMailBoundry}--\r\n";

				$sSubject = "$sOfferCode - Downloaded Leads File From Server By: $sTrackingUser";

				mail($sEmail, $sSubject, $sEmailMessage , $sHeaders);
				$sMessage = "Email Sent With Leads File To: $sEmail";
	
				// start of track users' activity in nibbles 
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  	VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Downloaded leads file: $sOfferCode\n\nDate: $sLeadsDate\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
			} else {
				$sMessage = "No leads file found for $sOfferCode.  Please contact IT";
			}
		} else {
			$sMessage = "Please select date.";
		}
	} else {
		$sMessage = "Please select offer code.";
	}
}
	
	


	
	
$sOpenedDirectory = opendir("/home/sites/admin.popularliving.com/html/admin/leads");
$aDateArray = array();
while ($sFile = readdir($sOpenedDirectory)) {
	if (strlen($sFile) == 8) {
		array_push($aDateArray, $sFile);
	}
}
	
if (count($aDateArray) > 0) {
	arsort($aDateArray);
	$sDateOption = "<option value=''>Select Date</option>";;
	foreach ($aDateArray as $sTemp) {
		$sShowVal = substr($sTemp,0,4).'-'.substr($sTemp,4,2).'-'.substr($sTemp,6,2);
		$sVal = substr($sTemp,0,4).substr($sTemp,4,2).substr($sTemp,6,2);
		
		if ($sLeadsDate == $sVal) {
			$sSelected = 'selected';
		} else {
			$sSelected = '';
		}
		$sDateOption .= "<option value='$sVal' $sSelected>$sShowVal</option>";
	}
}


$sOffersQuery = "SELECT offerCode FROM offers
			 ORDER BY offerCode";
$rOffersResult = dbQuery($sOffersQuery);
echo dbError();
$sOffersOptions .= "<option value=''>OfferCode";
while ($oOffersRow = dbFetchObject($rOffersResult)) {
	if ($oOffersRow->offerCode == $sOfferCode)
	{
		$sOfferCodeSelected = "selected";
	} else {
		$sOfferCodeSelected = "";
	}
	$sOffersOptions .= "<option value='$oOffersRow->offerCode' $sOfferCodeSelected>$oOffersRow->offerCode";
}
	
// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";	
	
include("../../includes/adminHeader.php");
?>

<script language=JavaScript>
function funcRecPerPage(form1) {
	document.form1.elements['sAdd'].value='';
	document.form1.submit();
}		
</script>
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden; ?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr>
	<td><input type=submit name=sViewReport value='Download File'></td>
</tr>


<tr><td></td><td>Offer Code: <select name=sOfferCode>
	<?php echo $sOffersOptions;?>
	</select>
</td></tr>

<tr><td></td><td>Date: <select name=sLeadsDate>
	<?php echo $sDateOption;?>
	</select>
</td></tr>



<tr><td colspan=2><b>Notes:</b><br>
Select offer code and date to download leads file from server.<br>
Email with leads file will be sent to logged in user [<?php echo $sEmail; ?>] and copied to IT<br>
</td></tr>
</table>

</form>
	
<?php
include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>