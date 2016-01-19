<?php

/*********

Script to Display List/Add/Edit/Delete Affiliate Management Company information

*********/

include("../../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "MyHealthyLiving Products Management - Add/Edit Product";

if (hasAccessRight($iMenuId) || isAdmin()) {

	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);	

	
$thumbFileUrl = $sGblMhlSiteRoot."/images/thumb";
$imageFileUrl = $sGblMhlSiteRoot."/images/product";

$thumbFilePath = $sGblMhlWebRoot."/images/thumb/";
$imageFilePath = $sGblMhlWebRoot."/images/product/";

if ($sSaveClose || $sSaveNew) {
	// Prepare comma-separated Categories if record added or edited
	
	$categoryQuery = "SELECT id
			 			  FROM   categories
				 		  ORDER BY id";
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
	
	if (($sSaveClose || $sSaveNew) && !($prID)) {
		// if new data submitted
		//Check For Dupe
		$checkQuery = "SELECT *
					   FROM   products
					   WHERE  prNo = '$prNo'";
		$checkResult = dbQuery($checkQuery);
		if (dbNumRows($checkResult) > 0 ) {
			$sMessage = "Product No./Mfg. Product No. exists...";
			$keepValues = true;
		} else {
			$addQuery = "INSERT INTO products (prNo, mfgPrNo, prName, prBrandId,
							prDescription, prSize, prAbsorbency,
							prCost, prOurPrice, prCPDiscount, prPackaging, prFrontPageSpecial)
					 VALUES('$prNo', '$mfgPrNo', '$prName', '$prBrandId', 
						 '$prDescription', '$prSize', '$prAbsorbency',
						'$prCost', '$prOurPrice', '$prCPDiscount', '$prPackaging', '$prFrontPageSpecial')";
			
			// start of track users' activity in nibbles
			$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $addQuery\")";
			$rResult = dbQuery($sAddQuery);
			echo  dbError();
			// end of track users' activity in nibbles
			
			$result = dbQuery($addQuery);
			
			if ($result) {
				if ($_FILES['image1']['tmp_name'] && $_FILES['image1']['tmp_name'] != "none") {
					
					$upSource = $_FILES['image1']['tmp_name'];
					
					//Get Extention
					$ar = explode(".",$_FILES['image1']['name']);
					$i = count($ar) - 1;
					$thisext = $ar[$i];
					$thumbFile = $prNo.".$thisext";
					$prThumbPath  = $thumbFilePath . $prNo . ".$thisext";
					//$prThumbURL = $thumbfileurl . $prNo . ".$thisext";
					move_uploaded_file( $upSource, $prThumbPath);
					
					// update image file names
					$updateQuery = "UPDATE products
									SET    prThumb = '$thumbFile'
									WHERE  prID = '$prID'";
					$updateResult = dbQuery($updateQuery);
					
				}
				
				if ($_FILES['image2']['tmp_name']&& $_FILES['image2']['tmp_name'] != "none") {
					
					$upSource = $_FILES['image2']['tmp_name'];
					
					//Get Extention
					$ar = explode(".",$_FILES['image2']['name']);
					$i = count($ar) - 1;
					$thisext = $ar[$i];
					$imageFile = $prNo.".$thisext";
					$prImagePath  = $imageFilePath . $prNo . ".$thisext";
					//$prImageURL = $imagefileurl . $prNo . ".$thisext";
					move_uploaded_file( $upSource, $prImagePath);
					
					// update image file names
					$updateQuery = "UPDATE products
									SET    prImage = '$imageFile'
									WHERE  prID = '$prID'";
					$updateResult = dbQuery($updateQuery);
				}
				
			} else {
				echo dbError();
			}
		}
		
	} elseif (($sSaveClose || $sSaveNew) && ($prID)) {
		//if record edited
		
		//Check For Dupe
		$checkQuery = "SELECT *
				   FROM   products
				   WHERE  prNo = '$prNo'			 	   
				   AND   prID != '$prID'";
		$checkResult = dbQuery($checkQuery);
		echo dbError();
		
		if (dbNumRows($checkResult) > 0 ) {
			$sMessage = "Product No./Mfg. Product No. exists...";
			$keepValues = true;
		} else {
			
			$editQuery = "UPDATE products
					  	  SET 	 prNo = '$prNo',
								 mfgPrNo = '$mfgPrNo', 
								 prName = '$prName', 
								 prBrandId = '$prBrandId', 							 
								 prDescription = '$prDescription', 
								 prSize = '$prSize', 
								 prAbsorbency = '$prAbsorbency',
								 prCost = '$prCost', 
								 prOurPrice = '$prOurPrice', 
								 prCPDiscount = '$prCPDiscount', 
								 prPackaging = '$prPackaging',
								 prFrontPageSpecial = '$prFrontPageSpecial'
					  	  WHERE  prID = '$prID'";

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$result = dbQuery($editQuery);
			
			if ($_FILES['image1']['tmp_name'] && $_FILES['image1']['tmp_name'] != "none") {
				
				$upSource = $_FILES['image1']['tmp_name'];
				
				//Get Extention
				$ar = explode(".",$_FILES['image1']['name']);
				$i = count($ar) - 1;
				$thisext = $ar[$i];
				$thumbFile = $prNo.".$thisext";
				$prThumbPath  = $thumbFilePath . $prNo . ".$thisext";
				//echo "<BR>1 ".$upSource. " ".$prThumbPath;
				move_uploaded_file( $upSource, $prThumbPath);
				// update image file names
				$updateQuery = "UPDATE products
							SET    prThumb = '$thumbFile'
							WHERE  prID = '$prID'";
				$updateResult = dbQuery($updateQuery);
				
			}
			
			if ($_FILES['image2']['tmp_name']&& $_FILES['image2']['tmp_name'] != "none") {
				
				$upSource = $_FILES['image2']['tmp_name'];
				
				//Get Extention
				$ar = explode(".",$_FILES['image2']['name']);
				$i = count($ar) - 1;
				$thisext = $ar[$i];
				$imageFile = $prNo.".$thisext";
				$prImagePath  = $imageFilePath . $prNo . ".$thisext";
				move_uploaded_file( $upSource, $prImagePath);
				
				//echo "<BR>2 ".$upSource. " ".$prImagePath;
				// update image file names
				$updateQuery = "UPDATE products
							SET    prImage = '$imageFile'
							WHERE  prID = '$prID'";
				$updateResult = dbQuery($updateQuery);
				
			}
			
			// update product categories
			$categoriesString = substr($categoriesString, 0, strlen($categoriesString)-1);
			// Delete if any category unchecked for the offer to be displayed in.
			$deleteQuery = "DELETE FROM product_category
							WHERE  pctProductID = '$prID'";
			if ($categoriesString != '') {
				$deleteQuery .= " AND    pctCategoryID NOT IN (".$categoriesString.")";
			}
			$deleteResult = dbQuery($deleteQuery);
			//}
			
			if (count($categoriesArray) > 0) {
				for ($i = 0; $i<count($categoriesArray); $i++) {
					$checkQuery = "SELECT *
								   FROM   product_category
								   WHERE  pctCategoryID = ".$categoriesArray[$i]."
				 				   AND    pctProductID = '$prID'";
					$checkResult = dbQuery($checkQuery);
					if (dbNumRows($checkResult) == 0) {
						// INSERT OfferCategoryRel record
						
						$insertQuery = "INSERT INTO product_category (pctCategoryID, pctProductID)
										VALUES('".$categoriesArray[$i]."', '$prID')";
						$insertResult = dbQuery($insertQuery);
						echo dbError();
					}
				}
			}
		}
		//echo $editQuery.$result;
	}
	
	
	
	//If product upsell added by typing product no
	if (trim($upsellPrNo) != '') {
		//get prId for this product No...
		$tempQuery = "SELECT prID
					  FROM   products
					  WHERE  prNo = '$upsellPrNo'";
		$tempResult = dbQuery($tempQuery) ;
		while ($tempRow = dbFetchObject($tempResult)) {
			$puUpsellID = $tempRow->prID;
		}
	} else 	if ($addProduct != '') {
		// If offer selected from the selection box to add
		$puUpsellID = $addProduct;
	}
	
	//echo "up ".$puUpsellID;
	if ($puUpsellID !='') {
		$checkQuery = "SELECT *
					   FROM   product_upsell
					   WHERE  puParentID = '$prID'
					   AND    puUpsellID = '$puUpsellID'";
		$checkResult = dbQuery($checkQuery);
		
		if (dbNumRows($checkResult) == 0) {
			if (!($addSortOrder)) {
				$addSortOrder = 0;
			}
			$addQuery = "INSERT INTO product_upsell(puParentId, puUpsellID)
						 VALUES('$prID', '$puUpsellID')";
			$addResult = dbQuery($addQuery);
			echo $addQuery.dbError();
		} else {
			$sMessage = "Upshell already exists for this product....";
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
			$prNo = '';
			$mfgPrNo = '';
			$prName = '';
			$prBrandId = '';
			$prCategory = '';
			$prDescription = '';
			$prSize = '';
			$prAbsorbency = '';
			$prCost = '';
			$prOurPrice = '';
			$prCPDiscount = '';
			$prPackaging = '';
		}
	}
}
if ($prID != '') {
	// If Clicked on Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   products
			  		WHERE  prID = '$prID'";
	$result = dbQuery($selectQuery);
	
	if ($result) {
		
		while ($row = dbFetchObject($result)) {
			$prNo = $row->prNo;
			$mfgPrNo = $row->mfgPrNo;
			$prName = $row->prName;
			$prBrandId = $row->prBrandId;
			$prCategory = $row->prCategory;
			$prDescription = ascii_encode($row->prDescription);
			$prSize = $row->prSize;
			$prAbsorbency = $row->prAbsorbency;
			$prCost = $row->prCost;
			$prOurPrice = $row->prOurPrice;
			$prCPDiscount = $row->prCPDiscount;
			$prPackaging = $row->prPackaging;
			$prFrontPageSpecial = $row->prFrontPageSpecial;
			$prThumbImage = "<img src='$thumbFileUrl/$row->prThumb'>";
			$prImage = "<img src='$imageFileUrl/$row->prImage'>";
		}
		dbFreeResult($result);
	} else {
		echo dbError();
	}
}  else {
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

// prepare brand options

$brandQuery = "SELECT *
			   FROM   brands
			   ORDER BY brandName";
$brandResult = dbQuery($brandQuery) ;
while ($brandRow = dbFetchObject($brandResult)) {
	if ($brandRow->id == $prBrandId) {
		$selected = "selected";
	} else {
		$selected = "";
	}
	
	$brandOptions .= "<option value='$brandRow->id' $selected>$brandRow->brandName";
}



// Prepare checkboxes for Categories
$categoryQuery = "SELECT *
				  FROM   categories 
				  ORDER BY catName";
$categoryResult = dbQuery($categoryQuery);
echo dbError();
$j = 0;
while ($categoryRow = dbFetchObject($categoryResult)) {
	$categoryId = $categoryRow->id;
	$category = $categoryRow->catName;
	
	$productQuery = "SELECT pctProductID
				   FROM   categories, product_category 
				   WHERE  product_category.pctCategoryID = '$categoryId'
				   AND    product_category.pctProductID = '$prID'
				   AND    product_category.pctCategoryID = categories.id";
	
	$productResult = dbQuery($productQuery);
	if(dbNumRows($productResult)>0){
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
	
	// check if this is a parent category
	$checkQuery = "SELECT *
				   FROM   categories
				   WHERE  parentId = '$categoryId'";
	$checkResult = dbQuery($checkQuery);
	if (dbNumRows($checkResult)>0 ) {
		$category = "<B>".$category."</B>";
	}
	
	$categoryCheckboxes .= "<td width=5% valign=top><input type=checkbox name='category_".$categoryRow->id."' value='".$categoryRow->id."' $categoryChecked></td><td  width=28%>$category</td>";
	$j++;
}
$categoryCheckboxes .= "</tr>";
if ($prFrontPageSpecial == 'Y') {
	$yesChecked = "checked";
} else {
	$noChecked = "checked";
}
$frontSpecialOptions = "<input type=radio name=prFrontPageSpecial value='Y' $yesChecked>Yes
						<input type=radio name=prFrontPageSpecial value='' $noChecked>No";

$upsellQuery = "SELECT P.*
				FROM   products P LEFT JOIN product_upsell PU ON P.prID = PU.puUpsellID
				WHERE PU.puUpsellID IS NULL		
				AND   P.prID != '$prID'		
				ORDER BY P.prNo";

$upsellResult = dbQuery($upsellQuery);
//echo mysql_error();
//echo mysql_error().mysql_num_rows($upsellResult);
$addUpsellOptions = "<option value=''>Select Product To Add As Upsell";
while ($upsellRow = dbFetchObject($upsellResult)) {
	
	$addUpsellOptions .= "<option value='".$upsellRow->prID."'>$upsellRow->prNo - ".substr($upsellRow->prName,0,25)."...";
}

// get upsells of this product and display
$upsellQuery2 = "SELECT P.*
				FROM   products P, product_upsell PU
				WHERE PU.puParentID = '$prID'
				AND   PU.puParentID = P.prID 
				ORDER BY P.prNo";

$upsellResult2 = dbQuery($upsellQuery2);

if (dbNumRows($upsellResult2)>0) {
	$upsellList = "<TR><TD colspan=4 class=header>Upsells Assigned To This Product</td></tR>";
	while ($upsellRow2 = dbFetchObject($upsellResult2)) {
		
		$upsellList .= "<tR><td colspan=6>$upsellRow2->prNo - ".substr($upsellRow2->prName,0,25)."...</td></tR>";
	}
}

// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>	
			<input type=hidden name=prID value='$prID'>";

	include("$sGblIncludePath/adminAddHeader.php");	
	
?>
</form>
<form action='<?php echo $PHP_SELF;?>' method=post enctype="multipart/form-data">
<?php echo $hidden;?>
<?php echo $reloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td width=35%>Product No.</td>
		<td><input type=text name='prNo' value='<?php echo $prNo;?>'></td>
	</tr>
	<tr><td width=35%>Mfg. Product No.</td>
		<td><input type=text name='mfgPrNo' value='<?php echo $mfgPrNo;?>'></td>
	</tr>
	<tr><td>Product Name</td>
		<td><input type=text name='prName' value='<?php echo $prName;?>' size=40></td>
	</tr>
	<tr><td>Brand</td>
		<td><select name='prBrandId'>
		<?php echo $brandOptions;?>
			</select></td>
	</tr>
	
	<tr><td>Description</td>
		<td><textarea name='prDescription' rows=5 cols=30><?php echo $prDescription;?></textarea></td>
	</tr>
	<tr><td>Size</td>
		<td><input type=text name='prSize' value='<?php echo $prSize;?>'></td>
	</tr>
	<tr><td>Absorbency</td>
		<td><input type=text name='prAbsorbency' value='<?php echo $prAbsorbency;?>'></td>
	</tr>
	<tr><td>MHL Cost</td>
		<td><input type=text name='prCost' value='<?php echo $prCost;?>'></td>
	</tr>
	<tr><td>Our Price</td>
		<td><input type=text name='prOurPrice' value='<?php echo $prOurPrice;?>'></td>
	</tr>
	<tr><td>Convenience Plan Discount</td>
		<td><input type=text name='prCPDiscount' value='<?php echo $prCPDiscount;?>'></td>
	</tr>
	<tr><td>Packaging</td>
		<td><input type=text name='prPackaging' value='<?php echo $prPackaging;?>'></td>
	</tr>	
	<tr><td valign=top>Current Thumbnail Image</td><td><?php echo $prThumbImage;?></td></tr>
	<tr><td>Thumbnail Image</td><td><input type=file name=image1></td></tr>
	<tr><td valign=top>Current Product Image</td><td><?php echo $prImage;?></td></tr>
	<tr><td>Product Image</td><td><input type=file name=image2></td></tr>	
	<tr><td colspan=2>Would you like this product to be included as a special on the front page? 
	&nbsp; &nbsp; <?php echo $frontSpecialOptions;?></td></tR>
	</table>

	<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td colspan=6 class=header align=center>Categories<BR><BR></td></tr>
	<?php echo $categoryCheckboxes;?>
	
	<tr><td colspan=6><BR><HR></td></tr>
	
	<?php echo $upsellList;?>
<tr><td colspan=6 class=header><BR>Select Product To Add As Upsell To This Product:</td></tr>
<tr><Td  colspan=4><select name=addProduct>
<?php echo $addUpsellOptions;?>
</select>
</td></tr>
<tr><td colspan=6 align=center class=header>OR</td></tr>
<tr><td class=header colspan=3><br>Add Product As Upsell:</td></tr>
<tr><Td colspan=5>MHL Product No. <input type=text name=upsellPrNo> <!--&nbsp; &nbsp; Sort Order: <input type=text name=addSortOrder value='' size=5>--></td></tr>
<tr><td colspan=6 class=header><HR></td></tr>
	</table>
	
<?php
	
include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	

?>