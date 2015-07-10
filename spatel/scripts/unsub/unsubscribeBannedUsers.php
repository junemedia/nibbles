<?php

/*
The purpose of this script is to remove users from all lists
if their email address contains Banned Domain

FCC: http://www.fcc.gov/cgb/policy/DomainNameDownload.html
*/

include( "/home/scripts/includes/cssLogFunctions.php" );
$iScriptId = cssLogStart( "unsubscribeBannedUsers.php" );
include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

// get banned domains
$sBannedDomainQuery = "SELECT domain
				FROM bannedDomains";
$rBannedDomainResult = dbQuery($sBannedDomainQuery);
while ($oBannedDomainRow = dbFetchObject($rBannedDomainResult)) {
	$sBannedDomain = $oBannedDomainRow->domain;

	// check if any user is currently subscribe to any newsletters with banned domain
	$sDomainQuery = "SELECT email, joinListId
				FROM joinEmailActive
				WHERE email LIKE \"%$sBannedDomain\"";
	$rDomainResult = dbQuery($sDomainQuery);
	
	// if user is currently subscribe to any newsletters with banned domain, then do following:
	// Delete all entries from joinEmailInactive with this email id.
	// Insert entry into joinEmailInactive with email id and listId.
	// Insert entry into joinEmailUnsub
	// Delete user from current active mailing list: joinEmailActive
	// Delete the entry from joinEmailPending
	// Delete from nonampere lists
	if ( dbNumRows($rDomainResult) > 0 ) {
		while ($oRow = dbFetchObject($rDomainResult)) {
			$sEmail = $oRow->email;
			$iJoinListId = $oRow->joinListId;

			
			// delete entry from joinEmailInative
			$sInactiveDeleteQuery = "DELETE FROM joinEmailInactive
						 WHERE  email = '$sEmail'";
			$rInactiveDeleteResult = dbQuery($sInactiveDeleteQuery);
			echo dbError();
			
			
			// insert entry into joinEmailInactive
			$sInactiveInsertQuery = "INSERT IGNORE INTO joinEmailInactive(email, joinListId, sourceCode, dateTimeAdded)
						 VALUES('$sEmail', '$iJoinListId', '', now())";
			$rInactiveInsertResult = dbQuery($sInactiveInsertQuery);
			echo dbError();

			
			// insert email id to joinEmailUnsub
			$sUnsubInsertQuery = "INSERT INTO joinEmailUnsub(email, joinListId, sourceCode, remoteIp, dateTimeAdded, isPurge)
				  	  VALUES('$sEmail', '$iJoinListId', '', '', now(), '1')";
			$rUnsubInsertResult = dbQuery($sUnsubInsertQuery);
			echo dbError();

			
			// delete from joinEmailActive
			$sActiveDeleteQuery = "DELETE FROM joinEmailActive
				   	   WHERE  email = '$sEmail'";
			$rActiveDeleteResult = dbQuery($sActiveDeleteQuery);
			echo dbError();


			// delete from pending
			$sPendingDeleteQuery = "DELETE FROM joinEmailPending
						WHERE  email = '$sEmail'";	
			$rPendingDeleteResult = dbQuery($sPendingDeleteQuery);
			echo dbError();


			// remove from nonampere lists
			$sNonAmpereListQuery = "SELECT *
						FROM   joinListsNonAmpere";
			$rNonAmpereListResult = dbQuery($sNonAmpereListQuery);
			echo dbError();
			while ($oNonAmpereListRow = dbFetchObject($rNonAmpereListResult)) {
				$sShortName = $oNonAmpereListRow->shortName;
				$sInsertQuery = "INSERT INTO myfree.mw(email, action, list)
					 VALUES('$sEmail', 'd', '$sShortName')";
				$rInsertResult = dbQuery($sInsertQuery);
				echo dbError();
			}
		}
	}
}

cssLogFinish( $iScriptId );
?>