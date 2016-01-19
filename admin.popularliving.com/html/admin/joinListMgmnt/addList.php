<?php

/*********

Script to Display Add/Edit Join List

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Nibbles Join Lists - Add/Edit Join List";



if (hasAccessRight($iMenuId) || isAdmin()) {
	
if ($sSaveClose || $sSaveNew) {
	$sCategoryQuery = "SELECT id, title
				  	   FROM   joinCategories
		 		  	   ORDER BY title";
	$rCategoryResult = dbQuery($sCategoryQuery);
	$i = 0;
	while ($oCategoryRow = dbFetchObject($rCategoryResult)) {
		
		// prepare Categories of this offer
		$sCheckboxName = "iCategory_".$oCategoryRow->id;		
		$iCheckboxValue = $$sCheckboxName;
		
		if ($iCheckboxValue) {
			
			$aCategoriesArray[$i] = $iCheckboxValue;
			$sCategoriesString .= $iCheckboxValue.",";
			$i++;
		}
	}
	
	if (($sSaveClose || $sSaveNew) && !($iId)) {
		// if new email content added
		
		// get next id. don't use auto increment because it will give list no greater than 900 and we want that for special only
		$iMaxId = 0;
		$sMaxQuery = "SELECT max(id) as maxId
					  FROM	 joinLists
					  WHERE  id < 900";
		$rMaxResult = dbQuery($sMaxQuery);
		echo dbError();
		while ($oMaxRow = dbFetchObject($rMaxResult)) {
			$iMaxId = $oMaxRow->maxId;
		}
		$iNextId = $iMaxId + 1;
		//echo $iNextId;
		$sAddQuery = "INSERT INTO joinLists (id, shortName, title, description, lyrisName, requiresConf, isActive, prechecked, sortOrder) 
					 VALUES ('$iNextId', '', \"$sTitle\", \"$sDescription\", '$sLyrisName', '$sRequiresConf', '$sIsActive', '$sPrechecked', '0')";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: " . addslashes($sAddQuery) . "\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sAddQuery);
		
		// Insert into joinListCategories according to category checkboxes checked
		if ($rResult) {
			if (count($aCategoriesArray) > 0) {
				for ($i = 0; $i < count($aCategoriesArray); $i++) {
					$sInsertQuery = "INSERT INTO joinListCategories(joinListId, categoryId, sortOrder)
									VALUES('$iNextId', '".$aCategoriesArray[$i]."','0')";		
					$rInsertResult = dbQuery($sInsertQuery);
					
					if (!($rInsertResult)) {
						echo dbError();
					}
				}
			}
		}
		
	} else if (($sSaveClose || $sSaveNew) && ($iId)) {
				
		$sEditQuery = "UPDATE joinLists
					  SET title = \"$sTitle\",
						  description = \"$sDescription\",
						  lyrisName = \"$sLyrisName\",
						  requiresConf = '$sRequiresConf',
						  isActive = '$sIsActive',
						  prechecked = '$sPrechecked'						  
					  WHERE id = '$iId'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: " . addslashes($sEditQuery) . "\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$rResult = dbQuery($sEditQuery);
		
		if (!($rResult)) {
			$sMessage = dbError();
		}
		
		// Delete records from joinListcategories with the categories which are not checked
		
		// remove last comma from the categories list
		$sCategoriesString = substr($sCategoriesString, 0, strlen($sCategoriesString)-1);
		// Delete if any category unchecked for the offer to be displayed in.
		$sDeleteQuery = "DELETE FROM joinListCategories
								WHERE  joinListId = '$iId'";
		if ($sCategoriesString != '') {
			$sDeleteQuery .= " AND    categoryId NOT IN (".$sCategoriesString.")";
		}
		
		$rDeleteResult = dbQuery($sDeleteQuery);
		
				
		if (count($aCategoriesArray) > 0) {
			for ($i = 0; $i<count($aCategoriesArray); $i++) {
				$sCheckQuery = "SELECT *
							   FROM   joinListCategories
							   WHERE  categoryId = ".$aCategoriesArray[$i]."
							   AND    joinListId = '$iId'";
				$rCheckResult = dbQuery($sCheckQuery);
				if (dbNumRows($rCheckResult) == 0) {
					// INSERT OfferCategoryRel record
					
					$sInsertQuery = "INSERT INTO joinListCategories (categoryId, joinListId, sortOrder)
											VALUES('".$aCategoriesArray[$i]."', '$iId', '0')";
					$rInsertResult = dbQuery($sInsertQuery);					
				}
			}
		}
	}
	
	if ($sSaveClose) {
		if ($bKeepValues != true) {
			echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";			
			// exit from this script
			exit();
		}
	} else if ($sSaveNew) {
		if ($bKeepValues != true) {
			$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";	
			
			$sTitle = '';
			$sDescription = '';
			$sLyrisName = '';
			$sRequiresConf = '';
			$sIsActive = '';
			$sPrechecked = '';
		}
	}
}

if ($iId) {
	
	// If Clicked to edit, get the data to display in fields
	
	$sSelectQuery = "SELECT * FROM joinLists
				    WHERE  id = '$iId'";
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$sTitle = ascii_encode($oSelectRow->title);
		$sDescription = ascii_encode($oSelectRow->description);
		$sLyrisName = $oSelectRow->lyrisName;
		$sRequiresConf = $oSelectRow->requiresConf;
		$sIsActive = $oSelectRow->isActive;
		$sPrechecked = $oSelectRow->prechecked;		
		
	}
} else {
	
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}


if ($sRequiresConf) {
	$sRequiresConfChecked = "checked";
}
if ($sIsActive) {
	$sIsActiveChecked = "checked";
}
if ($sPrechecked) {
	$sPrecheckedChecked = "checked";
}


// Prepare checkboxes for Join List Categories
$sCategoryQuery = "SELECT *
				  FROM   joinCategories 
				  ORDER BY title";
$rCategoryResult = dbQuery($sCategoryQuery);
echo dbError();
$j = 0;
while ($oCategoryRow = dbFetchObject($rCategoryResult)) {
	$iCategoryId = $oCategoryRow->id;
	$sCategoryTitle = $oCategoryRow->title;
	
	$sListQuery = "SELECT joinListId
				   FROM   joinCategories, joinListCategories
				   WHERE  joinListCategories.categoryId = '$iCategoryId'
				   AND    joinListCategories.joinListId = '$iId'
				   AND   joinListCategories.categoryId = joinCategories.id";
	
	$rListResult = dbQuery($sListQuery);
	if(dbNumRows($rListResult)>0){
		$sCategoryChecked  = "checked";
	} else {
		$sCategoryChecked = "";
	}
	
	if($j%3 == 0) {
		if($j != 0) {
			$sCategoryCheckboxes .= "</tr>";
		}
		$sCategoryCheckboxes .= "<tr>";
	}
	/*
	// check if this is a parent category
	$sCheckQuery = "SELECT *
	FROM   OfferCategories
	WHERE  parentCategory = '$categoryId'";
	$checkResult = mysql_query($checkQuery);
	if (mysql_num_rows($checkResult)>0 ) {
	$category = "<B>".$category."</B>";
	}
	*/
	$sCategoryCheckboxes .= "<td width=5% valign=top><input type=checkbox name='iCategory_".$oCategoryRow->id."' value='".$oCategoryRow->id."' $sCategoryChecked></td><td  width=28%>$sCategoryTitle</td>";
	$j++;
}
$sCategoryCheckboxes .= "</tr>";


// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

include("../../includes/adminAddHeader.php");
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><TD>Title</td><td><input type=text name=sTitle value='<?php echo $sTitle;?>'></td></tr>
		<tr><TD>Description</td><td><textarea name=sDescription rows=4 cols=40><?php echo $sDescription;?></textarea></td></tr>
		<tr><TD>Lyris Name</td><td><input type=text name=sLyrisName value='<?php echo $sLyrisName;?>'></td></tr>
		<tr><td colspan=2 class=header>Below section does not apply to special lists such as total.</td></tr>		
		<tr><TD>Requires Confirmation</td><td><input type=checkbox name=sRequiresConf value='1' <?php echo $sRequiresConfChecked;?>></td></tr>
		<tr><TD>Is Active</td><td><input type=checkbox name=sIsActive value='1' <?php echo $sIsActiveChecked;?>></td></tr>
		<tr><TD>Prechecked</td><td><input type=checkbox name=sPrechecked value='1' <?php echo $sPrecheckedChecked;?>></td></tr>		
	</table>	
	<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>	
	<tr>
	<?php echo $sCategoryCheckboxes;?>
	</tr>
</table>

<script language=JavaScript>
if (document.form1.iId.value > 900 && document.form1.iId.value < 1000) {

						for(j=0;j<document.form1.elements.length;j++) {
							var eleName = document.form1.elements[j].name;
							if ( eleName != 'sTitle' && eleName != 'sDescription' &&  eleName != 'iId' &&  eleName != 'iMenuId'  &&  eleName != 'sLyrisName') {
								 document.form1.elements[j].disabled = true;
								
								
							}
						}
					
}
</script>
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>