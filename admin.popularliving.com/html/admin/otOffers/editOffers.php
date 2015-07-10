<?php

/*********

Script to sort/add/remove offers under a page

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();


$sPageTitle = "Nibbles Sort/Add/Remove Offers In OT Page ";

if (hasAccessRight($iMenuId) || isAdmin()) {

	// get page name
	$sPageQuery = "SELECT pageName
			   FROM   otPages
			   WHERE  id = '$iId'";
	$rPageResult = dbQuery($sPageQuery);
	echo dbError();
	while ($oPageRow = dbFetchObject($rPageResult)) {
		$sPageTitle .= " $oPageRow->pageName";
	}

	if ($sSaveClose || $sSaveContinue) {
		// Change the sort orders
		if(is_array($aSortOrder)) {
			while (list($key, $val) = each($aSortOrder)) {
				$sEditQuery = "UPDATE pageMap
						   SET    sortOrder = '$val',
								  isTopDisplay = '".$aIsTopDisplay[$key]."',
								  precheck = '".$aPrecheck[$key]."'
						   WHERE  pageId = '$iId'
						   AND    offerCode = '$key'";
				$rEditResult = dbQuery($sEditQuery);

				// start of track users' activity in nibbles 
				$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $sEditQuery\")"; 
				$rLogResult = dbQuery($sLogAddQuery); 
				echo  dbError(); 
				// end of track users' activity in nibbles		
			
			
			}
		}

		// If New offer added to this page

		// check if offer already exists...
		if ($sOfferCode !='') {

			$sCheckQuery = "SELECT *
				 	    FROM   pageMap
						WHERE  pageId = '$iId'
						AND    offerCode = '$sOfferCode'";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult) == 0) {
				if (!($iAddSortOrder)) {
					$iAddSortOrder = 0;
				}

				$sAddQuery = "INSERT IGNORE INTO pageMap(pageId, offerCode, sortOrder, isTopDisplay, precheck)
						 VALUES('$iId', '$sOfferCode', '$iAddSortOrder', '', '')";

				// start of track users' activity in nibbles 
				$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View report: $sAddQuery\")"; 
				$rLogResult = dbQuery($sLogAddQuery); 
				echo  dbError(); 
				// end of track users' activity in nibbles		
				
				
				$rAddResult = dbQuery($sAddQuery);
				echo dbError();
			} else {
				$sMessage = "Offer Already Exists In This Page....";
			}
		}

		if (is_array($aRemove)) {

			while (list($key, $val) = each($aRemove)) {
				$sDeleteQuery = "DELETE FROM pageMap
							 WHERE  pageId = '$iId'
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

	if ($sSaveClose) {
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
		case "precheck" :
		$sCurrOrder = $sPrecheckOrder;
		$sPrecheckOrder = ($sPrecheckOrder != "DESC" ? "DESC" : "ASC");
		break;
		default:
		$sCurrOrder = $sSortOrderOrder;
		$sSortOrderOrder = ($sSortOrderOrder != "DESC" ? "DESC" : "ASC");
	}

	if( $iId ) {
		// Select Query to display list of data

		$sSelectQuery = "SELECT offers.offerCode, offers.name, pageMap.sortOrder, pageMap.isTopDisplay,
						offers.mode, offers.isLive, offerCompanies.creditStatus, pageMap.precheck
					FROM   otPages, pageMap, offers, offerCompanies
					WHERE  otPages.id = '$iId'
					AND    otPages.id = pageMap.pageId
					AND    offers.companyId = offerCompanies.id
					AND    pageMap.offerCode = offers.offerCode ";
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

			$sMode = $oRow->mode;
			$iIsLive = $oRow->isLive;
			$sCreditStatus = $oRow->creditStatus;
			if ($sMode == 'A' && $iIsLive && $sCreditStatus == 'ok') {
				$sTempOfferCode = "* ".$oRow->offerCode;
			} else {
				$sTempOfferCode = $oRow->offerCode;
			}

			$sOfferList .= "<tr class=$sBgcolorClass><TD><b>$sTempOfferCode</b><br>$sTitle</td>
						<TD><input type=text name=aSortOrder[".$oRow->offerCode."] value='$oRow->sortOrder' size=5></td>
						<td><input type=checkbox value='1' name=aIsTopDisplay[".$oRow->offerCode."] $sTopDisplayChecked></td>
						<td><input type=checkbox value='1' name=aPrecheck[".$oRow->offerCode."] $sPrecheckChecked></td>
						<td><input type=checkbox name=aRemove[".$oRow->offerCode."]></td>
						</tr>";
		}
		if (dbNumRows($rSelectResult) == 0) {
			$sMessage = "No Offers In This Page...";
		}


		$sOffersQuery = "SELECT O.offerCode, O.name, PM.pageId
				FROM   offers O LEFT JOIN pageMap PM ON O.offerCode = PM.offerCode
				AND    PM.pageId = '$iId'
				WHERE  ( PM.offerCode IS NULL)
				ORDER BY O.offerCode";

		$rOfferResult = dbQuery($sOffersQuery);
		//echo $sOffersQuery.mysql_error().mysql_num_rows($rOfferResult);
		$sAddOfferOptions = "<option value=''>Select Offer To Add";
		while ($oOfferRow = dbFetchObject($rOfferResult)) {
			$sAddOfferOptions .= "<option value='".$oOfferRow->offerCode."'>$oOfferRow->offerCode - $oOfferRow->name";
		}
	}
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iId=$iId";

	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	

	// Prepare data for the "otPages" dropdown.
	$sOtPagesQuery = "SELECT id, pageName FROM otPages ORDER BY pageName";
	$rOtPagesResult = dbQuery($sOtPagesQuery);

	while ($oRow = dbFetchObject($rOtPagesResult)) {
		if( $oRow->id == $iId ) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		$sOtPagesOptions .= "<option value='$oRow->id' $sSelected >$oRow->pageName";
	}
	// End prepare data for "otPages" dropdown.


	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

	include("../../includes/adminHeader.php");

	if( $iId ) {
?>
<script language="javascript">

function funcChangeOtPage() {
	var iOtPageSelIndex = document.form1.iOtPageId.selectedIndex;
	if (iOtPageSelIndex !=0) {
		var iOtPageSel = document.form1.iOtPageId.options[iOtPageSelIndex].value;
		var newLink = '<?php echo "$sGblAdminSiteRoot/otOffers/editOffers.php?iMenuId=$iMenuId&iId=";?>' + iOtPageSel;
			window.location.replace(newLink, '',"height=450, width=600, scrollbars=yes, resizable=yes, status=yes");	
		return true;
	}
}

</script>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=4 class=header><!--Select OT Page To Work On:--></td></tr>
<tr><Td  colspan=4><!--<select name=iOtPageId onchange="funcChangeOtPage();">
<?php echo $sOtPagesOptions;?>
</select>--></td></tr>
<tr><td colspan=4>* Shows that offer is currently live and collecting leads.</td></tr>
<tr>	
	<TD class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=offerCode&sOfferCodeOrder=<?php echo $sOfferCodeOrder;?>" class=header>Offer</a></td>
	<TD class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=sortOrder&sSortOrderOrder=<?php echo $sSortOrderOrder;?>" class=header>Sort Order</a></td>	
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=isTopDisplay&sIsTopDisplayOrder=<?php echo $sIsTopDisplayOrder;?>" class=header>Top Display</a></td>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=precheck&sPrecheckOrder=<?php echo $sPrecheckOrder;?>" class=header>Precheck</a></td>
	<td class=header>Remove from this Page</td>
</tr>
<?php echo $sOfferList;?>
<!--<input type=submit name=saveClose value="Save & Close">-->
<tr><td><BR></td></tr>
<tr><td colspan=4 class=header>Select Offer To Add To This Page:</td></tr>
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
<form>
<?php
	} else {
?><form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=4 class=header>Select OT Page To Work On:</td></tr>
<tr><Td  colspan=4><select name=iId>
<?php echo $sOtPagesOptions;?>
</select></td></tr>
</table>
	<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD align=center >
		<input type=submit name=sDoNothing value='Display OtPage'>
		</td>
	</tr>	
	</table>
<form>
<?php
	}
include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>