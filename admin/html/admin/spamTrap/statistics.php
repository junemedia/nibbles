<?php

/*********

Script to Display  Spam Trap Statistics

*********/

include("../../includes/paths.php");

$sPageTitle = "SpamTrap Statistics";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {

	
// start of track users' activity in nibbles 
$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Report\")"; 
$rLogResult = dbQuery($sLogAddQuery); 
echo  dbError(); 
// end of track users' activity in nibbles		
	
	
// Count Blacklist
$blacklistQuery = "SELECT count(*) AS totalBlacklist
				   FROM   spamTrapBlacklist";
$blacklistResult = mysql_query($blacklistQuery);
while ($blacklistRow = mysql_fetch_object($blacklistResult)) {
	$totalBlacklist = $blacklistRow->totalBlacklist;
}

// Count Blacklist
$autoBlacklistQuery = "SELECT count(*) AS totalAutomatedBlacklist
				   FROM   spamTrapBlacklist
				   WHERE  fromSeedAddr = 'Y'";
$autoBlacklistResult = mysql_query($autoBlacklistQuery);
while ($autoBlacklistRow = mysql_fetch_object($autoBlacklistResult)) {
	$totalAutomatedBlacklist = $autoBlacklistRow->totalAutomatedBlacklist;
}


// Count Whitelist
$whitelistQuery = "SELECT count(*) AS totalWhitelist
				   FROM   spamTrapWhitelist";
$whitelistResult = mysql_query($whitelistQuery);
while ($whitelistRow = mysql_fetch_object($whitelistResult)) {
	$totalWhitelist = $whitelistRow->totalWhitelist;
}

// Count Blacklist
$badAddrQuery = "SELECT count(*) AS totalBadAddr
				 FROM   spamTrapBadAddr";
$badAddrResult = mysql_query($badAddrQuery);
while ($badAddrRow = mysql_fetch_object($badAddrResult)) {
	$totalBadAddr = $badAddrRow->totalBadAddr;
}

$spamTrapLink = "<a href='index.php?iMenuId=$iMenuId'>Back To SpamTrap Admin Menu</a>";
	
	include("../../includes/adminHeader.php");	
?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=4><?php echo $spamTrapLink;?></td></tr>
<tr>
	<td align=left class=header>Whitelist</td>
	<td align=left class=header>Blacklist</td>
	<td align=left class=header>Blacklist From Seed Addresses</td>
	<td align=left class=header>Bad Addresses</td>
	<td>&nbsp; </td>
</tr>
<tr>
	<td align=left ><?php echo $totalWhitelist;?></td>
	<td align=left ><?php echo $totalBlacklist;?></td>
	<td align=left ><?php echo $totalAutomatedBlacklist;?></td>
	<td align=left ><?php echo $totalBadAddr;?></td>
	<td>&nbsp; </td>
</tr>

</table>


<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>

	
