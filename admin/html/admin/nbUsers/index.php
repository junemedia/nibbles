<?php

/*********

Script to Display List/Delete Nibbles Users

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Users - List/Delete Users";

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	if ($sDelete) {
		// if user record deleted
		
		$sDeleteQuery = "DELETE FROM nbUsers
	 			   		WHERE  id = $iId"; 

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sDeleteQuery);
		if (!($rResult)) {
			$sMessage = dbError();
		}
		// reset $id
		$iId = '';
	}
	
	// set default order by column
	if (!($sOrderColumn)) {
		$sOrderColumn = "firstName";
		$sFirstNameOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($sOrderColumn) {
		
		case "lastName" :
		$sCurrOrder = $sLastNameOrder;
		$sLastNameOrder = ($sLastNameOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "extension" :
		$sCurrOrder = $sExtensionOrder;
		$sExtensionOrder = ($sExtensionOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "address1" :
		$sCurrOrder = $sAddressOrder;
		$sAddressOrder = ($sAddressOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "homePhoneNo" :
		$sCurrOrder = $sHomePhoneNoOrder;
		$sHomePhoneNoOrder = ($sHomePhoneNoOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "cellPhoneNo" :
		$currOrder = $sCellPhoneNoOrder;
		$sCellPhoneNoOrder = ($sCellPhoneNoOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "email" :
		$sCurrOrder = $sEmailOrder;
		$sEmailOrder = ($sEmailOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "level" :
		$sCurrOrder = $sLevelOrder;
		$sLevelOrder = ($sLevelOrder != "DESC" ? "DESC" : "ASC");
		break;
		
		default:
		$sCurrOrder = $sFirstNameOrder;
		$sFirstNameOrder = ($sFirstNameOrder != "DESC" ? "DESC" : "ASC");
	}
	
	// Select Query to display list of Users
	
	$sSelectQuery = "SELECT * FROM nbUsers
					ORDER BY $sOrderColumn $sCurrOrder";
	
	$rSelectResult = dbQuery($sSelectQuery);
	
	while ($oRow = dbFetchObject($rSelectResult)) {
		
		// For alternate background color
		if ($sBgcolorClass=="ODD") {
			$sBgcolorClass="EVEN";
		} else {
			$sBgcolorClass="ODD";
		}
		$sUserList .= "<tr class=$sBgcolorClass><TD>$oRow->firstName</td><TD>$oRow->lastName</td>
						<TD>$oRow->email</td><TD>";
		if ($_SESSION["REMOTE_USER"] == $oRow->userName || isAdmin()) {
			$sUserList .= "<a href='JavaScript:void(window.open(\"addUser.php?iMenuId=$iMenuId&iId=".$oRow->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>&nbsp;";
		}
		if (isAdmin()) {
			$sUserList .= "<a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a>";
		}
		$sUserList .= "</td></tr>";
	
	}
	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Users Exist...";
	}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

	$sAddButton ="<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addUser.php?iMenuId=$iMenuId\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId";
	
	include("../../includes/adminHeader.php");	
	
	?>
	
<script language=JavaScript>
	function confirmDelete(form1,id)
	{
		if(confirm('Are you sure to delete this record ?'))
		{							
			document.form1.elements['sDelete'].value='Delete';
			document.form1.elements['iId'].value=id;
			document.form1.submit();								
		}
	}						
</script>

<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<input type=hidden name=sDelete>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=7 align=left><?php echo $sAddButton;?></td></tr>
<tr><td align=left valign=top><a href='<?php echo $sSortLink;?>&sOrderColumn=firstName&sFirstNameOrder=<?php echo $sFirstNameOrder;?>' class=header>First Name</a></td>
<td align=left valign=top><a href='<?php echo $sSortLink;?>&sOrderColumn=lastName&sLastNameOrder=<?php echo $sLastNameOrder;?>' class=header>Last Name</a></td>
<td align=left valign=top><a href='<?php echo $sSortLink;?>&sOrderColumn=email&sEmailOrder=<?php echo $sEmailOrder;?>' class=header>eMail</a></td>
<?php echo $sLevelHeading;?>
</tr>

<?php echo $sUserList;?>
<tr><td colspan=7 align=left><?php echo $sAddButton;?></td></tr>
</table>

</form>
	
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>