<?php

include("../../includes/paths.php");

session_start();

$sPageTitle = "Join Subscribers Info";

// Check user permission to access this page

$sRemoteIp = $_SERVER['REMOTE_ADDR'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	
	if ($sGetInfo) {

		
		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sEmail\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		// subscription info
		$sSubQuery = "SELECT joinEmailSub.*, joinLists.title
					  FROM   joinEmailSub, joinLists
					  WHERE  joinEmailSub.joinListId = joinLists.id
					  AND	 email = '$sEmail' 
					  ORDER BY dateTimeAdded DESC";
		$rSubResult = dbQuery($sSubQuery);
		echo dbError();
		while ($oSubRow = dbFetchObject($rSubResult)) {
			
			if ($sBgcolorClass == "ODD") {
					$sBgcolorClass = "EVEN";
			} else {
					$sBgcolorClass = "ODD";
			}
			$sSubInfo .= "<tr class=$sBgcolorClass><td>$oSubRow->title</td><td>$oSubRow->sourceCode</td>
							 	<td>$oSubRow->remoteIp</td><Td>$oSubRow->dateTimeAdded</td></tr>";
		}
		
		$sConfirmQuery = "SELECT *, joinLists.title
					  FROM   joinEmailConfirm, joinLists
					  WHERE  joinEmailConfirm.joinListId = joinLists.id
					  AND	 email = '$sEmail' 
					  ORDER BY dateTimeAdded DESC";
		$rConfirmResult = dbQuery($sConfirmQuery);
		echo dbError();
		while ($oConfirmRow = dbFetchObject($rConfirmResult)) {
			if ($sBgcolorClass == "ODD") {
					$sBgcolorClass = "EVEN";
			} else {
					$sBgcolorClass = "ODD";
			}
			
			$sConfirmInfo .= "<tr class=$sBgcolorClass><td>$oConfirmRow->title</td><td>$oConfirmRow->sourceCode</td>
							 	<td>$oConfirmRow->remoteIp</td><Td>$oConfirmRow->dateTimeAdded</td></tr>";
		}
		
		$sUnsubQuery = "SELECT *, joinLists.title
					  FROM   joinEmailUnsub, joinLists
					  WHERE  joinEmailUnsub.joinListId = joinLists.id
					  AND	 email = '$sEmail' 
					  AND isPurge !='1' 
					  ORDER BY dateTimeAdded DESC";
		$rUnsubResult = dbQuery($sUnsubQuery);
		echo dbError();
		while ($oUnsubRow = dbFetchObject($rUnsubResult)) {
			if ($sBgcolorClass == "ODD") {
					$sBgcolorClass = "EVEN";
			} else {
					$sBgcolorClass = "ODD";
			}
			$sUnsubInfo .= "<tr class=$sBgcolorClass><td>$oUnsubRow->title</td><td>$oUnsubRow->sourceCode</td>
							 	<td>$oUnsubRow->remoteIp</td><Td>$oUnsubRow->dateTimeAdded</td></tr>";
		}
		
		$sPurgeQuery = "SELECT *, joinLists.title
					  FROM   joinEmailUnsub, joinLists
					  WHERE  joinEmailUnsub.joinListId = joinLists.id
					  AND	 email = '$sEmail' 
					  AND isPurge ='1' 
					  ORDER BY dateTimeAdded DESC";
		$rPurgeResult = dbQuery($sPurgeQuery);
		echo dbError();
		while ($oPurgeRow = dbFetchObject($rPurgeResult)) {
			if ($sBgcolorClass == "ODD") {
					$sBgcolorClass = "EVEN";
			} else {
					$sBgcolorClass = "ODD";
			}
			$sPurgeInfo .= "<tr class=$sBgcolorClass><td>$oPurgeRow->title</td><td>$oPurgebRow->sourceCode</td>
							 	<td>$oPurgeRow->remoteIp</td><Td>$oPurgeRow->dateTimeAdded</td></tr>";
		}
		
		$sHeldQuery = "SELECT *, joinLists.title
					  FROM   joinEmailHeldJournal, joinLists
					  WHERE  joinEmailHeldJournal.joinListId = joinLists.id
					  AND	 email = '$sEmail' 
					  ORDER BY dateTimeAdded DESC";
		$rHeldResult = dbQuery($sHeldQuery);
		echo dbError();
		while ($oHeldRow = dbFetchObject($rHeldResult)) {
			if ($sBgcolorClass == "ODD") {
					$sBgcolorClass = "EVEN";
			} else {
					$sBgcolorClass = "ODD";
			}
			$sHeldInfo .= "<tr class=$sBgcolorClass><td>$oHeldRow->title</td><td>$oHeldRow->sourceCode</td>
							 	<Td>$oHeldRow->dateTimeAdded</td></tr>";
		}
		
		$sJoinSubInfo = "<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
						<tr><td colspan=4 class=bigHeader><BR><u>Sub Info</u></td></tr>
						<tr><td class=header>Join List</td><td class=header>Source Code</td>
							<td class=header>Remote IP</td><td class=header>Date Time Added</td></tr>
						$sSubInfo</table>
						<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
						<tr><td colspan=4 class=bigHeader><BR><u>Confirm Info</u></td></tr>
						<tr><td class=header>Join List</td><td class=header>Source Code</td>
							<td class=header>Remote IP</td><td class=header>Date Time Added</td></tr>
						$sConfirmInfo</table>		
						<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
						<tr><td colspan=4 class=bigHeader><BR><u>Unsub Info</u></td></tr>
						<tr><td class=header>Join List</td><td class=header>Source Code</td>
							<td class=header>Remote IP</td><td class=header>Date Time Added</td></tr>
						$sUnsubInfo</table>		
						<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
						<tr><td colspan=4 class=bigHeader><BR><u>Purge Info</u></td></tr>
						<tr><td class=header>Join List</td><td class=header>Source Code</td>
							<td class=header>Remote IP</td><td class=header>Date Time Added</td></tr>
						$sPurgeInfo</table>	
						<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
						<tr><td colspan=3 class=bigHeader><BR><u>	Held Info</u></td></tr>
						<tr><td class=header>Join List</td><td class=header>Source Code</td>
							<td class=header>Date Time Added</td></tr>
						$sHeldInfo</table>";
		
	}
	
	
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";
	
	include("../../includes/adminHeader.php");
	
?>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr>
	<td>Email Address</td>
	<td><input type=text name=sEmail value='<?php echo $sEmail;?>' size=40></td>
</tr>

<tr>
	<td></td>
	<td><input type=submit name=sGetInfo value='Get Join Info'></td>
</tr>
	
</table>

<?php echo $sJoinSubInfo;?>

</form>


<?php
include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>