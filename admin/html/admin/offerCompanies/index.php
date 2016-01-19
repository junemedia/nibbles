<?php

/*********

Script to Display List/Delete Offer Companies

**********/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Offer Companies - List/Delete Offer Company";

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {	
	
	if ($sDelete) {
		// if record deleted
		// check if offer exists 
		$sCheckQuery = "SELECT *
						FROM   offers
						WHERE  companyId = '$iId'";
		
		$rCheckResult = dbQuery($sCheckQuery);
		if (dbNumRows($rCheckResult) == 0 ) {
		$sDeleteQuery = "DELETE FROM offerCompanies
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
			// Delete any contact records of this Partner
			$sContactDeleteQuery = "DELETE FROM offerCompanyContacts
										WHERE companyId = '$id'";
			$rContactDeleteResult = dbQuery($sContactDeleteQuery);
		} else {
			echo dbQrror();
		}
				
		// reset $id
		$iId = '';
		} else {
			$sMessage = "Company can not be deleted. Offer related to this company...";
		}
	}
	

	include("../../includes/adminHeader.php");
		
	// Set Default order column
	if (!($sOrderColumn)) {
		$sOrderColumn = "companyName";
		$sCompanyNameOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($sOrderColumn) {
		case "code" :
		$sCurrOrder = $sCodeOrder;
		$sCodeOrder = ($sCodeOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "contact" :
		$sCurrOrder = $sContactOrder;
		$sContactOrder = ($sContactOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "phoneNo" :
		$sCurrOrder = $sPhoneNoOrder;
		$sPhoneNoOrder = ($sPhoneNoOrder != "DESC" ? "DESC" : "ASC");
		break;		
		case "paymentTerm" :
		$sCurrOrder = $sPaymentTermOrder;
		$sPaymentTermOrder = ($sPaymentTermOrder != "DESC" ? "DESC" : "ASC");
		break;		
		default:
		$sCurrOrder = $sCompanyNameOrder;
		$sCompanyNameOrder = ($sCompanyNameOrder != "DESC" ? "DESC" : "ASC");
	}
	
	// Prepare filter part of the query if filter specified...
	if ($sFilter != '') {
		if ($sExactMatch == 'Y') {
			$sFilterPart = " WHERE companyName = '$sFilter' ";
		} else {
			$sFilterPart = " WHERE companyName like '$sFilter%' ";
		}
	}
	
	// Query to get the list of offer companies
	$sSelectQuery = "SELECT O.*, PT.paymentTerm, C.id AS contactId, C.contact, C.email, C. phoneNo, 
						   C.address, C.address2, C.city, C.state, C.zip
					FROM   offerCompanies O LEFT JOIN offerCompanyContacts C
					ON  (O.id = C.companyId AND C.defaultContact = 'Y') 						
						LEFT JOIN paymentTerms PT ON (O.paymentTermId = PT.id)										
					$sFilterPart
					ORDER BY $sOrderColumn $sCurrOrder";
	
	$rSelectResult = dbQuery($sSelectQuery);
	echo dbError();
	while ($oRow = dbFetchObject($rSelectResult)) {
		
		// Findout Rep Designated for this company
				$sTempRepDesignated = '';
				if ($oRow->repDesignated == '') {
					$iTempVar = "'0'";
				} else {
					$iTempVar = $oRow->repDesignated;
				}
				
				// get first name of all rep. designated for this company
				$sRepQuery = "SELECT firstName
						 FROM   nbUsers
						 WHERE  id IN ( " . $iTempVar . " )";
				
				$rRepResult = dbQuery($sRepQuery);
				while ($oRepRow = dbFetchObject($rRepResult)) {
					$sTempRepDesignated .= $oRepRow->firstName."<br>";
				}
				
				if ($sBgcolorClass == "ODD") {
					$sBgcolorClass = "EVEN";
				} else {
					$sBgcolorClass = "ODD";
				}
				
				$sCompanyList .= "<tr class=$sBgcolorClass><td>$oRow->companyName</td>
					<td>$oRow->code</td>
					<td><a href='JavaScript:void(window.open(\"emailClient.php?iMenuId=$iMenuId&iId=".$oRow->contactId."\",\"Email\",\"width=600, height=400, scrollbars=yes\"));'>$oRow->contact</a>
					<br><a href='JavaScript:void(window.open(\"contacts.php?iMenuId=$iMenuId&iCompanyId=$oRow->id\",\"\",\"\"));'>Contacts...</a></td>					
					<td>$oRow->phoneNo</td>
					<td>$oRow->paymentTerm</td>
					<td>$sTempRepDesignated</td><td><a href='JavaScript:void(window.open(\"addCompany.php?iMenuId=$iMenuId&iId=".$oRow->id."&sReturnTo=list\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					&nbsp; <a href='JavaScript:confirmDelete(this,".$oRow->id.");' >Delete</a></td></tr>
					</td>";
					//<td>$oRow->address1<br>$oRow->address2</td>			
					//<td>$oRow->city</td><td>$oRow->state</td>
					//<td>$oRow->zip</td>
					
	}
	
	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Records Exist...";
	}
	
		
	$sAddButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addCompany.php?iMenuId=$iMenuId&sReturnTo=list\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";

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
						document.form1.elements['sDelete'].value='Delete';
						document.form1.elements['iId'].value=id;
						document.form1.submit();								
					}
				}						
</script>
		
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $sHidden;?>
<input type=hidden name=sDelete>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td><?php echo $sAddButton;?></td><td colspan=9 align=right><?php echo $sPixelsTrackingLink;?></td></tr>
<tr><td colspan=5>Alpha Search: &nbsp; <?php echo $sAlphaLinks;?></td></tr>
<tr><td>Filter By</td><td colspan="4"><input type=text name=sFilter value='<?php echo $sFilter;?>'> &nbsp; 
	<input type=checkbox name=sExactMatch value='Y' <?php echo $sExactMatchChecked;?>> Exact Match</td>
	<td colspan=3><input type=submit name=sViewReport value='View Report'></td></tr>	
<tr>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=companyName&sCompanyNameOrder=<?php echo $sCompanyNameOrder;?>" class=header>Company Name</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=code&sCodeOrder=<?php echo $sCodeOrder;?>" class=header>Code</a></th>	
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=contact&sContactOrder=<?php echo $sContactOrder;?>" class=header>Contact</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=phoneNo&sPhoneNoOrder=<?php echo $sPhoneNoOrder;?>" class=header>Phone No</a></th>	
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=paymentTerm&sPaymentTermOrder=<?php echo $sPaymentTermOrder;?>" class=header>Payment Terms</a></th>
			
	<td class=header>Rep.</td>
	<th>&nbsp; </th>
</tr>
<?php echo $sCompanyList;?>
<tr><td><?php echo $sAddButton;?></td><td colspan=9 align=right><?php echo $sPixelsTrackingLink;?></td></tr>
</table>
</form>
	
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>