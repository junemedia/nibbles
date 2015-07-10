<?php

/*******

Script to Add/Edit Offer Categories for MrFree site

*******/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Nibbles Editorial Offer Category Management - Add/Edit Category";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	

if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	
	$addQuery = "INSERT INTO edOfferCategories(category, title, link, parentCategory, frontPageDisplay, leftMenuDisplay, isNew)
				 VALUES('$category', '$title', '$link', '$parentCategory', '$frontPageDisplay', '$leftMenuDisplay', '$isNew')";
	
	
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add Entry: $addQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
	
	
	
	$result = mysql_query($addQuery);
	if (! $result) {
		$sMessage = mysql_error();
		$keepValues = true;
	}
	
} else if ( ($sSaveClose || $sSaveNew) && ($id)) {
	
	// If record edited
	$editQuery = "UPDATE edOfferCategories
				  SET category = '$category',					 
				  title = '$title',
				  link = '$link',
				  parentCategory = '$parentCategory',
				  frontPageDisplay = '$frontPageDisplay',
				  leftMenuDisplay = '$leftMenuDisplay',
				  isNew = '$isNew'
				  WHERE id = '$id'";
	
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit Entry: $editQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
	
	
	
	$result = mysql_query($editQuery);
	
	if (! $result) {
		$sMessage = mysql_error();
		$keepValues = true;
	}		
}

if ($sSaveClose) {
	if ($keepValues != true) {
	echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";		
	//exit from this script
	exit();			
	}
} else if ($sSaveNew) {
	$reloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";
	// Reset textboxes for new record
	if ($keepValues != true) {
	$category ='';
	$title = '';
	$link = '';
	$parentCategory = '';
	$frontPageDisplay = '';
	$leftMenuDisplay = '';
	$isNew = '';
	}
}

if ($id != '') {
	// If Clicked Edit, display values in fields 
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
						FROM   edOfferCategories
			  			WHERE  id = '$id'";
	$result = mysql_query($selectQuery);	
	if ($result) {
		while ($row = mysql_fetch_object($result)) {
			$category = ascii_encode($row->category);
			$title = ascii_encode($row->title);
			$link = $row->link;
			$parentCategory = $row->parentCategory;
			$frontPageDisplay = $row->frontPageDisplay;
			$leftMenuDisplay = $row->leftMenuDisplay;
			$isNew = $row->isNew;
		}
		mysql_free_result($result);
	} else {
		echo mysql_error();
	}	
} else {
	$category = ascii_encode(stripslashes($row->category));
	$title = ascii_encode(stripslashes($row->title));
	// if Add button is clicked, display another two buttons
		
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=abandonNew value=' Abandon & New  '>";	
}

// Prepare Parent Caregory Options
$parentCategoryOptions = "<option value='0'>";
$parentQuery = "SELECT *
				FROM   edOfferCategories
				WHERE  parentCategory = 0
				AND    id !='$id'
				ORDER BY category";
$parentResult = mysql_query($parentQuery);
while ($parentRow = mysql_fetch_object($parentResult)) {
	if ($parentRow->id == $parentCategory) {
		$catSelected = "selected";
	} else {
		$catSelected = "";
	}	
	$parentCategoryOptions .="<option value='".$parentRow->id."' $catSelected>$parentRow->category";
}

// While editing record, which Display options to be checked
if ($frontPageDisplay == 'Y') {
	$frontPageDisplayChecked = "checked";
}
if ($leftMenuDisplay == 'Y') {
	$leftMenuDisplayChecked = "checked";
}
if ($isNew == 'Y') {
	$isNewChecked = "checked";
}

// Hidden variable to be passed with form submit
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=id value='$id'>";


include("../../includes/adminAddHeader.php");
?>				

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>Category Name</td>
		<td><input type=text name='category' value='<?php echo $category;?>'></td>
	</tr>

	<tr><td>Title</td>
		<td><input type=text name='title' value='<?php echo $title;?>' size=70></td>
	</tr>
	<tr><td>URL</td>
		<td><input type=text name='link' value='<?php echo $link;?>' size=50><BR> (If entered, visitor sent to URL rather than category page)</td>
	</tr>
	<tr>
		<td>Parent Category</td>
		<td><select name=parentCategory>
		<?php echo $parentCategoryOptions;?>
			</select></td>
	</tr>
	<tr><td></td><td><input type=checkbox name=frontPageDisplay value='Y' <?php echo $frontPageDisplayChecked;?>> Front Page Display</td>
	</tr>
	<tr><td></td><td><input type=checkbox name=leftMenuDisplay value='Y' <?php echo $leftMenuDisplayChecked;?>> Left Menu Display</td>
	</tr>
	<tr><td></td><td><input type=checkbox name=isNew value='Y' <?php echo $isNewChecked;?>> Is New</td>
	</tr>
				
</table>


<?php
	include("../../includes/adminAddFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>