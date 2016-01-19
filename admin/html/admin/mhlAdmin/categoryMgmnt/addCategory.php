<?php

/*********

Script to Display List/Add/Edit/Delete Affiliate Management Company information

*********/

include("../../../includes/paths.php");

session_start();

$sPageTitle = "MyHealthyLiving Category Management - Add/Edit Category";

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	
	// SELECT hl DATABASE
	dbSelect($sGblMhlDBName);	

$imageFilePath = $sGblMhlWebRoot ."/images/category/";
$imageUrl = $sGblMhlSiteRoot."/images/category";

if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	if($catName == '') {
		$sMessage = "Category Name Is Required...";
		$keepValues = true;
	} else {
		//Check For Dupe
		$checkQuery = "SELECT *
				   FROM   categories
				   WHERE  catName = '$catName'";
		$checkResult = dbQuery($checkQuery);
		if (dbNumRows($checkResult) > 0 ) {
			$sMessage = "Category Already Exists...";
			$keepValues = true;
		} else {
			$addQuery = "INSERT INTO categories (catName, catFullName)
				 VALUES ('$catName', '$catFullName')";
			
			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $addQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		

			
			$result = dbQuery($addQuery);
			if ($result) {
				
				$sCheckQuery = "SELECT id
				   FROM   categories
				   WHERE  catName = '$catName'"; 
				$rCheckResult = dbQuery($sCheckQuery);
				$sRow = dbFetchObject($rCheckResult);
				
				
				
				$id = $sRow->id;
				if ($_FILES['catImage']['tmp_name'] && $_FILES['catImage']['tmp_name']!="none") {
					
					$upSource = $_FILES['catImage']['tmp_name'];
					
					//Get Extention
					$ar = explode(".",$_FILES['catImage']['name']);
					$i = count($ar) - 1;
					$thisext = $ar[$i];
					
					$fileName = "cat_" . $id . ".$thisext";
					$catImageFile  = $imageFilePath . $fileName;
					
					move_uploaded_file( $upSource, $catImageFile);
										
					$updateQuery = "UPDATE categories
									SET    image='$fileName'
									where id = '$id'";

					
					$result = dbQuery($updateQuery);
					// reset $id
					$id = '';
				}
				
			} else {
				echo dbError();
			}
		}
	}
	
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
	//if record edited
	if($catName == '') {
		$sMessage = "Category Name Is Required...";
		$keepValues = true;
	} else {
		//Check For Dupe
		$checkQuery = "SELECT *
				   		FROM   categories
				   		WHERE  catName = '$catName'
						AND   id != '$id'";
		$checkResult = dbQuery($checkQuery);
		if (dbNumRows($checkResult) > 0 ) {
			$sMessage = "Category Alredy Exists...";
			$keepValues = true;
		} else {
			
			$editQuery = "UPDATE categories
				  	  	  SET 	 catName = '$catName',
								 catFullName = '$catFullName'
				  		  WHERE  id = '$id'";

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$result = dbQuery($editQuery);
			if ($result) {
				if ($_FILES['catImage']['tmp_name'] && $_FILES['catImage']['tmp_name']!="none") {
					
					$upSource = $_FILES['catImage']['tmp_name'];
					
					//Get Extention
					$ar = explode(".",$_FILES['catImage']['name']);
					$i = count($ar) - 1;
					$thisext = $ar[$i];
					
					$fileName = "cat_" . $id . ".$thisext";
					$catImageFile  = $imageFilePath . $fileName;
					
					move_uploaded_file( $upSource, $catImageFile);
					
					
					$updateQuery = "UPDATE categories
									SET    image='$fileName'
									where id = '$id'";
					$updateResult = dbQuery($updateQuery);
					// reset $id
					$id = '';
				}
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
		$catName = '';
		$catFullName = '';
		$catImageFile = '';		
	}
}

if ($id != '') {
	// If Clicked on Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   categories
			  		WHERE  id = '$id'";
	$result = dbQuery($selectQuery);
	
	if ($result) {
		
		while ($row = dbFetchObject($result)) {
			$catName = $row->catName;			
			$catFullName = $row->catFullName;			
			$imageFile = $row->image;

			$currentImage = "<tr><td>Current Image</td><td><img src = '".$imageUrl."/$imageFile"."'></td>";		
		}		
	} else {
		echo dbError();
	}
}  else {
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>
			<input type=hidden name=id value='$id'>";

	include("$sGblIncludePath/adminAddHeader.php");	
?>

<form action='<?php echo $PHP_SELF;?>' method=post enctype="multipart/form-data">
<?php echo $hidden;?>
<?php echo $reloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td width=35%>Category Name</td>
		<td><input type=text name='catName' value='<?php echo $catName;?>' size=40></td>
	</tr>
	<tr><td width=35%>Category Full Name</td>
		<td><input type=text name='catFullName' value='<?php echo $catFullName;?>' size=40 ></td>
	</tr>
	<?php echo $currentImage;?>
	<tr><td>Category Image</td>
		<td><input type="file" name="catImage"></td>
	</tr>	
		
</table>

<?php

include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	
?>