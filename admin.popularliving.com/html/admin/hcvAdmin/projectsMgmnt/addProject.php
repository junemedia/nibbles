<?php

/*********

Script to Display Add/Edit HandCraftersVillage Add/Edit Project

*********/

include("../../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Handcrafters Village Project Management - Add/Edit Project";

$imageFilePath = $sGblHcvWebRoot ."/projectImages/";
$imageUrl = $sGblHcvSiteRoot."/projectImages";

$thFilePath = $sGblHcvWebRoot ."/thumb/";
$thUrl = $sGblHcvSiteRoot."/thumb";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
// SELECT HCV DATABASE
dbSelect($sGblHcvDBName);

// if image deleted
if ($deleteImage) {
	
	//Get image names
	$selectQuery = "SELECT *
			  		FROM   craftProjects
			  		WHERE  id = '$id'";
	$selectResult = dbQuery($selectQuery);
	while ($selectRow = dbFetchObject($selectResult)) {
		$tempImageToDelete = "image".$imageId;
		$tempThumbToDelete = "thumb".$imageId;
		
		$imageToDelete = $selectRow->$tempImageToDelete;
		$thumbToDelete = $selectRow->$tempThumbToDelete;
	}
	
	if ($deleteImage) {
		@unlink($imageFilePath.$imageToDelete);
		@unlink($thFilePath.$thumbToDelete);
		
		// delete image name from database
		$updateQuery = "UPDATE craftProjects
						SET    image".$imageId." = '',
							   thumb".$imageId." = '',
							   imageAlt".$imageId." = ''		
						WHERE  id = '$id'";
		$updateResult = dbQuery($updateQuery);
	}
}


if (($sSaveClose || $sSaveNew)) {
	
	if ($title == '') {
		$sMessage = "Project Name Is Required...";
		$keepValues = true;
	} else {
		
		// Prepare comma-separated Categories if record added or edited
		
		$categoryQuery = "SELECT id, title
			 			  	  FROM   categories
				 		  	  ORDER BY title";
		$categoryResult = dbQuery($categoryQuery);
		$i = 0;
		while ($categoryRow = dbFetchObject($categoryResult)) {
			
			// prepare Categories of this offer
			$checkboxName = "category_".$categoryRow->id;
			
			$checkboxValue = $$checkboxName;
			
			if ($checkboxValue != '') {
				$categoriesArray[$i] = $checkboxValue;
				$categoriesString .= $checkboxValue.",";
				$i++;
			}
		}
		//var_dump($categoriesArray);exit;
		
		if ($categoriesString != '') {
			$categoriesString = substr($categoriesString, 0, strlen($categoriesString)-1);
		}
		
		if (!($id)) {
			// if new data submitted
			/*if($title == '') {
			$sMessage = "Project Name Is Required...";
			$keepValues = true;
			} else {*/
			//Check For Dupe
			$checkQuery = "SELECT *
				   FROM   craftProjects
				   WHERE  title = '$title'";
			$checkResult = dbQuery($checkQuery);
			if (dbNumRows($checkResult) > 0 ) {
				$sMessage = "Project With This Title Already Exists...";
				$keepValues = true;
			} else {
				$addQuery = "INSERT INTO craftProjects (title, description, link, materials, instructions,
									imageAlt1, imageAlt2, imageAlt3, isFeatured)
				 VALUES ('$title', '$description', '$link', '$materials', '$instructions', 
								'$imageAlt1', '$imageAlt2', '$imageAlt3', '$isFeatured')";
				
				// start of track users' activity in nibbles
				$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
				$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
						  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: craftProjects.title='$title'\")";
				$rResult = dbQuery($sAddQuery);
				echo  dbError();
				// end of track users' activity in nibbles
				
				
				$result = dbQuery($addQuery);
				//if ($result) {
					
					$sCheckQuery = "SELECT id
							   FROM   craftProjects
							   WHERE  title = '$title'
							   AND description = '$description'
							   AND link = '$link'
							   AND materials = '$materials'
							   AND instructions = '$instructions'
							   AND imageAlt1 = '$imageAlt1'
							   AND imageAlt2 = '$imageAlt2'
							   AND imageAlt3 = '$imageAlt3'
							   AND isFeatured = '$isFeatured'"; 
					$rCheckResult = dbQuery($sCheckQuery);
					$sRow = dbFetchObject($rCheckResult);
					
					
					$id = $sRow->id;
					
					
					// Insert into OfferCategoryRel according to category checkboxes checked
						if (count($categoriesArray) > 0) {
							for ($i = 0; $i < count($categoriesArray); $i++) {
								$insertQuery = "INSERT INTO categoryMap(projectId, categoryId, sortOrder)
									VALUES('$id', '".$categoriesArray[$i]."', 0)";		
								$insertResult = dbQuery($insertQuery);
								if (!($insertResult)) {
									echo dbError();
								}
							}
						}
					
					for ($i=1;$i<=3;$i++) {
						$tempImage = "image".$i;
						if ($_FILES[$tempImage]['tmp_name'] && $_FILES[$tempImage]['tmp_name'] != "none") {
							
							$upSource = $_FILES[$tempImage]['tmp_name'];
							$imageSize = getimagesize($upSource);
							//Get Extention
							$ar = explode(".",$_FILES[$tempImage]['name']);
							$iCount = count($ar) - 1;
							$thisext = $ar[$iCount];
							
							$fileName = "project".$i."_" . $id . ".$thisext";
							$imageFile  = $imageFilePath . $fileName;
							
							move_uploaded_file( $upSource, $imageFile);
							
							$imageWidth = $imageSize[0];
							$imageHeight = $imageSize[1];
							
							$thMaxWidth = 100;
							
							// name of thumbnail image
							$thFileName = "thumb".$i."_" . $id . ".$thisext";
							$thFile = $thFilePath.$thFileName;
							$prevThumbFile = $thFilePath."thumb".$i."_$id";
							
							// delete the previous thumbnail image if exists in any form gif/jpg/png
							@unlink("$prevThumbFile.gif");
							@unlink("$prevThumbFile.jpg");
							@unlink("$prevThumbFile.png");
							// create thumbnail if not gif file, gif is not supported
							// and if the image width is greater than allowed width, height is ok.
							
							if ($thisext != 'gif' && $imageWidth > $thMaxWidth ) {
								
								$thWidth = $thMaxWidth;
								$thHeight = ($thWidth/$imageWidth) * $imageHeight;
								
								switch ($thisext) {
									case "jpg":
									case "jpeg":
									$imageSrc = imagecreatefromjpeg($imageFile);
									break;
									case "png":
									$imageSrc = imagecreatefrompng($imageFile);
									break;
								}
								
								$imageDest = imagecreate($thWidth, $thHeight);
								imagecopyresized($imageDest, $imageSrc, 0,0,0,0, $thWidth, $thHeight, $imageWidth, $imageHeight);
								
								switch ($thisext) {
									case "jpeg":
									case "jpg":
									$t = imagejpeg($imageDest, $thFile, 100);
									break;
									case "png":
									imagepng($imageDest, $thFile);
								}
								
								
								imagedestroy($imageDest);
								$updateQuery = "UPDATE craftProjects
									SET    image".$i." = '$fileName',
										   thumb".$i." = '$thFileName'	
									where id = '$id'";
							} else {
								$updateQuery = "UPDATE craftProjects
									SET    image".$i." = '$fileName',
										   thumb".$i." = ''
									where id = '$id'";
							}
							
							$result = dbQuery($updateQuery);
							
						}
					} // end of for loop
					// reset $id
					$id = '';
				/*} else {
					echo dbError();
				}*/
			}
			//}
			
		} else if ($id) {
			//if record edited
			/*if ($title == '') {
			$sMessage = "Project Name Is Required...";
			$keepValues = true;
			} else {*/
			//Check For Dupe
			$checkQuery = "SELECT *
				   		FROM   craftProjects
				   		WHERE  title = '$title'
						AND   id != '$id'";
			$checkResult = dbQuery($checkQuery);
			
			if (dbNumRows($checkResult) > 0 ) {
				$sMessage = "Project With This Title Already Exists...";
				$keepValues = true;
			} else {
				
				$editQuery = "UPDATE craftProjects
				  	  	  SET 	 title = '$title',
								 description = '$description',
								 link = '$link', 
								 materials = '$materials',
								 instructions = '$instructions',
								 imageAlt1 = '$imageAlt1',
								 imageAlt2 = '$imageAlt2',
								 imageAlt3 = '$imageAlt3',
								 isFeatured = '$isFeatured'
				  		  WHERE  id = '$id'";
				
				// start of track users' activity in nibbles
				$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
				$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
						  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: craftProjects.id='$id'\")";
				$rResult = dbQuery($sAddQuery);
				echo  dbError();
				// end of track users' activity in nibbles
				
				//var_dump($categoriesArray);exit;
				$result = dbQuery($editQuery);
				//if ($result) {
					
					
					
					// delete if not in selected list
					
					$sDeleteQuery = "DELETE FROM categoryMap
									 WHERE  projectId = '$id'
									 AND    categoryId NOT IN (".$categoriesString.")";
					$rDeleteResult = dbQuery($sDeleteQuery);
										
					// Insert into OfferCategoryRel according to category checkboxes checked
						if (count($categoriesArray) > 0) {
							for ($i = 0; $i < count($categoriesArray); $i++) {
								$insertQuery = "INSERT IGNORE INTO categoryMap(projectId, categoryId, sortOrder)
									VALUES('$id', '".$categoriesArray[$i]."', 0)";		
								$insertResult = dbQuery($insertQuery);
								if (!($insertResult)) {
									echo dbError();
								}
							}
						}			
						
					for ($i=1;$i<=3;$i++) {
						$tempImage = "image".$i;
						if ($_FILES[$tempImage]['tmp_name'] && $_FILES[$tempImage]['tmp_name']!="none") {
							
							$upSource = $_FILES[$tempImage]['tmp_name'];
							$imageSize = getimagesize($upSource);
							
							//Get Extention
							$ar = explode(".",$_FILES[$tempImage]['name']);
							$iCount = count($ar) - 1;
							$thisext = $ar[$iCount];
							
							$fileName = "project".$i."_" . $id . ".$thisext";
							$imageFile  = $imageFilePath . $fileName;
							move_uploaded_file( $upSource, $imageFile);
							
							$imageWidth = $imageSize[0];
							$imageHeight = $imageSize[1];
							
							$thMaxWidth = 100;
							
							// name of thumbnail image
							$thFileName = "thumb".$i."_". $id . ".$thisext";
							$thFile = $thFilePath.$thFileName;
							$prevThumbFile = $thFilePath."thumb".$i."_$id";
							
							// delete the previous thumbnail image if exists in any form gif/jpg/png
							@unlink("$prevThumbFile.gif");
							@unlink("$prevThumbFile.jpg");
							@unlink("$prevThumbFile.png");
							// create thumbnail if not gif file, gif is not supported
							// and if the image width is greater than allowed width, height is ok.
							
							if ($thisext != 'gif' && $imageWidth > $thMaxWidth ) {
								
								$thWidth = $thMaxWidth;
								$thHeight = ($thWidth/$imageWidth) * $imageHeight;
								
								switch ($thisext) {
									case "jpg":
									case "jpeg":
									$imageSrc = imagecreatefromjpeg($imageFile);
									break;
									case "png":
									$imageSrc = imagecreatefrompng($imageFile);
									break;
								}
								
								$imageDest = imagecreate($thWidth, $thHeight);
								imagecopyresized($imageDest, $imageSrc, 0,0,0,0, $thWidth, $thHeight, $imageWidth, $imageHeight);
								
								switch ($thisext) {
									case "jpeg":
									case "jpg":
									$t = imagejpeg($imageDest, $thFile, 100);
									break;
									case "png":
									imagepng($imageDest, $thFile);
								}
								
								
								imagedestroy($imageDest);
								$updateQuery = "UPDATE craftProjects
									SET    image".$i." = '$fileName',
										   thumb".$i." = '$thFileName'	
									where id = '$id'";
							} else {
								$updateQuery = "UPDATE craftProjects
									SET    image".$i." = '$fileName',
										   thumb".$i." = ''
									where id = '$id'";
							}
							$updateResult = dbQuery($updateQuery);
							
						}
					} // end for loop
					// reset $id
					$id = '';
				/*} else {
					echo dbError();
				}*/
			}
			//}
		}
	}
}

if ($sSaveClose) {
	if ($keepValues != true) {
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
		$image1 = '';
		$thumbImage1 = '';
		$image2 = '';
		$thumbImage2 = '';
		$image3 = '';
		$thumbImage3 = '';
		$description = '';
		$materials = '';
		$instructions = '';
		$link = '';
	}
}

if ($id != '') {
	// If Clicked on Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   craftProjects
			  		WHERE  id = '$id'";
	$result = dbQuery($selectQuery);
	
	if ($result) {
		
		while ($row = dbFetchObject($result)) {
			$categoryId = $row->categoryId;
			$title =$row->title;
			$description = $row->description;
			$link = $row->link;
			$materials = $row->materials;
			$instructions = $row->instructions;
			$imageAlt1 = $row->imageAlt1;
			$imageAlt2 = $row->imageAlt2;
			$imagealt3 = $row->imageAlt3;
			//echo $row->image1;
			for ($i=1;$i<=3;$i++) {
				
				$imgValue = "image".$i;
				$thumbValue = "thumb".$i;
				
				if ($row->$imgValue != '') {
					$$imgValue = "<img src='$imageUrl/".$row->$imgValue."'> <a href='$PHP_SELF?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder&id=$id&imageId=$i&deleteImage=Y'>Delete Image</a>";
				}/* else {
				$$imgValue = "No Image";
				}	*/
				
				if ($row->$thumbValue != '') {
					$$thumbValue = "<img src='$thUrl/".$row->$thumbValue."'>";
				} /*else {
				$$thumbValue = 'No Thumbnail';
				}*/
				if ($row->$imgValue !='') {
					$currImages .= "<tr><td>Current Image $i </td><td nowrap>".$$imgValue."</td></tr>
								<tr><td>Current Thumbnail $i Image</td>
									<td>".$$thumbValue."</td></tr>";
				}
			}
			$isFeatured = $row->isFeatured;
		}
	} else {
		echo dbError();
	}
}  else {
	
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

$title = ascii_encode(stripslashes($title));
$description = ascii_encode(stripslashes($description));
$materials = ascii_encode(stripslashes($materials));
$instructions = ascii_encode(stripslashes($instructions));
$link = ascii_encode(stripslashes($link));


/*
$categoryQuery = "SELECT *
FROM   categories
ORDER BY title";
$categoryResult = mysql_query($categoryQuery);
while ($categoryRow = mysql_fetch_object($categoryResult)) {
if ($categoryRow->id == $categoryId) {
$selected = "selected";
} else {
$selected = "";
}
$categoryOptions .= "<option value='$categoryRow->id' $selected>$categoryRow->title";

}


*/



$categoryQuery = "SELECT *
				  FROM   categories
				  ORDER BY title";
$categoryResult = dbQuery($categoryQuery);
echo dbError();
$j = 0;
while ($categoryRow = dbFetchObject($categoryResult)) {
	$categoryId = $categoryRow->id;
	$category = $categoryRow->title;
	
	$projectQuery = "SELECT projectId
				   FROM   categories, categoryMap
				   WHERE  categoryMap.categoryId = '$categoryId'
				   AND    categoryMap.projectId = '$id'
				   AND   categoryMap.categoryId = categories.id";
	
	$projectResult = dbQuery($projectQuery);
	if(dbNumRows($projectResult)>0){
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
	
	/*// check if this is a parent category
	$checkQuery = "SELECT *
	FROM   OfferCategories
	WHERE  parentCategory = '$categoryId'";
	$checkResult = mysql_query($checkQuery);
	if (mysql_num_rows($checkResult)>0 ) {
	$category = "<B>".$category."</B>";
	}*/
	
	$categoryCheckboxes .= "<td width=5% valign=top><input type=checkbox name='category_".$categoryRow->id."' value='".$categoryRow->id."' $categoryChecked></td><td  width=28%>$category</td>";
	$j++;
}
$categoryCheckboxes .= "</tr>";





if ($isFeatured) {
	$isFeaturedChecked = "checked";
} else {
	$isFeaturedChecked = "";
}

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
	
	<tr><td>Title</td><td><input type=text name='title' value='<?php echo $title;?>' size=35></td></tr>
	<tr><td>Description</td><td><textarea name='description' rows=4 cols=35><?php echo $description;?></textarea></td></tr>
	<tr><td>Materials</td><td><textarea name='materials' rows=3 cols=35><?php echo $materials;?></textarea></td></tr>
	<tr><td>Instructions</td><td><textarea name='instructions' rows=4 cols=35><?php echo $instructions;?></textarea></td></tr>
	<tr><td>Link</td><td><input type=text name='link' value='<?php echo $link;?>' size=35></td></tr>
	<?php echo $currImages;?>
	<!--<tr><td>Current Image 1 </td><td><php echo $image1;?></td></tr>
	<tr><td>Current Thumbnail 1 Image</td><td><php echo $thumbImage1;?></td></tr>-->
	<tr><td>Image 1 </td><td><input type=file name='image1'></td></tr>
	<tr><td>Image 1 Text </td><td><input type=text name='imageAlt1' value='<?php echo $imageAlt1;?>'></td></tr>
	<tr><td>Image 2 </td><td><input type=file name='image2'></td></tr>
	<tr><td>Image 2 Text </td><td><input type=text name='imageAlt2' value='<?php echo $imageAlt2;?>'></td></tr>
	<tr><td>Image 3 </td><td><input type=file name='image3'></td></tr>
	<tr><td>Image 3 Text </td><td><input type=text name='imageAlt3' value='<?php echo $imageAlt3;?>'></td></tr>
	
	
	<tr><td>Is Featured</td><Td><input type=checkbox name=isFeatured value='1' <?php echo $isFeaturedChecked;?>></td></tr>
	</table>
	<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr>
	<?php echo $categoryCheckboxes;?>
	</tr>		

	<?php 
	// include footer
	include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}		
	
?>