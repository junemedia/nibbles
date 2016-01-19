<?php

/*********

Script to Display List/Delete Offer Companies information

*********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Offer Company Management";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	
	if ($delete) {
		// if record deleted...
		
		$deleteQuery = "DELETE FROM edOfferCompanies
						   WHERE id = '$id'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$result = mysql_query($deleteQuery);
		
		if(!($result)) {
			echo mysql_error();
		} else {
			// Select all offers
			$offerQuery = "SELECT id, offerCode
							FROM edOffers
							WHERE  companyId = '$id'";
			$offerResult = mysql_query($offerQuery);
						
			while ($offerRow = mysql_fetch_object($offerResult)) {
				$offerCode = $offerRow->offerCode;
				$offerId  = $offerRow->id;
				// Delete all redirect entries for this offercode
				$deleteQuery = "DELETE FROM edOfferRedirectsTracking
								WHERE  offerCode = '$offerCode'";
				$deleteResult = mysql_query($deleteQuery);
				
				// Delete all OfferCategoryRel entries for this offerId
				$deleteQuery = "DELETE FROM edOfferCategoryRel
								WHERE  offerId = '$offerId'";
				$deleteResult = mysql_query($deleteQuery);
				echo mysql_error();
			}
			
			$deleteQuery = "DELETE FROM edOffers
							WHERE offerCode = \"$offerCode\"";
			$deleteResult = mysql_query($deleteQuery);
		
			
			//reset $id to null
			$id = '';
		}
	}
	
	// Set Default order column
	if (!($orderColumn)) {
		$orderColumn = "companyName";
		$companyNameOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	if(!($currOrder)) {
		switch ($orderColumn) {
			case "code" :
			$currOrder = $codeOrder;
			$codeOrder = ($codeOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "P.paymentMethod" :
			$currOrder = $paymentMethodOrder;
			$paymentMethodOrder = ($paymentMethodOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "paymentAmount" :
			$currOrder = $paymentAmountOrder;
			$paymentAmountOrder = ($paymentAmountOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "paymentTerms" :
			$currOrder = $paymentTermsOrder;
			$paymentTermsOrder = ($paymentTermsOrder != "DESC" ? "DESC" : "ASC");
			break;
			case "affiliateMgmntCompany" :
			$currOrder = $affiliateCompanyOrder;
			$affiliateCompanyOrder = ($affiliateCompanyOrder != "DESC" ? "DESC" : "ASC");
			break;
			default:
			$currOrder = $companyNameOrder;
			$companyNameOrder = ($companyNameOrder != "DESC" ? "DESC" : "ASC");
		}
	}
	
	// Prepare filter part of the query if filter specified...
	if ($filter != '') {
		
		if ($exactMatch == 'Y') {
			$filterPart = " AND (code = '$filter' || O.companyName = '$filter' ) ";
		} else {
			if ($alpha) {
				$filterPart = " AND (O.companyName like '$filter%') ";
			} else {
				$filterPart = " AND (O.companyName like '$filter%' || code like '$filter%') ";
			}
		}
	}
	
	
	// Specify Page no. settings
	if (!($recPerPage)) {
		$recPerPage = 10;
	}
	if (!($page)) {
		$page = 1;
	}
	$startRec = ($page-1) * $recPerPage;
	$endRec = $startRec + $recPerPage -1;
	
	$filter = stripslashes($filter);
	$sortLink = "$PHP_SELF?iMenuId=$iMenuId&filter=$filter&alpha=$alpha&exactMatch=$exactMatch&recPerPage=$recPerPage";
	
	$filter = ascii_encode(stripslashes($filter));
	
	// Query to get the list of Offer Companies
	$selectQuery = "SELECT O.*, A.companyName affiliateCompany, P.paymentMethod as payMethod
				FROM edOfferCompanies O LEFT JOIN edAffiliateMgmntCompanies A ON O.affiliateMgmntCompany = A.id, edOfferPaymentMethods P
				WHERE O.paymentMethod = P.id
				$filterPart ";
	
	$result = mysql_query($selectQuery);
	$numRecords = mysql_num_rows($result);
	// Specify Page no. settings
	if (!($recPerPage)) {
		$recPerPage = 10;
	}
	if (!($page)) {
		$page = 1;
	}
	$totalPages = ceil($numRecords/$recPerPage);
	
	// If current page no. is greater than total pages move to the last available page no.
	if ($page > $totalPages) {
		$page = $totalPages;
	}
	
	$startRec = ($page-1) * $recPerPage;
	$endRec = $startRec + $recPerPage -1;
	
	if ($numRecords > 0) {
		$currentPage = " Page $page "."/ $totalPages";
	}
	
	$selectQuery .= " ORDER BY $orderColumn $currOrder
					 LIMIT $startRec, $recPerPage";
	
	$result = mysql_query($selectQuery);
	
	if ($result) {
		
		if (mysql_num_rows($result) > 0) {
			
			while ($row = mysql_fetch_object($result)) {
				
				// Prepare Next/Prev/First/Last links
				if ($numRecords > ($endRec + 1)) {
					$nextPage = $page + 1;
					$nextPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$nextPage&currOrder=$currOrder' class=header>Next</a>";
					$lastPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$totalPages&currOrder=$currOrder' class=header>Last</a>";
				}
				if ($page != 1) {
					$prevPage = $page - 1;
					$prevPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=$prevPage&currOrder=$currOrder' class=header>Previous</a>";
					$firstPageLink = "<a href='".$sortLink."&orderColumn=$orderColumn&page=1&currOrder=$currOrder' class=header>First</a>";
				}
				
				// Findout Rep Designated for this partner
				$tempRepDesignated = '';
				$repQuery = "SELECT firstName
						 FROM   nbUsers
						 WHERE  id = '".$row->repDesignated."'";
				
				$repResult = mysql_query($repQuery);
				
				while ($repRow = mysql_fetch_object($repResult)) {
					$tempRepDesignated = $repRow->firstName;
				}
				
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				//echo "<BR>".$row->affiliateCompany;
				$companyList .= "<tr class=$bgcolorClass><td>$row->companyName</td>
					<td>$row->code</td>
					<td>$row->payMethod</td>
					<td align=right>$row->paymentAmount</td>			
					<td>$row->paymentTerms</td>
					<td>$row->affiliateCompany</td>
					<td>$tempRepDesignated</td>
					<td><a href='JavaScript:void(window.open(\"addOfferCompany.php?iMenuId=$iMenuId&id=".$row->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a></td></tr>";				
			}
		} else {
			$sMessage = "No Records Exist...";
		}
		mysql_free_result($result);
		
	} else {
		echo mysql_error();
	}
	
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addOfferCompany.php?iMenuId=$iMenuId\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
	
	if ($exactMatch == 'Y') {
		$exactMatchChecked = "checked";
	}
	
	// Prepare A-Z links
	for ($i = 65; $i <= 90; $i++) {
		$alphaLinks .= "<a href='$PHP_SELF?iMenuId=$iMenuId&filter=".chr($i)."&alpha=alpha'>".chr($i)."</a> ";
	}
	$alphaLinks .= " &nbsp; <a href='$PHP_SELF?iMenuId=$iMenuId&filter='>View All</a>";
	
	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";
	
		
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
					if(confirm('ALL THE OFFERS AND REDIRECT ENTRIES OF THIS COMPANY WILL BE DELETED\n\n                            Are you sure to delete this record ?'))
					{											
						document.form1.elements['delete'].value='Delete';
						document.form1.elements['id'].value=id;				
						document.form1.submit();												
					}
				}
				
</script>
		
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $hidden;?>
<input type=hidden name=delete>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td><?php echo $addButton;?></td></tr>
<tr><td colspan=5>Alpha Search: &nbsp; <?php echo $alphaLinks;?></td></tr>
<tr><td>Filter By</td><td colspan="4"><input type=text name=filter value='<?php echo $filter;?>'> &nbsp; 
	<input type=checkbox name=exactMatch value='Y' <?php echo $exactMatchChecked;?>> Exact Match</td><td><input type=submit name=viewReport value='View Report'></td></tr>	
<tr><td colspan=8 align=right class=header><input type=text name=recPerPage value='<?php echo $recPerPage;?>' size=2 onChange='funcRecPerPage(this);'> &nbsp;Records Per Page &nbsp; &nbsp; <?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>	
<tr>
	<td align=left><a href="<?php echo $sortLink;?>&orderColumn=O.companyName&companyNameOrder=<?php echo $companyNameOrder;?>" class=header>Company Name</a></td>
	<td align=left><a href="<?php echo $sortLink;?>&orderColumn=code&codeOrder=<?php echo $codeOrder;?>" class=header>Code</a></td>	
	<td align=left><a href="<?php echo $sortLink;?>&orderColumn=P.paymentMethod&paymentMethodOrder=<?php echo $paymentMethodOrder;?>" class=header>Payment Method</a></td>
	<td align=right><a href="<?php echo $sortLink;?>&orderColumn=paymentAmount&paymentAmountOrder=<?php echo $paymentAmountOrder;?>" class=header>Payment Amount</a></td>
	<td align=left><a href="<?php echo $sortLink;?>&orderColumn=paymentTerms&paymentTermsOrder=<?php echo $paymentTermsOrder;?>" class=header>Payment Terms</a></td>
	<td align=left><a href="<?php echo $sortLink;?>&orderColumn=A.companyName&affiliateCompanyOrder=<?php echo $affiliateCompanyOrder;?>" class=header>Affiliate Company</a></td>		
	<td class=header>Rep.</td>	
	<td>&nbsp; </td>
</tr>
<?php echo $companyList;?>
<tr><td colspan=8 align=right class=header><?php echo $firstPageLink;?> &nbsp; <?php echo $prevPageLink;?> &nbsp; <?php echo $nextPageLink;?> &nbsp; <?php echo $lastPageLink;?> &nbsp; <?php echo $currentPage;?></td></tr>			
<tr><td><?php echo $addButton;?></td></tr>
</table>

</form>

<?php

include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>

	
