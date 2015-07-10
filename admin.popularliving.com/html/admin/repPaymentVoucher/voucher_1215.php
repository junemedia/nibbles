<?php

/*********

Script to Display Add/Edit Partner Company

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Nibbles Partner Companies - Add/Edit Partner Company";

if (hasAccessRight($iMenuId) || isAdmin()) {

	$iCurrYear = date('Y');
	$iCurrMonth = date('m');
	$iCurrDay = date('d');
	$sCurrentMonthDate = "$iCurrYear-$iCurrMonth-01";
	
	// Get prev month name alongwith year
	$sPrevMonthQuery = "SELECT DATE_FORMAT(date_add(CURRENT_DATE, INTERVAL -1 MONTH), '%M %Y') AS prevMonthYear,
							   DATE_FORMAT(date_add('$sCurrentMonthDate', INTERVAL -1 DAY), '%m/%d/%Y') AS prevMonthLastDate ";
	$rPrevMonthResult = dbQuery($sPrevMonthQuery);
	
	while ($oPrevMonthRow = dbFetchObject($rPrevMonthResult)) {
		$sPrevMonthYear = $oPrevMonthRow->prevMonthYear;
		$sDateDue = $oPrevMonthRow->prevMonthLastDate;		
	}
	
		
	
// get partner details

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

$sRepDesignatedNames = '';

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

if ($sAcceptCc == 'Y') {
	$sDisplayAcceptCc = "Yes";
} else if($sAcceptCc =='N') {
	$sDisplayAcceptCc = "No";
} else if($sAcceptCc =='Yes Via PayPal') {
	$sDisplayAcceptCc = "Yes Via PayPal";
}

if ($sContactCity != '' || $sContactState != '' || $sContactZip != '') {
	$sContactAddress3 = "$sContactCity, $sContactState $sContactZip";
} else {
	$sContactAddress3 = "<BR><BR>";
}

	$fTotalAmount = 0.0;
	
	$sPaymentQuery = "SELECT campaigns.sourceCode, count(distinct otDataHistory.email) as uniqueUsers, 
							 count(distinct otDataHistory.email) * rate AS amount
					  FROM   userDataHistory, otDataHistory, campaigns	
					  WHERE  campaigns.sourceCode = otDataHistory.sourceCode
					  AND    campaigns.partnerId = '$iPartnerId'
					  AND	 userDataHistory.email = otDataHistory.email 
					  AND    date_format(otDataHistory.dateTimeAdded, '%Y-%m') = '$iYear-$iMonth'	
					  AND    address NOT LIKE \"3401 DUNDEE%\" AND userDataHistory.postalVerified = 'V' AND verified != 'I'
					  GROUP BY sourceCode ";
	
	$rPaymentResult = dbQuery($sPaymentQuery);
	echo dbError();
	while ($oPaymentRow = dbFetchObject($rPaymentResult)) {
		$sPaymentData .= "<tr><td>Marketing</td><td>$sPrevMonthYear lead generation for $oPaymentRow->sourceCode
							</td><td align=right>$oPaymentRow->amount</td></tr>" ;
		$fTotalAmount += $oPaymentRow->amount;
		
	}

$fTotalAmount =  sprintf("%12.2f",round($fTotalAmount, 2));
	
// Hidden fields to be passed with form submission
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iId value='$iId'>";

include("../../includes/adminAddHeader.php");

?>
<table cellpadding=5 cellspacing=5 bgcolor=#FFFFFF width=80% align=center>

<tr><td width=30%><img src='<?php echo $sGblSiteRoot;?>/images/ampereLogo.gif'></td>
	<td class=tiny align=center nowrap>The Experts In E-Magazine Publishing<BR><BR> 3400 Dundee Rd. Suite 236 &middot; Northbrook, IL 60062 847-205-9320 FAX: 847-205-9340</td></tr>	
	<tr><td colspan=2 class=header align=center><u>Payment Voucher</u><BR><BR></td></tr>
</table>
<table cellpadding=5 cellspacing=5 bgcolor=#FFFFFF width=70% align=center>	
	<tr><td class=header width=25%>Acct. Exec.: </td><td colspan=3><?php echo $sRepDesignatedNames;?></td></tr>
	<tr><td class=header>Name: </td><td colspan=3><?php echo $sCompanyName;?></td></tr>
	<tr><td class=header valign=top>Address: </td>
		<td colspan=3><?php echo $sContactAddress;?>
				<BR><?php echo $sContactAddress2;?>
				<BR><?php echo $sContactAddress3;?>
		</td>
	</tr>
	<tr><td class=header>Phone: </td><td width=20%><?php echo $sContactPhone;?></td>
		<td class=header >Fax: </td><td><?php echo $sFax;?></td>
	</tr>
	<tr><td class=header>E-Mail: </td><td><?php echo $sContactEmail;?></td>
		<td class=header>Tax I.D.#: </td><td><?php echo $sTaxId;?></td>
	</tr>
	<tr><td class=header>Contact Name: </td><td colspan=3><?php echo $sContactName;?></td></tr>
	<tr><td class=header>Payment terms:</td><td colspan=3><?php echo $sPaymentTerms;?></td></tr>
	<tr><td class=header>Credit Cards Accepted?</td><td colspan=3><?php echo $sDisplayAcceptCc;?></td></tr>	
	
</table>
<BR><BR>
<table cellpadding=2 cellspacing=0 bgcolor=#FFFFFF width=80% align=center border=1 bordercolor=#000000>
		
		
<tr><td class=header align=center>G/L acct</td>
	<td class=header align=center>Description</td>
	<td class=header  align=center width=70>Amount</td></tr>

<?php echo $sPaymentData;?>
</table>
<BR><BR>
<table cellpadding=2 cellspacing=0 bgcolor=#FFFFFF width=80% align=center border=0>
<tr><td>
	<table cellpadding=1 cellspacing=0 bgcolor=#FFFFFF width=40% align=right border=1 bordercolor=#000000>
<tr><td width=200>Total amount due: </td><td width=60 align=right>$ <?php echo $fTotalAmount;?></td></tr>
<tr><td width=200>Due Date: </td><td width=60 align=right><?php echo $sDateDue;?></td></tr>
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

</body>
</html>
<?php
} else {
	echo "You are not authorized to access this page...";
}
?>