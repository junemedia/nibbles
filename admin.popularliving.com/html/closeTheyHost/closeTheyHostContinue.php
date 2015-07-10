<?php

include("../includes/paths.php");

$sSessId = trim($_GET['PHPSESSID']);
if ($sSessId == '') { session_start(); }
$sTheyHostContinueURL = trim($_GET['sCloseTheyHostContinueUrl']);
$sRefererFile = trim($_GET['cthRefererPage']);


if ($sRefererFile != '') {
	$sCheckOtPageQuery = "SELECT * FROM otPages WHERE pageName = '$sRefererFile' LIMIT 1";
	$rCheckOtPageResult = dbQuery($sCheckOtPageQuery);
	if (dbNumRows($rCheckOtPageResult) > 0 ) {
		while($sOtRow = dbFetchObject($rCheckOtPageResult)) {
			if ($sOtRow->passOnPrepopCodes == '1') {
					$aQueryString = trim($_GET['sTempPassOn']);
					if ($aQueryString !='') {
						$aValue = explode("|", $aQueryString);
						//pass on prepop code is checked
						$sQueryString = "e=".$aValue[0]."&";
						$sQueryString .= "f=".$aValue[1]."&";
						$sQueryString .= "l=".$aValue[2]."&";
						$sQueryString .= "a1=".$aValue[3]."&";
						$sQueryString .= "a2=".$aValue[4]."&";
						$sQueryString .= "c=".$aValue[5]."&";
						$sQueryString .= "s=".$aValue[6]."&";
						$sQueryString .= "ss=".$aValue[7]."&";
						$sQueryString .= "z=".$aValue[8]."&";
						$sQueryString .= "p=".$aValue[9]."&";
						$sQueryString .= "pnd=".$aValue[10]."&";
						$sQueryString .= "ext=".$aValue[11]."&";
						$sQueryString .= "src=".$aValue[12]."&";
						$sQueryString .= "t=".$aValue[13]."&";
						$sQueryString .= "ip=".$aValue[14];
					} else {
						$sQueryString = '';
					}
			} else {
				if ($sSessId != '') {
					$sQueryString = "PHPSESSID=$sSessId";
				} else {
					$sQueryString = '';
				}
			}
		}
	} else {
		if ($sSessId != '') {
			$sQueryString = "PHPSESSID=$sSessId";
		} else {
			$sQueryString = '';
		}
	}
} else {
	if ($sSessId != '') {
		$sQueryString = "PHPSESSID=$sSessId";
	} else {
		$sQueryString = '';
	}
}

if ($sQueryString != '') {
	if (strstr($sTheyHostContinueURL,"?")) {
		$sTheyHostContinueURL .= "&".$sQueryString;
	} else {
		$sTheyHostContinueURL .= "?".$sQueryString;
	}
}

$sGetNextCloseTheyHostOffer = "closeTheyHostNextOffer.php?PHPSESSID=$sSessId";
$sOnClickCloseTheyHost = "onClick=\"response=coRegPopup.send('$sGetNextCloseTheyHostOffer','');parent.location='$sTheyHostContinueURL'\"";

?>

<html>
<head>
<SCRIPT LANGUAGE=JavaScript SRC="http://www.popularliving.com/libs/ajax.js" TYPE="text/javascript"></script>
</head>
<body>
<center>
<hr width="85%" align="center">
<input type="submit" value="I'm Finished With This Page" style="border-style:Double;" <?php echo $sOnClickCloseTheyHost ?>>
</center>
</body>
</html>