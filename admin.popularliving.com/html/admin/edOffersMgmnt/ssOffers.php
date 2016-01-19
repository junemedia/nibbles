<?php

/*********

Script to Sort Offers Under Special Status

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Nibbles Sort Offers Under Special Status";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {

	
if ($sSaveClose || $sSaveNew) {
	// Change the sort orders
	if(is_array($sortOrder)) {
		
		while (list($key, $val) = each($sortOrder)) {				
			$editQuery = "UPDATE edOffers
							  SET    ssSortOrder = '$val'
							  WHERE  id = '$key'";
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
	$checkQuery = "SELECT *
				   FROM   edOffers
				   WHERE  specialStatus = '$specialStatus'
				   AND    id = '$offerId'";
	$checkResult = mysql_query($checkQuery);
	
	echo mysql_error();
	if (mysql_num_rows($checkResult) == 0) {
		if (!($addSortOrder)) {
			$addSortOrder = 0;
		}
		$addQuery = "UPDATE edOffers 
					 SET    specialStatus = '$specialStatus',							
							ssSortOrder = '$addSortOrder'
					 WHERE  id = '$offerId'";
		$addResult = mysql_query($addQuery);
		
	} //else {
//		$message = "Offer Already Exists Under This Special Status....";
	//}	
	
	if (is_array($remove)) {
		
		while (list($key, $val) = each($remove)) {
			$deleteQuery = "UPDATE edOffers
							SET    specialStatus = ''
							WHERE  id = '$key'";
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
	$orderColumn = "ssSortOrder";
	$sortOrderOrder = "ASC";
}
// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
switch ($orderColumn) {
	
	case "ssSortOrder" :
	$currOrder = $sortOrderOrder;
	$sortOrderOrder = ($sortOrderOrder != "DESC" ? "DESC" : "ASC");
	break;
	/*case "description" :
	$currOrder = $descriptionOrder;
	$descriptionOrder = ($descriptionOrder != "DESC" ? "DESC" : "ASC");
	break;*/
	
	default:
	$currOrder = $offerCodeOrder;
	$offerCodeOrder = ($offerCodeOrder != "DESC" ? "DESC" : "ASC");
}

// Select Query to display list of data

$selectQuery = "SELECT *
				FROM    edOffers
				WHERE  specialStatus != ''";
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
	//$dispDescription = ascii_encode(substr($row->description,0,50));
	$offerList .= "<tr class=$bgcolorClass><TD><b>$row->offerCode</b><br>$dispHeadline...</td>";
	
	$specialStatus = $row->specialStatus;
	
	switch ($specialStatus) {
		case "hotBargain":
		$offerList .= "<td><input type=text name=sortOrder[".$row->id."] value='$row->ssSortOrder' size=5></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
		//$hotBargainSelected = "selected";
		break;
		case "freeBies":		
		$offerList .= "<td>&nbsp;</td><td><input type=text name=sortOrder[".$row->id."] value='$row->ssSortOrder' size=5></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";
//		$freeBiesSelected = "selected";		
		break;
		case "featuredSweepstake":
		$offerList .= "<td>&nbsp;</td><td>&nbsp;</td><td><input type=text name=sortOrder[".$row->id."] value='$row->ssSortOrder' size=5></td><td>&nbsp;</td><td>&nbsp;</td>";
		//$featuredSweepstakeSelected = "selected";
		break;		
		case "featuredOffer":
		$offerList .= "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td><input type=text name=sortOrder[".$row->id."] value='$row->ssSortOrder' size=5></td><td>&nbsp;</td>";
		break;
		case "freeCooking":
		$offerList .= "<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td><input type=text name=sortOrder[".$row->id."] value='$row->ssSortOrder' size=5></td>";
		
		//$featuredOfferSelected = "selected";	
	}
		
	$offerList .= "<td><input type=checkbox name=remove[".$row->id."]></td></tr>";
						

}
if (mysql_num_rows($selectResult) == 0) {
	$sMessage = "No Offers With Special Status...";
}

//$offersQuery = "SELECT O.*, OC.categoryId, O.id offerId
	//			FROM   Offers O LEFT JOIN OfferCategoryRel OC ON O.id = OC.offerId
		//		WHERE  (OC.offerId IS NULL
			//	||     (OC.offerId = O.id AND OC.categoryId != '$id'))";

$offersQuery = "SELECT *
				FROM   edOffers 	
				WHERE  specialStatus = ''
				ORDER BY substring(offerCode,1,3) $currOrder, substring(offerCode,4)+0 $currOrder";

$offersResult = mysql_query($offersQuery);
//echo mysql_num_rows($offersResult);
$addOfferOptions = "<option value=''>Select Offer To Add Under Special Status";
while ($offersRow = mysql_fetch_object($offersResult)) {
	
	$addOfferOptions .= "<option value='".$offersRow->id."'>$offersRow->offerCode - ".substr($offersRow->headline,0,25)."...";
}

// Which special status option is selected
$featuredOfferSelected = '';
$hotBargainSelected = '';
$featuredSweepstakeSelected = '';
$freeBiesSelected = "";
$freeCookingSelected = "";

switch ($specialStatus) {
	case "featuredSweepstake":
	$featuredSweepstakeSelected = "selected";
	break;
	case "hotBargain":
	$hotBargainSelected = "selected";
	break;
	case "featuredOffer":
	$featuredOfferSelected = "selected";
	break;
	case "freeBies":
	$freeBiesSelected = "selected";		
	break;
	case "freeCooking":
	$freeCookingSelected = "selected";		
	
	
}

$specialStatusOptions = "<option value=''>
						<option value='featuredOffer' $featuredOfferSelected>Featured Offer
						<option value='hotBargain' $hotBargainSelected>Today's Hot Bargains
						<option value='featuredSweepstake' $featuredSweepstakeSelected>Featured Sweepstake
						<option value='freeBies' $freeBiesSelected>Hot FreeBies
						<option value='freeCooking' $freeCookingSelected>Today's Free Cooking Offer";
						

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
<tr bgcolor=#FFFFFF><td class=header colspan=7 align=center><?php echo $sPageTitle;?><BR></td></tr>
<tr>	
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=offerCode&offerCodeOrder=<?php echo $offerCodeOrder;?>" class=header>Offer Code</a></td>
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=ssSortOrder&sortOrderOrder=<?php echo $sortOrderOrder;?>" class=header>Hot Bargain</td>	
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=ssSortOrder&sortOrderOrder=<?php echo $sortOrderOrder;?>" class=header>Freebies</td>	
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=ssSortOrder&sortOrderOrder=<?php echo $sortOrderOrder;?>" class=header>Featured Sweepstakes</td>	
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=ssSortOrder&sortOrderOrder=<?php echo $sortOrderOrder;?>" class=header>Featured Offer</td>	
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=ssSortOrder&sortOrderOrder=<?php echo $sortOrderOrder;?>" class=header>Free Cooking Offer</td>	
	<td class=header>Remove from special status</td>
</tr>
<?php echo $offerList;?>
<!--<input type=submit name=saveClose value="Save & Close">-->
<tr><td><BR></td></tr>
<tr><td colspan=7 class=header>Select Offer To Add Under Special Status:</td></tr>
<tr><Td  colspan=7><select name=addOffer>
<?php echo $addOfferOptions;?>
</select>
</td></tr>
<tr><td class=header colspan=6><br>Add Offer Under Special Status:</td></tr>
<tr><Td colspan=7>OfferCode: <input type=text name=offerCode> &nbsp; &nbsp; Sort Order: <input type=text name=addSortOrder value='' size=5></td></tr>
<tr><td colspan=7>Special Status: <select name=specialStatus>
		<?php echo $specialStatusOptions;?>
		</select></td></tr>
</table>


<?php
	include("../../includes/adminAddFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>
