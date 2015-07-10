<?php

/*$iScriptEndTime = getMicroTime();

$fScriptExecutionTime = round($iScriptEndTime - $iScriptStartTime,4);

$sInsertLoadTimeQuery= "INSERT INTO pageLoadTimes(pageId, dateTimeDisplayed, pageLoadTime)
						VALUES(".$_SESSION["iSesPageId"].",now(), \"$fScriptExecutionTime\")";
*/
//$rInsertLoadTimeResult = dbQuery($sInsertLoadTimeQuery);

/*
<table cellpadding=0 cellspacing=0 align=center>
<tr><td align=center class=tiny><BR><BR><?php echo $fScriptExecutionTime;?></td></tr>
</table>
*/

reset($aGblSiteNames);
reset($aGblSites);	
/*while (list($key,$val) = each($aGblSites)) {

		if ($_SERVER['SERVER_ADDR'] == $aGblSites[$key]) {
			echo "<table cellpadding=0 cellspacing=0 align=center>
					<tr><td align=center class=tiny><BR><BR><BR><BR><BR><BR><font color=#999999>".
					$key."<font></td></tr></table>";
		}
}*/
$sTemp = '';
switch ($_SERVER['SERVER_ADDR']) {
	case '64.132.70.110':
		$sTemp = 'w0';
		break;
	case '64.132.70.31':
		$sTemp = 'w1';
		break;
	case '64.132.70.32':
		$sTemp = 'w2';
		break;
	case '64.132.70.33':
		$sTemp = 'w3';
		break;
	case '64.132.70.34':
		$sTemp = 'w4';
		break;
	case '64.132.70.35':
		$sTemp = 'w5';
		break;
}

?>
<center><font size=1 color="#C0C0C0"><?php echo $sTemp; ?></font></center>
