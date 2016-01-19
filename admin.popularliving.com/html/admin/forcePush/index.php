<?php

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Email Contents - List/Delete Email Content";

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if ($sForcePush) {
	
//	$result = shell_exec("echo YOUR_root_PASSWORD ¦ sudo COMMAND_TO_RUN_AS_root"); 


	
}

include("../../includes/adminHeader.php");	
	
?>


<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td><input type=checkbox name=iMarkAsExported>Mark As Exported </td></tr>
<tr><td><input type=submit name=sForcePush value='Force Push'></td>
</tr>

<?php echo $sEmailContentsList;?>
<tr><td align=left><?php echo $sAddButton;?></td></tr>
</table>

</form>


<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>