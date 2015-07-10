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

		$sOffersQuery = "SELECT nibbles.edOffers.url, nibbles.edOffers.headline, nibbles.edOffers.description, nibbles.edOfferCategoryRel.offerId
						FROM	nibbles.edOffers, nibbles.edOfferCategoryRel, nibbles.edOfferCategories
						WHERE  nibbles.edOffers.id = nibbles.edOfferCategoryRel.offerId
						AND	nibbles.edOfferCategoryRel.categoryId = nibbles.edOfferCategories.id
						AND	nibbles.edOfferCategories.title = 'hcv'";
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Import: $sOffersQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		$rOffersResult = dbQuery($sOffersQuery) ;
		echo dbError();
		$iCount = 0;
		while ($oOffersRow= dbFetchObject($rOffersResult)) {

			$iOfferId = $oOffersRow->id;
			$sHeadline = $oOffersRow->headline;
			$sDescription = $oOffersRow->description;
			$sUrl = $oOffersRow->url;


			// check if already imported
			$sCheckQuery = "SELECT *
							 FROM   craftProjects
							 WHERE  title = \"$sHeadline\""; 
			$rCheckResult = dbQuery($sCheckQuery);

			echo dbError();
			if ( dbNumRows($rCheckResult) == 0 ) {
				$sImportQuery = "INSERT INTO craftProjects(title, description, link)
								 VALUES(\"$sHeadline\", \"$sDescription\", \"$sUrl\")";
				$rImportResult = dbQuery($sImportQuery);
				if ($rImportResult) {


					$sCheckQuery = "SELECT id
								   FROM   craftProjects
								   WHERE  title = \"$sHeadline\"
								   AND description = \"$sDescription\"
								   AND link = \"$sUrl\""; 
					$rCheckResult = dbQuery($sCheckQuery);
					$sRow = dbFetchObject($rCheckResult);

					$iProjectId = $sRow->id;

					//set project under uncategorized category
					if ($iProjectId && $iCategoryId) {
						$sInsertQuery2 = "INSERT INTO categoryMap(projectId, categoryId)
									  VALUES('$iProjectId', '$iCategoryId')";
						$rInsertResult2 = dbQuery($sInsertQuery2);
						echo dbError();

						$iCount++;
					} else {
						echo dbError();
					}
				}
			}
		}
		$sMessage = "$iCount Projects Imported";
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

