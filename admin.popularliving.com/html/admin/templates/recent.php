<?php

include("../../includes/paths.php");
session_start();
mysql_select_db('newsletter_templates');
$user = $_SERVER['PHP_AUTH_USER'];


echo "<b>Newsletters links generated by you (last 50):<br><br></b>";

$recent_links = '';
$recent_result = dbQuery("SELECT * FROM templates WHERE username = '$user' ORDER BY id DESC LIMIT 50");
while ($recent_row = dbFetchObject($recent_result)) {
	$recent_links .= "<a href='http://admin.popularliving.com/admin/templates/preview.php?id=".$recent_row->id."' target=_blank><b>$recent_row->name      </b>    $recent_row->id ($recent_row->dateTimeAdded)</a><br>";
}
echo $recent_links;




echo "<hr size='1'>";

echo "<b>Newsletters links generated by others (last 200):<br><br></b>";

$recent_links = '';
$recent_result = dbQuery("SELECT * FROM templates WHERE username != '$user' ORDER BY id DESC LIMIT 200");
while ($recent_row = dbFetchObject($recent_result)) {
	$recent_links .= "<a href='http://admin.popularliving.com/admin/templates/preview.php?id=".$recent_row->id."' target=_blank>$recent_row->id ($recent_row->dateTimeAdded, by $recent_row->username)</a><br>";
}
echo $recent_links;

?>
