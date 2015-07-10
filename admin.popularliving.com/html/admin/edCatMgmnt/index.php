<?php

/*********

Script to Display List/Delete Category information

*********/

include("../../includes/paths.php");

$categoryPageLink = "http://www.myfree.com/displayContent.php?content=offers";

$sPageTitle = "Nibbles Editorial Offer Category Management";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($sDelete) {
		// if record deleted...
		// Manage Offers of this category
		//or don't allow to delete if offer exists in this category
		
		$deleteQuery = "DELETE FROM edOfferCategories
			   			WHERE id = '$id'"; 

		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles

		$result = mysql_query($deleteQuery);
		if (!($result)) {
			echo mysql_error();
		}		
		//reset $id to null
		$id = '';
	}
	
	// set default order by column
	if (!($orderColumn)) {
		$orderColumn = "B.category";
		$categoryOrder = "ASC";
	}
	
	switch ($orderColumn) {
		case "A.category" :
		$currOrder = $categoryOrder;
		$categoryOrder = ($categoryOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "title" :
		$currOrder = $titleOrder;
		$titleOrder = ($titleOrder != "DESC" ? "DESC" : "ASC");
		break;
		default:
		$currOrder = $parentCategoryOrder;
		$parentCategoryOrder = ($parentCategoryOrder != "DESC" ? "DESC" : "ASC");
	}
	
	$selectQuery1 = "SELECT *
					 FROM   edOfferCategories
					 WHERE  parentCategory = ''
					 ORDER BY category";
	// If order column is parent category, apply ASC or DESC here in this query
	
	if ($orderColumn == "B.category") {
		$selectQuery1 .= " $currOrder";
	}
	$sTempQuery = $selectQuery1;
	
	$result1 = mysql_query($selectQuery1);
	$i=0;
	echo mysql_error();
	while ($row1 = mysql_fetch_object($result1)) {
		$catId = $row1->id;
		// get sub categories if this is a parent category
		$categoriesArray['id'][$i] = $row1->id;
		
		$categoriesArray['parentCategory'][$i] = "";
		
		$categoriesArray['category'][$i] = $row1->category;
		$categoriesArray['title'][$i] = $row1->title;
		//$categoriesArray[$i]['parentCategory'] = $row1->parentCategory;
		$categoriesArray['frontPageDisplay'][$i] = $row1->frontPageDisplay;
		$categoriesArray['leftMenuDisplay'][$i] = $row1->leftMenuDisplay;
		
		$subCatQuery = "SELECT *
						FROM   edOfferCategories
						WHERE  parentCategory = '$catId'
						ORDER BY category";
		$sTempQuery .= "\n$subCatQuery";
		$subCatResult = mysql_query($subCatQuery);
		if (mysql_num_rows($subCatResult)>0) {
			// Display as parent category in first column
			$categoriesArray['parentCategory'][$i] = "<B>".$categoriesArray['category'][$i]."</B>";
			// Don't display anything in category column of this row
			$categoriesArray['category'][$i] = "";
		}
		while ($subCatRow = mysql_fetch_object($subCatResult)) {
			$i++;
			$categoriesArray['id'][$i] = $subCatRow->id;
			// Display it's parent category in first column
			$categoriesArray['parentCategory'][$i] = $row1->category;
			// Don't display anything in category column of this ros
			$categoriesArray['category'][$i] = $subCatRow->category;
			$categoriesArray['title'][$i] = $subCatRow->title;
			//$categoriesArray[$i]['parentCategory'] = $row1->parentCategory;
			$categoriesArray['frontPageDisplay'][$i] = $row1->frontPageDisplay;
			$categoriesArray['leftMenuDisplay'][$i] = $row1->leftMenuDisplay;
			
		}
		$i++;
	}
	
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Display Category: $sTempQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
	
	
	
	
	switch ($orderColumn) {
		
		case "A.category" :
		if ($currOrder == "DESC") {
			array_multisort($categoriesArray['category'], SORT_DESC, $categoriesArray['parentCategory'], $categoriesArray['id'], $categoriesArray['title'], $categoriesArray['frontPageDisplay'], $categoriesArray['leftMenuDisplay']);
		} else {
			array_multisort($categoriesArray['category'],  $categoriesArray['parentCategory'], $categoriesArray['id'], $categoriesArray['title'], $categoriesArray['frontPageDisplay'], $categoriesArray['leftMenuDisplay']);
		}
		break;
		
		case "title" :
		if ($currOrder == "DESC") {
			array_multisort($categoriesArray['title'], SORT_DESC, $categoriesArray['category'],  $categoriesArray['parentCategory'], $categoriesArray['id'], $categoriesArray['title'], $categoriesArray['frontPageDisplay'], $categoriesArray['leftMenuDisplay']);
		} else {
			array_multisort($categoriesArray['title'], $categoriesArray['category'],  $categoriesArray['parentCategory'], $categoriesArray['id'], $categoriesArray['title'], $categoriesArray['frontPageDisplay'], $categoriesArray['leftMenuDisplay']);
		}
		break;
		// Don't make change in array if Order by is ParentCategory,
		// to display the list properly as parent category and then after subcategories under it
		// that's why, parent category order is directly applied to the query
		//default:
		//array_multisort($categoriesArray['parentCategory'], $categoriesArray['category'], $categoriesArray['title'], $categoriesArray['id'], $categoriesArray['title'], $categoriesArray['frontPageDisplay'], $categoriesArray['leftMenuDisplay']);
	}
	
	
	for ($i = 0; $i < count($categoriesArray['id']); $i++) {
		// For alternate background color
		if ($bgcolorClass == "ODD") {
			$bgcolorClass = "EVEN";
		} else {
			$bgcolorClass = "ODD";
		}
		$categoryList .= "<tr class=$bgcolorClass>
					<td>".$categoriesArray['parentCategory'][$i]."</td>
					<td>".$categoriesArray['category'][$i]."</td>					
					<td>".$categoriesArray['title'][$i]."</td><td>
					<a href='JavaScript:void(window.open(\"addCategory.php?iMenuId=$iMenuId&id=".$categoriesArray['id'][$i]."\", \"AddCategory\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					&nbsp;<a href='JavaScript:confirmDelete(this,".$categoriesArray['id'][$i].");' >Delete</a>		
					&nbsp;<a href='JavaScript:void(window.open(\"offers.php?iMenuId=$iMenuId&id=".$categoriesArray['id'][$i]."\",\"\",\"scrollbars=yes, resizable=yes, status=yes\"));'>Offers</a> 
					&nbsp;<a href='JavaScript:void(window.open(\"$categoryPageLink&offerCat=".$categoriesArray['id'][$i]."\",\"\",\"\"));'>Front End</a>
					</td></tr>";
	}
	
	// Display Add Button if user has the permission and Not already clicked on Add Button
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addCategory.php?iMenuId=$iMenuId\", \"AddCategory\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
	
	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";
	
	$sortLink =$PHP_SELF."?iMenuId=$iMenuId";
	
	
	include("../../includes/adminHeader.php");	
	
?>


<script language=JavaScript>
				function confirmDelete(form1,id)
				{
					if(confirm('Are you sure to delete this record ?'))
					{							
						document.form1.elements['sDelete'].value='Delete';
						document.form1.elements['id'].value=id;
						document.form1.submit();								
					}
				}						
</script>
		
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $hidden;?>
<input type=hidden name=sDelete>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><Td><?php echo $addButton;?></td></tr>
<tr>		
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=B.category&parentCategoryOrder=<?php echo $parentCategoryOrder;?>" class=header>Parent Category</a></td>
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=A.category&categoryOrder=<?php echo $categoryOrder;?>" class=header>Category</a></td>
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=title&titleOrder=<?php echo $titleOrder;?>" class=header>Title</td>
	<th>&nbsp; </th>
</tr>
<?php echo $categoryList;?>
<tr><Td><?php echo $addButton;?></td></tr>
</table>
</form>



<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>