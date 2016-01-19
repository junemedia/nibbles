<?php

/*********

Script to Display Add/Edit Nibbles Users

**********/

include("../../includes/paths.php");

session_start();

$pageTitle = "Nibbles Users - Add/Edit User";


if (hasAccessRight($iMenuId) || isAdmin()) {
	
if ($sSaveClose || $sSaveNew) {
	
	
	// Prepare comma-separated Menu if record added or edited
	
	$sMenuQuery = "SELECT id, menuItem
					FROM   menu
					ORDER BY menuItem";
	
	$rMenuResult = dbQuery($sMenuQuery);
	$i = 0;
	while ($oMenuRow = dbFetchObject($rMenuResult)) {

		// prepare Categories of this offer
		$sCheckboxName = "menu_".$oMenuRow->id;

		$iCheckboxValue = $$sCheckboxName;

		if ($iCheckboxValue != '') {
			$aMenuArray[$i] = $iCheckboxValue;
			$sMenuString .= $iCheckboxValue.",";
			$i++;
		}
	}


	if (!($iId)) {
		// if new user added
		
		
		$sAddQuery = "INSERT INTO nbUsers(userName, firstName, lastName, email)
				 VALUES('$sUserName', '$sFirstName', '$sLastName', '$sEmail')";		

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $sAddQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sAddQuery);
		if (!($rResult))
		$sMessage = dbError();
		
	} else if ($iId) {
		
		$sEditQuery = "UPDATE nbUsers
				  	   SET 	  userName = '$sUserName',
					  		  firstName = '$sFirstName',				
					  		  lastName = '$sLastName',					
					  		  email = '$sEmail' ";
		
		$sEditQuery .= " WHERE id = '$iId'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $sEditQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sEditQuery);
		if (!($rResult)) {
			$sMessage = dbError();
		}
	}
	
	
	
	// Delete records from access Rights with the Menu which are not checked
					
					// remove last comma from the menu list
					
					$sMenuString = substr($sMenuString, 0, strlen($sMenuString)-1);
					// Delete if any page unchecked for the offer to be displayed in.
					$sDeleteQuery = "DELETE FROM accessRights
									 WHERE  userId = '$iId'";
					if ($sMenuString != '') {
						$sDeleteQuery .= " AND menuId NOT IN (".$sMenuString.")";
					}

					$rDeleteResult = dbQuery($sDeleteQuery);
					
					//$sDelete = "DELETE FROM accessRights WHERE userId = '$iId' and menuId = '216'";
					//$rDelete = dbQuery($sDelete);

					if (count($aMenuArray) > 0) {
						for ($i = 0; $i<count($aMenuArray); $i++) {
							$sCheckQuery = "SELECT *
							   FROM   accessRights
							   WHERE  menuId = ".$aMenuArray[$i]."
							   AND    userId = '$iId'";
							$rCheckResult = dbQuery($sCheckQuery);
							
							if (dbNumRows($rCheckResult) == 0) {
								// INSERT OfferCategoryRel record
								
								$sInsertQuery = "INSERT INTO accessRights (menuId, userId, accessRight)
												VALUES('".$aMenuArray[$i]."', '$iId', 'Y')";
								$rInsertResult = dbQuery($sInsertQuery);
								echo dbError();
							} else {
								
								$sUpdateQuery = "UPDATE accessRights 
												 SET	accessRight = 'Y'
												 WHERE  menuId = '".$aMenuArray[$i]."'
												 AND	userId = '$iId'";
								$rUpdateResult = dbQuery($sUpdateQuery);
								//echo "<BR>".$sUpdateQuery;
								echo dbError();
							}
						}
					}
					
					echo dbError();
	
	if ($sSaveClose) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";			
		// exit from this script
		exit();
	} else if ($sSaveNew) {
		$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";	
		$sUserName = '';
		$sFirstName = '';
		$sLastName = '';
		$sEmail = '';
		
	}
}




if ($iId) {
	
	// If Clicked to edit, get the data to display in fields
	
	$sSelectQuery = "SELECT * FROM nbUsers
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$sUserName = $oSelectRow->userName;
		$sFirstName = $oSelectRow->firstName;
		$sLastName = $oSelectRow->lastName;
		$sEmail = $oSelectRow->email;
		
		
	}
} else {
	
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}


if (isAdmin()) {


	
// Prepare checkboxes for Menus
$sMenuQuery = "SELECT *
			    FROM  menu
				ORDER BY category, menuItem";
$rMenuResult = dbQuery($sMenuQuery);
$j = 0;
$sMenuCheckboxes = "<tr>";
while ($oMenuRow = dbFetchObject($rMenuResult)) {
	$iMenuId = $oMenuRow->id;
	$sMenuItem = $oMenuRow->menuItem;
	$sCategory = $oMenuRow->category;
	
	$sAccessRightsQuery = "SELECT accessRight
				   FROM  accessRights
				   WHERE  accessRights.menuId = '$iMenuId'
				   AND    accessRights.userId = '$iId'";
	
	$rAccessRightsResult = dbQuery($sAccessRightsQuery);
	
	if (dbNumRows($rAccessRightsResult) > 0) {
		$sMenuChecked  = "checked";
	} else {
		$sMenuChecked = "";
	}
	
	if ($j%3 == 0) {
		if ($j != 0) {
			$sMenuCheckboxes .= "</tr>";
		}
		$sMenuCheckboxes .= "<tr>";
	}
		
	if ($sCategory != $sOldCategory || $sOldCategory == '') {
		$sMenuCheckboxes .= "</tr><tr><td></td><td colspan=5><b>$sCategory</b></td></tr><tr>";
		$j=0;
	}
	
	if ($j%3 == 0) {
		if ($j != 0) {
			$sMenuCheckboxes .= "</tr>";
		}
		$sMenuCheckboxes .= "<tr>";
	}


	if ($sMenuItem == 'Campaigns Management' || $sMenuItem == 'Links Management') {
			$sUnRestrictedQuery = "SELECT accessRight
				   FROM  accessRights
				   WHERE  menuId = '216'
				   AND    userId = '$iId'";
			
			$rUnRestrictedResult = dbQuery($sUnRestrictedQuery);
			
			if (dbNumRows($rUnRestrictedResult) > 0) {
				$sUnRestrictedChecked  = "checked";
			} else {
				$sUnRestrictedChecked = "";
			}
	
		$sMenuItem = "<a href='JavaScript:void(window.open(\"partners.php?iMenuId=$iMenuId&iId=".$iId."\", \"SelectPartners\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>$sMenuItem</a>
					<input type=checkbox name='menu_216' value='216' $sUnRestrictedChecked> &nbsp;Unrestricted";
	}
	
	if ($sMenuItem != 'Unrestricted') {
		$sMenuCheckboxes .= "<td width=5% valign=top><input type=checkbox name='menu_".$oMenuRow->id."' value='".$oMenuRow->id."' $sMenuChecked></td><td  width=28%>$sMenuItem</td>";
	}
	$j++;
	$sOldCategory = $sCategory;
}
$sMenuCheckboxes .= "</tr>";
$sCheckAllLink = "<tr><td colspan=6><a href = 'JavaScript:checkAll();'>Check All</a> &nbsp; &nbsp; &nbsp; &nbsp; <a href = 'JavaScript:uncheckAll();'>Uncheck All</a></td></tr>";

$sCheckAllJavaScript = "
			<script language=JavaScript>
			function checkAll() {
				
			for(i = 0; i < document.forms[0].elements.length; i++) {

    	        elm = document.forms[0].elements[i];
	
        	    if (elm.type == 'checkbox') {            	   
                    	elm.checked = true;            	   
            	}
					
            }
			}

		function uncheckAll() {
				
			for(i = 0; i < document.forms[0].elements.length; i++) {

    	        elm = document.forms[0].elements[i];
	
        	    if (elm.type == 'checkbox') {            	   
                    	elm.checked = false;            	   
            	}
					
            }
			}
				</script>
				";
}

// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

include("../../includes/adminAddHeader.php");
?>
<?php echo $sCheckAllJavaScript;?>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><TD>User Name</td><td><input type=text name=sUserName value='<?php echo $sUserName;?>'></td></tr>
		<tr><TD>First name</td><td><input type=text name=sFirstName value='<?php echo $sFirstName;?>'></td></tr>
		<tr><TD>Last name</td><td><input type=text name=sLastName value='<?php echo $sLastName;?>'></td></tr>
			
	<tr><Td>eMail</td><td><input type=text name=sEmail value='<?php echo $sEmail;?>'></td></tr>			
	
	</table>	
	<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>	
	<?php echo $sCheckAllLink;?>
	<?php echo $sMenuCheckboxes;?>
		
</table>	
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>