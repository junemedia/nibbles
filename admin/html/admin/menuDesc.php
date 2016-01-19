<?php

/*********

Script to Display Add/Edit Banned Domains

**********/

include("../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Menu - Description";

$sMenuQuery = "SELECT *
			   FROM   menu
			   WHERE  id = '$iMenuId'";
$rMenuResult = dbQuery($sMenuQuery);
while ($oMenuRow = dbFetchObject($rMenuResult)) {
	$sMenuDesc = $oMenuRow->description;
}
?>

<html>

<head>
<title><?php echo $sPageTitle;?></title>
<LINK rel="stylesheet" href="<?php echo $sGblAdminSiteRoot;?>/styles.css" type="text/css" >
</head>
<table width=85% align=center>
	<tr><Td align=center colspan=2><?php echo $sMenuDesc;?>
</td></tr></table>	

</body>

</html>