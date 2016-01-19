<?php

/*********

Script to Display Note of SpamTrap 

*********/

include("../../includes/paths.php");

$listTypeCaption = ucfirst($listType);

$sPageTitle = "SpamTrap Management - Display Notes";

$listTypeCaption = ucfirst($listType);

if ($listType == "blacklist") {
	$listTable = "spamTrapBlacklist";
} else if ($listType == "whitelist") {
	$listTable = "spamTrapWhitelist";
} else if ($listType == "badAddr") {
	$listTable = "spamTrapBadAddr";
}

//get data
$selectQuery = "SELECT *
				FROM   $listTable
				WHERE id = '$id'";
		$selectResult = mysql_query($selectQuery);
		
		while ($selectRow = mysql_fetch_object($selectResult)) {
			$notes = $selectRow->notes."\n";
		}

?>
	
<html>
<head>
<title><?php echo $sPageTitle;?></title>
<LINK rel="stylesheet" href="<?php echo $sGblAdminSiteRoot;?>/styles.css" type="text/css" >
</head>

<body>
<br>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td class=header>Notes</td></tr>
		<tr><Td><?php echo $notes;?></td>
	</tr>
	
</table>
<br><br>
<table align=center>
	<tr><td colspan=2 align=center><input type=button name="close" value="Close" onClick="JavaScript:window.close();"></td>
	</tr>
</table>

</body>
</html>