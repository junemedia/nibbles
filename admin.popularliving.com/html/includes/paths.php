<?php

// set the config file path
// CAUTION: Following line is changed for test server
/*
$sConfigFile = "/home/sites/www_popularliving_com/config.php";
if ($_SERVER['SERVER_NAME'] == 'test.popularliving.com') {
	$sConfigFile = "/home/sites/test.popularliving.com/config.php";
}

if ($_SERVER['SERVER_NAME'] == 'admin.popularliving.com') {
	$sConfigFile = "/home/sites/admin.popularliving.com/config.php";
}*/

/*if ($_SERVER['SERVER_ADDR']=='64.132.70.111' || $_SERVER['SERVER_ADDR'] =='') {
	$sConfigFile = "/val0/html/admin.popularliving.com/config.php";
} else {
	$sConfigFile = "/val0/html/www_popularliving_com/config.php";
}*/

$sConfigFile = dirname(__FILE__) . "/../../config.php";

// include config file
include($sConfigFile);
//echo $sGblWebRoot;
//$sGblWebRoot = "/val0/html/admin.popularliving.com/html";


// select nibbles database here until removing config.php from library.php

$sGblImagePath = "$sGblWebRoot/images";
$sGblImageUrl = "$sGblSiteRoot/images";

$sGblOfferImagePath = "$sGblImagePath/offers";
$sGblOfferImageUrl = "$sGblImageUrl/offers";

$sGblOfferImagesPath = "$sGblImagePath/offers";
$sGblOfferImagesUrl = "$sGblImageUrl/offers";

$sGblDisplayOfferImagesUrl = $sGblImageServerSiteRoot."/images/offers";


//$sGblPageHeaderImagePath = "$sGblImagePath/otPages";
//$sGblPageHeaderImageUrl = "$sGblImageUrl/otPages";

// path where generated ot pages stored/should be stored 
$sGblOtPagesPath = "$sGblWebRoot/p";
$sGblOtPagesUrl = "$sGblSiteRoot/p";

$sGblPageImagesPath = $sGblOtPagesPath;
//$sGblPageImagesUrl = $sGblOtPagesUrl;

// images are copied on separate image server
$sGblPageImagesUrl = "$sGblImageServerSiteRoot/p";


$sGblDisplayPageImagesUrl = $sGblImageServerSiteRoot."/p";


$sGblLibsPath = "$sGblWebRoot/libs";
$sGblLibsUrl = "$sGblSiteRoot/libs";

$sGblIncludePath = "$sGblWebRoot/includes";
//$sGblIncludeUrl = "$sGblSiteRoot/includes";

$sGblLeadFilesPath = "$sGblAdminWebRoot/leads";

// leads file import directory
$sGblImportLeadsDir = "$sGblWebRoot/partners/upload";

// set Campaign Frames Root
$sGblCampaignFrameRoot = $sGblWebRoot."/campaignFrames/" ;

// set validation file path
//$sGblValidationFile = "/home/sites/www_popularliving_com/libs/validate.php";

$sGblPartnersPath = "$sGblWebRoot/partners";
$sGblPartnersUrl = "$sGblSiteRoot/partners";


// include library file before including any other files
include("$sGblLibsPath/dbFunctions.php");
//echo "$sGblLibsPath/dbFunctions.php";

// include library file
include("$sGblLibsPath/library.php");
// include fields file
//include("/home/sites/popularliving/html/nibbles2/fields.php");
// include functions file
//include("$sGblLibsPath/function.php");

$sGblForeignIPRedirectURL = "http://www.globaloffermall.com/index.php?pid=179&subid=ampGL";


?>