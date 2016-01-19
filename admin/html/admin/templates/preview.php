<?php

include("../../includes/paths.php");

session_start();

mysql_select_db('newsletter_templates');

if (!ctype_digit($id)) {
	$id = '';
}

$sSelectQuery = "SELECT * FROM templates WHERE id='$id' LIMIT 1";
$rSelectResult = dbQuery($sSelectQuery);
while ($oRow = dbFetchObject($rSelectResult)) {
	$html_code = stripslashes($oRow->content);
	$html_code = str_replace('REDIR:www.','REDIR:http://www.',$html_code);
	echo $html_code;
	exit;
}

?>
