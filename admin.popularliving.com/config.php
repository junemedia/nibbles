<?php


//echo 'exit';
//exit;


/************************************ config.php **************************************
 *																					  *
 * 						Do not push this file to any other server.                    *
 *																					  *
 *  config.php file contains configuration info which differs from server to server.  *
 * 		When modifying this file, make changes on each individual server.             *
 *																					  *
 **************************************************************************************/


/*
If primary sql fails, please change the $host to 1st slave.
When you update $host in this config file, please
make sure you update $host in all config files on all servers.

$host = "64.132.70.251"; //primary
$host = "64.132.70.15"; //1st slave
$host = "64.132.70.150"; //2nd slave

*/



//Legacy host/user/pass

$host = "8ec3cdb8845732ea5bbc2a32fa2a87d52453102e.rackspaceclouddb.com";
$dbase = "nibbles" ;
$user = "jingshi" ;
$pass = "kendeji12306!" ;

// only select privileges user:lees passwd:ls!28#di


//Legacy reporting host/user/pass
$reportingHost = "8ec3cdb8845732ea5bbc2a32fa2a87d52453102e.rackspaceclouddb.com";
$reportingDbase = "nibbles";
$reportingUser = "jingshi";
$reportingPass = "kendeji12306!";
//mysql_connect ($reportingHost, $reportingUser, $reportingPass);


//New host/user/pass
define('newHost', "8ec3cdb8845732ea5bbc2a32fa2a87d52453102e.rackspaceclouddb.com");
define('newdbase', "nibbles");
define('newUser', "jingshi");
define('newPass', "kendeji12306!");

//New reporting host/user/pass
define('newReportingHost', "8ec3cdb8845732ea5bbc2a32fa2a87d52453102e.rackspaceclouddb.com");
define('newReportingDbase', "nibbles");
define('newReportingUser', "jingshi");
define('newReportingPass',"kendeji12306!");



//DON"T UNCOMMENT THESE UNTIL THE IMPORT IS DONE
$host = newHost;
$reportingHost = newHost;


// DO NOT CHANGE THESE TWO LINES!

mysql_pconnect ($host, $user, $pass);

// mysql_connect ('localhost', $user, $pass); 

mysql_select_db ($dbase);

$bGlobalJoinInsertDisable = false;
/*
$aGblSites = array("site1" => "64.132.70.111", 
					"site2" => "64.132.70.125", 
					"site3" => "64.132.70.75", 
					"test" => "64.132.70.238",
					"smita" => "64.132.70.225",
					"josh" => "64.132.70.225",
					"jr" => "64.132.70.225",
					"lee" => "64.132.70.225",
					"site4" => "64.132.70.80",
					"site5" => "64.132.70.61");
					
					
$aGblSiteNames = array("site1" => "admin.popularliving.com", 
					"site2" => "web1.popularliving.com", 
					"site3" => "web2.popularliving.com",
					"test" => "test.popularliving.com",
					"smita" => "smita.popularliving.com",
					"josh" => "josh.popularliving.com",
					"jr" => "jr.popularliving.com",
					"lee" => "lee.popularliving.com",
					"site4" => "web3.popularliving.com",
					"site5" => "web4.popularliving.com");*/


$aGblSites = array("site1" => "216.48.124.134",
					"w1" => "216.48.124.134");
					
					
$aGblSiteNames = array("site1" => "admin.popularliving.com", 
					"site2" => "web1.popularliving.com", 
					"site3" => "web2.popularliving.com",
					"test" => "test.popularliving.com",
					"smita" => "smita.popularliving.com",
					"josh" => "josh.popularliving.com",
					"jr" => "jr.popularliving.com",
					"lee" => "lee.popularliving.com",
					"site4" => "web3.popularliving.com",
					"site5" => "web4.popularliving.com",
					"w1" => "test1.popularliving.com");
					
$sGblSiteIp1 = "216.48.124.135";
$sGblSiteIp2 = "216.48.124.135";
$sGblSiteIp3 = "216.48.124.135";
$sGblSiteIp4 = "216.48.124.135";
$sGblSiteIp5 = "216.48.124.135";
$sGblSiteIp6 = "216.48.124.135";
$sGblSiteIp7 = "216.48.124.135";


$sGblRoot = "/var/www/html/admin.popularliving.com";
$sGblWebRoot = "/var/www/html/admin.popularliving.com/html";
//$sGblWebRoot = $sGblWebRoot."nibbles";
$sGblAdminWebRoot = "$sGblWebRoot/admin";
$sGblRootMyFree = "/var/www/html/www_myfree_com";
$sGblWebRootMyFree = "/var/www/html/www_myfree_com/html";

$sGblMainSiteRoot = "http://www.popularliving.com";
//echo 'test';
//$sGblSiteRoot = "http://$SERVER_NAME";
$sGblSiteRoot = "http://admin.popularliving.com";
$sGblSiteRootMyFree = "http://www.myfree.com";
$sCurrSite = $_SERVER['SERVER_ADDR'];

reset($aGblSiteNames);
	reset($aGblSites);	
	while (list($key,$val) = each($aGblSites)) {
		
		if ($sCurrSite == $val) {
			//$sCurrServer = $aGblSiteNames[$key];		
			$sGblSiteRoot = "http://".$aGblSiteNames[$key];
		}
	}
	//if (strstr($url,"www.popularliving.com/p/")) {
		//$url = eregi_replace("www.popularliving.com", $sCurrServer, $url);
	//}
	
//$sGblSiteRoot = "http://www.popularliving.com";

//$sGblMyFreeWebRoot = "/var/www/html/www";
//$sGblMyFreeSiteRoot = "http://cory.myfree.com/www";

// specify site root of the images specified on different server
$sGblImageServerSiteRoot = "http://images.popularliving.com";

//$nbSiteRoot = $siteRoot."/nibbles";
$sGblAdminSiteRoot = "http://admin.popularliving.com/admin";

$sGblExportReportUrl = "http://admin.popularliving.com";
/*************    NOTE    ********************/
// Move following to another common file just like functions.php to include it in starting of all the scripts.
// header file is not included in starting, otherwise header() function can't be used anywhere.
// and include that file in the starting of each admin script
// Also get the title of the page from data base.

//$iMenuId = $_GET['menuId'];
//$sMenuFolder = $_GET['menuFolder'];
//$iParentMenuId = $_GET['parentMenuId'];
//$sParentMenuFolder = $_GET['parentMenuFolder'];
//$sMessage = $_GET['message'];


// Redirect paths
$sGblEditorialPath = "http://ed.myfree.com";

$sGblOfferRedirectsPath = $sGblEditorialPath."/r/r.php";
$sGblSourceRedirectsPath = "http://www.popularliving.com/r/r.php";

// Pixels Tracking paths
$sGblOfferPixelsTrackingPath = $sGblEditorialPath."/pixels/offerPixelTracking.php";
$sGblSourcePixelsTrackingPath = "http://www.popularliving.com/pixels/pixelTracking.php";
$sGblNlPixelsTrackingPath = $sGblEditorialPath."/pixels/nlPixelTracking.php";


//set default url, if url not found ( used in /r/r.php )
$sGblDefaultUrl = "http://www.popularliving.com/p/onetime.php";

// specify handcraftersvillage db name
$sGblHcvDBName = "hcv";

$sGblHcvWebRoot = "/var/www/html/hcv";
$sGblHcvSiteRoot = "http://cory.myfree.com/hcv";


$sGblMhlDBName = "hl";

$sGblMhlWebRoot = "/var/www/html/hl";
$sGblMhlSiteRoot = "http://cory.myfree.com/hl";


$sGblFpWebRoot = "/var/www/html/funpages.myfree.com";
$sGblFpSiteRoot = "http://funpages.myfree.com";

//$sGblImageWebRoot = "/var/www/html/www_popularliving_com/html";
//$sGblImageSiteRoot = "http://www.popularliving.com";

// [PAGE_2_HTML_IMAGE_PATH]
$sPage2HtmlImagePath = "http://www.popularliving.com/images/offers";

?>

