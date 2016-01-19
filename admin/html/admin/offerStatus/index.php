<?php

// Script to Display List/Delete Partner Companies

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

$sPageTitle = "Nibbles Offers - View/Change Offers";

	if ($sSaveOfferStatus) {
		
		$sTempMsg = '';
		$sSelectQuery = "SELECT offers.id, mode, isLive, offers.offerCode, offers.name, revPerLead,
								offerCompanies.companyName, offerCompanies.repDesignated,
								offers.imageName, offers.mediumImageName, offers.smallImageName
						 FROM   offers, offerCompanies
						 WHERE  offers.companyId = offerCompanies.id
						 ORDER BY offers.id";
		$rSelectResult = dbQuery($sSelectQuery);
		echo dbError();
		while ($oSelectRow = dbFetchObject($rSelectResult)) {
			$iTempId = $oSelectRow->id;
			$sOfferCode = $oSelectRow->offerCode;
			$sCompanyName = $oSelectRow->companyName;
			$sOfferName = $oSelectRow->name;
			$sRepDesignated = $oSelectRow->repDesignated;
			$fRevPerLead = $oSelectRow->revPerLead;
			$sSmallImg = $oSelectRow->smallImageName;
			$sMediumImg = $oSelectRow->mediumImageName;
			$sLargeImg = $oSelectRow->imageName;

			$sOfferRep = '';
			$sRepQuery = "SELECT *
						  FROM   nbUsers
						  WHERE  id IN (".$sRepDesignated.")";
			$rRepResult = dbQuery($sRepQuery);
			while ($oRepRow = dbFetchObject($rRepResult)) {
				$sOfferRep .= $oRepRow->firstName." ". $oRepRow->lastName.",";
			}
			if ($sOfferRep != '') {
				$sOfferRep = substr($sOfferRep,0,strlen($sOfferRep)-1);
			}
					
					
			$sOldMode = $oSelectRow->mode;
			$iOldIsLive = $oSelectRow->isLive;
			$sModeVarName = "sMode_".$iTempId;
			
			if (isset($$sModeVarName)) {
				$iIsLive = '';
				if ($$sModeVarName == 'A') {
					$iIsLive = "1";
				}
				
				if ($$sModeVarName == 'P') {
					$iIsLive = "1";
				}
				
				$bContinue = true;
				if ($$sModeVarName == 'A') {
					if ($sSmallImg == '' || $sMediumImg == '' || $sLargeImg == '') {
						$bContinue = false;
						$sTempMsg .= "The offer ($sOfferCode) does not have all 3 images so it can't go live.<br>";
					}
				}
				
				
				if ($bContinue) {
					$sUpdateQuery = "UPDATE offers
									 SET    mode = '".$$sModeVarName."',
											isLive = '$iIsLive' 
									 WHERE  id = '$iTempId'";
	
					// start of track users' activity in nibbles 
					$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
								VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Update: $sUpdateQuery\")"; 
					$rLogResult = dbQuery($sLogAddQuery); 
					// end of track users' activity in nibbles
					
					
					$rUpdateResult = dbQuery($sUpdateQuery);
					echo dbError();
					if ($rUpdateResult) {
						
						// this table will be used for ecpm reporting.
						if ($$sModeVarName == 'A' && $iIsLive == '1') {
							$sInsertLog = "INSERT IGNORE INTO liveOffers (offerCode,dateAdded)
										VALUES ('$sOfferCode', CURRENT_DATE)";
							$rInsertLogResult = dbQuery($sInsertLog);
						}
						
						
						
						
						
						// if offerStatus changed, send status email
						if ($$sModeVarName != $sOldMode) {
							$sTempOldModeValue = '';
							if ($sOldMode == 'A') {
								$sTempOldModeValue = 'Offer Up';
							} elseif ($sOldMode == 'I') {
								$sTempOldModeValue = 'Offer Down';
							} elseif ($sOldMode == 'P') {
								$sTempOldModeValue = 'Offer API Only';
							}
							
							
							$sInsertMode = "INSERT INTO offerStatusHistory (offerCode, mode, dateTimeAdded)
										VALUES ('$sOfferCode', '".$$sModeVarName."', NOW())";
							$rInsertMode = dbQuery($sInsertMode);
							
	
							$sOfferOnPages = '';
							$sPageQuery= "SELECT pageName, otPages.id as ids
										  FROM   otPages, pageMap
										  WHERE  otPages.id = pageMap.pageId
										  AND	 offerCode = '$sOfferCode'";
							$rPageResult = dbQuery($sPageQuery);
							echo dbError();
							while ( $oPageRow = dbFetchObject($rPageResult)) {
								$sCheckOtDataHistoryQuery = "SELECT count(*) as count FROM activePages WHERE pageId='$oPageRow->ids'";
								$rCheckOtDataHistoryResult = dbQuery($sCheckOtDataHistoryQuery);
								$oActivePageRow = dbFetchObject($rCheckOtDataHistoryResult);
								if ($oActivePageRow->count > 0) {
									$sOfferOnPages .= $oPageRow->pageName." [A] ".',';
								} else {
									$sCheckOtPagesDateAddedQuery = "SELECT count(*) as count FROM otPages WHERE id = '$oPageRow->ids'
											AND date_format(dateTimeAdded, '%Y-%m-%d') BETWEEN date_add(CURRENT_DATE, INTERVAL -30 DAY)
											AND date_add(CURRENT_DATE, INTERVAL -0 DAY)";
									$rCheckOtPagesDateAddedResult = dbQuery($sCheckOtPagesDateAddedQuery);
									$oPageDateRow = dbFetchObject($rCheckOtPagesDateAddedResult);
									if ($oPageDateRow->count > 0) {
										$sOfferOnPages .= $oPageRow->pageName." [A] ".',';
									} else {
										$sOfferOnPages .= $oPageRow->pageName." [I] ".',';
									}
								}
							}
							if ($sOfferOnPages != '') {
								$sOfferOnPages = substr($sOfferOnPages, 0, strlen($sOfferOnPages)-1);
							}
							 
							if ($$sModeVarName == 'A') {
								$sEmailSubject = "Offer Up - " . $sOfferCode;
								$sEmailMessage = "\r\nThis action by: ".$sTrackingUser."\n\n";
								$sEmailMessage .= "OfferCode:$sOfferCode\r\nOffer Name:$sOfferName\r\nOffer Company Name: $sCompanyName\r\nAE: $sOfferRep\r\n\r\nThis offer code has gone up";
								$sEmailMessage .= "\r\nOffer Rate: $fRevPerLead";
								
								$sOfferOnPages = str_replace(",","\n",$sOfferOnPages);
								
								$sEmailMessage .= "\r\n\nOffer On Pages: \n$sOfferOnPages";
								$sEmailMessage .= "\r\n\r\nA = Active Pages     I = Inactive Pages";
								$sEmailMessage .= "\r\n\r\nActive Pages:  Leads collected from that page within last 30 days OR no leads collected within 30 days and the page was created within last 30 days";
								
								// insert offersLog - START
								$sOfferLogQuery = "INSERT INTO nibbles.offersLog(offerCode, userName, dateTimeLogged, oldValue, newValue, changes) 
										  VALUES(\"$sOfferCode\", '$sTrackingUser', now(), \"$sTempOldModeValue\", \"Offer Up\", \"Offer Status\")";
								$rOfferLogResult = dbQuery($sOfferLogQuery);
								// insert offersLog - END
							}
							
							if ($$sModeVarName == 'I') {
								$sEmailSubject = "Offer Down - " . $sOfferCode;
								$sEmailMessage = "\r\nThis action by: ".$sTrackingUser."\n\n";
								$sEmailMessage .= "OfferCode:$sOfferCode\r\nOffer Name:$sOfferName\r\nOffer Company Name: $sCompanyName\r\nAE: $sOfferRep\r\n\r\nThis offer code has been taken down";
								$sEmailMessage .= "\r\nOffer Rate: $fRevPerLead";
								
								$sOfferOnPages = str_replace(",","\n",$sOfferOnPages);
								
								$sEmailMessage .= "\r\n\nOffer On Pages: \n$sOfferOnPages";
								$sEmailMessage .= "\r\n\r\nA = Active Pages     I = Inactive Pages";
								$sEmailMessage .= "\r\n\r\nActive Pages:  Leads collected from that page within last 30 days OR no leads collected within 30 days and the page was created within last 30 days";
								
								// insert offersLog - START
								$sOfferLogQuery = "INSERT INTO nibbles.offersLog(offerCode, userName, dateTimeLogged, oldValue, newValue, changes) 
										  VALUES(\"$sOfferCode\", '$sTrackingUser', now(), \"$sTempOldModeValue\", \"Offer Down\", \"Offer Status\")";
								$rOfferLogResult = dbQuery($sOfferLogQuery);
								// insert offersLog - END
							}
							
							if ($$sModeVarName == 'P') {
								$sEmailSubject = "Offer API Only - " . $sOfferCode;
								$sEmailMessage = "\r\nThis action by: ".$sTrackingUser."\n\n";
								$sEmailMessage .= "OfferCode:$sOfferCode\r\nOffer Name:$sOfferName\r\nOffer Company Name: $sCompanyName\r\nAE: $sOfferRep\r\n\r\nThis offer is set to recieve leads from API only.";
								$sEmailMessage .= "\r\nOffer Rate: $fRevPerLead";
								
								// insert offersLog - START
								$sOfferLogQuery = "INSERT INTO nibbles.offersLog(offerCode, userName, dateTimeLogged, oldValue, newValue, changes) 
										  VALUES(\"$sOfferCode\", '$sTrackingUser', now(), \"$sTempOldModeValue\", \"Offer API Only\", \"Offer Status\")";
								$rOfferLogResult = dbQuery($sOfferLogQuery);
								// insert offersLog - END
							}
	
							// get the recipients
							$sRecQuery = "SELECT *
										  FROM   emailRecipients
										  WHERE  purpose = 'Offer status change'";
							$rRecResult = dbQuery($sRecQuery);
							
							while ($oRecRow = dbFetchObject($rRecResult)) {
								$sEmailRecipients = $oRecRow->emailRecipients;
								
							}
							
							if ($sEmailRecipients != '') {
								$sEmailHeaders = "From: ot@amperemedia.com\r\n";
								$sEmailHeaders .= "X-Mailer: MyFree.com\r\n";
								$sEmailHeaders .= "cc:";
								$aEmailRecipients = explode(",",$sEmailRecipients);
								$sEmailTo = $aEmailRecipients[0];
								for ($i=1;$i<count($aEmailRecipients);$i++) {
									$sEmailHeaders .= $aEmailRecipients[$i].",";							
								}
								
								if (count($aEmailRecipients) > 1) {
									$sEmailHeaders = substr($sEmailHeaders, 0, strlen($sEmailHeaders)-1);
								}
								
								mail($sEmailTo, $sEmailSubject, $sEmailMessage, $sEmailHeaders);
								
							}
						}
					}
				}
			}
		}
		
		if ($sTempMsg !='') {
			$sMessage = $sTempMsg;
		}
	}
	
	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "offerCode";
		$sOfferCodeOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if (!($sCurrOrder)) {
	switch ($sOrderColumn) {
		case "mode" :
		$sCurrOrder = $sModeOrder;
		$sModeOrder = ($sModeOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "isLive" :
		$sCurrOrder = $sIsLiveOrder;
		$sIsLiveOrder = ($sIsLiveOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "headline" :
		$sCurrOrder = $sHeadlineOrder;
		$sHeadlineOrder = ($sHeadlineOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "companyName" :
		$sCurrOrder = $sCompanyNameOrder;
		$sCompanyNameOrder = ($sCompanyNameOrder != "DESC" ? "DESC" : "ASC");
		break;
		default:
		$sCurrOrder = $sOfferCodeOrder;
		$sOfferCodeOrder = ($sOfferCodeOrder != "DESC" ? "DESC" : "ASC");
	}
	}
	// Prepare filter part of the query if filter/exclude specified...
	
	if ($sFilter != '') {
		
		$sFilterPart .= " AND ( ";
		
		switch ($sSearchIn) {
			case "headline" :
			$sFilterPart .= ($iExactMatch) ? "headline = '$sFilter'" : "headline like '%$sFilter%'";
			break;
			case "mode" :
			$sFilterPart .= ($iExactMatch) ? "mode = '$sFilter'" : "mode like '%$sFilter%'";
			break;
			case "isLive" :
			$sFilterPart .= ($iExactMatch) ? "isLive = '$sFilter'" : "isLive like '%$sFilter%'";
			break;
			case "companyName" :
			$sFilterPart .= ($iExactMatch) ? "OC.companyName = '$sFilter'" : "OC.companyName like '%$sFilter%'";
			break;
			case "offerCode" :
			$sFilterPart .= ($iExactMatch) ? "offerCode = '$sFilter'" : "offerCode like '%$sFilter%'";
			break;
			//	case "dateLastUpdated" :
			//$sFilterPart .= ($sExactMatch == 'Y') ? "dateLastUpdated = '$sFilter'" : "dateLsatUpdated like '%$sFilter%'";
			//break;
			default:
			$sFilterPart .= ($iExactMatch) ? "offerCode = '$sFilter' || OC.companyName = '$sFilter' || headline = '$sFilter' || mode = '$sFilter' || isLive = '$sFilter'" : " offerCode like '%$sFilter%' || OC.companyName LIKE '%$sFilter%' || headline like '%$sFilter%' || mode like '%$sFilter%' || isLive like '%$sFilter%'";
		}
		
		$sFilterPart .= ") ";
	}
	
	if ($sExclude != '') {
		$sFilterPart .= " AND ( ";
		switch ($sExclude) {
			case "headline" :
			$sFilterPart .= "headline NOT LIKE '%$sExclude%'";
			break;
			case "mode" :
			$sFilterPart .= "mode NOT LIKE '%$sExclude%'";
			break;
			case "isLive" :
			$sFilterPart .= "isLive NOT LIKE '%$sExclude%'";
			break;
			case "companyName" :
			$sFilterPart .= "OC.companyName NOT LIKE '%$sExclude%'";
			break;
			case "offerCode" :
			$sFilterPart .= "offerCode NOT LIKE '%$sExclude%'";
			break;
			//	case "dateLastUpdated" :
			//	$sFilterPart .= "dateLastUpdated NOT LIKE '%$sExclude%'";
			//	break;
			default:
			$sFilterPart .= "offerCode NOT LIKE '%$sExclude%' && OC.companyName NOT LIKE '%$sExclude%' && headline NOT LIKE '%$sExclude%' && mode NOT LIKE '%$sExclude%' && isLive NOT LIKE '%$sExclude%'" ;
		}
		$sFilterPart .= " ) ";
		
	}
	
	$sFilter = ascii_encode(stripslashes($sFilter));
	$sExclude = ascii_encode(stripslashes($sExclude));
	
	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 20;
	}
	if (!($iPage)) {
		$iPage = 1;
	}
	
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sFilter=$sFilter&iExactMatch=$iExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn&iRecPerPage=$iRecPerPage";
	
	// Query to get the list of Categories
	$sSelectQuery = "SELECT O.*, OC.companyName, OC.creditStatus
					FROM offers O, offerCompanies OC
					WHERE O.companyId = OC.id
					$sFilterPart 	";
	
	
	$sSelectQuery .= " ORDER BY $sOrderColumn $sCurrOrder ";
	
	
	$rSelectResult = dbQuery($sSelectQuery);	
	echo dbError();
	
	// Count no of records and total pages
	$rResult = dbQuery($sSelectQuery);
	//echo $selectQuery;
	$iNumRecords = dbNumRows($rResult);
	
	$iTotalPages = ceil($iNumRecords/$iRecPerPage);
	
	// If current page no. is greater than total pages move to the last available page no.
	if ($iPage > $iTotalPages) {
		$iPage = $iTotalPages;
	}
	
	$iStartRec = ($iPage-1) * $iRecPerPage;
	$iEndRec = $iStartRec + $iRecPerPage -1;
	
	if ($iNumRecords > 0) {
		$sCurrentPage = " Page $iPage "."/ $iTotalPages";
	}
	
	// use query to fetch only the rows of the page to be displayed
	$sSelectQuery .= " LIMIT $iStartRec, $iRecPerPage";
	
	$rResult = dbQuery($sSelectQuery);
	if ($rResult) {
		
		if (dbNumRows($rResult) > 0) {
			// Prepare Next/Prev/First/Last links
			
			if ($iTotalPages > $iPage ) {
				$iNextPage = $iPage+1;
				$sNextPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iNextPage&sCurrOrder=$sCurrOrder' class=header>Next</a>";
				$sLastPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iTotalPages&sCurrOrder=$sCurrOrder' class=header>Last</a>";
			}
			if ($iPage != 1) {
				$iPrevPage = $iPage-1;
				$sPrevPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iPrevPage&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>Previous</a>";
				$sFirstPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=1&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>First</a>";
			}
			
			while ($oRow = dbFetchObject($rResult)) {
				if ($sBgcolorClass == "ODD") {
					$sBgcolorClass = "EVEN";
				} else {
					$sBgcolorClass = "ODD";
				}
				
				$sDispHeadline = ascii_encode(substr($oRow->headline,0,50));
				
				$iIsLive = $oRow->isLive;
				$sMode = $oRow->mode;
												
				$sDownChecked = '';
				$sActiveChecked = '';
				$sApiOnlyChecked = '';
								
				if ($sMode == 'I' && $iIsLive == '') {
					$sDownChecked = "checked";
				} else if ($sMode == 'A' && $iIsLive =='1') {
					$sActiveChecked = "checked";
				} else if ($sMode == 'P' && $iIsLive =='1') {
					$sApiOnlyChecked = "checked";	
				}
				
				$sOfferList .= "<tr class=$sBgcolorClass>
					<td>$oRow->offerCode</td>
					<td>$sDispHeadline ...</td>		
					<td>$oRow->companyName</td>
					<td>$oRow->creditStatus</td>
					<td nowrap><input type=radio name='sMode_".$oRow->id."' value='I' $sDownChecked> Down &nbsp;
							   <input type=radio name='sMode_".$oRow->id."' value='A' $sActiveChecked> Up &nbsp;
							   <input type=radio name='sMode_".$oRow->id."' value='P' $sApiOnlyChecked> API Only
					</td>
				
					</tr>";
			}			
		} else {
			$sMessage = "No Records Exist...";
		}
	}	
		
	if ($iExactMatch) {
		$sExactMatchChecked = "checked";
	}	
	
	switch ($sSearchIn) {
		case 'headline':
		$sHeadlineSelected = "selected";
		break;
		case 'mode':
		$sModeSelected = "selected";
		break;
		case 'offerCode':
		$sOfferCodeSelected = "selected";
		break;
		case 'companyName':
		$sCompanyNameSelected = "selected";
		break;
		default:
		$sAllFieldsSelected = "selected";
	}
	
	$sSearchInOptions = "<option value='' $sAllFieldsSelected>All Fields
						<option value='headline' $sHeadlineSelected>Headline
						<option value='mode' $sModeSelected>Offer Status
						<option value='offerCode' $sOfferCodeSelected>OfferCode
						<option value='companyName' $sCompanyNameSelected>Offer Company";
			
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";
	
	include("../../includes/adminHeader.php");
	
	?>
<script language=JavaScript>
				function confirmDelete(form1,id)
				{
					if(confirm('Are you sure to delete this record ?'))
					{	
					dblConfirmDelete(form1, id);							
												
					}
				}	
				
				function dblConfirmDelete(form1,id) {
					if(confirm('THIS OFFER AND ALL THE ENTRIES RELATED TO THIS OFFER WILL BE DELETED\n\n                            Are you sure to delete this record ?'))
					{											
						document.form1.elements['sDelete'].value='Delete';
						document.form1.elements['iId'].value=id;
						document.form1.submit();												
					}
				}
					
				function funcRecPerPage(form1) {
					document.form1.elements['sAdd'].value='';
					document.form1.submit();
				}					
</script>
		
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<input type=hidden name=sDelete>
<table cellpadding=3 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td>Filter By</td>
	<td colspan=3><input type=text name=sFilter value='<?php echo $sFilter;?>'> &nbsp; 
		<input type=checkbox name=iExactMatch value='Y' <?php echo $sExactMatchChecked;?>> Exact Match
	</td>
</tr>	

<tr><td>Exclude</td><td><input type=text name=sExclude value='<?php echo $sExclude;?>'></tR>
<tr><td>Search In</td><td><select name=sSearchIn>
	<?php echo $sSearchInOptions;?>
	</select></td><td colspan=2><input type=submit name=sViewOffers value='Query'></td>
	<td><input type=submit name=sSaveOfferStatus value='Update Offer Status'></td></tr>
	<tr><td colspan=4><BR></td></tr>
<tr><td colspan=3 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>

<tr>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=offerCode&sOfferCodeOrder=<?php echo $sOfferCodeOrder;?>" class=header>OfferCode</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=headline&sHeadlineOrder=<?php echo $sHeadlineOrder;?>" class=header>Headline</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=companyName&sCompanyNameOrder=<?php echo $sCompanyNameOrder;?>" class=header>Offer Company</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=creditSatus&sCreditStatusOrder=<?php echo $sCreditStatusOrder;?>" class=header>Credit Status</a></th>		
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=mode&sModeOrder=<?php echo $sModeOrder;?>" class=header>Offer Status</a></th>		
</tr>
<?php echo $sOfferList;?>
<tr><td colspan=4 align=right class=header><?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>
<tr><td colspan=4><BR></td></tr>
<tr><td colspan=4>&nbsp;</td><td><input type=submit name=sSaveOfferStatus value='Update Offer Status'></td></tr>
<tr><td colspan=5><b>Notes:</b><BR><BR> &nbsp; &nbsp;
					<b>Following images are required in order to make the offer live: 75 x 30, 88 x 31, & 120 x 60.</b>
					<BR> &nbsp; &nbsp; If Offer Status is set to Up, isLive is set to '1' and mode is set to 'A'.
					<BR> &nbsp; &nbsp; If Offer Status is set to Down, isLive is set to NULL and mode is set to 'I'.
					<BR> &nbsp; &nbsp; If Offer Status is set to API Only, isLive is set to '1' and mode is set to 'P'.
					 </td></tr>
</table>
</form>
	
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>