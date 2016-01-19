<?php

/*********

Script to Display/Edit Permissions For the selected Nibbles Menu Item

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles - Partner Access Rights";

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($iAccessRightMenuId && $sSubmit) {
		
		$sPartnerContactQuery = "SELECT * from partnerContacts";
		$rPartnerContactResult = dbQuery($sPartnerContactQuery);
		while ($oPartnerContactRow = dbFetchObject($rPartnerContactResult)) {
			$iTempPartnerContactId = $oPartnerContactRow->id;
			// check if record for this userid and menuid exists in permission table
			// if exists then edit it, otherwise insert new record
			
			// permission set for this userId
			$sTempAccessRight = $aAccessRight[$iTempPartnerContactId];			
						
			// get the permissions for this menu item, 
			// Update if exists, insert if not exist..
			$sCheckQuery = "SELECT *
						   FROM   partnerAccessRights
						   WHERE  partnerMenuId = '$iAccessRightMenuId'
						   AND    partnerContactId = '$iTempPartnerContactId'";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult)>0) {
				// update permission
				if ($sTempAccessRight != '') {
				$sUpdateQuery = "UPDATE partnerAccessRights
								SET accessRight = '$sTempAccessRight'								
								WHERE partnerMenuId = $iAccessRightMenuId
								AND   partnerContactId = '$iTempPartnerContactId'";
				$rUpdateResult = dbQuery($sUpdateQuery);

				
				
				// start of track users' activity in nibbles 
				$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $sUpdateQuery\")"; 
				$rLogResult = dbQuery($sLogAddQuery); 
				echo  dbError(); 
				// end of track users' activity in nibbles		
				
				
				} else {
					$sDeleteQuery = "DELETE FROM partnerAccessRights
									 WHERE  partnerMenuId = '$iAccessRightMenuId'
									 AND	partnerContactId = '$iTempPartnerContactId'";
					$rDeleteResult = dbQuery($sDeleteQuery);

					// start of track users' activity in nibbles 
					$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
					$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteQuery\")"; 
					$rLogResult = dbQuery($sLogAddQuery); 
					echo  dbError(); 
					// end of track users' activity in nibbles		
					
					
					echo dbError();
				}
			} else if ($sTempAccessRight != ''){
				// insert new record for permission

				$sInsertQuery = "INSERT INTO partnerAccessRights(partnerMenuId, partnerContactId, accessRight)
								VALUES('$iAccessRightMenuId', '$iTempPartnerContactId', '$sTempAccessRight')";
				$rInsertResult = dbQuery($sInsertQuery);


				// start of track users' activity in nibbles 
				$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $sInsertQuery\")"; 
				$rLogResult = dbQuery($sLogAddQuery); 
				echo  dbError(); 
				// end of track users' activity in nibbles						
				
				echo dbError();
			}		
		}		
	}
	
	if ($iAccessRightMenuId) {
		
		// Get the menuItem to display from it's id...
		$sMenuQuery = "SELECT menuItem
				  FROM   partnerMenu
				  WHERE  id = '$iAccessRightMenuId'";
		$rMenuResult = dbQuery($sMenuQuery);
		while ($oMenuRow = dbFetchObject($rMenuResult)) {
			$sMenuItem = $oMenuRow->menuItem;
		}

		// get users one by one
		$sPartnerContactQuery = "SELECT partnerContacts.id, contact, email, companyName
				  FROM   partnerContacts, partnerCompanies
				  WHERE  partnerCompanies.id = partnerContacts.partnerId
				  AND	 passwd != ''
				  ORDER BY partnerCompanies.companyName, contact";
		$rPartnerContactResult = dbQuery($sPartnerContactQuery);
		echo dbError();
		while ($oPartnerContactRow = dbFetchObject($rPartnerContactResult)) {
			$iPartnerContactId = $oPartnerContactRow->id;
			
			if ($sBgcolorClass == "ODD") {
				$sBgcolorClass = "EVEN";
			} else {
				$sBgcolorClass="ODD";
			}
			
			$sAccessRightsList .="<tr class=$sBgcolorClass><td>$oPartnerContactRow->companyName</td><td>$oPartnerContactRow->contact</td><td>$oPartnerContactRow->email</td>";
			
			$sAccessRightChecked = "";			
			
			// check permission for the users one-by-one
			$sAccessQuery = "SELECT *
					FROM   partnerAccessRights
					WHERE  partnerMenuId = '$iAccessRightMenuId'
					AND    partnerContactId = '$iPartnerContactId'
				  	ORDER BY partnerContactId";
			$rAccessResult = dbQuery($sAccessQuery);
			echo dbError();
			
			while ($oAccessRow = dbFetchObject($rAccessResult)) {
				
				if ($oAccessRow->accessRight == 'Y') {
					$sAccessRightChecked = "checked";
				}
							
			}
			$sAccessRightsList .= "<td><input type=checkbox name=aAccessRight[".$iPartnerContactId ."] value='Y' $sAccessRightChecked></td></tr>";
		}
	}

	// Hidden variable to be passed with form submit	
	$sHidden = "<input type=hidden name=iAccessRightMenuId value=$iAccessRightMenuId>
			<input type=hidden name=iMenuId value='$iMenuId'>";
	
	$sMenuListLink = "index.php?iMenuId=$iMenuId";

	include("../../includes/adminHeader.php");	
	
	?>
	
	<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><Td align=center valign=top colspan=5>Permissions for <b><?php echo $sMenuItem;?></b> |
 <a href='<?php echo $sMenuListLink;?>'>Back to Menu list</a></td></tr>

<tr><Td align=left valign=top  class=header>User</th>
<TD align=left valign=top class=header>Access Right</td>
</tr>

<?php echo $sAccessRightsList;?>
<tr><td colspan=2 align=center><input type=submit name=sSubmit value="Set Access Rights">
&nbsp; &nbsp; <input type=reset name=reset value="Reset"></td></tr>
</table>
</form>

<?php
include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>