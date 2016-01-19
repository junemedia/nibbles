<?php

/*********

Script to Display Add/Edit HandCraftersVillage Add/Edit Project

*********/

include("../../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Handcrafters Village Category Management - Add/Edit Category";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	// SELECT HCV DATABASE
	dbSelect($sGblHcvDBName);	
	
	
// get project category name
$categoryQuery = "SELECT title
				  FROM   categories
				  WHERE  id = '$id'";
$categoryResult = dbQuery($categoryQuery);
echo dbError();
while ($categoryRow = dbFetchObject($categoryResult)) {
	$sPageTitle .= " $categoryRow->title";
}

if ($sSaveClose || $sSaveNew) {
	// Change the sort orders
	if(is_array($sortOrder)) {
		while (list($key, $val) = each($sortOrder)) {
			$editQuery = "UPDATE categoryMap
							  SET    sortOrder = '$val'
							  WHERE  categoryId = '$id'
							  AND    projectId = '$key'";
			
			// start of track users' activity in nibbles
			$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")";
			$rResult = dbQuery($sAddQuery);
			echo  dbError();
			// end of track users' activity in nibbles
			
			
			$editResult = dbQuery($editQuery);
		}
	}
	// add new offers	
	
	/*//If offer added by typing offercode
	if (trim($offerCode) != '') {
		//get offerId for this offerCode...
		$tempQuery = "SELECT id
					  FROM   craftProjects
					  WHERE  id = '$offerCode'";
		$tempResult = mysql_query($tempQuery) ;
		while ($tempRow = mysql_fetch_object($tempResult)) {
			$offerId = $tempRow->id;
		}
	} else 	if */
	if ($addProject != '') {
	// If offer selected from the selection box to add	
		$projectId = $addProject;
	}
	
	// check if offer already exists...
	if ($projectId !='') {
	$checkQuery = "SELECT *
					   FROM   categoryMap
					   WHERE  categoryId = '$id'
					   AND    projectId = '$projectId'";
	$checkResult = dbQuery($checkQuery);
	if (dbNumRows($checkResult) == 0) {
		if (!($addSortOrder)) {
			$addSortOrder = 0;
		}
		$addQuery = "INSERT INTO categoryMap(categoryId, projectId, sortOrder)
						 VALUES('$id', '$projectId', '$addSortOrder')";
		$addResult = dbQuery($addQuery);
	} else {
		$sMessage = "Project Already Exists In This Category....";
	}
	}
	
	if (is_array($remove)) {
		
		while (list($key, $val) = each($remove)) {
			$deleteQuery = "DELETE FROM categoryMap
								WHERE  categoryId = '$id'
								AND    projectId = '$key'";
			$deleteResult = dbQuery($deleteQuery);
			$sMessage = '';
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

$selectQuery = "SELECT craftProjects.title, categoryMap.sortOrder, categoryMap.projectId
					FROM   categories, categoryMap, craftProjects
					WHERE  categories.id = '$id'
					AND    categories.id = categoryMap.categoryId
					AND    categoryMap.projectId = craftProjects.id";
$selectQuery .= " ORDER BY $orderColumn $currOrder";

$selectResult = dbQuery($selectQuery);
echo dbError();

while ($row = dbFetchObject($selectResult)) {
	
	// For alternate background color
	if ($bgcolorClass == "ODD") {
		$bgcolorClass = "EVEN";
	} else {
		$bgcolorClass = "ODD";
	}
	$dispTitle = ascii_encode(substr($row->title,0,50));	
	$projectList .= "<tr class=$bgcolorClass><TD>$dispTitle...</td>
						
						<TD><input type=text name=sortOrder[".$row->projectId."] value='$row->sortOrder' size=5></td>
						<td><input type=checkbox name=remove[".$row->projectId."]></td>
						</tr>";
}
if (dbNumRows($selectResult) == 0) {
	$sMessage = "No Projects In This Category...";
}


$projectsQuery = "SELECT P.*, CM.categoryId, P.id projectId
				FROM   craftProjects P LEFT JOIN categoryMap CM ON P.id = CM.projectId
				AND    CM.categoryId = '$id'
				WHERE  ( CM.projectId IS NULL)
				ORDER BY title";

$projectsResult = dbQuery($projectsQuery);
echo dbError();
$addProjectOptions = "<option value=''>Select Project To Add";
while ($projectsRow = dbFetchObject($projectsResult)) {
	
	$addProjectOptions .= "<option value='".$projectsRow->projectId."'>".substr($projectsRow->title,0,25)."...";
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


<?php echo $reloadWindowOpener;?>
</form>
<form action='<?php echo $PHP_SELF;?>' method=post enctype="multipart/form-data">
<?php echo $hidden;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr>	
	
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=title&titleOrder=<?php echo $titleOrder;?>" class=header>Title</td>
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=sortOrder&sortOrderOrder=<?php echo $sortOrderOrder;?>" class=header>Sort Order</td>	
	<td class=header>Remove from this Category</td>
</tr>
<?php echo $projectList;?>
<!--<input type=submit name=saveClose value="Save & Close">-->
<tr><td><BR></td></tr>
<tr><td colspan=4 class=header>Select Project To Add To This Category:</td></tr>
<tr><Td  colspan=4><select name=addProject>
<?php echo $addProjectOptions;?>
</select>
</td></tr>

	
<?php
// include footer

	include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}				
?>	