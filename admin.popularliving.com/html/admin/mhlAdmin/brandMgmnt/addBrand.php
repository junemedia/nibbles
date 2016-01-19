<?php

/*********

Script to Display List/Add/Edit/Delete Affiliate Management Company information

*********/

include("../../../includes/paths.php");


$sPageTitle = "MyHealthyLiving Brand Management - Add/Edit Brand";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	
	// SELECT hl DATABASE
	dbSelect($sGblMhlDBName);	
	

$imageFilePath = $sGblMhlWebRoot ."/images/brands/";
$imageUrl = $sGblMhlSiteRoot."/images/brands";

if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	if($brandName == '') {
		$sMessage = "Brand Name Is Required...";
		$keepValues = true;
	} else {
		//Check For Dupe
		$checkQuery = "SELECT *
				   FROM   brands
				   WHERE  brandName = '$brandName'";
		$checkResult = dbQuery($checkQuery);
		if (dbNumRows($checkResult) > 0 ) {
			$sMessage = "Brand Already Exists...";
			$keepValues = true;
		} else {
			$addQuery = "INSERT INTO brands (brandName)
				 VALUES ('$brandName')";
			
			// start of track users' activity in nibbles
			$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $addQuery\")";
			$rResult = dbQuery($sAddQuery);
			echo  dbError();
			// end of track users' activity in nibbles
			
			
			$result = dbQuery($addQuery);
			if ($result) {
				
				$sCheckQuery = "SELECT id
				   FROM   brands
				   WHERE  brandName = '$brandName'"; 
				$rCheckResult = dbQuery($sCheckQuery);
				$sRow = dbFetchObject($rCheckResult);
				
				$id = $sRow->id;
				if ($_FILES['brImage']['tmp_name'] && $_FILES['brImage']['tmp_name']!="none") {
					
					$upSource = $_FILES['brImage']['tmp_name'];
					
					//Get Extention
					$ar = explode(".",$_FILES['brImage']['name']);
					$i = count($ar) - 1;
					$thisext = $ar[$i];
					
					$fileName = "br_" . $id . ".$thisext";
					$brImageFile  = $imageFilePath . $fileName;
					
					move_uploaded_file( $upSource, $brImageFile);
					
					
					$updateQuery = "UPDATE brands
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
	if($brandName == '') {
		$sMessage = "Brand Name Is Required...";
		$keepValues = true;
	} else {
		//Check For Dupe
		$checkQuery = "SELECT *
				   FROM   brands
				   WHERE  brandName = '$brandName'
					AND   id != '$id'";
		$checkResult = dbQuery($checkQuery);
		if (dbNumRows($checkResult) > 0 ) {
			$sMessage = "Brand Alredy Exists...";
			$keepValues = true;
		} else {
			
			$editQuery = "UPDATE brands
				  	  SET 	 brandName='$brandName'
				  WHERE  id = '$id'";
			
			// start of track users' activity in nibbles
			$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")";
			$rResult = dbQuery($sAddQuery);
			echo  dbError();
			// end of track users' activity in nibbles
			
			$result = dbQuery($editQuery);
			if ($result) {
				if ($_FILES['brImage']['tmp_name'] && $_FILES['brImage']['tmp_name']!="none") {
					
					$upSource = $_FILES['brImage']['tmp_name'];
					
					//Get Extention
					$ar = explode(".",$_FILES['brImage']['name']);
					$i = count($ar) - 1;
					$thisext = $ar[$i];
					
					$fileName = "br_" . $id . ".$thisext";
					$brImageFile  = $imageFilePath . $fileName;
					
					move_uploaded_file( $upSource, $brImageFile);
					
					
					$updateQuery = "UPDATE brands
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
		$brandName = '';
		$brImageFile = '';		
	}
}

if ($id != '') {
	// If Clicked on Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   brands
			  		WHERE  id = '$id'";
	$result = dbQuery($selectQuery);
	
	if ($result) {
		
		while ($row = dbFetchObject($result)) {
			$brandName = $row->brandName;			
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
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>			
			<input type=hidden name=id value='$id'>";

	include("$sGblIncludePath/adminAddHeader.php");	

?>

<form action='<?php echo $PHP_SELF;?>' method=post enctype="multipart/form-data">
<?php echo $sHidden;?>
<?php echo $reloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td width=35%>Brand Name</td>
		<td><input type=text name='brandName' value='<?php echo $brandName;?>' ></td>
	</tr>
	<?php echo $currentImage;?>	
	<tr><td>Brand Image</td>
		<td><input type="file" name="brImage"></td>
	</tr>	
		
</table>

<?php

include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	

?>