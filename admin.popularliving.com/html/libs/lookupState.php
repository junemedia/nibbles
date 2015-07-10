<?php

include("../includes/paths.php");
$sZip = trim($_GET['zip']);

$sQuery = "select * from zipStateCity WHERE zip='$sZip' LIMIT 1";
$rResult = mysql_query($sQuery);
while ($oStateRow = dbFetchObject($rResult)) {
	if ($oStateRow->state !='') {
		echo $oStateRow->state;
	}
}


?>
