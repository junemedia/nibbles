<?php

/*********

Script to Display List/Delete Partner Companies

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
$sPageTitle = "Nibbles Offers - List/Delete Offers";


	if ($sDelete) {
		// if record deleted
		// get the offercode
		$sTempQuery = "SELECT offerCode
					   FROM   offers
					   WHERE  id = '$iId'";
		$sTempResult = dbQuery($sTempQuery);
		while ($sTempRow = dbFetchObject($sTempResult)) {
			$sOfferCode = $sTempRow->offerCode;
		}
		
		if ($sOfferCode != '') {
			$sDeleteQuery = "DELETE FROM offers
	 			   		WHERE  id = $iId"; 

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$rResult = dbQuery($sDeleteQuery);
			if($rResult) {
				// Delete any related records from other tables regarding this offer
				/**** tables affected  ******/
				// offer specific data from page2Map, capCounts, offerLeadSpec
				// relative entries to remove from  - offersOptOut, categoryMap, pageMap
				
				$rResult = dbQuery("DELETE FROM offerCaps WHERE offerCode='$sOfferCode' LIMIT 1");
				
				$sDropTable = "DROP TABLE targetData.$sOfferCode";
				$rDropTableResult = dbQuery($sDropTable);
				
				
				// delete phantom page if created.  Open They Host
				$sTempPageName = 'th_'.$sOfferCode;
				$sDeleteQuery = "DELETE FROM otPages WHERE pageName = '$sTempPageName' LIMIT 1";
				$rResult = dbQuery($sDeleteQuery);
				
				
				// delete phantom page if created.  Close They Host
				$sTempPageName = 'cth_'.$sOfferCode;
				$sDeleteQuery = "DELETE FROM otPages WHERE pageName = '$sTempPageName' LIMIT 1";
				$rResult = dbQuery($sDeleteQuery);
				
				
				// delete phantom page if created.  CoReg Popup
				$sTempPageName = 'coReg_'.$sOfferCode;
				$sDeleteQuery = "DELETE FROM otPages WHERE pageName = '$sTempPageName' LIMIT 1";
				$rResult = dbQuery($sDeleteQuery);


				// delete entry from capCounts
				$sCapDeleteQuery = "DELETE FROM capCounts
							   	   WHERE  offerCode = '$sOfferCode'";
				$rCapDeleteResult = dbQuery($sCapDeleteQuery);
				
				// delete entry from offerLeadSpec
				$sLeadSpecDeleteQuery = "DELETE FROM offerLeadSpec
										 WHERE  offerCode = '$sOfferCode'";
				$rLeadSpecDeleteResult = dbQuery($sLeadSpecDeleteQuery);				
				
				// delete entry from pageMap
				$sPageMapDeleteQuery = "DELETE FROM pageMap
										WHERE  offerCode = '$sOfferCode'";
				$rPageMapDeleteResult = dbQuery($sPageMapDeleteQuery);
				
				// delete entry from categoryMap
				$sCatMapDeleteQuery = "DELETE FROM categoryMap
									   WHERE  offerCode = '$sOfferCode'";
				$rCatMapDeleteResult = dbQuery($sCatMapDeleteQuery);
				
				// delete entry from page2Map
				$sPage2MapDeleteQuery = "DELETE FROM page2Map
									   WHERE  offerCode = '$sOfferCode'";
				$rPage2MapDeleteResult = dbQuery($sPage2MapDeleteQuery);								
				
				// delete entry from offersMutExclusive
				$sMutExclusiveDeleteQuery = "DELETE FROM offersMutExclusive
									   WHERE  offerCode1 = '$sOfferCode'
										OR    offerCode2 = '$sOfferCode'";
				$rMutExclusiveDeleteResult = dbQuery($sMutExclusiveDeleteQuery);
				
				
				// delete all the images of this offer
				if (is_dir("$sGblOfferImagePath/$sOfferCode") ) {	
					$rImageDir = opendir("$sGblOfferImagePath/$sOfferCode");
					if ($rImageDir) {
				
						while (($sFile = readdir($rImageDir)) != false) {	
							if (!is_dir("$sGblOfferImagePath/$sOfferCode/$sFile")) {
								@unlink("$sGblOfferImagePath/$sOfferCode/$sFile");							
							}
						}
						@rmdir("$sGblOfferImagePath/$sOfferCode");
					}
				}
				
			} else {
				echo dbError();
			}
			
			// reset $iId
			echo dbError();
			$iId = '';
		}
	}
	
	include("../../includes/adminHeader.php");
	
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
		case "creditStatus" :
		$sCurrOrder = $sCreditStatusOrder;
		$sCreditStatusOrder = ($sCreditStatusOrder != "DESC" ? "DESC" : "ASC");
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
	
	// Specify Page no. settings
	if (!($iRecPerPage)) {
		$iRecPerPage = 20;
	}
	if (!($iPage)) {
		$iPage = 1;
	}
	
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sFilter=".urlencode(stripslashes($sFilter))."&iExactMatch=$iExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn&iRecPerPage=$iRecPerPage";
	
	
	$sFilter = ascii_encode(stripslashes($sFilter));
	$sExclude = ascii_encode(stripslashes($sExclude));
	
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

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View offer list: $sSelectQuery\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles		
	
	
	$rResult = dbQuery($sSelectQuery);
	if ($rResult) {
		
		if (dbNumRows($rResult) > 0) {
			// Prepare Next/Prev/First/Last links
			
			if ($iTotalPages > $iPage ) {
				$iNextPage = $iPage+1;
				$sNextPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iNextPage&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>Next</a>";
				$sLastPageLink = "<a href='".$sSortLink."&sOrderColumn=$sOrderColumn&iPage=$iTotalPages&sCurrOrder=$sCurrOrder&iRecPerPage=$iRecPerPage' class=header>Last</a>";
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
				
				$sOfferList .= "<tr class=$sBgcolorClass>
					<td>$oRow->id</td><td>$oRow->offerCode</td>
					<td>$sDispHeadline ...</td>		
					<td>$oRow->companyName</td>			
					<td>$oRow->creditStatus</td>			
					<td>$oRow->mode</td>
					<td>$oRow->isLive</td>
					
					<td nowrap><a href='JavaScript:void(window.open(\"addOffer.php?iMenuId=$iMenuId&iId=".$oRow->id."&sOfferCode=".$oRow->offerCode."&iRecPerPage=$iRecPerPage&sFilter=$sFilter&iExactMatch=$iExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn\", \"AddOffer\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					| <a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a>
					| <a href='JavaScript:void(window.open(\"listMapFields.php?iMenuId=20&sOfferCode=".$oRow->offerCode."\", \"Page2Fields\", \"\"));' >Page2 Fields</a>
					| <a href='JavaScript:void(window.open(\"cloneOffer.php?iMenuId=$iMenuId&iId=".$oRow->id."&sOfferCode=".$oRow->offerCode."&iRecPerPage=$iRecPerPage&sFilter=$sFilter&iExactMatch=$iExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn\", \"AddOffer\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Clone</a>
					| <a href='JavaScript:void(window.open(\"mutExclusiveOffers.php?iMenuId=$iMenuId&iId=".$oRow->id."&sOfferCode=".$oRow->offerCode."&iRecPerPage=$iRecPerPage&sFilter=$sFilter&iExactMatch=$iExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn\", \"AddOffer\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Mut. Excl.</a>
					| <a href='JavaScript:void(window.open(\"precheck.php?iMenuId=$iMenuId&sOfferCode=".$oRow->offerCode."\", \"precheck\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Precheck</a></td></tr>";
			}			
		} else {
			$sMessage = "No Records Exist...";
		}
	}	
	
	$sAddButton = "<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addOffer.php?iMenuId=$iMenuId&iRecPerPage=$iRecPerPage&sFilter=$sFilter&iExactMatch=$iExactMatch&sExclude=$sExclude&sSearchIn=$sSearchIn\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
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
		case 'isLive':
		$sIsLiveSelected = "selected";
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
						<option value='mode' $sModeSelected>Mode
						<option value='isLive' $sIsLiveSelected>Is Live
						<option value='offerCode' $sOfferCodeSelected>OfferCode
						<option value='companyName' $sCompanyNameSelected>Offer Company";
	
	
	/* Prepare A-Z links
	for ($i = 65; $i <= 90; $i++) {
	$sAlphaLinks .= "<a href='$PHP_SELF?iMenuId=$iMenuId&sFilter=".chr($i)."'>".chr($i)."</a> ";
	}
	
	$sAlphaLinks .= " &nbsp; <a href='$PHP_SELF?iMenuId=$iMenuId&sFilter='>View All</a>";
	*/
	
	// set pixel tracking link
	//$sPixelsTrackingLink = "<a href='../pixels/report.php?menuId=13' class=header>Pixel Tracking</a>";
	
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";
	
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
<tr><Td><?php echo $sAddButton;?> </td></tr>

<tr><td>Filter By</td><td colspan=4><input type=text name=sFilter value='<?php echo $sFilter;?>'> &nbsp; 
	<input type=checkbox name=iExactMatch value='Y' <?php echo $sExactMatchChecked;?>> Exact Match</td></tr>	

<tr><td>Exclude</td><td><input type=text name=sExclude value='<?php echo $sExclude;?>'></tR>
<tr><td>Search In</td><td><select name=sSearchIn>
	<?php echo $sSearchInOptions;?>
	</select></td><td><input type=submit name=sViewOffers value='Query'></td></tr>
<tr><td colspan=8 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>

<tr>
	<td class=header>OfferId</td>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=offerCode&sOfferCodeOrder=<?php echo $sOfferCodeOrder;?>" class=header>OfferCode</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=headline&sHeadlineOrder=<?php echo $sHeadlineOrder;?>" class=header>Headline</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=companyName&sCompanyNameOrder=<?php echo $sCompanyNameOrder;?>" class=header>Offer Company</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=creditStatus&sCreditStatusOrder=<?php echo $sCreditStatusOrder;?>" class=header>Credit Status</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=mode&sModeOrder=<?php echo $sModeOrder;?>" class=header>Mode</a></th>	
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=isLive&sIsLiveOrder=<?php echo $sIsLiveOrder;?>" class=header>Is Live</a></th>		
	<th width=18%>&nbsp; </th>
</tr>
<?php echo $sOfferList;?>
<tr><td colspan=8 align=right class=header><?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>
<tr><td colspan=8><b>Notes:</b>
					<BR><BR>- Mode: A = Active, I = Inactive, T = Test, P = API Only
					<BR> &nbsp; &nbsp; If Mode is Active, offer may or may not be live. And It can be live any time automatically depending on its cap limits.
					<BR> &nbsp; &nbsp; If Mode is Inactive, offer can't be live on any pages.
					<BR> &nbsp; &nbsp; If Mode is Test, offer can be live on pages and collect the leads but those leads will not be delivered.
					<BR> &nbsp; &nbsp; If Mode is API Only, offers can't be live on any pages, but collect leads from API.
					<BR><BR>- Is Live: 1 = Live.
					<BR> &nbsp; &nbsp; If Is Live = 1, Offer is Active and currently displayed on Nibbles Pages.
					 </td></tr>
<tr><Td colspan=8><?php echo $sAddButton;?> </td></tr>

</table>
</form>
	
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>