
<br>
<center><?php echo $sMainMenuLink;?></center>

<?php

reset($aGblSites);
/*while (list($key,$val) = each($aGblSiteNames)) {
	
		if ($val == $_SERVER['SERVER_NAME']) {

			echo "<table cellpadding=0 cellspacing=0 align=center>
					<tr><td align=center class=tiny><BR><BR><BR><BR><BR><BR><font color=#999999>".
					$key."<font></td></tr></table>";
		}
}*/

if( $_SESSION['reportsRunning'][$_SERVER['PHP_AUTH_USER']] == "1" ) {
	//echo "Val=1, setting Val=0.<br>";
	$_SESSION['reportsRunning'][$_SERVER['PHP_AUTH_USER']] = "0";
}

?>


</body>

</html>
