<?php
include_once('/home/sites/admin.popularliving.com/html/includes/paths.php');

//take src, get everything off of the link
if($src == ''){
	echo 'nope';
	exit();
}

$linkSQL = "SELECT * FROM links WHERE sourceCode = '$src'";
$res = dbQuery($linkSQL);
$link = dbFetchObject($res);


$sEmailSiteDb = '';
$sMemberSiteDb = '';
if ($link->emailCapType == 'uniqueSite' || $link->memberCapType == 'uniqueSite') {
	$sEmailSiteDb = ' Unique to Site. ';
	$sMemberSiteDb = ' Unique to Site. ';
}
if ($link->emailCapType == 'uniqueDB' || $link->memberCapType == 'uniqueDB') {
	$sEmailSiteDb = ' Unique to DB. ';
	$sMemberSiteDb = ' Unique to DB. ';
}


$out = '';
switch($link->captureType){
	case 'emailCapture':
		$out = "This source code is set as Email Capture (pixel fire after Email Capture Page).  $sEmailSiteDb ";
		break;
	case 'memberCapture':
		$out = "This source code is set as Member Capture (pixel fire after Reg Page).  $sMemberSiteDb ";
		break;
	case 'neither':
		$out = "This source code is set as Neither Email Capture Nor Member Capture.";
		break;
}
//"This is 
echo $out;
exit();

?>