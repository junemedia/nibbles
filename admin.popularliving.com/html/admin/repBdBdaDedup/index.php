<?php

/*
Dedupe - bd versus bda - need script, needs to give report on who is sub'd to bd and dba. Not supposed to be on both
sallyjoinc: Option to unsub from bda if on bd
sallyjoinc: on bd means confirmed and active on bd
*/

include("../../includes/paths.php");
include("$sGblIncludePath/reportInclude.php");

session_start();

$sPageTitle = "BD / BDA Dedup";

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($sSubmit && $iUnsub) {
		if ($sAllowReport == 'N') {
			$sMessage = "Server Load Is High. Please check back soon...";
		} else {
			
		for ($i=0; $i <= 50000; $i=$i+500) {
			
			
			$sBdaQuery = "SELECT a.*
			  FROM joinEmailActive AS a, joinEmailActive AS b
			  WHERE a.email = b.email 
			  AND a.joinListId = '215' 
			  AND b.joinListId = '162' 
			  LIMIT $i, 500";
			$rBdaResult = dbQuery($sBdaQuery);
			while ($oBdaRow = dbFetchObject($rBdaResult)) {
				$sEmail = $oBdaRow->email;
				$sRemoteIp = $oBdaRow->remoteIp;
				
				// unsub from bda
				$iJoinListId = "215";
				$sInactiveInsertQuery = "INSERT IGNORE INTO joinEmailInactive(email, joinListId, sourceCode, dateTimeAdded)
							 VALUES('$sEmail', '$iJoinListId', 'dedup BD', now())";
				
				$rInactiveInsertResult = dbQuery($sInactiveInsertQuery);
				echo dbError();
				$sUnsubInsertQuery = "INSERT INTO joinEmailUnsub(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
							  	  VALUES('$sEmail', '$iJoinListId', 'dedup BD', '$sRemoteIp', now())";
				
				$rUnsubInsertResult = dbQuery($sUnsubInsertQuery);
				echo dbError();
				$sActiveDeleteQuery = "DELETE FROM joinEmailActive
							   	   WHERE  email = '$sEmail'
							   	   AND    joinListId = '$iJoinListId'";
				
				$rActiveDeleteResult = dbQuery($sActiveDeleteQuery);
				echo dbError();
			}
			
			$iTemp = $i+500;
			
			echo "<BR> $iTemp unsubscribed from BDA. Please wait...";
			sleep(10);
		}
				
	   }
	}
	
	$iDups = 0;
	
	$sBdaQuery = "SELECT count(a.email) as dups
			  FROM joinEmailActive AS a, joinEmailActive AS b
			  WHERE a.email = b.email 
			  AND a.joinListId = '215' 
			  AND b.joinListId = '162' ";
	$rBdaResult = dbQuery($sBdaQuery);
	while ($oBdaRow = dbFetchObject($rBdaResult)) {
		$iDups = $oBdaRow->dups;
	}
	
	if ($iUnsub) {
		$sUnsubChecked = "checked";
	}
	
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";
	
	include("../../includes/adminHeader.php");
	
	// display javascript from reportInclude.php which defined funcReportClicked() function
	echo $sReportJavaScript;
?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<input type=hidden name=reportClicked>
<input type=hidden name=sViewReport>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=2>Found <font color=red><b><?php echo $iDups;?></b></font> users subscribed to both - BD and BDA.</td></tr>
<tr>
	<td width=20%>Unsubscribe from BDA</td>
	<td><input type=checkbox name=iUnsub <?php echo $sUnsubChecked;?>></td>
</tr>

<tr>
	<td></td>
	<td><input type=button name=sSubmit value='Submit' onClick="funcReportClicked('report');"> 		
	</td>
</tr>

</table>

</form>

<?php
include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}

?>