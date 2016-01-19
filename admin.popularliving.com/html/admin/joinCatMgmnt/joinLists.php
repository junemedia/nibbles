<?php

/*********

Script to Display Add/Edit Category

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Nibbles Sort Join Lists In Category ";

if (hasAccessRight($iMenuId) || isAdmin()) {

// get category name
$sCategoryQuery = "SELECT title
				  FROM   joinCategories
				  WHERE  id = '$iId'";
$rCategoryResult = dbQuery($sCategoryQuery);
echo dbError();
while ($oCategoryRow = dbFetchObject($rCategoryResult)) {
	$sPageTitle .= " $oCategoryRow->title";
}

if ($sSaveClose || $sSaveNew) {

	// Change the sort orders
	if(is_array($aSortOrder)) {
		while (list($key, $val) = each($aSortOrder)) {
			$sEditQuery = "UPDATE joinListCategories
							  SET    sortOrder = '$val'
							  WHERE  categoryId = '$iId'
							  AND    joinListId = '$key'";
			$rEditResult = dbQuery($sEditQuery);

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Update joinListCategories: $sEditQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
		
		
		}
	}
	
	// If New Join List added to this category
	
	//echo $iJoinListId;
	// check if join list already exists...
	if ($iJoinListId !='') {

		$sCheckQuery = "SELECT *
				    FROM   joinListCategories
					WHERE  categoryId = '$iId'
					AND    joinListId = '$iJoinListId'";
		$rCheckResult = dbQuery($sCheckQuery);
		if (dbNumRows($rCheckResult) == 0) {
			if (!($iAddSortOrder)) {
				$iAddSortOrder = 0;
			}
			
			$sAddQuery = "INSERT INTO joinListCategories(categoryId, joinListId, sortOrder)
						 VALUES('$iId', '$iJoinListId', '$iAddSortOrder')";

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Insert joinListCategories: $sAddQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$rAddResult = dbQuery($sAddQuery);
		} else {
			$sMessage = "Offer Already Exists In This Category....";
		}
	}
	
	if (is_array($aRemove)) {
		
		while (list($key, $val) = each($aRemove)) {
			$sDeleteQuery = "DELETE FROM joinListCategories
								WHERE  categoryId = '$iId'
								AND    joinListId = '$key'";
			$rDeleteResult = dbQuery($sDeleteQuery);

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$sMessage = '';
		}
	}
	
if ($sSaveClose) {
	echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";			
	// exit from this script
	exit();
}

}



// Set Default order column
if (!($sOrderColumn)) {
	$sOrderColumn = "sortOrder";
	$sSortOrderOrder = "ASC";
}
// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
switch ($sOrderColumn) {
	
	case "title" :
	$sCurrOrder = $sTitleOrder;
	$sTitleOrder = ($sTitleOrder != "DESC" ? "DESC" : "ASC");	
	break;
	case "description" :
	$sCurrOrder = $sDescriptionOrder;
	$sDescriptionOrder = ($sDescriptionOrder != "DESC" ? "DESC" : "ASC");
	break;
	default:
	$sCurrOrder = $sSortOrderOrder;
	$sSortOrderOrder = ($sSortOrderOrder != "DESC" ? "DESC" : "ASC");
}

// Select Query to display list of data

$sSelectQuery = "SELECT joinLists.title, joinListCategories.joinListId, joinListCategories.sortOrder
					FROM   joinCategories, joinListCategories, joinLists
					WHERE  joinCategories.id = '$iId'
					AND    joinCategories.id = joinListCategories.categoryId
					AND    joinListCategories.joinListId = joinLists.id ";
$sSelectQuery .= " ORDER BY $sOrderColumn $sCurrOrder";

$rSelectResult = dbQuery($sSelectQuery);

while ($oRow = dbFetchObject($rSelectResult)) {
	
	// For alternate background color
	if ($sBgcolorClass == "ODD") {
		$sBgcolorClass = "EVEN";
	} else {
		$sBgcolorClass = "ODD";
	}
	$sTitle = ascii_encode(substr($oRow->title,0,50));
	$sJoinListList .= "<tr class=$sBgcolorClass><TD>$sTitle</td>
						<TD><input type=text name=aSortOrder[".$oRow->joinListId."] value='$oRow->sortOrder' size=5></td>
						<td><input type=checkbox name=aRemove[".$oRow->joinListId."]></td>
						</tr>";
}
if (dbNumRows($rSelectResult) == 0) {
	$sMessage = "No Join Lists In This Category...";
}


$sJoinListQuery = "SELECT L.*, LC.categoryId
				FROM   joinLists L LEFT JOIN joinListCategories LC ON L.id = LC.joinListId
				AND    LC.categoryId = '$iId'
				WHERE  ( LC.joinListId IS NULL)
				ORDER BY L.title";

$rJoinListResult = dbQuery($sJoinListQuery);
//echo $sJoinListQuery.mysql_error().mysql_num_rows($rJoinListResult);
$sAddJoinListOptions = "<option value=''>Select Join List To Add";
while ($oJoinListRow = dbFetchObject($rJoinListResult)) {
	
	$sAddJoinListOptions .= "<option value='".$oJoinListRow->id."'>$oJoinListRow->title";
}



$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iId=$iId";

$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	


// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

include("../../includes/adminAddHeader.php");
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr>	
	<TD class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=title&sTitleOrder=<?php echo $sTitleOrder;?>" class=header>Join List</a></td>
	<TD class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=sortOrder&sSortOrderOrder=<?php echo $sSortOrderOrder;?>" class=header>Sort Order</td>	
	<td class=header>Remove from this Category</td>
</tr>
<?php echo $sJoinListList;?>
<!--<input type=submit name=saveClose value="Save & Close">-->
<tr><td><BR></td></tr>
<tr><td colspan=4 class=header>Select Join List To Add To This Category:</td></tr>
<tr><Td  colspan=4><select name=iJoinListId>
<?php echo $sAddJoinListOptions;?>
</select> &nbsp; &nbsp; Sort Order: <input type=text name=iAddSortOrder value='' size=5></td></tr>
</table>

	
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>