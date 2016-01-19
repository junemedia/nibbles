<?php

/*********

Script to Display List/Delete Campaigns

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Campaigns - List/Delete Campaign";

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	
	if ($sShowRedirect) {
		$sRedirectLink = $sGblSourceRedirectsPath . "?src=". strtolower($sSourceCode);
		$sShowRedirect = "<center><font face=\"Arial, Helvetica, sans-serif\" size=2><b> Redirect:</b>&nbsp; &nbsp;<a href= 'JavaScript:void(window.open(\"".$sRedirectLink."\",\"\", \"\"));'>" . $sRedirectLink . "</a></font></center>
					<center><font face=\"Arial, Helvetica, sans-serif\" size=2><b> Pixel Tracking:</b>&nbsp; &nbsp;".htmlspecialchars("<IMG src=\"" . $sGblSourcePixelsTrackingPath . "?s=$sSourceCode\" width=\"1\" height=\"1\">")."</font></center>";	
	}
	
	
	if ($sDelete) {
		// if record deleted
		//select the sourcecode for reference
		$sSelectQuery = "SELECT sourceCode
						FROM campaigns
						WHERE id = $iId";
		$rSelectResult = dbQuery($sSelectQuery);
		while ($oRow = dbFetchObject($rSelectResult)) {
			$sSourceCodeToDelete = $oRow->sourceCode;
		}
		
		$sDeleteQuery = "DELETE FROM campaigns
	 			   		 WHERE  id = $iId"; 
		$rResult = dbQuery($sDeleteQuery);
		if ($rResult) {
			if ($sSourceCodeToDelete) {
					
				$sDeleteCustomFrameQuery = "DELETE FROM campaignCustomFrames
									 		WHERE  sourceCode = '$sSourceCodeToDelete'";
				$rDeleteCustomFrameResult = dbQuery($sDeleteCustomFrameQuery)	;
				
				$sDeleteTrackingDeleteQuery = "DELETE FROM offerStats
	 				    					   WHERE  sourceCode = '$sSourceCodeToDelete'"; 
				$rDeleteTrackingDeleteResult = dbQuery($sDeleteTrackingDeleteQuery);
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
		break;
		default:
		$sCurrOrder = $sSourceCodeOrder;
		$sSourceCodeOrder = ($sSourceCodeOrder != "DESC" ? "DESC" : "ASC");
	}
	
	
	// Prepare filter part of the query if filter specified...
	if ($sFilter != '') {
		if ($sExactMatch == 'Y') {
			$sFilterPart = " AND (sourceCode = '$sFilter' || url = '$sFilter' || companyName = '$sFilter') ";
		} else {
			if ($sAlpha) {
				$sFilterPart = " AND (sourceCode like '$sFilter%') ";
			} else {
				$sFilterPart = " AND (sourceCode like '$sFilter%' || url like '$sFilter%' || companyName like '$sFilter%') ";
			}
		}
	}
	
	
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
		$sSelectQuery = "SELECT campaigns.*, partnerCompanies.companyName AS partnerName
					 FROM   campaigns, partnerCompanies
					 WHERE  campaigns.partnerId = partnerCompanies.id
	 						$sFilterPart 
					 ORDER BY $sOrderColumn $sCurrOrder";
	
		$rSelectResult = dbQuery($sSelectQuery);
		while ($oRow = dbFetchObject($rSelectResult)) {
				$bIsActive = false;
				
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
								<td>$oRow->partnerName</td>
						<TD><a href='JavaScript:void(window.open(\"addCampaign.php?iMenuId=$iMenuId&iId=".$oRow->id."&sFilter=$sFilter&sAlpha=$sAlpha&sExactMatch=$sExactMatch&sShowActive=$sShowActive&iRecPerPage=$iRecPerPage\", \"AddCampaign\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					    	&nbsp;<a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a>
							&nbsp;<a href='".$sSortLink."&sSourceCode=".$oRow->sourceCode."&sShowRedirect=true&iPage=$iPage&sOrderColumn=$sOrderColumn&sCurrOrder=$sCurrOrder'>Show Redirect & Pixel Tracking</a>
						</td>
						<td><a href = 'JavaScript:void(window.open(\"notes.php?iId=".$oRow->id."&iMenuId=$iMenuId\",\"\",\"scrollbars=yes\"));'>Notes</a>
						</td></tr>";
					
				}
		}
		
	} else {
		
	$sSelectQuery = "SELECT campaigns.*, partnerCompanies.companyName AS partnerName
					 FROM   campaigns, partnerCompanies
					 WHERE  campaigns.partnerId = partnerCompanies.id
	 						$sFilterPart 
					 ORDER BY $sOrderColumn $sCurrOrder";
	
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
								<td>$oRow->partnerName</td>
						<TD><a href='JavaScript:void(window.open(\"addCampaign.php?iMenuId=$iMenuId&iId=".$oRow->id."&sFilter=$sFilter&sAlpha=$sAlpha&sExactMatch=$sExactMatch&sShowActive=$sShowActive&iRecPerPage=$iRecPerPage\", \"AddCampaign\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					    	&nbsp;<a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a>
							&nbsp;<a href='".$sSortLink."&sSourceCode=".$oRow->sourceCode."&sShowRedirect=true&iPage=$iPage&sOrderColumn=$sOrderColumn&sCurrOrder=$sCurrOrder'>Show Redirect & Pixel Tracking</a>
						</td>
						<td><a href = 'JavaScript:void(window.open(\"notes.php?iId=".$oRow->id."&iMenuId=$iMenuId\",\"\",\"scrollbars=yes\"));'>Notes</a>
						</td></tr>";
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
	
	$sPartnerMgmntLink = "<a href='$sGblAdminSiteRoot/partnersMgmnt/index.php?iMenuId=14' class=header>Partner Management</a>";
	$sPixelTrackingLink = "<a href='$sGblAdminSiteRoot/repBdPixels/index.php?iMenuId=107' class=header>Pixel Tracking</a>";
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";
	
	$sAddButton ="<input type=button name=sAdd value=Add onClick='JavaScript:void(window.open(\"addCampaign.php?iMenuId=$iMenuId&sFilter=$sFilter&sAlpha=$sAlpha&sExactMatch=$sExactMatch&iRecPerPage=$iRecPerPage\", \"addCampaign\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
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
	<td><?php echo $sReportLink;?> &nbsp; &nbsp; <?php echo $sFrameMgmntLink;?></td>
	<td colspan=2 align=right><?php echo $sPartnerMgmntLink;?> &nbsp; &nbsp; <?php echo $sPixelTrackingLink;?></td></tr>
<tr><td colspan=5>Alpha Search: &nbsp; <?php echo $sAlphaLinks;?></td></tr>
<tr><td>Filter By</td>
	<td colspan="2"><input type=text name=sFilter value='<?php echo $sFilter;?>'> &nbsp; 
		<input type=checkbox name=sExactMatch value='Y' <?php echo $sExactMatchChecked;?>> Exact Match
		<input type=checkbox name=sShowActive value='Y' <?php echo $sShowActiveChecked;?>> Show Actives Only</td>
	<td><input type=submit name=sViewReport value='View Report'></td></tr>	
<tr><td colspan=4 align=right class=header><input type=text name=iRecPerPage value='<?php echo $iRecPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; 
&nbsp; Go To Page <input type=text name=iPage value='<?php echo $iPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp; &nbsp; <?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrentPage;?></td></tr>
<tr>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=sourceCode&sSourceCodeOrder=<?php echo $sSourceCodeOrder;?>' class=header>Source Code</a></td>
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=url&sUrlOrder=<?php echo $sUrlOrder;?>' class=header>URL</a></td>	
	<td><a href='<?php echo $sSortLink;?>&sOrderColumn=partnerName&sPartnerNameOrder=<?php echo $sPartnerNameOrder;?>' class=header>Partner Name</a></td>
</tr>

<?php echo $sCampaignList;?>
<tr><td colspan=4 align=right class=header><?php echo $sFirstPageLink;?> &nbsp; <?php echo $sPrevPageLink;?> &nbsp; <?php echo $sNextPageLink;?> &nbsp; <?php echo $sLastPageLink;?> &nbsp; <?php echo $sCurrPage;?></td></tr>
<tr><td align=left><?php echo $sAddButton;?></td><td colspan=2><?php echo $sReportLink;?> &nbsp; &nbsp; <?php echo $sFrameMgmntLink;?></td><td colspan=2 align=right><?php echo $sPartnerMgmntLink;?> &nbsp; &nbsp; <?php echo $sPixelTrackingLink;?></td></tr>
</table>

</form>
	
<?php
include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>