<?php

include("../../includes/paths.php");

session_start();

$sPageTitle = "Run Editor's Push";

$aCommands = array();
$out = '';

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($sForcePush) {
			//touch the flag
			exec("touch flag",$resp);
		}
	

include("../../includes/adminHeader.php");	
?>
<center><font size=2 color=red><?PHP echo $out; ?></font></center>
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td><input type=submit name=sForcePush value='Force Push'></td>
</tr>
</table>
<input type='hidden' name='iMenuId' value='<?php echo $iMenuId; ?>'>
</form>

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>
