<?php
//takes a zip, and returns a 1 if that zip is eligible for this offer. 
include("../includes/paths.php");

mysql_select_db('targetData');



$sZip = (!ereg("^[0-9-]{5,}$", strtoupper(trim($_GET['z']))) ? '' : strtoupper(trim($_GET['z'])));
$sOfferCode = (!(ctype_alnum(trim($_GET['sOfferCode']))) ? '' : trim($_GET['sOfferCode']));

//my var is going to be called sZip
//so, something like http://www.popularliving.com/images/offers/WPCM_WIN.php?sZip=60625
if (($sZip != '')&&($sOfferCode != '')) {

$sZip = substr($sZip,0,5);

$sql = "SELECT 1 as out FROM $sOfferCode WHERE zip = '$sZip'";
$res = dbQuery($sql);
$oZip = dbFetchObject($res);

if($oZip->out){
	echo "1" ;
	exit();
} else {
	echo "0";
	exit();
}
}

?>