<?php

/*********

Script to Display List/Delete Spam Trap Blacklist

*********/

include("../../includes/paths.php");

$listTypeCaption = ucfirst($listType);

if ($listType == "blacklist") {
	$listTable = "spamTrapBlacklist";
} else if ($listType == "whitelist") {
	$listTable = "spamTrapWhitelist";
}

$sPageTitle = "SpamTrap Management - Generate ".$listTypeCaption;

//get data
$selectQuery = "SELECT *
				FROM   $listTable";
$selectResult = mysql_query($selectQuery);

if ($generateDeclude) {
	$displayWidth = 60;
	while ($selectRow = mysql_fetch_object($selectResult)) {
		$spamTrapList .= "WHITELIST IP ".str_pad($selectRow->ipAddress,20," ", STR_PAD_RIGHT) ;
						
		if ($selectRow->serverName != '' || $selectRow->notes != '') {
			$spamTrapList .= " # ".$selectRow->serverName." ".$selectRow->notes;
		}
		$spamTrapList .= "\n";
	}
} else {
	$displayWidth = 20;
	while ($selectRow = mysql_fetch_object($selectResult)) {
		$spamTrapList .= $selectRow->ipAddress."\n";
	}
}

// Hidden variables to be passed with form submission
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>";

?>

<html>
<head>
<title><?php echo $sPageTitle;?></title>
<LINK rel="stylesheet" href="<?php echo $sGblAdminSiteRoot;?>/styles.css" type="text/css" >
</head>

<body>
<br>
<form action="<?php echo $PHP_SELF;?>" method="post">
<?php echo $hidden;?>
<table width=95% align=center><tr><TD class=message align=center><?php echo $message;?></td></tr></table>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>Blacklist</td>
		<Td><textarea name="blacklist" rows=15 cols=<?php echo $displayWidth;?>><?php echo $spamTrapList;?></textarea></td>
	</tr>
	
</table>
<br><br>
<table align=center>
	<tr><td colspan=2 align=center><input type=button name="close" value="Close" onClick="JavaScript:window.close();"></td>
	</tr>
</table>
</form>
</body>
</html>	