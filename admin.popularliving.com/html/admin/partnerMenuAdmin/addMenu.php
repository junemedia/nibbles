<?php

/*********

Script to Display Add/Edit of Menu Items in Nibbles Main Menu

*********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles - Add/Edit Partner Menu";

if (hasAccessRight($iMenuId) || isAdmin()) {

if (($sSaveClose || $sSaveNew) && !($iId)) {

// if new menu added
	
	$sAddQuery = "INSERT INTO partnerMenu(category, menuItem, menuLink, description, parentMenu, displayMenu)
					 VALUES('$sCategory', '$sMenuItem', '$sMenuLink', '$sDescription', '$iParentMenu','$sDisplayMenu')";		
	$rResult = dbQuery($sAddQuery);

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $sAddQuery\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles		
	
	
	if (!($rResult)) {
		$sMessage = dbError();
	}
	
} else if (($sSaveClose || $sSaveNew) && ($iId)) {
	// if menu info edited
	
	$sEditQuery = "UPDATE partnerMenu SET
					category = '$sCategory',
					menuItem = '$sMenuItem',
					menuLink = '$sMenuLink',
					description = '$sDescription',
					parentMenu = '$iParentMenu',
					displayMenu = '$sDisplayMenu'
	 				WHERE id = '$iId'";	

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $sEditQuery\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles		
	
	
	$rResult = dbQuery($sEditQuery);
	if (!($result)) {
		$sMessage = dbError();
	}
}

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
	
	$sCategory = '';
	$sMenuItem = '';
	$sMenuLink = '';
	$sDescription = '';
	$iParentMenu = '';
	$sDisplayMenu = '';
}

if ($iId) {
	// If Clicked to edit, get the data to display in fields and
	// buttons to edit it...
	$sSelectQuery = "SELECT *
				  	  FROM   partnerMenu 
				  	  WHERE  id = $iId";
	$rSelectResult = dbQuery($sSelectQuery);
	echo dbError();
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$sCategory = $oSelectRow->category;
		$sMenuItem = $oSelectRow->menuItem;
		$sMenuLink = $oSelectRow->menuLink;
		$sDescription = $oSelectRow->description;
		$iParentMenu = $oSelectRow->parentMenu;
		$sDisplayMenu = $oSelectRow->displayMenu;
	}	
} else {
	// If add button is clicked, display another two buttons
	$sDisplayMenu = "Y";
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

// prepare parent menu options
$sParentMenuQuery = "SELECT *
					FROM   partnerMenu
					WHERE  parentMenu = '0'
					ORDER BY menuItem";
$rParentMenuResult = dbQuery($sParentMenuQuery);
$sParentMenuOptions = "<option value='0'>";
while ($oParentMenuRow = dbFetchObject($rParentMenuResult)) {
	$iPaId = $oParentMenuRow->id;
	$sPaMenuItem = $oParentMenuRow->menuItem;
	if ($iPaId == $iParentMenu) {
		$sParentMenuSelected = "selected";
	} else {
		$sParentMenuSelected = "";
	}
	$sParentMenuOptions .= "<option value='$iPaId' $sParentMenuSelected>$sPaMenuItem";
}
 

$sDisplayMenuChecked = '';
if ($sDisplayMenu == 'Y') {
	$sDisplayMenuChecked = "checked";
}

// hidden variables to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iId value='$iId'>";

include("../../includes/adminAddHeader.php");	
?>
	
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><Td>Category</td><td><input type=text name=sCategory value='<?php echo $sCategory;?>' size=30></td></tr>
		<tr><Td>Menu Item</td><td><input type=text name=sMenuItem value='<?php echo $sMenuItem;?>' size=30></td></tr>
	<tr><Td>Menu Link</td><td><input type=text name=sMenuLink value='<?php echo $sMenuLink;?>' size=30></td></tr>
	<tr><Td>Description</td><td><textarea name=sDescription rows=5 cols=35><?php echo $sDescription;?></textarea></td></tr>
	<tr><Td>Parent Menu</td><td><select name=iParentMenu><?php echo $sParentMenuOptions;?>
		</select></td></tr>	
	<tr><Td>Display Under Nibbles/Parent Menu</td><td><input type=checkbox name=sDisplayMenu value='Y' <?php echo $sDisplayMenuChecked;?>></td></tr>	
	</table>
<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>