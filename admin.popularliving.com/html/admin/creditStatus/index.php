<?php

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Offer Companies - View/Adjust Credit Status";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	$aOfferCompaniesId = array();
	if ($sSaveCreditStatus) {
		$sSelectQuery = "SELECT *
						 FROM   offerCompanies
						 ORDER BY id";
		$rSelectResult = dbQuery($sSelectQuery);
		$ii = 0;
		while ($oSelectRow = dbFetchObject($rSelectResult)) {
			$iTempId = $oSelectRow->id;
			$sVarName = "sCS_".$iTempId;
			$sPaymentTermVarName = "iPayId_".$iTempId;
			$sCLimitVarName = "fCL_".$iTempId;
			
			$sOldCredit = $oSelectRow->creditStatus;
			
			if (isset($$sVarName)) {
				$sUpdateQuery = "UPDATE offerCompanies
								 SET    creditStatus = '".$$sVarName."'										
								 WHERE  id = '$iTempId'";	
				$sTempData = $sUpdateQuery;
				$rUpdateResult = dbQuery($sUpdateQuery);
				echo dbError();
				
				if ($sOldCredit != $$sVarName) {
					$aOfferCompaniesId[$ii] = $iTempId;
					$ii++;
				}
			}
			if (isset($$sPaymentTermVarName)) {
				$sUpdateQuery = "UPDATE offerCompanies
								 SET    paymentTermId = '".$$sPaymentTermVarName."'
								 WHERE  id = '$iTempId'";
				$sTempData .= "\n".$sUpdateQuery;
				$rUpdateResult = dbQuery($sUpdateQuery);
				echo dbError();
			}
			if (isset($$sCLimitVarName)) {
				$sUpdateQuery = "UPDATE offerCompanies
								 SET    creditLimit = '".$$sCLimitVarName."'
								 WHERE  id = '$iTempId'";
				$sTempData .= "\n".$sUpdateQuery;
				$rUpdateResult = dbQuery($sUpdateQuery);
				echo dbError();
			}

			// start of track users' activity in nibbles
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Save Credit Status - $sTempData\")";
			$rResult = dbQuery($sAddQuery);
			echo  dbError();
			// end of track users' activity in nibbles
		}
		
		//print_r($aOfferCompaniesId);
		// send alert email that the credit status changed.
		if (count($aOfferCompaniesId) > 0) {
			foreach ($aOfferCompaniesId as $iCompanyId) {
				$sSelectQuery = "SELECT *
						 FROM   offerCompanies
						 WHERE id = '$iCompanyId'";
				$rSelectResult = dbQuery($sSelectQuery);
				while ($oSelectRow = dbFetchObject($rSelectResult)) {
					$sRepName = '';
					
					$sGetRep = "SELECT concat(firstName,' ',lastName) as name
							FROM nbUsers WHERE id IN ($oSelectRow->repDesignated)";
					$rRepResult = dbQuery($sGetRep);
					while ($oRepRow = dbFetchObject($rRepResult)) {
						$sRepName .= $oRepRow->name." / ";
					}
					
					$sMailMessage = "This action by:  $sTrackingUser\n";
					
					if ($sRepName !='') {
						$sRepName = substr($sRepName,0,strlen($sRepName)-3);
						$sMailMessage .= "Rep:  $sTrackingUser\n";
					}
					
					$sMailMessage .= "Company Name:  $oSelectRow->companyName\n";
					$sMailMessage .= "\n\nNew Credit Status: ";
					
					if ($oSelectRow->creditStatus == 'ok') {
						$sMailMessage .= "OK\n";
						$sMailMessage .= "Old Credit Status: HOLD\n";
					} else {
						$sMailMessage .= "HOLD\n";
						$sMailMessage .= "Old Credit Status: OK\n";
					}

					
					$sGetEmail = "SELECT * FROM emailRecipients WHERE purpose='Offer Companies Credit Changed'";
					$rEmailResult = dbQuery($sGetEmail);
					while ($oEmailRow = dbFetchObject($rEmailResult)) {
						$sEmailTo = $oEmailRow->emailRecipients;
					}
					
					
					$sHeader = "FROM: nibbles@amperemedia.com";
					mail($sEmailTo,'Offer Credit Changed',$sMailMessage,$sHeader);
				}
			}
			unset($aOfferCompaniesId);
		}
		// End - send alert email that the credit status changed.
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
		case "creditStatus" :
		$sCurrOrder = $sCreditStatusOrder;
		$sCreditStatusOrder = ($sCreditStatusOrder != "DESC" ? "DESC" : "ASC");
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
	
				
	// Query to get the list of BDPartners
	$sSelectQuery = "SELECT O.*, C.id AS contactId, C.contact, C.email, C. phoneNo, 
						   C.address, C.address2, C.city, C.state, C.zip
					FROM   offerCompanies O LEFT JOIN offerCompanyContacts C
					ON  (O.id = C.companyId AND C.defaultContact = 'Y')
					$sFilterPart
					ORDER BY $sOrderColumn $sCurrOrder";
	
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Report Button Clicked: $sSelectQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
	
	
	
	
	$rSelectResult = dbQuery($sSelectQuery);
	echo dbError();
	while ($oRow = dbFetchObject($rSelectResult)) {
		
		$sCreditStatus = $oRow->creditStatus;
		
		// Findout Rep Designated for this partner
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
				
				
				// Prepare Payment Term options for selection box
				// get payment term result to display separate selections box for each company
	
				$sPaymentTermQuery = "SELECT *
								 	  FROM   paymentTerms
									  ORDER BY paymentTerm";
	
				$rPaymentTermResult = dbQuery($sPaymentTermQuery);
				
				$sPaymentTermOptions = "<option value=''>";
				while ($oPaymentTermRow = dbFetchObject($rPaymentTermResult)) {
					if ($oPaymentTermRow->id == $oRow->paymentTermId) {
						$sSelected = "selected";
					} else {
						$sSelected = "";
					}
		
					$sPaymentTermOptions .= "<option value=$oPaymentTermRow->id $sSelected>$oPaymentTermRow->paymentTerm";	
				}

				$sCompanyList .= "<tr class=$sBgcolorClass><td>$oRow->companyName</td>
					<td>$oRow->code</td>
					<td><a href='JavaScript:void(window.open(\"$sGblAdminSiteRoot/offerCompanies/emailClient.php?iMenuId=15&iId=".$oRow->contactId."\",\"Email\",\"width=600, height=400, scrollbars=yes\"));'>$oRow->contact</a>
					<br><a href='JavaScript:void(window.open(\"$sGblAdminSiteRoot/offerCompanies/contacts.php?iMenuId=15&iCompanyId=$oRow->id\",\"\",\"\"));'>Contacts...</a></td>					
					<td>$oRow->phoneNo</td>
					<td>$sTempRepDesignated</td>
					<td><select name='iPayId_".$oRow->id."' >$sPaymentTermOptions</select></td>";
				
				$sOkChecked = '';
				$sHoldCehcked = '';
				
				if ($sCreditStatus == 'ok') {
					$sOkChecked = "checked";
				} else if ($sCreditStatus == 'hold') {
					$sHoldCehcked = "checked";
				}
				
				$sCompanyList .= "<td><input type=radio name='sCS_".$oRow->id."' value='ok' $sOkChecked> OK &nbsp;
									  <input type=radio name='sCS_".$oRow->id."' value='hold' $sHoldCehcked> Hold	
									</td>";
				
				$sCompanyList .= "<td>\$<input type=text name='fCL_".$oRow->id."' value='".$oRow->creditLimit."' size=10></td>";
				
					$sCompanyList .= "</tr>";
				
				
					
	}
	
	if (dbNumRows($rSelectResult) == 0) {
		$sMessage = "No Records Exist...";
	}
		
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
	
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>
<input type=hidden name=sDelete>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td colspan=8>Alpha Search: &nbsp; <?php echo $sAlphaLinks;?></td></tr>
<tr><td>Filter By</td><td colspan=3><input type=text name=sFilter value='<?php echo $sFilter;?>'> &nbsp; 
	<input type=checkbox name=sExactMatch value='Y' <?php echo $sExactMatchChecked;?>> Exact Match</td>
	<td colspan=3><input type=submit name=sViewReport value='View Report'></td>
	<td><input type="submit" name="sSaveCreditStatus" value="Save Credit Status"></td></tr>	
<tr>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=companyName&sCompanyNameOrder=<?php echo $sCompanyNameOrder;?>" class=header>Company Name</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=code&sCodeOrder=<?php echo $sCodeOrder;?>" class=header>Code</a></th>	
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=contact&sContactOrder=<?php echo $sContactOrder;?>" class=header>Contact</a></th>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=phoneNo&sPhoneNoOrder=<?php echo $sPhoneNoOrder;?>" class=header>Phone No</a></th>	
	<td class=header>Rep.</td>
	<th align=left><a href="<?php echo $sSortLink;?>&sOrderColumn=paymentTerm&sPaymentTermOrder=<?php echo $sPaymentTermOrder;?>" class=header>Payment Terms</a></th>			
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=creditStatus&sCreditStatusOrder=<?php echo $sCreditStatusOrder;?>" class=header>Credit Status</a></td>
	<td class=header><a href="<?php echo $sSortLink;?>&sOrderColumn=creditLimit&sCreditLimitOrder=<?php echo $sCreditLimitOrder;?>" class=header>Credit Limit</a></td>
</tr>
<?php echo $sCompanyList;?>
<tr><td colspan=7>&nbsp;</td>
<td><input type="submit" name="sSaveCreditStatus" value="Save Credit Status"></td>
</tr>
</table>
</form>
	
<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>