<?php


include("../../includes/paths.php");

include_once("/var/www/html/admin.popularliving.com/config_newsletter_archive.php");

mysql_select_db($dbase);

$live = trim($_GET['live']);
$id = trim($_GET['id']);

if (!ctype_digit($id)) {
	$id = '';
}
if (!ctype_alpha($live) || strlen($live) != 1) {
	$live = '';
}

if ($live == '' || $id == '') {
	// do nothing
} else {
	$update = "UPDATE newsletters SET live = '$live' WHERE id='$id' LIMIT 1";
	$update_result = mysql_query($update);
	echo mysql_error();
}

exit;


?>
