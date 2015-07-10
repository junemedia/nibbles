<?php

/*********

Script to Generate Spam Trap Bad Addr list

*********/

include("../../includes/paths.php");

$sPageTitle = "SpamTrap Management - Generate Bad Addresses";
session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
//get data
$selectQuery = "SELECT *
				FROM   spamTrapBadAddr";
$selectResult = mysql_query($selectQuery);

if ($generateDeclude) {
	
	while ($selectRow = mysql_fetch_object($selectResult)) {
		if (stristr($selectRow->badAddress, "@")) {
			$badAddress = $selectRow->badAddress;
		} else {			
			$badAddress = "@" . $selectRow->badAddress;
		}
		$badAddrList .= $badAddress . "\n";
	}
} else {	
	while ($selectRow = mysql_fetch_object($selectResult)) {
		$badAddrList .= $selectRow->badAddress."\n";
	}
}

// Hidden variable to be passed with form submission
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=menuFolder value='$menuFolder'>";

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
	<tr><td>Bad Addresses</td>
		<Td><textarea name="badAddresses" rows=15 cols=20><?php echo $badAddrList;?></textarea></td>
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

<?php
		
} else {
	echo "You are not authorized to access this page...";
}
?>