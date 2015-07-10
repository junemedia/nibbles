<?php

/*******

Script to Display You Won Admin Menu

*********/

include("../../includes/paths.php");

$sPageTitle = "Spam Trap Menu";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
$blacklistLink = "<a href = 'listSpamTrap.php?iMenuId=$iMenuId&listType=blacklist'>SpamTrap Blacklist</a>";
$whitelistLink = "<a href = 'listSpamTrap.php?iMenuId=$iMenuId&listType=whitelist'>SpamTrap Whitelist</a>";
$badAddrLink = "<a href = 'listBadAddr.php?iMenuId=$iMenuId'>SpamTrap Bad Addresses</a>";
$statisticsLink = "<a href = 'statistics.php?iMenuId=$iMenuId'>SpamTrap Statistics</a>";
// Parse the variables in the Template

	include("../../includes/adminHeader.php");	
	
?>
<table align="center" width="550">
<tr>
<td colspan="2" align="center" bgcolor = "c1c1c1"><b>SpamTrap Administration</b></td>
</tr>
<tr><td valign="top" bgcolor = "eeeeee" width="50%" height=30>
		<ul>
		<li><?php echo $blacklistLink;?></li>
		</ul>
	</td>
	<td valign="top" bgcolor = "eeeeee" width="50%">
		<ul>
		<li><?php echo $whitelistLink;?></li>
		</ul>				
	</td>
</tr>
<tr><td valign="top" bgcolor = "eeeeee" width="50%" height=30>
		<ul>
		<li><?php echo $badAddrLink;?></li>
		</ul>
	</td>
	<td valign="top" bgcolor = "eeeeee" width="50%">
		<ul>
		<li><?php echo $statisticsLink;?></li>
		</ul>
	</td>
</tr>

</table>
<?php 
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}

?>