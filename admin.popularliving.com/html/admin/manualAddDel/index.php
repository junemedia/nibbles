<?php

include("../../includes/paths.php");

session_start();

$sPageTitle = "Manual Add/Delete To The List";

// Check user permission to access this page

$sRemoteIp = "198.63.247.2";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($sAddToList || $sDeleteFromList) {
		
		$aEmailArray = explode("\n",$sEmailList);
		
		if (count($aEmailArray) == '') {
			$sMessage = "Please enter email address...";
		} else {
			
			
			$iEmailCount = count($aEmailArray);
			
			if ($sAddToList) {
				// insert into active
				// insert into sub
				// insert into confirm
				//delete from pending
				//delete from inactive
				
				for ($i=0; $i<count($aEmailArray); $i++) {
					
					$sEmail = $aEmailArray[$i];
					$sEmail = trim($sEmail);
					if ($sEmail != '') {

						// start of track users' activity in nibbles 
						$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
		
						$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
						  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $sEmail to $iJoinListId\")"; 
						$rLogResult = dbQuery($sLogAddQuery); 
						echo  dbError(); 
						// end of track users' activity in nibbles		
						
						
						
						// Insert emails in joinEmailSub
						$sSubInsertQuery = "INSERT INTO joinEmailSub(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
											VALUES('$sEmail', '$iJoinListId', 'import', '$sRemoteIp', NOW() )";
						$rSubInsertResult = dbQuery($sSubInsertQuery);
						
						// Insert email in joinEmailConf
						$sConfInsertQuery = "INSERT INTO joinEmailConfirm(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
											 VALUES('$sEmail', '$iJoinListId', 'import', '$sRemoteIp', NOW() )";
						$rConfInsertResult = dbQuery($sConfInsertQuery);
						
						// Insert email in joinEmailActive if not exists with the same listId
						$sActiveInsertQuery = "INSERT IGNORE INTO joinEmailActive(email, joinListId, sourceCode, dateTimeAdded)
								   				VALUES('$sEmail', '$iJoinListId', 'import', NOW() )";
						$rActiveInsertResult = dbQuery($sActiveInsertQuery);
						
						// delete from joinEmailInactive
						$sInactiveDeleteQuery = "DELETE FROM joinEmailInactive
												 WHERE  email = '$sEmail'
												 AND    joinListId = '$iJoinListId'";	
						$rInactiveDeleteResult = dbQuery($sInactiveDeleteQuery);
						
						// delete from pending
						$sPendingDeleteQuery = "DELETE FROM joinEmailPending
												WHERE  email = '$sEmail'
												AND    joinListId = '$iJoinListId'";	
						$rPendingDeleteResult = dbQuery($sPendingDeleteQuery);
					}
					
				}

				$sMessage = "$iEmailCount user(s) has been subscribed to the selected list...";
				
			} else if ($sDeleteFromList) {				
				
				for ($i=0;$i<count($aEmailArray);$i++) {
					
					$sEmail = $aEmailArray[$i];
					$sEmail = trim($sEmail);
					if ($sEmail != '') {
						$sActiveQuery = "SELECT *
									 FROM	 joinEmailActive
									 WHERE   email = '$sEmail'
									 AND	 joinListId = '$iJoinListId'";
						$rActiveResult = dbQuery($sActiveQuery);
						
						while ($oActiveRow = dbFetchObject($rActiveResult)) {

							

							// start of track users' activity in nibbles 
							$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
			
							$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
							  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Unsub: $sEmail from $iJoinListId\")"; 
							$rLogResult = dbQuery($sLogAddQuery); 
							echo  dbError(); 
							// end of track users' activity in nibbles		
							
							
							// insert into inactive
							// insert into unsub
							// delete from active
							$sInactiveInsertQuery = "INSERT IGNORE INTO joinEmailInactive(email, joinListId, sourceCode, dateTimeAdded)
									 VALUES('$sEmail', '$iJoinListId', 'import', now())";
							
							$rInactiveInsertResult = dbQuery($sInactiveInsertQuery);
							echo dbError();
							$sUnsubInsertQuery = "INSERT INTO joinEmailUnsub(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
							  	  VALUES('$sEmail', '$iJoinListId', 'import', '$sRemoteIp', now())";
							
							$rUnsubInsertResult = dbQuery($sUnsubInsertQuery);
							echo dbError();
							$sActiveDeleteQuery = "DELETE FROM joinEmailActive
							   	   WHERE  email = '$sEmail'
							   	   AND    joinListId = '$iJoinListId'";
							
							$rActiveDeleteResult = dbQuery($sActiveDeleteQuery);
							echo dbError();
						}
					}
				}
				
				$sMessage = "$iEmailCount user(s) has been unsubscribed from the selected list...";
				
			}
		}
	}
	
	$sJoinListQuery = "SELECT *
		   		   FROM   joinLists
		   		   ORDER BY title";
	
	$rJoinListResult = dbQuery($sJoinListQuery);
	
	$sJoinListsList = "";
	
	while ($oJoinListRow = dbFetchObject($rJoinListResult)) {
		$sJoinListsList.= "<option value='$oJoinListRow->id'>$oJoinListRow->title";
	}
	
	$sSendWelcomeLetterChecked = '';
	if ($iSendWelcomeLetter) {
		$sSendWelcomeLetterChecked = "checked";
	}
	
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";
	
	include("../../includes/adminHeader.php");
	
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td>Select List</td>
	<td><select name=iJoinListId>
		<?php echo $sJoinListsList;?>
		</select></td>
</tr>
<tr>
	<td>Email Addresses</td>
	<td><textarea name=sEmailList rows=20 cols=40><?php echo $sEmailList;?></textarea></td>
</tr>
<tr>
	<td></td>
	<td><input type=submit name=sAddToList value='Add To The List Selected'> 
		&nbsp; &nbsp; &nbsp; <input type=submit name=sDeleteFromList value='Delete From The List Selected'>		
	</td>
</tr>
<tr><td colspan=2><BR><b>Notes:</b> 
		<BR>Welcome and confirm emails will not be sent.	
		
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