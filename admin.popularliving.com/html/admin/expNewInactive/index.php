<?php

ini_set('max_execution_time', 5000000);

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Email Contents - List/Delete Email Content";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
$sExportData = '';
// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	/*function isBannedEmailString($eMail) {
		$isBanned = false;
		$checkQuery = "SELECT * FROM bannedEmailString";
		$checkResult = dbQuery($checkQuery);
		while ($checkRow = dbFetchObject($checkResult)) {
			if (strstr($eMail,$checkRow->emailString)) {
				$isBanned = true;
			}
		}
		if ($checkResult) {
			dbFreeResult($checkResult);
		}
		return $isBanned;
	}*/
	
	if ($sExportNewInactives) {

	/*	$sGetAllBannedEmail = "SELECT * FROM bannedEmails";
		$rGetBannedEmailResult = dbQuery($sGetAllBannedEmail);
		$aBannedEmail = array();
		$i = 0;
		while ($oBannedEmailRow = dbFetchObject($rGetBannedEmailResult)) {
			$aBannedEmail[$i] = strtolower($oBannedEmailRow->email);
			$i++;
		}*/
	
	
		// start of track users' activity in nibbles 
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  		VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Export new inactives\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
		
		
		// get max id
		$sMaxIdQuery = "SELECT max(id) as maxId
						FROM   joinEmailInactive";
		$rMaxIdResult = dbQuery($sMaxIdQuery);
		while ($oMaxRow = dbFetchObject($rMaxIdResult)) {
			$iMaxId = $oMaxRow->maxId;
		}
		
		$sActiveQuery = "SELECT joinEmailInactive.email, joinLists.lyrisName
						 FROM   joinEmailInactive, joinLists
						 WHERE  joinEmailInactive.joinListId = joinLists.id
						 AND	exported = '' 
						 AND	joinEmailInactive.joinListId !='215' 
						 AND	joinEmailInactive.joinListId !='904'
						 AND	joinEmailInactive.id <= '$iMaxId'";
		$rActiveResult = dbQuery ($sActiveQuery);
		while ($oActiveResult = dbFetchObject($rActiveResult)) {
			$sEmail = $oActiveResult->email;
			$sLyrisName = $oActiveResult->lyrisName;
		
			/*$bAllowThisEntry = true;
			if (in_array(strtolower($sEmail), $aBannedEmail)) {
				$bAllowThisEntry = false;
			}

			if ($bAllowThisEntry == true) {
				$sStartEmail = substr($sEmail,0,strpos($sEmail, '@'));
				$sGetAllBannedStart = "SELECT * FROM bannedEmailStart WHERE startsWith = \"$sStartEmail\"";
				$rGetBannedStartResult = dbQuery($sGetAllBannedStart);
				if (mysql_num_rows($rGetBannedStartResult) > 0 ) {
					$bAllowThisEntry = false;
				}
			}

			if ($bAllowThisEntry == true) {
				$domain = strstr($sEmail, '@');
				$domain = substr($domain,1,strlen($domain));
				$sGetAllBannedDomains = "SELECT * FROM bannedDomains WHERE domain = \"$domain\"";
				$rGetBannedDomainsResult = dbQuery($sGetAllBannedDomains);
				if (mysql_num_rows($rGetBannedDomainsResult) > 0 ) {
					$bAllowThisEntry = false;
				}
			}
			
			// check if email contains banned email string
			if ($bAllowThisEntry == true) {
				if (isBannedEmailString($sEmail)) {
					$bAllowThisEntry = false;
				}
			}*/

			//if ($bAllowThisEntry == true) {
				$sExportData .= "\"$sEmail\",\"$sLyrisName\"\r\n";
			//}
		}

		if ($iMarkAsExported) {
			$sUpdateQuery = "UPDATE joinEmailInactive
							 SET    exported = '1',
							 		exportedDateTime = now()
							 WHERE  exported = ''
							 AND    id <= '$iMaxId'"; 
									
			$rUpdateResult = dbQuery($sUpdateQuery);
		}
		
		
		// get max id for mw
		$sMaxIdQuery = "SELECT max(id) as maxId
						FROM   myfree.mw";
		$rMaxIdResult = dbQuery($sMaxIdQuery);
		while ($oMaxRow = dbFetchObject($rMaxIdResult)) {
			$iMwMaxId = $oMaxRow->maxId;
		}
		
		
		$sMwQuery = "SELECT *
					 FROM   myfree.mw
					 WHERE  action = 'd'
					 AND    id <= '$iMwMaxId'";
		$rMwResult = dbQuery($sMwQuery);
		while ($oMwRow = dbFetchObject($rMwResult)) {
			$sEmail = $oMwRow->email;
			$sLyrisName = $oMwRow->list;
			
		/*	$bAllowThisEntry = true;
			if (in_array(strtolower($sEmail), $aBannedEmail)) {
				$bAllowThisEntry = false;
			}

			if ($bAllowThisEntry == true) {
				$sStartEmail = substr($sEmail,0,strpos($sEmail, '@'));
				$sGetAllBannedStart = "SELECT * FROM bannedEmailStart WHERE startsWith = \"$sStartEmail\"";
				$rGetBannedStartResult = dbQuery($sGetAllBannedStart);
				if (mysql_num_rows($rGetBannedStartResult) > 0 ) {
					$bAllowThisEntry = false;
				}
			}

			if ($bAllowThisEntry == true) {
				$domain = strstr($sEmail, '@');
				$domain = substr($domain,1,strlen($domain));
				$sGetAllBannedDomains = "SELECT * FROM bannedDomains WHERE domain = \"$domain\"";
				$rGetBannedDomainsResult = dbQuery($sGetAllBannedDomains);
				if (mysql_num_rows($rGetBannedDomainsResult) > 0 ) {
					$bAllowThisEntry = false;
				}
			}
			
			// check if email contains banned email string
			if ($bAllowThisEntry == true) {
				if (isBannedEmailString($sEmail)) {
					$bAllowThisEntry = false;
				}
			}

			if ($bAllowThisEntry == true) {*/
				$sExportData .= "\"$sEmail\",\"$sLyrisName\"\r\n";
			//}
		}
		
		if ($iMarkAsExported) {
			$sDeleteQuery = "DELETE FROM myfree.mw					 
							 WHERE  action='d'
							 AND	id <= '$iMwMaxId'"; 
			$rDeleteResult = dbQuery($sDeleteQuery);
		}
		

		header("Content-type: text/plain");
		header("Content-Disposition: attachment; filename=newInactive.txt");
		header("Content-Description: Text output");
		echo $sExportData;
		// if didn't exit, all the html page content will be saved as excel file.	
		exit();
		
	}
	

include("../../includes/adminHeader.php");	
	
?>


<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td><input type=checkbox name=iMarkAsExported >Mark As Exported</td></tr>
<tr><td><input type=submit name=sExportNewInactives value='Export New Inactives'></td>
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