<?php

/*********

Script to Display List/Add/Edit/Delete of Menu Items in MARS Main Menu

*********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Partner Menus - List/Delete Nibbles Partner Menu";

// Check if user is permitted to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {

	if ($sDelete) {
		// if user record deleted
		
		$sDeleteQuery = "DELETE FROM partnerMenu
	 			   		WHERE  id = $iId"; 

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sDeleteQuery);
		if ($rResult) {
			$sDeleteQuery2 = "DELETE FROM partnerAccessRights
							  WHERE  partnerMenuId = '$iId'";
			$rDeleteResult2 = dbQuery($sDeleteQuery2);
		} else {
				$sMessage = dbError();
		}
		// reset $id
		$iId = '';
	}	
		
	// Select Query to display list of Menu Items in MARS Main menu
	$sSelectQuery = "SELECT *
				  FROM   partnerMenu
				  ORDER BY category, menuItem";
	
	$rSelectResult = dbQuery($sSelectQuery);
	echo dbError();
	while ($oRow = dbFetchObject($rSelectResult)) {
		
		// For alternate background of the rows
		if ($sBgcolorClass=="ODD") {
			$sBgcolorClass="EVEN";
		} else {
			$sBgcolorClass="ODD";
		}
		
		$sMenuList .= "<tr class=$sBgcolorClass><TD>$oRow->category</td><TD>$oRow->menuItem</td>
				<TD>$oRow->menuLink</td>
				<TD><a href='JavaScript:void(window.open(\"addMenu.php?iMenuId=$iMenuId&iId=".$oRow->id."\", \"AddMenu\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
				&nbsp;<a href='JavaScript:confirmDelete(this,".$oRow->id.");'>Delete</a>
		 		&nbsp;<a href='accessRights.php?iMenuId=$iMenuId&iAccessRightMenuId=$oRow->id'>Access Rights</a></td></tr>";
		
		$sMenuList .= "</td></tr>";
	}
	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Menu Item exist...";
	}
	
	// hidden variables to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
		<input type=hidden name=iId value='$iId'>";

	$sAddButton ="<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addMenu.php?iMenuId=$iMenuId\", \"AddMenu\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";

	include("../../includes/adminHeader.php");	
	
?>
	
<script language=JavaScript>
	function confirmDelete(form1,id)
	{
		if(confirm('Are you sure to delete this record ?'))
		{							
			document.form1.elements['sDelete'].value='Delete';
			document.form1.elements['iId'].value = id;
			document.form1.submit();								
		}
	}						
</script>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<input type=hidden name=sDelete>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><th colspan=7 align=left><?php echo $sAddButton;?></th></tr>

<tr><Td align=left valign=top class=header>Category</th>
<Td align=left valign=top class=header>Menu Item</th>
<Td align=left valign=top class=header>Menu Link</th>
</tr>

<?php echo $sMenuList;?>
<tr><th colspan=7 align=left><?php echo $sAddButton;?></th></tr>
</table>
</form>
	
<?php

include("../../includes/adminFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>