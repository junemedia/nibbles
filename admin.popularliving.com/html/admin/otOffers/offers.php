<?php

/*********

Script to sort/add/remove offers under a page

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

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
		
		$sEmailHeaders = "From: ot@amperemedia.com\r\nX-Mailer: MyFree.com\r\n";
		$sNotes = "\r\n\r\nA = Active Pages     I = Inactive Pages";
		$sNotes .= "\r\n\r\nActive Pages:  Leads collected from that page within last 30 days OR no leads collected within 30 days and the page was created within last 30 days";
		
		// get the recipients
		$sRecQuery = "SELECT * FROM   emailRecipients WHERE  purpose = 'Offer OT Pages'";
		$rRecResult = dbQuery($sRecQuery);
		while ($oRecRow = dbFetchObject($rRecResult)) {
			$sEmailRecipients = $oRecRow->emailRecipients;
		}


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
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $sEditQuery\")";
				$rLogResult = dbQuery($sLogAddQuery);
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

				// Offer added to a page
				$sGetListOfOldPages = "SELECT otPages.id as ids, pageName FROM otPages, pageMap
								WHERE otPages.id = pageMap.pageId
								AND pageMap.offerCode = '$sOfferCode'";
				$rGetListOfOldPages = dbQuery($sGetListOfOldPages);
				$iCountOfOldPages = 0;
				$sListOfOldPages = '';
				while ($oOldPageNameRow = dbFetchObject($rGetListOfOldPages)) {
					$sCheckOtDataHistoryQuery = "SELECT count(*) as count FROM activePages WHERE pageId='$oOldPageNameRow->ids'";
					$rCheckOtDataHistoryResult = dbQuery($sCheckOtDataHistoryQuery);
					$oActivePageRow = dbFetchObject($rCheckOtDataHistoryResult);
					if ($oActivePageRow->count > 0) {
						$sListOfOldPages .= $oOldPageNameRow->pageName." [A] ".',';
					} else {
						$sCheckOtPagesDateAddedQuery = "SELECT count(*) as count FROM otPages WHERE id = '$oOldPageNameRow->ids'
								AND date_format(dateTimeAdded, '%Y-%m-%d') BETWEEN date_add(CURRENT_DATE, INTERVAL -30 DAY)
								AND date_add(CURRENT_DATE, INTERVAL -0 DAY)";
						$rCheckOtPagesDateAddedResult = dbQuery($sCheckOtPagesDateAddedQuery);
						$oPageDateRow = dbFetchObject($rCheckOtPagesDateAddedResult);
						if ($oPageDateRow->count > 0) {
							$sListOfOldPages .= $oOldPageNameRow->pageName." [A] ".',';
						} else {
							$sListOfOldPages .= $oOldPageNameRow->pageName." [I] ".',';
						}
					}
					$iCountOfOldPages++;
				}
				$sListOfOldPages = substr($sListOfOldPages, 0, strlen($sListOfOldPages)-1);
				$sListOfOldPages = str_replace(",", "\n",$sListOfOldPages);
				
				$sAddQuery = "INSERT IGNORE INTO pageMap(pageId, offerCode, sortOrder, isTopDisplay, precheck)
						 VALUES('$iId', '$sOfferCode', '$iAddSortOrder', '', '')";
				$rAddResult = dbQuery($sAddQuery);
				echo dbError();
				
				
				$sGetPageNameQuery = "SELECT * FROM otPages WHERE id='$iId'";
				$rGetPageNameResult = dbQuery($sGetPageNameQuery);
				while ($sAddPageRow = dbFetchObject($rGetPageNameResult)) {
					$sAddedPage = $sAddPageRow->pageName;
				}

				// Get list of new pages
				$sGetListOfCurrPages = "SELECT otPages.id as ids, pageName FROM otPages, pageMap
										WHERE otPages.id = pageMap.pageId
										AND pageMap.offerCode = '$sOfferCode'";
				$rGetListOfCurrPages = dbQuery($sGetListOfCurrPages);
				$iCountOfNewPages = 0;
				$sCurrPages = '';
				while ($oCurrPageNameRow = dbFetchObject($rGetListOfCurrPages)) {
					$sCheckOtDataHistoryQuery = "SELECT count(*) as count FROM activePages WHERE pageId='$oCurrPageNameRow->ids'";
					$rCheckOtDataHistoryResult = dbQuery($sCheckOtDataHistoryQuery);
					$oActivePageRow = dbFetchObject($rCheckOtDataHistoryResult);
					if ($oActivePageRow->count > 0) {
						$sCurrPages .= $oCurrPageNameRow->pageName." [A] ".',';
						if ($sAddedPage == $oCurrPageNameRow->pageName) {
							$sAddedPage = $oCurrPageNameRow->pageName." [A] ";
						}
					} else {
						$sCheckOtPagesDateAddedQuery = "SELECT count(*) as count FROM otPages WHERE id = '$oCurrPageNameRow->ids'
								AND date_format(dateTimeAdded, '%Y-%m-%d') BETWEEN date_add(CURRENT_DATE, INTERVAL -30 DAY)
								AND date_add(CURRENT_DATE, INTERVAL -0 DAY)";
						$rCheckOtPagesDateAddedResult = dbQuery($sCheckOtPagesDateAddedQuery);
						$oPageDateRow = dbFetchObject($rCheckOtPagesDateAddedResult);
						if ($oPageDateRow->count > 0) {
							$sCurrPages .= $oCurrPageNameRow->pageName." [A] ".',';
							if ($sAddedPage == $oCurrPageNameRow->pageName) {
								$sAddedPage = $oCurrPageNameRow->pageName." [A] ";
							}
						} else {
							$sCurrPages .= $oCurrPageNameRow->pageName." [I] ".',';
							if ($sAddedPage == $oCurrPageNameRow->pageName) {
								$sAddedPage = $oCurrPageNameRow->pageName." [I] ";
							}
						}
					}
					$iCountOfNewPages++;
				}
				$sCurrPages = substr($sCurrPages, 0, strlen($sCurrPages)-1);
				$sCurrPages = str_replace(",", "\n",$sCurrPages);
				
				
				// start of track users' activity in nibbles 
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $sAddQuery\")"; 
				$rLogResult = dbQuery($sLogAddQuery); 
				// end of track users' activity in nibbles
				
				
				$sCheckQuery = "SELECT offers.*, offerCompanies.companyName, offerCompanies.repDesignated
								FROM   offers, offerCompanies
								WHERE  offers.companyId = offerCompanies.id AND offers.offerCode = '$sOfferCode'";
				$rCheckResult = dbQuery($sCheckQuery);
				$iCheckNumRows = dbNumRows($rCheckResult);
				while ($oCheckRow = dbFetchObject($rCheckResult)) {
					$sCompanyName = $oCheckRow->companyName;
					$sOfferName = $oCheckRow->name;
					$sRepQuery = "SELECT * FROM nbUsers WHERE id IN (".$oCheckRow->repDesignated.")";
					$rRepResult = dbQuery($sRepQuery);
					$sOfferRep = '';
					while ($oRepRow = dbFetchObject($rRepResult)) {
						$sOfferRep .= $oRepRow->firstName." ". $oRepRow->lastName.",";
					}
					if ($sOfferRep != '') {
						$sOfferRep = substr($sOfferRep,0,strlen($sOfferRep)-1);
					}
				}

				$sEmailMessage = "This action by: ".$sTrackingUser."\n\n";
				$sEmailMessage .= "OfferCode: $sOfferCode\r\nOffer Name: $sOfferName\r\nOffer Company Name: $sCompanyName\r\nAE: $sOfferRep\r\n\r\n";
				$sEmailMessage .= "\r\nOld Page Count: $iCountOfOldPages\r\nNew Page Count: $iCountOfNewPages\n";
				$sEmailMessage .= "\r\nOffer Added To: $sAddedPage\n";
				$sEmailMessage .= "\r\nOffer Was Previously Assigned To The Following OT Pages:\n$sListOfOldPages";
				$sEmailMessage .= "\r\n\nCurrently Offer Assigned To The Following OT Pages:\n$sCurrPages";
				$sEmailMessage .= "\r\n".$sNotes;
				mail($sEmailRecipients, "Offer OT Pages Assignments Update - $sOfferCode", $sEmailMessage, $sEmailHeaders);
			} else {
				$sMessage = "Offer Already Exists In This Page....";
			}
		}

		if (is_array($aRemove)) {
			$sMessage = '';
			while (list($key, $val) = each($aRemove)) {
				
				$sGetPageNameQuery = "SELECT * FROM otPages WHERE id='$iId'";
				$rGetPageNameResult = dbQuery($sGetPageNameQuery);
				while ($sRemovedPageRow = dbFetchObject($rGetPageNameResult)) {
					$sRemovedPage = $sRemovedPageRow->pageName;
				}
				
				// Offer added to a page
				$sGetListOfOldPages = "SELECT otPages.id as ids, pageName FROM otPages, pageMap
								WHERE otPages.id = pageMap.pageId
								AND pageMap.offerCode = '$key'";
				$rGetListOfOldPages = dbQuery($sGetListOfOldPages);
				$iCountOfOldPages = 0;
				$sListOfOldPages = '';
				while ($oOldPageNameRow = dbFetchObject($rGetListOfOldPages)) {
					$sCheckOtDataHistoryQuery = "SELECT count(*) as count FROM activePages WHERE pageId='$oOldPageNameRow->ids'";
					$rCheckOtDataHistoryResult = dbQuery($sCheckOtDataHistoryQuery);
					$oActivePageRow = dbFetchObject($rCheckOtDataHistoryResult);
					if ($oActivePageRow->count > 0) {
						$sListOfOldPages .= $oOldPageNameRow->pageName." [A] ".',';
						if ($sRemovedPage == $oOldPageNameRow->pageName) {
							$sRemovedPage = $sRemovedPage." [A] ";
						}
					} else {
						$sCheckOtPagesDateAddedQuery = "SELECT count(*) as count FROM otPages WHERE id = '$oOldPageNameRow->ids'
								AND date_format(dateTimeAdded, '%Y-%m-%d') BETWEEN date_add(CURRENT_DATE, INTERVAL -30 DAY)
								AND date_add(CURRENT_DATE, INTERVAL -0 DAY)";
						$rCheckOtPagesDateAddedResult = dbQuery($sCheckOtPagesDateAddedQuery);
						$oPageDateRow = dbFetchObject($rCheckOtPagesDateAddedResult);
						if ($oPageDateRow->count > 0) {
							$sListOfOldPages .= $oOldPageNameRow->pageName." [A] ".',';
							if ($sRemovedPage == $oOldPageNameRow->pageName) {
								$sRemovedPage = $sRemovedPage." [A] ";
							}
						} else {
							$sListOfOldPages .= $oOldPageNameRow->pageName." [I] ".',';
							if ($sRemovedPage == $oOldPageNameRow->pageName) {
								$sRemovedPage = $sRemovedPage." [I] ";
							}
						}
					}
					$iCountOfOldPages++;
				}
				$sListOfOldPages = substr($sListOfOldPages, 0, strlen($sListOfOldPages)-1);
				$sListOfOldPages = str_replace(",", "\n",$sListOfOldPages);


				$sDeleteQuery = "DELETE FROM pageMap WHERE  pageId = '$iId' AND offerCode = '$key'";
				$rDeleteResult = dbQuery($sDeleteQuery);
				
				
				
				// Get list of new pages
				$sGetListOfCurrPages = "SELECT otPages.id as ids, pageName FROM otPages, pageMap
										WHERE otPages.id = pageMap.pageId
										AND pageMap.offerCode = '$key'";
				$rGetListOfCurrPages = dbQuery($sGetListOfCurrPages);
				$iCountOfNewPages = 0;
				$sCurrPages = '';
				while ($oCurrPageNameRow = dbFetchObject($rGetListOfCurrPages)) {
					$sCheckOtDataHistoryQuery = "SELECT count(*) as count FROM activePages WHERE pageId='$oCurrPageNameRow->ids'";
					$rCheckOtDataHistoryResult = dbQuery($sCheckOtDataHistoryQuery);
					$oActivePageRow = dbFetchObject($rCheckOtDataHistoryResult);
					if ($oActivePageRow->count > 0) {
						$sCurrPages .= $oCurrPageNameRow->pageName." [A] ".',';
					} else {
						$sCheckOtPagesDateAddedQuery = "SELECT count(*) as count FROM otPages WHERE id = '$oCurrPageNameRow->ids'
								AND date_format(dateTimeAdded, '%Y-%m-%d') BETWEEN date_add(CURRENT_DATE, INTERVAL -30 DAY)
								AND date_add(CURRENT_DATE, INTERVAL -0 DAY)";
						$rCheckOtPagesDateAddedResult = dbQuery($sCheckOtPagesDateAddedQuery);
						$oPageDateRow = dbFetchObject($rCheckOtPagesDateAddedResult);
						if ($oPageDateRow->count > 0) {
							$sCurrPages .= $oCurrPageNameRow->pageName." [A] ".',';
						} else {
							$sCurrPages .= $oCurrPageNameRow->pageName." [I] ".',';
						}
					}
					$iCountOfNewPages++;
				}
				$sCurrPages = substr($sCurrPages, 0, strlen($sCurrPages)-1);
				$sCurrPages = str_replace(",", "\n",$sCurrPages);
				
				$sCheckQuery = "SELECT offers.*, offerCompanies.companyName, offerCompanies.repDesignated
								FROM   offers, offerCompanies
								WHERE  offers.companyId = offerCompanies.id AND offers.offerCode = '$key'";
				$rCheckResult = dbQuery($sCheckQuery);
				$iCheckNumRows = dbNumRows($rCheckResult);
				while ($oCheckRow = dbFetchObject($rCheckResult)) {
					$sCompanyName = $oCheckRow->companyName;
					$sOfferName = $oCheckRow->name;
					$sRepQuery = "SELECT * FROM nbUsers WHERE id IN (".$oCheckRow->repDesignated.")";
					$rRepResult = dbQuery($sRepQuery);
					$sOfferRep = '';
					while ($oRepRow = dbFetchObject($rRepResult)) {
						$sOfferRep .= $oRepRow->firstName." ". $oRepRow->lastName.",";
					}
					if ($sOfferRep != '') {
						$sOfferRep = substr($sOfferRep,0,strlen($sOfferRep)-1);
					}
				}

				$sEmailMessage = "This action by: ".$sTrackingUser."\n\n";
				$sEmailMessage .= "OfferCode: $key\r\nOffer Name: $sOfferName\r\nOffer Company Name: $sCompanyName\r\nAE: $sOfferRep\r\n\r\n";
				$sEmailMessage .= "\r\nOld Page Count: $iCountOfOldPages\r\nNew Page Count: $iCountOfNewPages\n";
				$sEmailMessage .= "\r\nOffer Removed From: $sRemovedPage\n";
				$sEmailMessage .= "\r\nOffer Was Previously Assigned To The Following OT Pages:\n$sListOfOldPages";
				$sEmailMessage .= "\r\n\nCurrently Offer Assigned To The Following OT Pages:\n$sCurrPages";
				$sEmailMessage .= "\r\n".$sNotes;
				mail($sEmailRecipients, "Offer OT Pages Assignments Update - $key", $sEmailMessage, $sEmailHeaders);

				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteQuery\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
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

	// Effective Rate - revPerLead
	// Pay Rate - actualRevPerLead
	
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
		case "revPerLead" :
		$sCurrOrder = $sRevPerLeadOrder;
		$sRevPerLeadOrder = ($sRevPerLeadOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "actualRevPerLead" :
		$sCurrOrder = $sActualRevPerLeadOrder;
		$sActualRevPerLeadOrder = ($sActualRevPerLeadOrder != "DESC" ? "DESC" : "ASC");
		break;
		default:
		$sCurrOrder = $sSortOrderOrder;
		$sSortOrderOrder = ($sSortOrderOrder != "DESC" ? "DESC" : "ASC");
	}

	// Select Query to display list of data

	$sSelectQuery = "SELECT offers.offerCode, offers.name, pageMap.sortOrder, pageMap.isTopDisplay,
						offers.mode, offers.isLive, offerCompanies.creditStatus, pageMap.precheck,
						offers.revPerLead as revPerLead, offers.actualRevPerLead as actualRevPerLead
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

		// Effective Rate - revPerLead
		// Pay Rate - actualRevPerLead
		$sOfferList .= "<tr class=$sBgcolorClass><td><b>$sTempOfferCode</b><br>$sTitle</td>
						<td>$oRow->revPerLead</td>
						<td>$oRow->actualRevPerLead</td>
						<td><input type=text name=aSortOrder[".$oRow->offerCode."] value='$oRow->sortOrder' size=5></td>
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

	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&iId=$iId";

	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	


	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

	include("../../includes/adminAddHeader.php");

	// Effective Rate - revPerLead
	// Pay Rate - actualRevPerLead

?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=6>* Shows that offer is currently live and collecting leads.</td></tr>
<tr>	
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=offerCode&sOfferCodeOrder=<?php echo $sOfferCodeOrder;?>" class=header>Offer</a></td>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=revPerLead&sRevPerLeadOrder=<?php echo $sRevPerLeadOrder;?>" class=header>Effective Rate</a></td>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=actualRevPerLead&sActualRevPerLeadOrder=<?php echo $sActualRevPerLeadOrder;?>" class=header>Pay Rate</a></td>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=sortOrder&sSortOrderOrder=<?php echo $sSortOrderOrder;?>" class=header>Sort Order</a></td>	
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=isTopDisplay&sIsTopDisplayOrder=<?php echo $sIsTopDisplayOrder;?>" class=header>Top Display</a></td>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=precheck&sPrecheckOrder=<?php echo $sPrecheckOrder;?>" class=header>Precheck</a></td>
	<td class=header>Remove from this Page</td>
</tr>
<?php echo $sOfferList;?>
<!--<input type=submit name=saveClose value="Save & Close">-->
<tr><td><BR></td></tr>
<tr><td colspan=6 class=header>Select Offer To Add To This Page:</td></tr>
<tr><Td  colspan=6><select name=sOfferCode>
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
include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>