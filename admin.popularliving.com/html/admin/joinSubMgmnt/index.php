<?php

include("../../includes/paths.php");

session_start();

$sPageTitle = "Join Subscribers Management";

// Check user permission to access this page

$sRemoteIp = $_SERVER['REMOTE_ADDR'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	
	if ($sSubscribe || $sUnsubscribe || $sConfirm) {
		
		if ($sEmail == '') {
			$sMessage = "Please enter email address...";
		} else {
			
			if ($aJoinLists[0] != 'all') {
					for ($i=0; $i < count($aJoinLists); $i++) {
						$sCommaSeparatedJoinList .= "'" . $aJoinLists[$i] . "',";
						
					}
					if ($sCommaSeparatedJoinList != '') {
						$sCommaSeparatedJoinList = substr($sCommaSeparatedJoinList,0,strlen($sCommaSeparatedJoinList)-1);
						
					}
				}
								
				
			if ($sSubscribe) {
				// insert into active
				// insert into sub
				// insert into confirm
				//delete from pending
				//delete from inactive
				
				// Insert email in joinEmailSub if not exists with the same listId
				
				
				
				$sJoinListQuery = "SELECT *
				   		   		   FROM   joinLists ";
				if ($sCommaSeparatedJoinList != '') {
					$sJoinListQuery .= " WHERE id IN ( $sCommaSeparatedJoinList ) ";
				}
				
				$rJoinListResult = dbQuery($sJoinListQuery);
				
				//echo $sJoinListQuery.dbError().dbNumRows($rJoinListResult);
				echo dbError();
				
				while ($oJoinListRow = dbFetchObject($rJoinListResult)) {
					
					$iJoinListId = $oJoinListRow->id;
					
					// start of track users' activity in nibbles 
					$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
					$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $sEmail to $iJoinListId\")"; 
					$rLogResult = dbQuery($sLogAddQuery); 
					echo  dbError(); 
					// end of track users' activity in nibbles		
					
					
					$sSubInsertQuery = "INSERT INTO joinEmailSub(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
							VALUES('$sEmail', '$iJoinListId', '', '$sRemoteIp', NOW() )";
					$rSubInsertResult = dbQuery($sSubInsertQuery);
					
					// Insert email in joinEmailConf if not exists with the same listId
					$sConfInsertQuery = "INSERT INTO joinEmailConfirm(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
							 VALUES('$sEmail', '$iJoinListId', '', '$sRemoteIp', NOW() )";
					$rConfInsertResult = dbQuery($sConfInsertQuery);
					
					// Insert email in joinEmailActive if not exists with the same listId
					$sActiveInsertQuery = "INSERT IGNORE INTO joinEmailActive(email, joinListId, sourceCode, dateTimeAdded)
							   VALUES('$sEmail', '$iJoinListId', '', NOW() )";
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
				
				if ($iSendWelcomeLetter) {
					
					// send confirmation email
					
					$sListEmailQuery = "SELECT *
				   					FROM   emailContents
				   					WHERE  system = 'join'
				  				    AND	   emailPurpose = 'welcome' ";
					$rListEmailResult =  dbQuery($sListEmailQuery);
					echo dbError();
					while ($oListEmailRow = dbFetchObject($rListEmailResult)) {
						$sConfirmEmailContent = $oListEmailRow->emailBody;
						$sConfirmEmailSubject = $oListEmailRow->emailSub;
						$sConfirmEmailFromAddr = $oListEmailRow->emailFrom;
						
						$sConfirmEmailContent = ereg_replace("\[EMAIL\]", $sEmail, $sConfirmEmailContent);
						$sConfirmEmailContent = ereg_replace("\[REMOTE_IP\]", $sRemoteIp, $sConfirmEmailContent);
						//$sConfirmEmailContent = ereg_replace("\[DATE_TIME_SUB\]", $sDateTimeSubscribed, $sConfirmEmailContent);
						$sConfirmEmailContent = ereg_replace("\[MMDDYY\]", date("m").date("d").date("y"), $sConfirmEmailContent);
						$sConfirmEmailContent = ereg_replace("\[SOURCE_CODE\]", $_SESSION['sSesSourceCode'], $sConfirmEmailContent);
						
						
						$sConfirmEmailHeaders = "From: $sConfirmEmailFromAddr\r\n";
						$sConfirmEmailHeaders .= "X-Mailer: MyFree.com\r\n";
						$sConfirmEmailHeaders .= "Content-Type: text/plain; charset=iso-8859-1\r\n"; // Mime type
						
						mail($sEmail, $sConfirmEmailSubject, $sConfirmEmailContent, $sConfirmEmailHeaders);
					}
				}
				
				$sMessage = "$sEmail has been subscribed to the selected lists...";
				
			} else if ($sUnsubscribe) {

				// start of track users' activity in nibbles 
				$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Unsub: $sEmail from $sCommaSeparatedJoinList\")"; 
				$rLogResult = dbQuery($sLogAddQuery); 
				echo  dbError(); 
				// end of track users' activity in nibbles		
				
				
				$sJoinListQuery = "SELECT *
				   		   FROM   joinLists ";
				if ($sCommaSeparatedJoinList != '' && !$iPurge) {
					$sJoinListQuery .= " WHERE id IN ( $sCommaSeparatedJoinList ) ";
				}
												
				$rJoinListResult = dbQuery($sJoinListQuery);
				
				//echo $sJoinListQuery.dbError().dbNumRows($rJoinListResult);
				echo dbError();
				
				while ($oJoinListRow = dbFetchObject($rJoinListResult)) {
					
					$iJoinListId = $oJoinListRow->id;
					
					if ($iPurge) {
						
						// unsubscribe from all lists without checking if exists in Active or not
						
						// delete from inactive
						// insert into inactive
						// insert into unsub
						// delete from active
						
						$sInactiveDeleteQuery = "DELETE FROM joinEmailInactive
												 WHERE  email = '$sEmail'
												 AND	joinListId = '$iJoinListId'";
						$rInactiveDeleteResult = dbQuery($sInactiveDeleteQuery);
						
						$sInactiveInsertQuery = "INSERT IGNORE INTO joinEmailInactive(email, joinListId, sourceCode, dateTimeAdded)
									 VALUES('$sEmail', '$iJoinListId', '', now())";
						
						$rInactiveInsertResult = dbQuery($sInactiveInsertQuery);
						echo dbError();
						$sUnsubInsertQuery = "INSERT INTO joinEmailUnsub(email, joinListId, sourceCode, remoteIp, dateTimeAdded, isPurge)
							  	  VALUES('$sEmail', '$iJoinListId', '', '$sRemoteIp', now(), '1')";
						
						$rUnsubInsertResult = dbQuery($sUnsubInsertQuery);
						echo dbError();
						$sActiveDeleteQuery = "DELETE FROM joinEmailActive
							   	   WHERE  email = '$sEmail'
							   	   AND    joinListId = '$iJoinListId'";
						
						$rActiveDeleteResult = dbQuery($sActiveDeleteQuery);
						echo dbError();
						
					} else {
					$sActiveQuery = "SELECT *
									 FROM	 joinEmailActive
									 WHERE   email = '$sEmail'
									 AND	 joinListId = '$iJoinListId'";
					$rActiveResult = dbQuery($sActiveQuery);
					
					while ($oActiveRow = dbFetchObject($rActiveResult)) {
												
						// insert into inactive
						// insert into unsub
						// delete from active	

						
						
						$sInactiveInsertQuery = "INSERT IGNORE INTO joinEmailInactive(email, joinListId, sourceCode, dateTimeAdded)
									 VALUES('$sEmail', '$iJoinListId', '', now())";
						
						$rInactiveInsertResult = dbQuery($sInactiveInsertQuery);
						echo dbError();
						$sUnsubInsertQuery = "INSERT INTO joinEmailUnsub(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
							  	  VALUES('$sEmail', '$iJoinListId', '', '$sRemoteIp', now())";
						
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
				
				
				// delete from pending and unsub from nonampere lists also in case of purge only
				if ($sUnsubscribe && $iPurge) {
					
					// delete from pending
					$sPendingDeleteQuery = "DELETE FROM joinEmailPending
											WHERE  email = '$sEmail'";	
					$rPendingDeleteResult = dbQuery($sPendingDeleteQuery);
					echo dbError();
					
					$sNonAmpereListQuery = "SELECT *
											FROM   joinListsNonAmpere";
					$rNonAmpereListResult = dbQuery($sNonAmpereListQuery);
					echo dbError();
					while ($oNonAmpereListRow = dbFetchObject($rNonAmpereListResult)) {
						
						$sShortName = $oNonAmpereListRow->shortName;
						$sInsertQuery = "INSERT INTO myfree.mw(email, action, list)
										 VALUES('$sEmail', 'd', '$sShortName')";
					//	$rInsertResult = dbQuery($sInsertQuery);
					//	echo dbError();
						
					}  
					
				}
				
				
				$sMessage = "$sEmail has been unsubscribed from the selected lists...";
			} else if ($sConfirm) {
				
				// confirm all pending

				// start of track users' activity in nibbles 
				$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Confirm all pending\")"; 
				$rLogResult = dbQuery($sLogAddQuery); 
				echo  dbError(); 
				// end of track users' activity in nibbles		
				
				
				$sSelectQuery = "SELECT *
				 FROM   joinEmailPending
				 WHERE  email = '$sEmail'" ;
				$rSelectResult = dbQuery($sSelectQuery);
				if ( dbNumRows($rSelectResult) >0) {
					while ($oSelectRow = dbFetchObject($rSelectResult)) {
						
						$iJoinListId = $oSelectRow->joinListId;
						
						$sConfirmInsertQuery = "INSERT INTO joinEmailConfirm(email, joinListId, sourceCode, remoteIp, dateTimeAdded)
							VALUES(\"$sEmail\", '$iJoinListId', \"\",'$sRemoteIp'  , now())";
						$rConfirmInsertResult = dbQuery($sConfirmInsertQuery);
						
						$sActiveInsertQuery = "INSERT IGNORE INTO joinEmailActive(email, joinListId, sourceCode, dateTimeAdded)
							VALUES(\"$sEmail\", '$iJoinListId', \"\", now())";
						$rActiveInsertResult = dbQuery($sActiveInsertQuery);
						
						// if inserted in confirm and active successfully, delete from pending
						if ($rConfirmInsertResult && $rActiveInsertResult) {
							
							$sDeleteQuery = "DELETE FROM   joinEmailPending
				 		 WHERE  email = '$sEmail'";
							$rDeleteResult = dbQuery($sDeleteQuery);
							echo dbError();
						}
						
					}
					
					// send welcome email
					if ($iSendWelcomeLetter) {
						
						$sListEmailQuery = "SELECT *
				  					FROM   emailContents
				   					WHERE  system = 'join'
				   					AND	   emailPurpose = 'welcome' ";
						$rListEmailResult =  dbQuery($sListEmailQuery);
						echo dbError();
						while ($oListEmailRow = dbFetchObject($rListEmailResult)) {
							$sConfirmEmailContent = $oListEmailRow->emailBody;
							$sConfirmEmailSubject = $oListEmailRow->emailSub;
							$sConfirmEmailFromAddr = $oListEmailRow->emailFrom;
							
							$sConfirmEmailContent = ereg_replace("\[EMAIL\]", $sEmail, $sConfirmEmailContent);
							$sConfirmEmailContent = ereg_replace("\[REMOTE_IP\]", $sRemoteIp, $sConfirmEmailContent);
							$sConfirmEmailContent = ereg_replace("\[MMDDYY\]", date("m").date("d").date("y"), $sConfirmEmailContent);
							
							$sConfirmEmailHeaders = "From: $sConfirmEmailFromAddr\r\n";
							$sConfirmEmailHeaders .= "X-Mailer: MyFree.com\r\n";
							$sConfirmEmailHeaders .= "Content-Type: text/plain; charset=iso-8859-1\r\n"; // Mime type
							
							mail($sEmail, $sConfirmEmailSubject, $sConfirmEmailContent, $sConfirmEmailHeaders);
							
						}
					}
				}
				
				$sMessage = "$sEmail has been confirmed for all the pending lists...";
				
			}
		}
	}
	
	$sJoinListQuery = "SELECT *
		   		   FROM   joinLists
		   		   ORDER BY shortName";
	
	$rJoinListResult = dbQuery($sJoinListQuery);
	
	$sJoinListsList = "<option value='all'>All";
	
	while ($oJoinListRow = dbFetchObject($rJoinListResult)) {
		$sJoinListsList.= "<option value='$oJoinListRow->id'>$oJoinListRow->shortName";
	}
	
	
	$sSendWelcomeLetterChecked = '';
	if ($iSendWelcomeLetter) {
		$sSendWelcomeLetterChecked = "checked";
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
<tr><td>Select List</td>
	<td><select name=aJoinLists[] multiple size=6>
		<?php echo $sJoinListsList;?>
		</select></td>
</tr>
<tr>
	<td>Send Welcome Letter</td>
	<td><input type=checkbox name=iSendWelcomeLetter> &nbsp; &nbsp; (For subscribe and confirm email pending)</td>
</tr>
<tr>
	<td>Purge</td>
	<td><input type=checkbox name=iPurge value='1'></td>
</tr>
<tr>
	<td></td>
	<td><input type=submit name=sSubscribe value='Subscribe & Confirm Selected Lists'> 
		&nbsp; &nbsp; &nbsp; <input type=submit name=sUnsubscribe value='Unsubscribe Selected List'>
		&nbsp; &nbsp; &nbsp; <input type=submit name=sConfirm value='Confirm All Pending'>
	</td>
</tr>
<tr><td colspan=2><BR><b>Note:</b> 
		<BR>Email requesting to confirm subscription will not be sent.		
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