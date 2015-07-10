<?php

include_once("../includes/paths.php");
include_once('session_handlers.php');
include_once("libs/function.php");
include_once("libs/fields.php");


$PHPSESSID = $_GET['PHPSESSID'];
session_start();

$sOtPath = "ot.php?PHPSESSID=".session_id();

if (count($_SESSION['aSesCloseTheyHostOffers']) > 0) {
	$sOffersQuery = "SELECT * FROM offers 
				WHERE offerCode = '".$_SESSION['aSesCloseTheyHostOffers'][0]."'
				AND isCloseTheyHost = 'Y' 
				AND (offerType = 'CTH' OR offerType='OTH_CTH')
				AND mode = 'A'
				AND isLive = '1' LIMIT 1";
	$rOffersResult = dbQuery($sOffersQuery);
	echo dbError();
	while ($oOfferRow = dbFetchObject($rOffersResult)) {
		$sUrl = $oOfferRow->closeTheyHostUrl;
		$sTheyHostPassOnPrepopCodes = $oOfferRow->closeTheyHostPrePop;
		$sPassOnCodeVarMap = $oOfferRow->closeTheyHostVarMap;
		$sUrl = str_replace("[SOURCE_CODE_ID]", $_SESSION['iSesSourceCodeId'], $sUrl);
		
		if (count($_SESSION['aSesCampaignHeaders'][$_SESSION['iSesCurrentPositionInFlow']]) > 0) {
			//added 2006-06-23, BB, for multiple headers per campaign.
			if($_SESSION['aSesCampaignHeaders'][$_SESSION['iSesCurrentPositionInFlow']]){
				$sHeader = $_SESSION['aSesCampaignHeaders'][$_SESSION['iSesCurrentPositionInFlow']];
			}
		} else {
			if ($oOfferRow->closeTheyHostHeader == '') {
				$sHeader = "<img src='http://www.popularliving.com/images/thHeaderDefault.gif'>";
			} else {
				$sHeader = "<img src='http://www.popularliving.com/images/offers/$oOfferRow->offerCode/$oOfferRow->closeTheyHostHeader'>";
			}
		}

		if ($oOfferRow->iFrameHeight == 0 || $oOfferRow->iFrameHeight == null) {
			$iFrameHeight = 1500;
		} else {
			$iFrameHeight = $oOfferRow->iFrameHeight;
		}
	}
	
	$sTempPrePop = '';
	if ($sTheyHostPassOnPrepopCodes == 'Y') {
		if ($sPassOnCodeVarMap !='') {
			$aPassOnCodeVarMap = explode(",",$sPassOnCodeVarMap);
			for ($i=0; $i<count($aPassOnCodeVarMap); $i++) {
				$aKeyValuePair = explode("=",$aPassOnCodeVarMap[$i]);
				if ($aKeyValuePair[0] == 'e') { $sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesEmail"]."&"; }
				if ($aKeyValuePair[0] == 'f') {	$sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesFirst"]."&"; }
				if ($aKeyValuePair[0] == 'l') {	$sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesLast"]."&"; }
				if ($aKeyValuePair[0] == 'a1') { $sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesAddress"]."&"; }
				if ($aKeyValuePair[0] == 'a2') { $sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesAddress2"]."&"; }
				if ($aKeyValuePair[0] == 'c') {	$sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesCity"]."&"; }
				if ($aKeyValuePair[0] == 's') {	$sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesState"]."&"; }
				if ($aKeyValuePair[0] == 'z') {	$sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesZip"]."&"; }
				if ($aKeyValuePair[0] == 'p') {	$sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesPhone"]."&"; }
				if ($aKeyValuePair[0] == 'pnd') { $sTempPrePop .= urldecode($aKeyValuePair[1])."=".$_SESSION["sSesPhoneNoDash"]."&"; }
			}
		} else {
			$sTempPrePop .= "e=".$_SESSION["sSesEmail"]."&";
			$sTempPrePop .= "f=".$_SESSION["sSesFirst"]."&";
			$sTempPrePop .= "l=".$_SESSION["sSesLast"]."&";
			$sTempPrePop .= "a1=".$_SESSION["sSesAddress"]."&";
			$sTempPrePop .= "a2=".$_SESSION["sSesAddress2"]."&";
			$sTempPrePop .= "c=".$_SESSION["sSesCity"]."&";
			$sTempPrePop .= "s=".$_SESSION["sSesState"]."&";
			$sTempPrePop .= "z=".$_SESSION["sSesZip"]."&";
			$sTempPrePop .= "p=".$_SESSION["sSesPhone"]."&";
			$sTempPrePop .= "pnd=".$_SESSION["sSesPhoneNoDash"]."&";
		}
		$sTempPrePop .= "sesId=".session_id();
	}
	
	if ($sTempPrePop != '') {
		if (strstr($sUrl,"?")) {
			$sUrl = $sUrl.'&'.$sTempPrePop;
		} else {
			$sUrl = $sUrl.'?'.$sTempPrePop;
		}
	}
	unset($_SESSION['aSesCloseTheyHostOffers'][0]);
	unset($_SESSION['aSesPage2Offers'][0]);

} else {
	header("Location:$sOtPath");
}

$sPageIncrementUsingAjax = "response=coRegPopup.send('pageIncrement.php?".SID."','');";

// $_SESSION['sShowSkipSubmitCth']:
// the possible values are:  0 for skip, 1 for submit, and 2 for both skip and submit
$sButton = '';
if ($_SESSION['sShowSkipSubmitCth'] == 0) {
	$sButton = "<input type='submit' value='Skip' style=\"border-style:Double;\" onclick=\"$sPageIncrementUsingAjax parent.location='$sOtPath'\">";
} elseif ($_SESSION['sShowSkipSubmitCth'] == 1) {
	$sButton = "<input type='submit' value='Submit' style=\"border-style:Double;\" onclick=\"$sPageIncrementUsingAjax parent.location='$sOtPath'\">";
} elseif ($_SESSION['sShowSkipSubmitCth'] == 2) {
	$sButton = "<input type='submit' value='Submit' style=\"border-style:Double;\" onclick=\"$sPageIncrementUsingAjax parent.location='$sOtPath'\">&nbsp;&nbsp;
		<input type='submit' value='Skip' style=\"border-style:Double;\" onclick=\"$sPageIncrementUsingAjax parent.location='$sOtPath'\">";
}


?>

<html>
<head>
<SCRIPT LANGUAGE=JavaScript SRC="http://www.popularliving.com/libs/javaScriptFunctions.js" TYPE=text/javascript></script>
<SCRIPT LANGUAGE=JavaScript SRC="http://www.popularliving.com/nibbles2/libs/ajax.js" TYPE=text/javascript></script>
<title><?php echo $_SESSION['aDefaultTitle'][$_SESSION['iSesCurrentPositionInFlow']]; ?></title>
<LINK rel="stylesheet" href="../pageStyles.css" type="text/css">

<style type="text/css">
	<?php echo $_SESSION['sSesCampaignCSS']; ?>
</style>

</head>
<body bgcolor=#ffffff>
<center>
<?php echo $sHeader; ?>
</center>
<BR>
	<iframe src="<?php echo $sUrl; ?>" width=100% height=<?php echo $iFrameHeight; ?> scrolling='no' frameborder=0></iframe>
<BR>
<center>
<?php echo $sButton; ?>
<input name='sSubmit' value='submit' type='hidden'>

<?php echo $_SESSION['sSesHiddenSourceCode']; ?>

</form>
</center>
<?php echo $_SESSION['sSesFooter']; ?>
</body>

</html>