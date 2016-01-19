<?php

/*********

Script to Display List/Delete Join Lists

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Join Lists - List/Delete Join List";

// Check user permission to access this page
	
if (hasAccessRight($iMenuId) || isAdmin()) {
		
	if ($sDelete) {
		// if record deleted
		
		// check if the join list is used in any ot pages, if so, don't allow to delete it
		$sCheckQuery = "SELECT *
						FROM   otPages
						WHERE  listIdToDisplay = '$iId'";
		$rCheckResult = dbQuery($sCheckQuery);
		if (dbNumRows($rCheckResult) == 0) {
		$sDeleteQuery = "DELETE FROM joinLists
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
		
		} else {
			while ($oCheckRow = dbFetchObject($rCheckResult)) {
				$sPageName = $oCheckRow->pageName;
			}
			$sMessage = "Can't Delete The Join List. Displayed on otPage - $sPageName... ";
		}
		// reset $id
		$iId = '';
	}
	
	// set default order by column
	if (!($sOrderColumn)) {
		$sOrderColumn = "title";
		$sTitleOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($sOrderColumn) {
		
		case "description" :
		$sCurrOrder = $sDescriptionOrder;
		$sDescriptionOrder = ($sDescriptionOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "requiresConf" :
		$sCurrOrder = $sRequiresConfOrder;
		$sRequiresConfOrder = ($sRequiresConfOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "isActive" :
		$sCurrOrder = $sIsActiveOrder;
		$sIsActiveOrder = ($sIsActiveOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "prechecked" :
		$sCurrOrder = $sPrecheckedOrder;
		$sPrecheckedOrder = ($sPrecheckedOrder != "DESC" ? "DESC" : "ASC");
		break;		
		default:
		$sCurrOrder = $sTitleOrder;
		$sTitleOrder = ($sTitleOrder != "DESC" ? "DESC" : "ASC");
		break;
		
	}
		
	// Select Query to display list of payment methods
	
	$sSelectQuery = "SELECT * FROM joinLists
					 ORDER BY $sOrderColumn $sCurrOrder";
	
	$rSelectResult = dbQuery($sSelectQuery);
	
	while ($oRow = dbFetchObject($rSelectResult)) {
		
		// For alternate background color
		if ($sBgcolorClass=="ODD") {
			$sBgcolorClass="EVEN";
		} else {
			$sBgcolorClass="ODD";
		}
		$sJoinListsList .= "<tr class=$sBgcolorClass><TD>$oRow->title</td>
							<td>$oRow->description</td><td>$oRow->requiresConf</td>
							<td>$oRow->isActive</td><td>$oRow->prechecked</td>							
						<TD><a href='JavaScript:void(window.open(\"addList.php?iMenuId=$iMenuId&iId=".$oRow->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					    &nbsp;<a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a>
						</td></tr>";
	}
	
	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Records Exist...";
	}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

	$sAddButton ="<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addList.php?iMenuId=$iMenuId\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
		
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
<tr><td align=left><?php echo $sAddButton;?></td></tr>
<tr><td><a href='<?php echo $sSortLink;?>&sOrderColumn=title&sTitleOrder=<?php echo $sTitleOrder;?>' class=header>Title</a></td>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=description&sDescriptionOrder=<?php echo $sDescriptionOrder;?>' class=header>Description</a></td>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=reqioresConf&sReqioresConfOrder=<?php echo $sRequiresConfOrder;?>' class=header>Reqires Conf.</a></td>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=isActive&sIsActiveOrder=<?php echo $sIsActiveOrder;?>' class=header>Is Active</a></td>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=prechecked&sPrecheckedOrder=<?php echo $sPrecheckedOrder;?>' class=header>Prechecked</a></td>	
</tr>

<?php echo $sJoinListsList;?>
<tr><td align=left><?php echo $sAddButton;?></td></tr>
</table>

</form>
	
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>