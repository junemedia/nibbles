<?php

/*********

Script to Display Add/Edit Partner Company

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblIncludePath/reportInclude.php");

session_start();

$sPageTitle = "Nibbles Partner Companies - Payment Voucher";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($sAllowReport == 'N') {
		$sMessage = "Server Load Is High. Please check back soon...";
	} else {
		
		
		$iCurrYear = date('Y');
		$iCurrMonth = date('m');
		$iCurrDay = date('d');
		
		//$sVoucherMonthDate = "$iYear-$iMonth-01";
		
		// Get voucher month name alongwith year
		$sVoucherMonthQuery = "SELECT DATE_FORMAT('$iYear-$iMonth-01', '%M %Y') AS voucherMonthYear,
							   DATE_FORMAT(date_add(date_add('$iYear-$iMonth-01', INTERVAL 2 MONTH), INTERVAL -1 DAY), '%m/%d/%Y') AS dueDate ";
		$rVoucherMonthResult = dbQuery($sVoucherMonthQuery);
		
		while ($oVoucherMonthRow = dbFetchObject($rVoucherMonthResult)) {
			$sVoucherMonthYearDisplay = $oVoucherMonthRow->voucherMonthYear;
			$sDueDateDisplay = $oVoucherMonthRow->dueDate;
			$sDueDate = substr($sDueDateDisplay,6,4)."-".substr($sDueDateDisplay,0,2)."-".substr($sDueDateDisplay,3,2);
			
		}
		
		// Get last date of voucher month
		$sVoucherDateQuery = "SELECT DATE_FORMAT(date_add(date_add('$iYear-$iMonth-01', INTERVAL 1 MONTH), INTERVAL -1 DAY), '%Y-%m-%d') AS dateTo ";
		$rVoucherDateResult = dbQuery($sVoucherDateQuery);
		while ($oVoucherDateRow = dbFetchObject($rVoucherDateResult)) {
			$sDateTo = $oVoucherDateRow->dateTo;
		}
		$sDateFrom = "$iYear-$iMonth-01";
		$sDateTimeFrom = "$iYear-$iMonth-01 00:00:00";
		$sDateTimeTo = $sDateTo." 23:59:59";
		
		// get dateFrom and dateTo
		$sVoucherMonthYear = "$iYear-$iMonth";
		
		// get partner details
		
	// check if voucher already created for the selected partner and for selected month
	//get all partner's ids
	$sSelectQuery = "SELECT *
					  FROM   partnerCompanies where id = '11' order by id desc limit 0,1";
	
	$rSelectResult = dbQuery($sSelectQuery);
	while ($oSelectRow = dbFetchObject($rSelectResult)) {
		$iPartnerId = $oSelectRow->id;					
	
	$sCheckQuery = "SELECT *
					FROM   paymentVouchers
					WHERE  partnerId = '$iPartnerId' 
					AND    voucherMonthYear = '$iYear-$iMonth'";
	$rCheckResult = dbQuery($sCheckQuery);
	echo dbError(); 	 
		
	if (dbNumRows($rCheckResult) == 0 ) {
		unset($aPaymentArray);
		$sCompanyName = '';
		$sRepDesignated = '';
		$sPaymentTerms = '';
		$sFax = '';
		$sTaxId = '';
		$sAcceptCc = '';
		$sRepDesignatedNames = '';
		$sContactName = '';
		$sContactAddress = '';
		$sContactAddress2 = '';
		$sContactCity = '';
		$sContactState = '';
		$sContactZip = '';
		$sContactPhone = '';
		$sContactEmail = '';
					
		$sPartnerQuery = "SELECT *
				  FROM   partnerCompanies
				  WHERE  id = '$iPartnerId'";
		$rPartnerResult = dbQuery($sPartnerQuery);
		
		while ($oPartnerRow = dbFetchObject($rPartnerResult)) {
			$sCompanyName = $oPartnerRow->companyName;
			$sRepDesignated = $oPartnerRow->repDesignated;
			$sPaymentTerms = $oPartnerRow->paymentTerms;
			$sFax = $oPartnerRow->faxNo;
			$sTaxId = $oPartnerRow->taxId;
			$sAcceptCc = $oPartnerRow->acceptCc;
		}

		
		$sRepQuery = "SELECT *
			  FROM   nbUsers
			  WHERE  id IN (".$sRepDesignated.")";
		
		$rRepResult = dbQuery($sRepQuery);
		echo dbError();
		while ($oRepRow = dbFetchObject($rRepResult)) {
			$sRepDesignatedNames .= $oRepRow->userName.", ";
		}

		if ($sRepDesignatedNames != '') {
			$sRepDesignatedNames = substr($sRepDesignatedNames,0, strlen($sRepDesignatedNames)-2);
		}

		// get accounting contact info

		$sContactQuery = "SELECT *
				  FROM	 partnerContacts
				  WHERE  partnerId = '$iPartnerId'
				  AND    accountingContact = 'Y'";
		$rContactResult = dbQuery($sContactQuery);
		while ($oContactRow = dbFetchObject($rContactResult)) {
			$sContactName = $oContactRow->contact;
			$sContactAddress = $oContactRow->address1;
			$sContactAddress2 = $oContactRow->address2;
			$sContactCity = $oContactRow->city;
			$sContactState = $oContactRow->state;
			$sContactZip = $oContactRow->zip;

			$sContactPhone = $oContactRow->phoneNo;
			$sContactEmail = $oContactRow->email;
		}

		

		if ($sContactCity != '' || $sContactState != '' || $sContactZip != '') {
			$sContactAddress3 = "$sContactCity, $sContactState $sContactZip";
		} else {
			$sContactAddress3 = "<BR><BR>";
		}

		$fTotalAmount = 0.0;
		
		
/***************  If campaign is regular OT campaign and CPA *************************/
// fourth character in sourceCode would be "t" or "s" or "f"

		$sPaymentQuery = "SELECT otDataHistory.sourceCode, campaigns.rate,
							 count(distinct otDataHistory.email) as uniqueUsers, 
							 count(distinct otDataHistory.email) * rate AS amount
					  FROM   otDataHistory, campaigns	
					  WHERE  otDataHistory.dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
					  AND    campaigns.partnerId = '$iPartnerId'
					  AND    campaigns.typeCode != 'j'
					  AND    campaigns.campaignTypeId = '1'
					  AND    campaigns.sourceCode = otDataHistory.sourceCode
					  AND    processStatus = 'P'
	 				  AND    postalVerified = 'V'
					  GROUP BY otDataHistory.sourceCode 
					  ORDER BY otDataHistory.sourceCode";
		
		$rPaymentResult = dbQuery($sPaymentQuery);
		echo "<BR>".$sPaymentQuery.dbError();
		
		$i = 0;
		
		while ($oPaymentRow = dbFetchObject($rPaymentResult)) {
			$aPaymentArray['gAcct'][$i] = "Marketting";
			$aPaymentArray['sourceCode'][$i] = $oPaymentRow->sourceCode;
			$aPaymentArray['rate'][$i] = $oPaymentRow->rate;
			$aPaymentArray['amount'][$i] = $oPaymentRow->amount;
			$fTotalAmount += $aPaymentArray['amount'][$i];

			$i++;
		}		
		
/***************** End of regular OT campaign calculation **************************/

		
		
/***************** If campaign is Join campaign and CPA  *****************************/
// fourth character in sourceCode would be "j"
		$sCampaignsQuery = "SELECT *
						FROM   campaigns
						WHERE  partnerId = '$iPartnerId'
						AND    typeCode = 'j'
						AND    campaignTypeId = '1' ";
		$rCampaignsResult= dbQuery($sCampaignsQuery);
		echo dbError();
		
		while ($oCampaignsRow = dbFetchObject($rCampaignsResult)) {
			
			$sSourceCode = $oCampaignsRow->sourceCode;
			$fRate = $oCampaignsRow->rate;
									
			$sSubQuery = "SELECT joinEmailConfirm.sourceCode, count(distinct joinEmailConfirm.email) as uniqueUsers,
							 count(distinct joinEmailConfirm.email) * $fRate AS amount
				  FROM   joinEmailConfirm
				  WHERE  joinEmailConfirm.sourceCode = '$sSourceCode'
				  AND    joinEmailConfirm.dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
				  GROUP BY joinEmailConfirm.sourceCode 
				  ORDER BY joinEmailConfirm.sourceCode";			
			
			$rSubResult = dbQuery($sSubQuery);
			echo "<BR>".$sSubQuery;
			echo dbError();
			while ($oSubRow = dbFetchObject($rSubResult)) {
				$aPaymentArray['gAcct'][$i] = "Marketting";
				$aPaymentArray['sourceCode'][$i] = $sSourceCode;
				$aPaymentArray['rate'][$i] = $fRate;
				$aPaymentArray['amount'][$i] = $oSubRow->amount;
				$fTotalAmount += $aPaymentArray['amount'][$i];
				
				$i++;

			}
		}

/***************** End of CPA campaign calculation **************************/

		$fTotalAmount =  sprintf("%12.2f",round($fTotalAmount, 2));
	
		// get the next voucher no.
		$sTempQuery = "SELECT max(voucherNo) as maxVoucherNo
					   FROM   paymentVouchers";
		$rTempResult = dbQuery($sTempQuery);
		$iMaxVoucherNo = 0;
		while ($oTempRow = dbFetchObject($rTempResult)) {
			$iMaxVoucherNo = $oTempRow->maxVoucherNo;
		}
		
		$iNewVoucherNo = $iMaxVoucherNo +1;
		if ($iNewVoucherNo < 100) {
			$iNewVoucherNo = 100;
		}
					
		$iNewVoucherNo = str_pad($iNewVoucherNo, 6, 0, STR_PAD_LEFT);

		// make entry into paymentVouchers table
		$sVoucherInsertQuery = "INSERT INTO paymentVouchers(voucherNo, partnerId, contact, email, phoneNo,
								 	address1, address2, address3, paymentTerms, acceptCc, faxNo, taxId, 
								 	voucherMonthYear, totalAmountDue, dueDate, dateTimeCreated, createdBy )
							VALUES('$iNewVoucherNo', '$iPartnerId', \"$sContactName\", '$sContactEmail', '$sContactPhone',
								 \"$sContactAddress\", \"$sContactAddress2\", \"$sContactAddress3\", \"$sPaymentTerms\", 
								\"$sAcceptCc\", \"$sFax\", \"$sTaxId\", \"$sVoucherMonthYear\", \"$fTotalAmount\", 
								\"$sDueDate\", now(), '".$_SERVER['PHP_AUTH_USER']."')";
		$rVoucherInsertResult = dbQuery($sVoucherInsertQuery);

		echo dbError();
				
		for ($i=0; $i < count($aPaymentArray['sourceCode']); $i++) {

			//$sPaymentData .= "<tr><td class=mid>Marketing</td><td class=mid>$sVoucherMonthYearDisplay lead generation for ".$aPaymentArray['sourceCode'][$i]."
				//			</td><td align=right class=mid>".$aPaymentArray['amount'][$i]."</td></tr>" ;
			
			// make entry into voucher transaction table
			$sTransactionQuery = "INSERT INTO paymentVoucherTransactions(voucherNo, gAcct, sourceCode, rate, amount)
							  VALUES('$iNewVoucherNo', '".$aPaymentArray['gAcct'][$i]."', '".$aPaymentArray['sourceCode'][$i]."', 
									'".$aPaymentArray['rate'][$i]."', '".$aPaymentArray['amount'][$i]."')"; 
			$rTransactionResult = dbQuery($sTransactionQuery);
			echo dbError();
		}
		
		
	}
	
	
	 //else {
		// get voucher data from vouchers table
		$sVoucherQuery = "SELECT paymentVouchers.*, partnerCompanies.companyName
						   FROM   paymentVouchers, partnerCompanies
						   WHERE  paymentVouchers.partnerId = partnerCompanies.id
						   AND	  paymentVouchers.partnerId = '$iPartnerId'
						   AND	  voucherMonthYear = '$iYear-$iMonth'";

		$rVoucherResult = dbQuery($sVoucherQuery);

		while ($oVoucherRow = dbFetchObject($rVoucherResult)) {
			$iVoucherNo = $oVoucherRow->voucherNo;
			$sCompanyName = $oVoucherRow->companyName;
			$sRepDesignated = $oVoucherRow->repDesignated;
			$sPaymentTerms = $oVoucherRow->paymentTerms;
			$sFax = $oVoucherRow->faxNo;
			$sTaxId = $oVoucherRow->taxId;
			$sAcceptCc = $oVoucherRow->acceptCc;
			$sContactName = $oVoucherRow->contact;
			$sContactAddress = $oVoucherRow->address1;
			$sContactAddress2 = $oVoucherRow->address2;
			$sContactAddress3 = $oVoucherRow->address3;
			$fTotalAmount = $oVoucherRow->totalAmountDue;
		}

		// get voucher transaction info
		$i = 0;
		$sTransactionQuery = "SELECT *
							  FROM   paymentVoucherTransactions
							  WHERE  voucherNo = '$iVoucherNo'";
		$rTransactionResult = dbQuery($sTransactionQuery);
		echo dbError();
		while ($oTransactionRow = dbFetchObject($rTransactionResult)) {
				$aPaymentArray['gAcct'][$i] = $oTransactionRow->gAcct;
				$aPaymentArray['sourceCode'][$i] = $oTransactionRow->sourceCode;
				$aPaymentArray['rate'][$i] = $oTransactionRow->rate;
				$aPaymentArray['amount'][$i] = $oTransactionRow->amount;
				$i++;
		}

		/// get data from array to prepare voucher
		
		if (count($aPaymentArray['sourceCode']) > 0) {
			array_multisort($aPaymentArray['sourceCode'], SORT_ASC, $aPaymentArray['gAcct'], $aPaymentArray['rate'], $aPaymentArray['amount']);
		}

		$sVoucherData .= "
<table cellpadding=5 cellspacing=5 bgcolor=#FFFFFF width=80% align=center>

<tr><td width=30%><img src='$sGblSiteRoot/images/ampereLogo.gif'></td>
	<td class=tiny align=center nowrap><BR><BR> 3400 Dundee Rd. Suite 236 &middot; Northbrook, IL 60062 847-205-9320 FAX: 847-205-9340</td></tr>	
	<tr><td colspan=2 class=midHeader align=center><u>Payment Voucher</u><BR><BR></td></tr>
</table>
<table cellpadding=5 cellspacing=5 bgcolor=#FFFFFF width=70% align=center>	
	<tr><td class=midHeader width=25%>Acct. Exec.: </td><td colspan=3 class=mid>$sRepDesignatedNames</td></tr>
	<tr><td class=midHeader>Name: </td><td colspan=3 class=mid>$sCompanyName</td></tr>
	<tr><td class=midHeader valign=top>Address: </td>
		<td colspan=3 class=mid>$sContactAddress
				<BR>$sContactAddress2
				<BR>$sContactAddress3
		</td>
	</tr>
	<tr><td class=midHeader>Phone: </td><td width=20% class=mid>$sContactPhone</td>
		<td class=midHeader >Fax: </td><td class=mid>$sFax</td>
	</tr>
	<tr><td class=midHeader>E-Mail: </td><td class=mid>$sContactEmail</td>
		<td class=midHeader>Tax I.D.#: </td><td class=mid>$sTaxId</td>
	</tr>
	<tr><td class=midHeader>Contact Name: </td><td colspan=3 class=mid>$sContactName</td></tr>
	<tr><td class=midHeader>Payment terms:</td><td colspan=3 class=mid>$sPaymentTerms</td></tr>
	<tr><td class=midHeader>Credit Cards Accepted?</td><td colspan=3 class=mid>$sAcceptCc</td></tr>	
	
</table>
<BR><BR>
<table cellpadding=2 cellspacing=0 bgcolor=#FFFFFF width=80% align=center border=1 bordercolor=#000000>
		
		
<tr><td class=midHeader align=center>G/L acct</td>
	<td class=midHeader align=center>Description</td>
	<td class=midHeader  align=center width=70>Amount</td></tr>";
	
	
		for ($i=0; $i < count($aPaymentArray['sourceCode']); $i++) {

			$sPaymentData .= "<tr><td class=mid>Marketing</td><td class=mid>$sVoucherMonthYearDisplay lead generation for ".$aPaymentArray['sourceCode'][$i]."
							</td><td align=right class=mid>".$aPaymentArray['amount'][$i]."</td></tr>" ;
						
		}
		
		$sVoucherData .= "</table>
<BR><BR>
<table cellpadding=2 cellspacing=0 bgcolor=#FFFFFF width=80% align=center border=0>
<tr><td>
	<table cellpadding=1 cellspacing=0 bgcolor=#FFFFFF width=40% align=right border=1 bordercolor=#000000>
<tr><td width=200 class=mid>Total amount due: </td><td width=60 align=right class=mid>$ $fTotalAmount</td></tr>
<tr><td width=200 class=mid>Due Date: </td><td width=60 align=right class=mid>$sDueDateDisplay</td></tr>
</table>
</td></tr></table>

<BR><BR>
<table cellpadding=1 cellspacing=0 bgcolor=#FFFFFF width=80% align=center border=0>
<tr><td>
<table cellpadding=5 cellspacing=5 bgcolor=#FFFFFF width=70% align=right border=0>
<tr>
	<td class=header width=30% nowrap>Ampere Media contact approval:</td>	
	<td><u>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
	    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
	    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
	    </u></td>	
</tr>

<tr>
	<td class=header nowrap>Additional approval, if required:</td>	
	<td><u>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
	    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
	    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
	    </u></td>
</tr>

<tr>
	<td class=header nowrap>President approval:</td>	
	<td><u>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
	    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
	    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
	    </u></td>
</tr>

</table>
</td></tr></table><BR><BR><BR><BR>";
		
		
	} // end of partner select while loop
	} // end of load check else loop
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";
	
	include("../../includes/adminAddHeader.php");
	
?>
<?php echo $sVoucherData;?>
<!--<table cellpadding=5 cellspacing=5 bgcolor=#FFFFFF width=80% align=center>

<tr><td width=30%><img src='<php echo $sGblSiteRoot;?>/images/ampereLogo.gif'></td>
	<td class=tiny align=center nowrap><BR><BR> 3400 Dundee Rd. Suite 236 &middot; Northbrook, IL 60062 847-205-9320 FAX: 847-205-9340</td></tr>	
	<tr><td colspan=2 class=midHeader align=center><u>Payment Voucher</u><BR><BR></td></tr>
</table>
<table cellpadding=5 cellspacing=5 bgcolor=#FFFFFF width=70% align=center>	
	<tr><td class=midHeader width=25%>Acct. Exec.: </td><td colspan=3 class=mid><php echo $sRepDesignatedNames;?></td></tr>
	<tr><td class=midHeader>Name: </td><td colspan=3 class=mid><php echo $sCompanyName;?></td></tr>
	<tr><td class=midHeader valign=top>Address: </td>
		<td colspan=3 class=mid><php echo $sContactAddress;?>
				<BR><php echo $sContactAddress2;?>
				<BR><php echo $sContactAddress3;?>
		</td>
	</tr>
	<tr><td class=midHeader>Phone: </td><td width=20% class=mid><php echo $sContactPhone;?></td>
		<td class=midHeader >Fax: </td><td class=mid><php echo $sFax;?></td>
	</tr>
	<tr><td class=midHeader>E-Mail: </td><td class=mid><php echo $sContactEmail;?></td>
		<td class=midHeader>Tax I.D.#: </td><td class=mid><php echo $sTaxId;?></td>
	</tr>
	<tr><td class=midHeader>Contact Name: </td><td colspan=3 class=mid><php echo $sContactName;?></td></tr>
	<tr><td class=midHeader>Payment terms:</td><td colspan=3 class=mid><php echo $sPaymentTerms;?></td></tr>
	<tr><td class=midHeader>Credit Cards Accepted?</td><td colspan=3 class=mid><php echo $sAcceptCc;?></td></tr>	
	
</table>
<BR><BR>
<table cellpadding=2 cellspacing=0 bgcolor=#FFFFFF width=80% align=center border=1 bordercolor=#000000>
		
		
<tr><td class=midHeader align=center>G/L acct</td>
	<td class=midHeader align=center>Description</td>
	<td class=midHeader  align=center width=70>Amount</td></tr>

<php echo $sPaymentData;?>
</table>
<BR><BR>
<table cellpadding=2 cellspacing=0 bgcolor=#FFFFFF width=80% align=center border=0>
<tr><td>
	<table cellpadding=1 cellspacing=0 bgcolor=#FFFFFF width=40% align=right border=1 bordercolor=#000000>
<tr><td width=200 class=mid>Total amount due: </td><td width=60 align=right class=mid>$ <php echo $fTotalAmount;?></td></tr>
<tr><td width=200 class=mid>Due Date: </td><td width=60 align=right class=mid><php echo $sDueDateDisplay;?></td></tr>
</table>
</td></tr></table>

<BR><BR>
<table cellpadding=1 cellspacing=0 bgcolor=#FFFFFF width=80% align=center border=0>
<tr><td>
<table cellpadding=5 cellspacing=5 bgcolor=#FFFFFF width=70% align=right border=0>
<tr>
	<td class=header width=30% nowrap>Ampere Media contact approval:</td>	
	<td><u>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
	    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
	    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
	    </u></td>	
</tr>

<tr>
	<td class=header nowrap>Additional approval, if required:</td>	
	<td><u>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
	    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
	    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
	    </u></td>
</tr>

<tr>
	<td class=header nowrap>President approval:</td>	
	<td><u>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
	    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
	    &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
	    </u></td>
</tr>

</table>
</td></tr></table>
-->
</body>
</html>
<?php
} else {
	echo "You are not authorized to access this page...";
}
?>