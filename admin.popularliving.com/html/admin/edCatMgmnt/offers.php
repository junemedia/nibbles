<?php

/*********

Script to Sort Offers In A Category

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Sort Offers In Category";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	

// get offer category name
$categoryQuery = "SELECT title
				  FROM   edOfferCategories
				  WHERE  id = '$id'";
$categoryResult = mysql_query($categoryQuery);
while ($categoryRow = mysql_fetch_object($categoryResult)) {
	$sPageTitle .= " $categoryRow->title";
}

if ($sSaveClose || $sSaveNew) {
	// Change the sort orders
	if(is_array($sortOrder)) {
		while (list($key, $val) = each($sortOrder)) {
			$editQuery = "UPDATE edOfferCategoryRel
							  SET    sortOrder = '$val'
							  WHERE  categoryId = '$id'
							  AND    offerId = '$key'";
			
			// start of track users' activity in nibbles
			$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit Entry: $editQuery\")";
			$rResult = dbQuery($sAddQuery);
			echo  dbError();
			// end of track users' activity in nibbles
			
			$editResult = mysql_query($editQuery);
		}
	}
	// add new offers	
	
	//If offer added by typing offercode
	if (trim($offerCode) != '') {
		//get offerId for this offerCode...
		$tempQuery = "SELECT id
					  FROM   edOffers
					  WHERE  offerCode = '$offerCode'";
		$tempResult = mysql_query($tempQuery) ;
		while ($tempRow = mysql_fetch_object($tempResult)) {
			$offerId = $tempRow->id;
		}
	} else 	if ($addOffer != '') {
	// If offer selected from the selection box to add	
		$offerId = $addOffer;
	}
	
	// check if offer already exists...
	if ($offerId !='') {
	$checkQuery = "SELECT *
					   FROM   edOfferCategoryRel
					   WHERE  categoryId = '$id'
					   AND    offerId = '$offerId'";
	$checkResult = mysql_query($checkQuery);
	if (mysql_num_rows($checkResult) == 0) {
		if (!($addSortOrder)) {
			$addSortOrder = 0;
		}
		$addQuery = "INSERT INTO edOfferCategoryRel(categoryId, offerId, sortOrder)
						 VALUES('$id', '$offerId', '$addSortOrder')";
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add Entry: $addQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		$addResult = mysql_query($addQuery);
	} else {
		$sMessage = "Offer Already Exists In This Category....";
	}
	}
	
	if (is_array($remove)) {
		
		while (list($key, $val) = each($remove)) {
			$deleteQuery = "DELETE FROM edOfferCategoryRel
								WHERE  categoryId = '$id'
								AND    offerId = '$key'";
			
			// start of track users' activity in nibbles
			$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete Entry: $deleteQuery\")";
			$rResult = dbQuery($sAddQuery);
			echo  dbError();
			// end of track users' activity in nibbles
			
			$deleteResult = mysql_query($deleteQuery);
			$sMessage = '';
		}
	}
}

if ($sSaveClose) {
	echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";			
	// exit from this script
	exit();		
}

// Set Default order column
if (!($orderColumn)) {
	$orderColumn = "sortOrder";
	$sortOrderOrder = "ASC";
}
// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
switch ($orderColumn) {
	
	case "sortOrder" :
	$currOrder = $sortOrderOrder;
	$sortOrderOrder = ($sortOrderOrder != "DESC" ? "DESC" : "ASC");
	break;
	case "description" :
	$currOrder = $descriptionOrder;
	$descriptionOrder = ($descriptionOrder != "DESC" ? "DESC" : "ASC");
	break;
	
	default:
	$currOrder = $offerCodeOrder;
	$offerCodeOrder = ($offerCodeOrder != "DESC" ? "DESC" : "ASC");
}

// Select Query to display list of data

$selectQuery = "SELECT edOffers.offerCode, edOffers.headline, edOffers.description, edOfferCategoryRel.offerId, edOfferCategoryRel.sortOrder
					FROM   edOfferCategories, edOfferCategoryRel, edOffers
					WHERE  edOfferCategories.id = '$id'
					AND    edOfferCategories.id = edOfferCategoryRel.categoryId
					AND    edOfferCategoryRel.offerId = edOffers.id
					AND	   CURRENT_DATE BETWEEN edOffers.activationDate AND edOffers.expirationDate";
$selectQuery .= " ORDER BY $orderColumn $currOrder";

$selectResult = mysql_query($selectQuery);
echo mysql_error();

while ($row = mysql_fetch_object($selectResult)) {
	
	// For alternate background color
	if ($bgcolorClass == "ODD") {
		$bgcolorClass = "EVEN";
	} else {
		$bgcolorClass = "ODD";
	}
	$dispHeadline = ascii_encode(substr($row->headline,0,50));
	$dispDescription = ascii_encode(substr($row->description,0,50));
	$offerList .= "<tr class=$bgcolorClass><TD><b>$row->offerCode</b><br>$dispHeadline...</td>
						<td>$dispDescription...</td>
						<TD><input type=text name=sortOrder[".$row->offerId."] value='$row->sortOrder' size=5></td>
						<td><input type=checkbox name=remove[".$row->offerId."]></td>
						</tr>";
}
if (mysql_num_rows($selectResult) == 0) {
	$sMessage = "No Offers In This Category...";
}

//$offersQuery = "SELECT O.*, OC.categoryId, O.id offerId
	//			FROM   Offers O LEFT JOIN OfferCategoryRel OC ON O.id = OC.offerId
		//		WHERE  (OC.offerId IS NULL
			//	||     (OC.offerId = O.id AND OC.categoryId != '$id'))";

$offersQuery = "SELECT O.*, OC.categoryId, O.id offerId
				FROM   edOffers O LEFT JOIN edOfferCategoryRel OC ON O.id = OC.offerId
				AND    OC.categoryId = '$id'
				WHERE  ( OC.offerId IS NULL)
				AND	   CURRENT_DATE BETWEEN O.activationDate AND O.expirationDate
				ORDER BY substring(O.offerCode,1,3) $currOrder, substring(O.offerCode,4)+0 $currOrder";

$offersResult = mysql_query($offersQuery);
//echo mysql_num_rows($offersResult);
$addOfferOptions = "<option value=''>Select Offer To Add";
while ($offersRow = mysql_fetch_object($offersResult)) {
	
	$addOfferOptions .= "<option value='".$offersRow->offerId."'>$offersRow->offerCode - ".substr($offersRow->headline,0,25)."...";
}

// Hidden fields to be passed with form submission
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=id value='$id'>";

$sortLink = $PHP_SELF."?iMenuId=$iMenuId&id=$id";

$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=abandonNew value=' Abandon & New  '>";	

include("../../includes/adminAddHeader.php");
?>


<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $sReloadWindowOpener;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr bgcolor=#FFFFFF><td class=header colspan=4 align=center><?php echo $sPageTitle;?><BR></td></tr>
<tr>	
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=offerCode&offerCodeOrder=<?php echo $offerCodeOrder;?>" class=header>Offer Code</a></td>
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=description&descriptionOrder=<?php echo $descriptionOrder;?>" class=header>Description</td>
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=sortOrder&sortOrderOrder=<?php echo $sortOrderOrder;?>" class=header>Sort Order</td>	
	<td class=header>Remove from this Category</td>
</tr>
<?php echo $offerList;?>
<!--<input type=submit name=saveClose value="Save & Close">-->
<tr><td><BR></td></tr>
<tr><td colspan=4 class=header>Select Offer To Add To This Category:</td></tr>
<tr><Td  colspan=4><select name=addOffer>
<?php echo $addOfferOptions;?>
</select>
</td></tr>
<tr><td class=header><br>Add Offer To This Category:</td></tr>
<tr><Td colspan=3>OfferCode: <input type=text name=offerCode> &nbsp; &nbsp; Sort Order: <input type=text name=addSortOrder value='' size=5></td></tr>
</table>


<?php
	include("../../includes/adminAddFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>