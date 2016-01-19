<?php

include_once('/home/sites/popularliving/html/includes/paths.php');

$oc = trim($_GET['oc']);

if ($oc !='') {
	$q = "SELECT revPerLead FROM offers WHERE offerCode = '$oc'";
	$r = dbQuery($q);
	$row = dbFetchObject($r);
	echo $row->revPerLead;
} else {
	exit;
}

?>