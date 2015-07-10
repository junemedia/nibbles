<?php 

/***********

Script to Manage Site Contents of HandCraftersVillage site

*************/

include("../../../includes/paths.php");

$sPageTitle = "Import Projects From Editorial OFfers";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {		
	
	// SELECT HCV DATABASE
	dbSelect($sGblHcvDBName);
	
	if ($sImport) {
		// get the offers from mars which is only under HCV category
		
		//get category Id of 'uncategorized' category
		
		$sCatQuery = "SELECT id
					  FROM	  categories
					  WHERE   title = 'uncategorized'";
		$rCatResult = dbQuery($sCatQuery);
		while ($oCatRow = dbFetchObject($rCatResult)) {
			$iCategoryId = $oCatRow->id;
		}
		
		$sOffersQuery = "SELECT myfree.Offers.*
						 FROM	myfree.Offers, myfree.OfferCategoryRel, myfree.OfferCategories
						 WHERE  myfree.Offers.id = myfree.OfferCategoryRel.offerId
						 AND	myfree.OfferCategoryRel.categoryId = myfree.OfferCategories.id
						 AND	myfree.OfferCategories.title = 'hcv'";
		$rOffersResult = dbQuery($sOffersQuery) ;
		echo dbError();
		while ($oOffersRow= dbFetchObject($rOffersResult)) {
			
			$iOfferId = $oOffersRow->id;
			$sHeadline = $oOffersRow->headline;
			$sDescription = $oOffersRow->description;			
			
			// check if not assigned to any other category in Offers
			$sCheckQuery = "SELECT myfree.OfferCategoryRel.*
							FROM   myfree.OfferCategoryRel, myfree.OfferCategories
							WHERE  myfree.OfferCategoryRel.categoryId = myfree.OfferCategories.id
							AND	   myfree.OfferCategoryRel.offerId = '$iOfferId'
							AND	   myfree.OfferCategories.title != 'hcv'";
			$rCheckResult = dbQuery($sCheckQuery);
			echo dbError();
			// check if already imported
			$sCheckQuery2 = "SELECT *
							 FROM   craftProjects
							 WHERE  title = \"$sHeadline\""; 
			$rCheckResult2 = dbQuery($sCheckQuery2);
			
			echo dbError();
			if ( dbNumRows($rCheckResult) == 0 && dbNumRows($rCheckResult2) == 0) {
				$sImportQuery = "INSERT INTO craftProjects(title, description)
								 VALUES(\"$sHeadline\", \"$sDescription\")";
				$rImportResult = dbQuery($sImportQuery);
				if ($rImportResult) {
					$iProjectId = dbInsertId();
					
					//set project under uncategorized category
					if ($iProjectId && $iCategoryId) {
					$sInsertQuery2 = "INSERT INTO categoryMap(projectId, categoryId)
									  VALUES('$iProjectId', '$iCategoryId')";
					$rInsertResult2 = dbQuery($sInsertQuery2);
					echo dbError();

				} else {
					echo dbError();
				}
			}
		}
	}
}

	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iParentMenuId value='$iParentMenuId'>
				<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>";
	
	include("$sGblIncludePath/adminHeader.php");
	
	?>
	
<form name=form1 action='<?php echo $PHP_SELF;?>'>

<?php echo $hidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td><b>Note:</b><BR>Projects will be imported into 'uncategorized' category and won't be displayed in the site.
		<BR>You can assign imported projects to other categories to display them.
<tr><th colspan=3 align=left><input type=submit name=sImport value='Import'></th></tr>

</table>

</form>
<?php
// include footer

include("$sGblIncludePath/adminFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}						
?>	

