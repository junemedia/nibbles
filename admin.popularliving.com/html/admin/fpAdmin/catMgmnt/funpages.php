<?php

/*********

Script to Display Add/Edit HandCraftersVillage Add/Edit Project

*********/

include("../../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Funpages In Category";

	
session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
// get project category name
$categoryQuery = "SELECT title
				  FROM   funPageCategories
				  WHERE  id = '$id'";
$categoryResult = mysql_query($categoryQuery);
echo mysql_error();
while ($categoryRow = mysql_fetch_object($categoryResult)) {
	$pageTitle .= " $categoryRow->title";
}

if ($sSaveClose || $sSaveNew) {
	
	// Change the sort orders
	if(is_array($sortOrder)) {
		while (list($key, $val) = each($sortOrder)) {
			$editQuery = "UPDATE funPageCategoryInt
							  SET    sortOrder = '$val'
							  WHERE  CatId = '$id'
							  AND    pageId = '$key'";
			
			// start of track users' activity in nibbles
			$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")";
			$rResult = dbQuery($sAddQuery);
			echo  dbError();
			// end of track users' activity in nibbles
			
			
			$editResult = mysql_query($editQuery);
		}
	}
	
	
	if ($addPage != '') {
	// If page selected from the selection box to add
		$pageId = $addPage;
	}
	
	// check if page already exists in this category...
	if ($pageId !='') {
	$checkQuery = "SELECT *
					   FROM   funPageCategoryInt
					   WHERE  CatId = '$id'
					   AND    pageId = '$pageId'";
	$checkResult = mysql_query($checkQuery);
	if (mysql_num_rows($checkResult) == 0) {
		if (!($addSortOrder)) {
			$addSortOrder = 0;
		}
		$addQuery = "INSERT INTO funPageCategoryInt(CatId, pageId, sortOrder)
						 VALUES('$id', '$pageId', '$addSortOrder')";
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $addQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		
		$addResult = mysql_query($addQuery);
		echo mysql_error();
	} else {
		$message = "Page Already Exists In This Category....";
	}
	}
	
	if (is_array($remove)) {
		
		while (list($key, $val) = each($remove)) {
			$deleteQuery = "DELETE FROM funPageCategoryInt
								WHERE  CatId = '$id'
								AND    pageId = '$key'";
			
			// start of track users' activity in nibbles
			$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")";
			$rResult = dbQuery($sAddQuery);
			echo  dbError();
			// end of track users' activity in nibbles
			
			
			$deleteResult = mysql_query($deleteQuery);
			$message = '';
		}
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

// Set Default order column
if (!($orderColumn)) {
	$orderColumn = "sortOrder";
	$sortOrderOrder = "ASC";
}
// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
switch ($orderColumn) {
	
	case "sortOrder" :
	$currOrder = $sortOrderOrder;
	$sortOrderOrder = ($sortOrderOrder != "DESC" ? "DESC" : "ASC");
	break;
	
	default:
	$currOrder = $titleOrder;
	$titleOrder = ($titleOrder != "DESC" ? "DESC" : "ASC");
}

// Select Query to display list of data

$selectQuery = "SELECT funPages.title, funPageCategoryInt.sortOrder, funPageCategoryInt.pageId
					FROM   funPageCategories, funPageCategoryInt, funPages
					WHERE  funPageCategories.id = '$id'
					AND    funPageCategories.id = funPageCategoryInt.CatId
					AND    funPageCategoryInt.pageId = funPages.id";
$selectQuery .= " ORDER BY $orderColumn $currOrder";

$selectResult = mysql_query($selectQuery);
echo mysql_error();

while ($row = mysql_fetch_object($selectResult)) {
	
	// For alternate background color
	if ($bgcolorClass == "ODD") {
		$bgcolorClass = "EVEN";
	} else {
		$bgcolorClass = "ODD";
	}
	$dispTitle = ascii_encode(substr($row->title,0,50));	
	$pageList .= "<tr class=$bgcolorClass><TD>$dispTitle...</td>
						
						<TD><input type=text name=sortOrder[".$row->pageId."] value='$row->sortOrder' size=5></td>
						<td><input type=checkbox name=remove[".$row->pageId."]></td>
						</tr>";
}
if (mysql_num_rows($selectResult) == 0) {
	$message = "No Pages In This Category...";
}


$pagesQuery = "SELECT P.*, CM.CatId, P.id pageId
				FROM   funPages P LEFT JOIN funPageCategoryInt CM ON P.id = CM.pageId
				AND    CM.CatId = '$id'
				WHERE  ( CM.pageId IS NULL)
				ORDER BY title";

$pagesResult = mysql_query($pagesQuery);
echo mysql_error();
$addPageOptions = "<option value=''>Select Page To Add";
while ($pagesRow = mysql_fetch_object($pagesResult)) {
	
	$addPageOptions .= "<option value='".$pagesRow->pageId."'>".substr($pagesRow->title,0,25)."...";
}


$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	

$sortLink = $PHP_SELF."?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder&id=$id";

// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>
			<input type=hidden name=id value='$id'>";

	include("$sGblIncludePath/adminAddHeader.php");	
?>

<form action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $reloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr>	
	
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=title&titleOrder=<?php echo $titleOrder;?>" class=header>Title</td>
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=sortOrder&sortOrderOrder=<?php echo $sortOrderOrder;?>" class=header>Sort Order</td>	
	<td class=header>Remove from this Category</td>
</tr>
<?php echo $pageList;?>
<!--<input type=submit name=saveClose value="Save & Close">-->
<tr><td><BR></td></tr>
<tr><td colspan=4 class=header>Select Page To Add To This Category:</td></tr>
<tr><Td  colspan=4><select name=addPage>
<?php echo $addPageOptions;?>
</select>
</td></tr>

	</table>
	
<?php

include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	

?>