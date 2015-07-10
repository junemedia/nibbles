<?php

/*********
Script to Display List/Delete Links
**********/

include("../../includes/paths.php");
include_once("../../nibbles2/libs/pixel.php");
session_start();

$sPageTitle = "Nibbles Links - List/Delete Link";
$sLoggedUser = $_SERVER['PHP_AUTH_USER'];
$sTrackingUser = $sLoggedUser;

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	$sCheckQuery = "SELECT * 
					FROM  accessRights, nbUsers
					WHERE  accessRights.userId = nbUsers.id
					AND accessRights.menuId = '216'
					AND nbUsers.userName = '$sLoggedUser'";
	$rCheckResult = dbQuery($sCheckQuery);
	
	if (dbNumRows($rCheckResult) == 0) {
		$sApprovedPartnersQuery = "SELECT partnerId 
								 FROM   campaignMgmntAccessRights
								 WHERE  userName = '$sLoggedUser'";
		
		$rResult = dbQuery($sApprovedPartnersQuery);
		
		if (dbNumRows($rResult) > 0) {
			$sBuildId = "";
			while ($oPartnersRow = dbFetchObject($rResult)) {
				$sBuildId .= "'$oPartnersRow->partnerId'".",";
			}
			$sBuildId = substr($sBuildId, 0, strlen($sBuildId)-1);
			
			$sPartnerToInclude = " AND links.partnerId IN ( $sBuildId )";
		} else {
			$sPartnerToInclude = " AND links.partnerId IN ( '0' )";
		}
	} else {
		$sPartnerToInclude = "";	
	}
	
	if ($sShowRedirect) {
		// start of track users' activity in nibbles
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					  VALUES('$sLoggedUser', '$PHP_SELF', now(), 'Clicked on Show Link & Pixel Tracking - SourceCode: $sSourceCode')";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		//$sNibbles2Path = "http://www.popularliving.com/nibbles2/ot.php";
		$linksQuery = "SELECT L.*, D.* FROM links L, maskingDomains D WHERE L.sourceCode = '$sSourceCode' and L.domainId = D.id";
		$rResult = dbQuery($linksQuery);
		$oLink = dbFetchObject($rResult);
		
		$sRedirectDomain = ($oLink->domainName ? "http://".$oLink->domainName."/nibbles2/ot.php" : "http://www.popularliving.com/nibbles2/ot.php");
		//$sUnsubDomain = ($oLink->domainName ? "http://".$oLink->domainName : "http://www.popularliving.com");
		
		if ($sMore == 'yes') {
			$sShowRedirect = '';
			$sTempSrc = explode(",", $sSourceCode);
			$sCountTempSrc = count($sTempSrc);
			$sNibbles2Links = "<br><center><b> Links:</b></center><br>";
			$sLegacySystemLinks = "<br><center><b> Legacy Link:</b></center><br>";
			$sNibbles2Supression = "<br><center><b> Unsub Link:</b></center><br>";
			$sPixelTracking = "<br><center><b> Pixel:</b></center><br>";
			
			$sContent = "<br><center>";
			for ($iCount=0; $iCount<$sCountTempSrc; $iCount++) {
				$sSourceCode = $sTempSrc[$iCount];
				$linksQuery = "SELECT L.*, D.* FROM links L, maskingDomains D WHERE L.sourceCode = '$sSourceCode' and L.domainId = D.id";
				$rResult = dbQuery($linksQuery);
				$oLink = dbFetchObject($rResult);
				$sRedirectDomain = ($oLink->domainName ? "http://".$oLink->domainName."/nibbles2/ot.php" : "http://www.popularliving.com/nibbles2/ot.php");
				
				$sRedirectLink = $sGblSourceRedirectsPath . "?src=". strtolower($sSourceCode);
				$sNibbles2Link = $sRedirectDomain . "?src=". strtolower($sSourceCode);
				
				$sUnSubLink = "http://".($oLink->domainName ? $oLink->domainName : "www.popularliving.com")."/partners/suppression/s.php?src=".strtolower($sSourceCode)."&e=[EMAIL]";
				$sNibbles2Links = "<center><font face=\"Arial, Helvetica, sans-serif\" size=2>Link: <a href= 'JavaScript:void(window.open(\"".$sNibbles2Link."\",\"\", \"\"));'>" . $sNibbles2Link . "</a></font></center>";
				$sLegacySystemLinks = "<center><font face=\"Arial, Helvetica, sans-serif\" size=2>Legacy Link: <a href= 'JavaScript:void(window.open(\"".$sRedirectLink."\",\"\", \"\"));'>" . $sRedirectLink . "</a></font></center>";
				$sNibbles2Supression = "<center><font face=\"Arial, Helvetica, sans-serif\" size=2>Unsub Link: <a href= 'JavaScript:void(window.open(\"".$sUnSubLink."\",\"\", \"\"));'>" . $sUnSubLink . "</a></font></center>";
				$sPixelTracking = "<center><font face=\"Arial, Helvetica, sans-serif\" size=2>Pixel: ".htmlspecialchars("<IMG src=\"" . $sGblSourcePixelsTrackingPath . "?s=$sSourceCode\" width=\"1\" height=\"1\">")."</font></center>";

				$sContent .= "$sNibbles2Links  $sNibbles2Supression <br>";
				$sPixels .= "$sPixelTracking<br>";
				$sLegacy .= "$sLegacySystemLinks<br>";
			}
			$sContent = $sContent.$sPixels.$sLegacy."</center><br>";
			$sShowRedirect = $sContent;
		} else {
			$sRedirectLink = $sGblSourceRedirectsPath . "?src=". strtolower($sSourceCode);
			$sNibbles2Link = $sRedirectDomain . "?src=". strtolower($sSourceCode);
			$sUnSubLink = "http://".($oLink->domainName ? $oLink->domainName : "www.popularliving.com")."/partners/suppression/s.php?src=".strtolower($sSourceCode)."&e=[EMAIL]";
			$sShowRedirect = "<center><font face=\"Arial, Helvetica, sans-serif\" size=2>
						Link:&nbsp; &nbsp;<a href= 'JavaScript:void(window.open(\"".$sNibbles2Link."\",\"\", \"\"));'>" . $sNibbles2Link . "</a><br>
						Unsub Link:&nbsp; &nbsp;<a href= 'JavaScript:void(window.open(\"".$sUnSubLink."\",\"\", \"\"));'>" . $sUnSubLink . "</a><br><br>
						Pixel:&nbsp; &nbsp;".htmlspecialchars("<IMG src=\"" . $sGblSourcePixelsTrackingPath . "?s=$sSourceCode\" width=\"1\" height=\"1\">")."<br><br>
						Legacy Link:&nbsp; &nbsp;<a href= 'JavaScript:void(window.open(\"".$sRedirectLink."\",\"\", \"\"));'>" . $sRedirectLink . "</a><br><br>
						</font></center>";
		}
	}
	
	
	if ($sDelete) {
		
		// if record deleted
		//select the sourcecode for reference
		$sSelectQuery = "SELECT sourceCode
						FROM links
						WHERE id = $iId";
		$rSelectResult = dbQuery($sSelectQuery);
		while ($oRow = dbFetchObject($rSelectResult)) {
			$sSourceCodeToDelete = $oRow->sourceCode;
		}
		
		$sDeleteQuery = "DELETE FROM links
	 			   		 WHERE  id = $iId"; 
		$rResult = dbQuery($sDeleteQuery);
		if ($rResult) {
			
			// start of track users' activity in nibbles
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sDeleteQuery) . "\")";
			$rLogResult = dbQuery($sLogAddQuery);
			// end of track users' activity in nibbles
		
			if ($sSourceCodeToDelete) {
					
				$sDeleteCustomFrameQuery = "DELETE FROM campaignCustomFrames
									 		WHERE  sourceCode = '$sSourceCodeToDelete'";
				$rDeleteCustomFrameResult = dbQuery($sDeleteCustomFrameQuery);
				
				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sDeleteCustomFrameQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
				
				$sDeleteTrackingDeleteQuery = "DELETE FROM offerStats
	 				    					   WHERE  sourceCode = '$sSourceCodeToDelete'"; 
				$rDeleteTrackingDeleteResult = dbQuery($sDeleteTrackingDeleteQuery);
				
				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  			VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sDeleteTrackingDeleteQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
			}
		} else {
			$sMessage = dbError();
		}
		
		// reset $id
		$iId = '';
	}
	
	// set default order by column
	if (!($sOrderColumn)) {
		$sOrderColumn = "sourceCode";
		$sSourceCodeOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($sOrderColumn) {
		
		case "partnerName" :
		$sCurrOrder = $sPartnerNameOrder;
		$sPartnerNameOrder = ($sPartnerNameOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "url" :
		$sCurrOrder = $sUrlOrder;
		$sUrlOrder = ($sUrlOrder != "DESC" ? "DESC" : "ASC");
		case "ioId":
		$sCurrOrder = $sioIdOrder;
		$sioIdOrder = ($sioIdOrder != "DESC" ? "DESC" : "ASC");
		case "groupName" :
		$sCurrOrder = $sGroupOrder;
		$sGroupOrder = ($sGroupOrder != "DESC" ? "DESC" : "ASC");
		break;
		default:
		$sCurrOrder = $sSourceCodeOrder;
		$sSourceCodeOrder = ($sSourceCodeOrder != "DESC" ? "DESC" : "ASC");
	}
	
	
	// Prepare filter part of the query if filter specified...
	if ($sFilter != '') {
		switch ($sSearchIn) {
			default:
			case 'any':
				$sFilterPart = " AND (groupName like '%$sFilter%' || url like '%$sFilter%' || sourceCode like '%$sFilter%' || dateTimeCreated like '%$sFilter%' || partnerCompanies.companyName like '%$sFilter%') ";
			break;
			case 'groupName':
				if ($sExactMatch == 'Y') {
					$sFilterPart = " AND (groupName = '$sFilter' || url = '$sFilter' ) ";
				} else {
					if ($sAlpha) {
						$sFilterPart = " AND (groupName like '%$sFilter%') ";
					} else {
						$sFilterPart = " AND (groupName like '%$sFilter%' || url like '%$sFilter%' ) ";
					}
				}
			break;
			case 'whoCreated':
				if ($sExactMatch == 'Y') {
					$sFilterPart = " AND (userName = '$sFilter') ";
				} else {
					$sFilterPart = " AND (userName like '%$sFilter%') ";
				}
			break;
			case 'dateTimeCreated':
				if ($sExactMatch == 'Y') {
					$sFilterPart = " AND (dateTimeCreated = '$sFilter') ";
				} else {
					$sFilterPart = " AND (dateTimeCreated like '%$sFilter%') ";
				}
			break;
/*			case 'IO':
				if ($sExactMatch == 'Y') {
					$sFilterPart = " AND (upper(concat(io.type,' ',partnerCompanies.companyName)) = upper('$sFilter')) ";
				} else {
					$sFilterPart = " AND (upper(concat(io.type,' ',partnerCompanies.companyName)) like upper('%$sFilter%')) ";
				}
			break;*/
			case 'partnerName':
				if ($sExactMatch == 'Y') {
					$sFilterPart = " AND (partnerCompanies.companyName = '$sFilter') ";
				} else {
					$sFilterPart = " AND (partnerCompanies.companyName like '%$sFilter%') ";
				}
			break;
			case 'sourceCode':
				if ($sExactMatch == 'Y') {
					$sFilterPart = " AND (sourceCode = '$sFilter' || url = '$sFilter') ";
				} else {
					if ($sAlpha) {
						$sFilterPart = " AND (sourceCode like '%$sFilter%') ";
					} else {
						$sFilterPart = " AND (sourceCode like '%$sFilter%' || url like '%$sFilter%' ) ";
					}
				}
			break;
		}
	}
	
	$sAnySelected = '';
	$sGroupNameSelected = '';
	$sDateTimeAddedSelected = '';
	$sIOSelected = '';
	$sPartnerNameSelected = '';
	$sSourceCodeSelected = '';
	switch ($sSearchIn) {
		case 'groupName':
		$sGroupNameSelected = "selected";
		break;
		case 'dateTimeCreated':
		$sDateTimeAddedSelected = "selected";
		break;
/*		case 'IO':
		$sIOSelected = "selected";
		break;*/
		case 'partnerName':
		$sPartnerNameSelected = "selected";
		break;
		case 'whoCreated':
		$sWhoCreatedSelected = "selected";
		break;
		case 'sourceCode':
		$sSourceCodeSelected = "selected";
		default:
		case 'any':
		$sAnySelected = "selected";
	}
	
						//<option value='IO' $sIOSelected>IO
	$sSearchInOptions = "<option value='any' $sAnySelected>Any
						<option value='sourceCode' $sSourceCodeSelected>Source Code
						<option value='partnerName' $sPartnerNameSelected>Partner Name
						<option value='whoCreated' $sWhoCreatedSelected>Who Created
						<option value='dateTimeCreated' $sDateTimeAddedSelected>Date/Time Created (yyyy-mm-dd hh:ii:ss)
						<option value='groupName' $sGroupNameSelected>Group Name";
	
	
	// Select Query to display list of payment methods
	// Specify Page no. settings
	
	if (!($iRecPerPage)) {
		$iRecPerPage = 10;
	}
	if (!($iPage)) {
		$iPage = 1;
	}
	
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sFilter=$sFilter&sExactMatch=$sExactMatch&sShowActive=$sShowActive&iRecPerPage=$iRecPerPage";
	
	if ($sShowActive == 'Y') {
		$sSelectQuery = "SELECT links.*,partnerCompanies.companyName AS partnerName, 
								partnerCompanies.id AS partnerId".	//LEFT JOIN concat(io.type,' ',partnerCompanies.companyName) as ioTitle ON partnerCompanies.id = io.partnerId
					 " FROM   links, partnerCompanies
					 WHERE  links.partnerId = partnerCompanies.id
	 						$sFilterPart 
	 						$sPartnerToInclude 
					 ORDER BY $sOrderColumn $sCurrOrder";
		
	
		$rSelectResult = dbQuery($sSelectQuery);
		while ($oRow = dbFetchObject($rSelectResult)) {
				$bIsActive = false;
				$iSiteId = '';
				$iSiteId = $oRow->siteId;
				
				// check if it's active campaign
				$sClicksQuery = "SELECT id
								 FROM	bdRedirectsTracking
								 WHERE  sourceCode = '$sSourceCode'";
				$rClicksResult = dbQuery($sClicksQuery);
				if ( dbNumRows($rClicksResult) > 0) {
					$bIsActive = true;					
				} else {
					$sClicksQuery2 = "SELECT id
								 FROM	bdRedirectsTrackingHistorySum
								 WHERE  sourceCode = '$sSourceCode'
								 AND    clickDate BETWEEN date_add(CURRENT_DATE, INTERVAL -90 DAY) AND CURRENT_DATE
								 LIMIT 0, 1";
					$rClicksResult2 = dbQuery($sClicksQuery2);
					if ( dbNumRows($rClicksResult2) > 0) {
						$bIsActive = true;					
					} else {
						//check leads ( as it may be created for partner api )
						$sLeadsQuery1 = "SELECT id
										 FROM	otData
										 WHERE  sourceCode = '$sSourceCode'";
						$rLeadsResult1 = dbQuery($sLeadsQuery1);
						if ( dbNumRows($rLeadsResult1) > 0) {
							$bIsActive = true;					
						} else {
							$sLeadsQuery2 = "SELECT id
										 FROM	otDataHistory
										 WHERE  sourceCode = '$sSourceCode'
										 AND    dateTimeAdded BETWEEN date_add(CURRENT_DATE, INTERVAL -90 DAY) AND CURRENT_DATE
										 LIMIT 0,1";
							$rLeadsResult2 = dbQuery($sLeadsQuery2);
							if ( dbNumRows($rLeadsResult2) > 0) {
							}
						}
					}
				}
				
				if ($bIsActive) {
				
				// For alternate background color
				
				
					if ($sBgcolorClass=="ODD") {
						$sBgcolorClass="EVEN";
					} else {
						$sBgcolorClass="ODD";
					}
					if ($sShowRedirect && $sSourceCode == $oRow->sourceCode) {
						$sSourceCodeDisplay = "<b>".$oRow->sourceCode."</b>";
					} else {
						$sSourceCodeDisplay = $oRow->sourceCode;
					}
					
					
					$sCampaignList .= "<tr class=$sBgcolorClass>
								<td>$sSourceCodeDisplay</td>
								<td>$oRow->url</td>
								<td>$oRow->ioId</td>
								<td>$oRow->groupName</td>
								<td>$oRow->partnerName</td>
						<TD><a href='JavaScript:void(window.open(\"addLink.php?iMenuId=$iMenuId&iId=".$oRow->id."&sFilter=$sFilter&sAlpha=$sAlpha&sExactMatch=$sExactMatch&sShowActive=$sShowActive&iRecPerPage=$iRecPerPage&iSiteId=$iSiteId\", \"AddCampaign\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					    	&nbsp;<a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a>
							&nbsp;<a href='".$sSortLink."&sSourceCode=".$oRow->sourceCode."&sShowRedirect=true&iPage=$iPage&sOrderColumn=$sOrderColumn&sCurrOrder=$sCurrOrder'>Show Link & Pixel Tracking</a>";
							if($oRow->ioId) $sCampaignList .= "&nbsp; <a href='JavaScript:void(window.open(\"/admin/ioManagement/IOPdf.php?iMenuId=245&iId=".$oRow->ioId."\", \"IO\", \"height=450,width=600,scrollbars=yes,resizable=yes,status=yes\"));'>Print IO</a>";
						$sCampaignList .= "
						<a href='http://test.popularliving.com/admin/rulesMgmnt/index.php?iMenuId=252&src=".$oRow->sourceCode."'>Nibbles2 Link Rules</a>
						<a href='JavaScript:void(window.open(\"/admin/popupsMgmnt/linkPopUpExclude.php?iMenuId=251&sourceCode=".$oRow->sourceCode."\", \"Exclude Specific Popups\", \"height=400, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Manage Popups Exclusion</a>
						<a href='JavaScript:void(window.open(\"customErrorMessages.php?iMenuId=251&sourceCode=".$oRow->sourceCode."\", \"Customize Error Messages.\", \"height=400, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Customize Error Messages.</a>

						<a href='JavaScript:void(window.open(\"customColors.php?iMenuId=$iMenuId&iId=".$oRow->id."\", \"Customize Colors\", \"height=400, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Customize Colors</a>
						</td>
						<td><a href = 'JavaScript:void(window.open(\"notes.php?iId=".$oRow->id."&iMenuId=$iMenuId\",\"\",\"scrollbars=yes\"));'>Notes</a>
						</td>
						<td><a href='JavaScript:void(window.open(\"emailLinks.php?src=".$oRow->sourceCode."&iMenuId=$iMenuId\",\"\",\"scrollbars=yes\"));'>Email this Link</a></td></tr>";
					
				}
		}
		
	} else {
		
	$sSelectQuery = "SELECT links.*, 
								partnerCompanies.companyName AS partnerName, 
								partnerCompanies.id AS partnerId".	//LEFT JOIN concat(io.type,' ',partnerCompanies.companyName) as ioTitle ON partnerCompanies.id = io.partnerId
					 " FROM   links, partnerCompanies
					 WHERE  links.partnerId = partnerCompanies.id
	 						$sFilterPart 
	 						$sPartnerToInclude 
					 ORDER BY $sOrderColumn $sCurrOrder";
	
	//mail('bbevis@amperemedia.com', __line__." links query","$sSelectQuery");
	
	$rSelectResult = dbQuery($sSelectQuery);
	echo dbError();
	$iNumRecords = dbNumRows($rSelectResult);
	
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
	
	$rSelectResult = dbQuery($sSelectQuery);
	if ($rSelectResult) {
		if (dbNumRows($rSelectResult) > 0) {
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
			
			while ($oRow = dbFetchObject($rSelectResult)) {
				
				// For alternate background color
				$iSiteId = '';
				$iSiteId = $oRow->siteId;
				
				if ($sBgcolorClass=="ODD") {
					$sBgcolorClass="EVEN";
				} else {
					$sBgcolorClass="ODD";
				}
				if ($sShowRedirect && $sSourceCode == $oRow->sourceCode) {
					$sSourceCodeDisplay = "<b>".$oRow->sourceCode."</b>";
				} else {
					$sSourceCodeDisplay = $oRow->sourceCode;
				}
				
				$pFactory = new PixelFactory();
				$list = $pFactory->pixelList($oRow->sourceCode);
				
				if(!is_array($list) || (count($list) == 0)){
						
					//then it should be a "make a pixel" link
					$MakeAPixelLink  = '';
					$args = array();
					array_push($args, 'src='.$oRow->sourceCode);
					array_push($args, 'pid='.$oRow->partnerId);
					
					if($oRow->captureType == 'emailCapture'){
						array_push($args, 'type=emailCap');
						array_push($args, 't=[email]');
						
					} else if($oRow->captureType == 'memberCapture'){
						array_push($args, 'memberCap');
						array_push($args, 't=[email]');
					}
					
					$MakeAPixelLink = "<td><a href='JavaScript:void(window.open(\"/admin/pixelMgmnt/addPixel.php?iMenuId=281&".join('&',$args)."\", \"AddPixel\", \"height=450, width=700, scrollbars=yes, resizable=yes, status=yes\"));'>Create a Pixel</a></td>";
					
				} else {
					
					$MakeAPixelLink = "<td><a href='JavaScript:void(window.open(\"/admin/pixelMgmnt/index.php?iMenuId=281&src=".$oRow->sourceCode."\", \"AddPixel\", \"height=450, width=700, scrollbars=yes, resizable=yes, status=yes\"));'>Manage Pixels</a></td>";
					
				}
				
				$sCampaignList .= "<tr class=$sBgcolorClass>
								<td>$sSourceCodeDisplay</td>
								<td>$oRow->url</td>
								<td>$oRow->ioId</td>
								<td>$oRow->groupName</td>
								<td>$oRow->partnerName</td>
						<TD><a href='JavaScript:void(window.open(\"addLink.php?iMenuId=$iMenuId&iId=".$oRow->id."&sFilter=$sFilter&sAlpha=$sAlpha&sExactMatch=$sExactMatch&sShowActive=$sShowActive&iRecPerPage=$iRecPerPage&iSiteId=$iSiteId\", \"AddCampaign\", \"height=450, width=700, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					    	&nbsp;<a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a>
							&nbsp;<a href='".$sSortLink."&sSourceCode=".$oRow->sourceCode."&sShowRedirect=true&iPage=$iPage&sOrderColumn=$sOrderColumn&sCurrOrder=$sCurrOrder'>Show Link & Pixel Tracking</a>";
							if($oRow->ioId) $sCampaignList .= "&nbsp; <a href='JavaScript:void(window.open(\"/admin/ioManagement/IOPdf.php?iMenuId=245&iId=".$oRow->ioId."\", \"IO\", \"height=450,width=600,scrollbars=yes,resizable=yes,status=yes\"));'>Print IO</a>";
						$sCampaignList .= "
						<a href='http://test.popularliving.com/admin/rulesMgmnt/index.php?iMenuId=252&src=".$oRow->sourceCode."'>Nibbles2 Link Rules</a>
						<a href='JavaScript:void(window.open(\"/admin/popupsMgmnt/linkPopUpExclude.php?iMenuId=251&sourceCode=".$oRow->sourceCode."\", \"Exclude Specific Popups\", \"height=400, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Manage Popups Exclusion</a>
						<a href='JavaScript:void(window.open(\"customErrorMessages.php?iMenuId=251&sourceCode=".$oRow->sourceCode."\", \"Customize Error Messages.\", \"height=400, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Customize Error Messages.</a>
						
						<a href='JavaScript:void(window.open(\"customColors.php?iMenuId=$iMenuId&iId=".$oRow->id."\", \"Customize Colors\", \"height=400, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Customize Colors</a>
						
						</td>
						$MakeAPixelLink
						
						<td><a href = 'JavaScript:void(window.open(\"notes.php?iId=".$oRow->id."&iMenuId=$iMenuId\",\"\",\"scrollbars=yes\"));'>Notes</a>
						</td>
						<td><a href='JavaScript:void(window.open(\"emailLinks.php?src=".$oRow->sourceCode."&iMenuId=$iMenuId\",\"\",\"scrollbars=yes\"));'>Email this Link</a></td></tr>";
			}
			
		} else {
			$sMessage = "No Records Exist...";
		}
	}
	
	}
	
	// Prepare A-Z links
	for ($i = 65; $i <= 90; $i++) {
		$sAlphaLinks .= "<a href='$PHP_SELF?iMenuId=$iMenuId&sFilter=".chr($i)."&sAlpha=Alpha'>".chr($i)."</a> ";
	}
	$sAlphaLinks .= " &nbsp; <a href='$PHP_SELF?iMenuId=$iMenuId&sFilter='>View All</a>";
	
	$sReportLink = "<a href=\"$sGblAdminSiteRoot/repBdRedirects/index.php?iMenuId=106\">Redirects Reporting</a>";
	//$frameMgmntLink = "<a href='Javascript:void(window.open(\"frameMgmnt.php?menuId=$menuId&menuFolder=$menuFolder\",\"\",\"\"));'>Frame Management</a>";
	
	$sSourceCodeAnalysisLink = "<a href=\"$sGblAdminSiteRoot/repSourceAnal/index.php?iMenuId=39\">Source Analysis</a>";
	
	$sIOMgmntLink = "<a href='$sGblAdminSiteRoot/ioManagement/index.php?iMenuId=245' class=header>IO Management</a>";
	
	$sPartnerMgmntLink = "<a href='$sGblAdminSiteRoot/partnersMgmnt/index.php?iMenuId=14' class=header>Partner Management</a>";
	
	$sAddGroupLink = "<a class=header href='JavaScript:void(window.open(\"addGroup.php\", \"AddGroup\", \"height=400, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Add New Group</a>";
	
	$sPixelTrackingLink = "<a href='$sGblAdminSiteRoot/repBdPixels/index.php?iMenuId=107' class=header>Pixel Tracking</a>";
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";
	
	$sAddButton ="<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addLink.php?iMenuId=$iMenuId&sFilter=$sFilter&sAlpha=$sAlpha&sExactMatch=$sExactMatch&iRecPerPage=$iRecPerPage\", \"addLink\", \"height=450, width=700, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
	$sFrameMgmntLink = "<a href='Javascript:void(window.open(\"frameMgmnt.php?iMenuId=$iMenuId\",\"frameMgmnt\",\"\"));'>Frame Management</a>";

	$sExactMatchChecked = '';
	if ($sExactMatch == 'Y') {
		$sExactMatchChecked = "checked";
	}
	
	$sShowActiveChecked = '';
	if ($sShowActive == 'Y') {
		$sShowActiveChecked = "checked";
	}
	
	include("../../includes/adminHeader.php");
	
	?>
	
<script language=JavaScript>
function confirmDelete(form1,id)
{
	if(confirm('Are you sure to delete this record ?'))
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
<?php echo $sShowRedirect;?>
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<input type=hidden name=sDelete>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td align=left><?php echo $sAddButton;?></td>
	<td><?php echo $sSourceCodeAnalysisLink; ?></td>
	<td colspan=2 align=right><?php echo $sIOMgmntLink;?> &nbsp; &nbsp; <?php echo $sPartnerMgmntLink;?> &nbsp; &nbsp; <?php echo $sPixelTrackingLink;?></td>
	<td colspan=2> &nbsp; &nbsp;<?php echo $sAddGroupLink;?></td>
	</tr>
<tr><td colspan=5>Alpha Search: &nbsp; <?php echo $sAlphaLinks;?></td></tr>
<tr><td>Filter By</td>
	<td colspan="2"><input type=text name=sFilter value='<?php echo $sFilter;?>'> &nbsp; 
		<input type=checkbox name=sExactMatch value='Y' <?php echo $sExactMatchChecked;?>> Exact Match
		<input type=checkbox name=sShowActive value='Y' <?php echo $sShowActiveChecked;?>> Show Active Links Only</td>
	<td><input type=submit name=sViewReport value='View Report'></td></tr>	
	
<tr><td>Search In:</td>
<td><select name="sSearchIn">
<?php echo $sSearchInOptions; ?>
</select>
</td>
<td></td>
<td><input type='text' name='sLinksToThisEmail' value=''><input name='bEmailTo' type='button' value='Mail Links To This Address'></td>
</tr>

<tr><td colspan=4 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>
<tr>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=sourceCode&sSourceCodeOrder=<?php echo $sSourceCodeOrder;?>' class=header>Source Code</a></td>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=url&sUrlOrder=<?php echo $sUrlOrder;?>' class=header>URL</a></td>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=ioId&sioIdOrder=<?php echo $sioIdOrder;?>' class=header>IO #</a></td>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=url&sGroupOrder=<?php echo $sGroupOrder;?>' class=header>Group</a></td>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=partnerName&sPartnerNameOrder=<?php echo $sPartnerNameOrder;?>' class=header>Partner Name</a></td>
</tr>

<?php echo $sCampaignList;?>
<tr><td colspan=4 align=right class=header><?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrPage;?></td></tr>
<tr><td align=left><?php echo $sAddButton;?></td><td colspan=2></td><td colspan=2 align=right><?php echo $sPartnerMgmntLink;?> &nbsp; &nbsp; <?php echo $sPixelTrackingLink;?></td></tr>

<tr><td colspan=2><b>Notes:</b><BR> &nbsp;Active links list shows the links which has clicks or leads in last 3 months.
					<BR> &nbsp;If only active links are listed, list page won't be refreshed after adding/editing a campaign. </td></tr>
</table>

</form>
	
<?php
include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>