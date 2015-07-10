<?php

/*********

Script to sort offers under a category

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Nibbles Sort Offers In Category ";

if (hasAccessRight($iMenuId) || isAdmin()) {	
// get category name
$sCategoryQuery = "SELECT title
				   FROM   categories
				   WHERE  id = '$iId'";
$rCategoryResult = dbQuery($sCategoryQuery);
echo dbError();
while ($oCategoryRow = dbFetchObject($rCategoryResult)) {
	$sPageTitle .= " $oCategoryRow->title";
}


if ($sSaveClose || $sSaveContinue) {
	// Change the sort orders
	if(is_array($aSortOrder)) {
		while (list($key, $val) = each($aSortOrder)) {
			$sEditQuery = "UPDATE categoryMap
						   SET    sortOrder = '$val',
				 			 	  isTopDisplay = '".$aIsTopDisplay[$key]."',
								  precheck = '".$aPrecheck[$key]."'
						   WHERE  categoryId = '$iId'
						   AND    offerCode = '$key'";

			
			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $sEditQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$rEditResult = dbQuery($sEditQuery);
		}
	}
	
	// If New Join List added to this category
	
	// check if join list already exists...
	if ($sOfferCode !='') {
		
		$sCheckQuery = "SELECT *
				    FROM   categoryMap
					WHERE  categoryId = '$iId'
					AND    offerCode = '$sOfferCode'";
		$rCheckResult = dbQuery($sCheckQuery);
		if (dbNumRows($rCheckResult) == 0) {
			if (!($iAddSortOrder)) {
				$iAddSortOrder = 0;
			}
			
			$sAddQuery = "INSERT INTO categoryMap(categoryId, offerCode, sortOrder, isTopDisplay, precheck)
						 VALUES('$iId', '$sOfferCode', '$iAddSortOrder', '', '')";

			
			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $sAddQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$rAddResult = dbQuery($sAddQuery);
			echo dbError();
		} else {
			$sMessage = "Offer Already Exists In This Category....";
		}
	}
	
	if (is_array($aRemove)) {
		
		while (list($key, $val) = each($aRemove)) {
			$sDeleteQuery = "DELETE FROM categoryMap
								WHERE  categoryId = '$iId'
								AND    offerCode = '$key'";

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$rDeleteResult = dbQuery($sDeleteQuery);
			$sMessage = '';
		}
	}
}

if ($saveClose) {
	echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";			
	// exit from this script
	exit();
}


// Set Default order column
if (!($sOrderColumn)) {
	$sOrderColumn = "sortOrder";
	$sSortOrderOrder = "ASC";
}
// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
switch ($sOrderColumn) {
	
	case "offerCode" :
	$sCurrOrder = $sOfferCodeOrder;
	$sOfferCodeOrder = ($sOfferCodeOrder != "DESC" ? "DESC" : "ASC");	
	break;	
	case "isTopDisplay" :
	$sCurrOrder = $sIsTopDisplayOrder;
	$sIsTopDisplayOrder = ($sIsTopDisplayOrder != "DESC" ? "DESC" : "ASC");	
	break;	
	default:
	$sCurrOrder = $sSortOrderOrder;
	$sSortOrderOrder = ($sSortOrderOrder != "DESC" ? "DESC" : "ASC");
}

// Select Query to display list of data

$sSelectQuery = "SELECT offers.offerCode, offers.name, categoryMap.sortOrder, categoryMap.isTopDisplay, categoryMap.precheck
					FROM   categories, categoryMap, offers
					WHERE  categories.id = '$iId'
					AND    categories.id = categoryMap.categoryId
					AND    categoryMap.offerCode = offers.offerCode ";
$sSelectQuery .= " ORDER BY $sOrderColumn $sCurrOrder";

$rSelectResult = dbQuery($sSelectQuery);

while ($oRow = dbFetchObject($rSelectResult)) {
	
	// For alternate background color
	if ($sBgcolorClass == "ODD") {
		$sBgcolorClass = "EVEN";
	} else {
		$sBgcolorClass = "ODD";
	}
	$sTitle = ascii_encode(substr($oRow->name,0,50));
	if ($oRow->isTopDisplay) {
		$sTopDisplayChecked = "checked";
	} else {
		$sTopDisplayChecked = "";
	}
	if ($oRow->precheck) {
		$sPrecheckChecked = "checked";
	} else {
		$sPrecheckChecked = "";
	}
	$sOfferList .= "<tr class=$sBgcolorClass><TD><b>$oRow->offerCode</b><br>$sTitle</td>
						<TD><input type=text name=aSortOrder[".$oRow->offerCode."] value='$oRow->sortOrder' size=5></td>
						<td><input type=checkbox value='1' name=aIsTopDisplay[".$oRow->offerCode."] $sTopDisplayChecked></td>
						<td><input type=checkbox value='1' name=aPrecheck[".$oRow->offerCode."] $sPrecheckChecked></td>
						<td><input type=checkbox name=aRemove[".$oRow->offerCode."]></td>
						</tr>";
}
if (dbNumRows($rSelectResult) == 0) {
	$sMessage = "No Offers In This Category...";
}


$sOffersQuery = "SELECT O.*, CM.categoryId
				FROM   offers O LEFT JOIN categoryMap CM ON O.offerCode = CM.offerCode
				AND    CM.categoryId = '$iId'
				WHERE  ( CM.offerCode IS NULL)
				ORDER BY O.offerCode";

$rOfferResult = dbQuery($sOffersQuery);
//echo $sOffersQuery.mysql_error().mysql_num_rows($rOfferResult);
$sAddOfferOptions = "<option value=''>Select Offer To Add";
while ($oOfferRow = dbFetchObject($rOfferResult)) {	
	$sAddOfferOptions .= "<option value='".$oOfferRow->offerCode."'>$oOfferRow->offerCode - $oOfferRow->name";
}

$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iId=$iId";

$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	


// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

include("../../includes/adminAddHeader.php");
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr>	
	<TD class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=offerCode&sOfferCodeOrder=<?php echo $sOfferCodeOrder;?>" class=header>Offer</a></td>
	<TD class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=sortOrder&sSortOrderOrder=<?php echo $sSortOrderOrder;?>" class=header>Sort Order</td>	
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=isTopDisplay&sIsTopDisplayOrder=<?php echo $sIsTopDisplayOrder;?>" class=header>Top Display</a></td>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=precheck&sPrecheckOrder=<?php echo $sPrecheckOrder;?>" class=header>Precheck</a></td>
	<td class=header>Remove from this Category</td>
</tr>
<?php echo $sOfferList;?>
<!--<input type=submit name=saveClose value="Save & Close">-->
<tr><td><BR></td></tr>
<tr><td colspan=4 class=header>Select Offer To Add To This Category:</td></tr>
<tr><Td  colspan=4><select name=sOfferCode>
<?php echo $sAddOfferOptions;?>
</select> &nbsp; &nbsp; Sort Order: <input type=text name=iAddSortOrder value='' size=5></td></tr>
</table>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD align=center >
		<input type=submit name=sSaveClose value='Save & Close'> &nbsp; &nbsp; 
		<input type=button name=sAbandonClose value='Abandon & Close' onclick="self.close();" >
		<BR>	<BR>
		<input type=submit name=sSaveContinue value='Save & Continue'> &nbsp; &nbsp; 
		
		<input type=reset name=sAbandonContinue value='Abandon & Continue'>
		</td>
	</tr>	
	</table>
<?php
include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>