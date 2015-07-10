<?php

/*********
Script to Display List/Add/Edit/Delete Affiliate Management Company information
*********/

include("../../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "FunPages Management - Add/Edit Fun Page";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

$imagePath = "$sGblFpWebRoot/images";
$soundPath = "$sGblFpWebRoot/sounds";
$imageUrl = "$sGblFpSiteRoot/images";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
		

if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	//Check For Dupe
	$checkQuery = "SELECT *
				   FROM   funPages
				   WHERE  title = \"$title\"";
	$checkResult = mysql_query($checkQuery);
	if (mysql_num_rows($checkResult) > 0 ) {
		$message = "Fun Page exists.";
		$keepValues = true;
	} else {
		
		$addQuery = "INSERT INTO funPages(title, featured, funpageurl, dateAdded)
						VALUES(\"$title\", \"$featured\", \"$funpageurl\", CURRENT_DATE )";
		
		
		// start of track users' activity in nibbles
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $addQuery\")";
		$rResult = dbQuery($sAddQuery);
		// end of track users' activity in nibbles
		
		
		$result = mysql_query($addQuery);
		if (! $result) {
			echo mysql_error();
		} else {
			
			$sCheckQuery = "SELECT id
			   FROM   funPages
			   WHERE  title = \"$title\"
			   AND featured = \"$featured\"
			   AND funpageurl = \"$funpageurl\"
			   AND dateAdded = 'CURRENT_DATE'";
			$rCheckResult = dbQuery($sCheckQuery);
			$sRow = dbFetchObject($rCheckResult);

			$id = $sRow->id;
		}
	}
	
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
	//if record edited
	
	//Check For Dupe
	$checkQuery = "SELECT *
				   FROM   funPages
				   WHERE  title = \"$title\"
					AND   id != '$id'";
	$checkResult = mysql_query($checkQuery);
	if (mysql_num_rows($checkResult) > 0 ) {
		$message = "Fun Page exists.";
		$keepValues = true;
	} else {
		
		$editQuery = "UPDATE funPages
					SET    title = \"$title\",
						   funpageurl = \"$funpageurl\",
						   featured = \"$featured\" 
				 	WHERE  id = '$id'";
		
		// start of track users' activity in nibbles
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")";
		$rResult = dbQuery($sAddQuery);
		// end of track users' activity in nibbles
		
		
		$result = mysql_query($editQuery);
		echo $editQuery.mysql_error();
	}
}
if (($sSaveClose || $sSaveNew) && $id) {
	if ($_FILES['funImage']['tmp_name'] && $_FILES['funImage']['tmp_name']!="none") {
		$upSource = $_FILES['funImage']['tmp_name'];
		$ar = explode(".",$_FILES['funImage']['name']);
		$i = count($ar) - 1;
		$fileExt = $ar[$i];
		$imageFilePath = $imagePath."/funImage_".$id.".$fileExt";
		$imageFileUrl = "funImage_".$id.".$fileExt";
		move_uploaded_file( $upSource, $imageFilePath);
	}
	
	if ($_FILES['funSound']['tmp_name'] && $_FILES['funSound']['tmp_name']!="none") {
		$upSource = $_FILES['funSound']['tmp_name'];
		$ar = explode(".",$_FILES['funSound']['name']);
		$i = count($ar) - 1;
		$fileExt = $ar[$i];
		$soundFilePath = $soundPath."/funSound_".$id.".$fileExt";
		$soundFileUrl = "funSound_".$id.".$fileExt";
		move_uploaded_file( $upSource, $soundFilePath);
	}
	
	$updateQuery = "UPDATE funPages
					SET    funpageurl = \"$funpageurl\"";
	if ($imageFileUrl != '') {
		$updateQuery .= ", image = \"$imageFileUrl\"";
	}
	if($soundFileUrl != '') {
		$updateQuery .= ", sound = \"$soundFileUrl\"";
	}
	
	$updateQuery .= " WHERE  id = '$id'";
	$updateResult = mysql_query($updateQuery);

	
	//save categories in which this fun page should be displayed
	$categoryQuery = "SELECT id, title
			 		  FROM   funPageCategories
				 	  ORDER BY title";
	$categoryResult = mysql_query($categoryQuery);
	$i = 0;
	while ($categoryRow = mysql_fetch_object($categoryResult)) {
		
		// prepare Categories of this offer
		$checkboxName = "category_".$categoryRow->id;
		
		$checkboxValue = $$checkboxName;
		
		if ($checkboxValue != '') {
			$categoriesArray[$i] = $checkboxValue;
			$categoriesString .= $checkboxValue.",";
			$i++;
		}
	}

	$categoriesString = substr($categoriesString, 0, strlen($categoriesString)-1);
		// Delete if any category unchecked for the offer to be displayed in.
		$deleteQuery = "DELETE FROM funPageCategoryInt
						WHERE  pageId = '$id'";
		if ($categoriesString != '') {
			$deleteQuery .= " AND    CatId NOT IN (".$categoriesString.")";
		}
		
		$deleteResult = mysql_query($deleteQuery);
					
		if (count($categoriesArray) > 0) {
		for ($i = 0; $i<count($categoriesArray); $i++) {
			$checkQuery = "SELECT *
				   FROM   funPageCategoryInt
				   WHERE  CatId = ".$categoriesArray[$i]."
				   AND    pageId = '$id'";
			$checkResult = mysql_query($checkQuery);
			if (mysql_num_rows($checkResult) == 0) {
				// INSERT OfferCategoryRel record
				
				$insertQuery = "INSERT INTO funPageCategoryInt (CatId, pageId, sortOrder)
					VALUES('".$categoriesArray[$i]."', '$id', '0')";
				$insertResult = mysql_query($insertQuery);
			}
		}
	}	
}


if ($sSaveClose) {
	if ($keepValues !=true) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";					
		// exit from this script
		exit();
	}
} else if ($sSaveNew) {
	$reloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";
	// Reset textboxes for new record
	if ($keepValues != true) {
		$title = '';
		$funImage = '';
		$funSound = '';
		$featured = '';
		$funpageurl = '';
	}
}

if ($id != '') {
	// If Clicked on Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   funPages
					WHERE  id = '$id'";
	$result = mysql_query($selectQuery);
	
	if ($result) {
		
		while ($row = mysql_fetch_object($result)) {
			$title = ascii_encode($row->title);
			$funImage = $row->image;
			$currentFpImage = "<img src='$imageUrl/$row->image'>";
			$funSound = $row->sound;
			$featured = $row->featured;
			$funpageurl = $row->funpageurl;
		}
		
		mysql_free_result($result);
		
	} else {
		
		echo mysql_error();
	}
}  else {
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

// display funpage categories to choose
$categoryQuery = "SELECT *
			 FROM   funPageCategories
		  	 ORDER BY title";
$categoryResult = mysql_query($categoryQuery);
$j = 0;
while ($categoryRow = mysql_fetch_object($categoryResult)) {
	
	$categoryId = $categoryRow->id;
	$funPageQuery = "SELECT pageId
				   FROM   funPageCategories, funPageCategoryInt
				   WHERE  funPageCategoryInt.CatId = '$categoryId'
				   AND    funPageCategoryInt.pageId = '$id'
				   AND   funPageCategoryInt.CatId = funPageCategories.id";
	
	$funPageResult = mysql_query($funPageQuery);
	echo mysql_error();
	if(mysql_num_rows($funPageResult)>0){
		$categoryChecked  = "checked";
	} else {
		$categoryChecked = "";
	}
		
	if($j%3 == 0) {
		if($j != 0) {
			$categoryCheckboxes .= "</tr>";
		}
		$categoryCheckboxes .= "<tr>";
	}
	
	$categoryCheckboxes .= "<td width=5% valign=top><input type=checkbox name='category_".$categoryRow->id."' value='".$categoryRow->id."' $categoryChecked></td><td  width=28%>$categoryRow->title</td>";
	$j++;
}
$categoryCheckboxes .= "</tr>";

if ($featured) {
	$featuredChecked = "checked";
}
// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>		
			<input type=hidden name=id value='$id'>";

	include("$sGblIncludePath/adminAddHeader.php");	

?>

</form>
<form action='<?php echo $PHP_SELF;?>' method=post enctype="multipart/form-data">
<?php echo $hidden;?>
<?php echo $reloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>

	<tr><td>Fun Page Title</td>
		<td><input type=text name='title' value="<?php echo $title;?>" size=40></td>
	</tr>	
	<tr><td valign=top>Current Image</td>
		<td><?php echo $currentFpImage;?></td>
	</tr>
	<tr><td>Image File</td>
		<td><input type=file name=funImage></td>
	</tr>
	<tr><td valign=top>Current Sound File</td>
		<td><?php echo $currentFpSound;?></td>
	</tr>
	<tr><td>Sound File</td>
		<td><input type=file name=funSound></td>
	</tr>
	<tr><td>FunPage URL</td>
		<td><input type=text name='funpageurl' value="<?php echo $funpageurl;?>"></td>
	</tr>
	<tr><td>Featured</td>
		<td><input type=checkbox name='featured' value='1' <?php echo $featuredChecked;?>></td>
	</tr>
</table>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<?php echo $categoryCheckboxes;?>
</table>

<?php

include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	


?>