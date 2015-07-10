<?php

/*********
Script to display Insertaion Orders
**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Insertion Order Management";

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	include("../../includes/adminHeader.php");
	// Prepare filter part of the query if filter specified...
	$sPartnerCompanyFilterId = '';
	$sOfferCompanyFilterId = '';
	if ($sFilter != '') {
		if ($sExactMatch == 'Y') {
			$sPFilterQuery = "SELECT id FROM partnerCompanies WHERE companyName = '$sFilter'";
			$sOFilterQuery = "SELECT id FROM offerCompanies WHERE companyName = '$sFilter'";
		} else {
			$sPFilterQuery = "SELECT id FROM partnerCompanies WHERE companyName LIKE '%$sFilter%'";
			$sOFilterQuery = "SELECT id FROM offerCompanies WHERE companyName LIKE '%$sFilter%'";
		}

		$rResult = dbQuery($sPFilterQuery);
		while ($pRow = dbFetchObject($rResult)) {
			$sPartnerCompanyFilterId .= "'$pRow->id',";
		}
		if ($sPartnerCompanyFilterId !='') {
			$sPartnerCompanyFilterId = substr($sPartnerCompanyFilterId,0,strlen($sPartnerCompanyFilterId)-1);
			$sPartnerCompanyFilterId = " AND (publisherId IN ($sPartnerCompanyFilterId) ";
		}

		$rResult = dbQuery($sOFilterQuery);
		while ($oRow = dbFetchObject($rResult)) {
			$sOfferCompanyFilterId .= "'$oRow->id',";
		}
		if ($sOfferCompanyFilterId !='') {
			$sOfferCompanyFilterId = substr($sOfferCompanyFilterId,0,strlen($sOfferCompanyFilterId)-1);
			$sOfferCompanyFilterId = " OR advertiserId IN ($sOfferCompanyFilterId)) ";
		}
	}
	
	// Query to get the list of BDPartners
	$sSelectQuery = "SELECT * FROM io
					WHERE 1
					$sPartnerCompanyFilterId
					$sOfferCompanyFilterId ";
	$rSelectResult = dbQuery($sSelectQuery);
	
	echo dbError();
	while ($oRow = dbFetchObject($rSelectResult)) {
		// Findout Rep Designated for this partner
		$sTempRepDesignated = '';
		if ($oRow->repId == '') {
			$iTempVar = "'0'";
		} else {
			$iTempVar = $oRow->repId;
		}

		// get user name of all rep. designated for this company
		$sRepQuery = "SELECT userName
				 FROM   nbUsers
				 WHERE  id IN ( " . $iTempVar . " )";
		$rRepResult = dbQuery($sRepQuery);
		while ($oRepRow = dbFetchObject($rRepResult)) {
			$sTempRepDesignated .= $oRepRow->userName."<br>";
		}
		
		$sCompanyName = '';
		$sContactName = '';
		if ($oRow->mediaType == 'buying') {
			// get partner companies drop down
			$sContactQuery = "SELECT C.companyName, P.contact FROM partnerCompanies C, partnerContacts P WHERE C.id = P.partnerId AND P.defaultContact = 'Y' and C.id = ".$oRow->publisherId;
			$rRepResult = dbQuery($sContactQuery);
			while ($oRepRow = dbFetchObject($rRepResult)) {
				$sCompanyName = $oRepRow->companyName;
				$sContactName = $oRepRow->contact;
			}
		} else {
			// get partner companies drop down
			$sContactQuery = "SELECT offerCompanies.companyName, offerCompanyContacts.contact 
								FROM offerCompanies, offerCompanyContacts
								WHERE offerCompanies.id = offerCompanyContacts.companyId
								AND offerCompanies.id = ".$oRow->advertiserId;
			$rRepResult = dbQuery($sContactQuery);
			while ($oRepRow = dbFetchObject($rRepResult)) {
				$sCompanyName = $oRepRow->companyName;
				$sContactName = $oRepRow->contact;
			}
		}
				
		
				
		if ($sBgcolorClass == "ODD") {
			$sBgcolorClass = "EVEN";
		} else {
			$sBgcolorClass = "ODD";
		}
				
				
		$sIOList .= "<tr class=$sBgcolorClass>
			<td>$oRow->ioNum</td>
			<td>$sTempRepDesignated</td>
			<td>$sCompanyName</td>
			<td>$oRow->agencyName</td>
			<td>$sContactName</td>
			<td>$oRow->dateGenerated</td>
			<td><a href='IOPdf.php?iMenuId=$iMenuId&iId=$oRow->id' target='_other'>View / Print PDF</a> | 
			<a href='printRtf.php?iMenuId=$iMenuId&iId=$oRow->id' target='_other'>View / Print Word</a> | 
			<a href='JavaScript:void(window.open(\"addIO.php?iMenuId=$iMenuId&iId=$oRow->id\", \"\", \"height=450,width=600,scrollbars=yes,resizable=yes,status=yes\"));'>Edit</a></td>
			</tr></td>";
	}
	//http://test.popularliving.com/admin/ioManagement/
	
	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Records Exist...";
	}
	
	// Display Add Button if user has the permission and Not already clicked on Add Button

	$sAddButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addIO.php?iMenuId=$iMenuId\", \"\", \"height=450,width=600,scrollbars=yes,resizable=yes,status=yes\"));'>";

	if ($sExactMatch == 'Y') {
		$sExactMatchChecked = "checked";
	}
	
	// Prepare A-Z links
	for ($i = 65; $i <= 90; $i++) {
		$sAlphaLinks .= "<a href='$PHP_SELF?iMenuId=$iMenuId&sFilter=".chr($i)."'>".chr($i)."</a> ";
	}
	
	$sAlphaLinks .= " &nbsp; <a href='$PHP_SELF?iMenuId=$iMenuId&sFilter='>View All</a>";
	
		
	$sSortLink = $PHP_SELF."?iMenuId=$iMenuId&sFilter=$sFilter&sExactMatch=$sExactMatch";
		
	// set pixel tracking link		
	$sPartnersManagementLink = "<a href='$sGblAdminSiteRoot/linksMgmnt/index.php?iMenuId=23' class=header>Links Management</a>";
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";
		
	?>
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td><?php echo $sAddButton;?></td><td colspan=9 align=right><?php echo $sPartnersManagementLink;?></td></tr>
<tr><td colspan=5>Alpha Search: &nbsp; <?php echo $sAlphaLinks;?></td></tr>
<tr><td>Filter By</td><td colspan="4"><input type=text name=sFilter value='<?php echo $sFilter;?>'> &nbsp; 
	<input type=checkbox name=sExactMatch value='Y' <?php echo $sExactMatchChecked;?>> Exact Match
	&nbsp;&nbsp;&nbsp;&nbsp;<input type=submit name=sViewReport value='View Report'></td></tr>	
<tr>
	<th class="header" align="left">IO #</th>
	<th class="header" align="left">Rep</th>
	<th class="header" align="left">Publisher / Advertiser</th>
	<th class="header" align="left">Agency</th>
	<th class="header" align="left">Contact Name</th>
	<th class="header" align="left">Date Generated</th>
</tr>
<?php echo $sIOList;?>
<tr><td><?php echo $sAddButton;?></td><td colspan=9 align=right><?php echo $sPartnersManagementLink;?></td></tr>
</table>
</form>
	
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>