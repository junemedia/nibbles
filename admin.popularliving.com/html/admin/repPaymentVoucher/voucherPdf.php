<?php

/*********

Script to Display Add/Edit Partner Company

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblLibsPath/pdfFunctions.php");
include("$sGblIncludePath/reportInclude.php");


$iCurrYear = date('Y');
$iCurrMonth = date('m');
$iCurrDay = date('d');

$iCurrHH = date('H');
$iCurrMM = date('i');
$iCurrSS = date('s');

session_start();

$sPageTitle = "Nibbles Partner Companies - Payment Voucher";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];


if (hasAccessRight($iMenuId) || isAdmin()) {

	if ($sAllowReport == 'N') {
		$sMessage = "Server Load Is High. Please check back soon...";
	} else {
		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Get voucher: $sViewReport $iVoucherNo\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
		
		
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

		// set pdf object
		$oPdf = new FPDF();

		$sTempRepDesignated = "'".$iRepDesignated."'";

		if ($iVoucherNo) {
			$sSelectQuery = "SELECT *
							 FROM   paymentVouchers
							 WHERE  voucherNo = '$iVoucherNo'";
			$rSelectResult = dbQuery($sSelectQuery);
			while ($oSelectRow = dbFetchObject($rSelectResult)) {
				$iPartnerId = $oSelectRow->partnerId;
				$sVoucherMonthYear = $oSelectRow->voucherMonthYear;
				$iYear = substr($sVoucherMonthYear,0,4);
				$iMonth = substr($sVoucherMonthYear,5,2);
			}
		}

		//get all partner's ids
		if ($sViewReport == 'calculate') {
			$sSelectQuery = "SELECT *
					 	 FROM   partnerCompanies ";
			if ($iRepDesignated != 'all' && $iRepDesignated != '') {
				$sSelectQuery .= " WHERE FIND_IN_SET(\"$sTempRepDesignated\", repDesignated) > 0 ";
			}

			if ($iPartnerId != 'all' && $iPartnerId != '') {
				if (strstr($sSelectQuery,"WHERE")) {
					$sSelectQuery .= " AND id = '$iPartnerId' ";
				} else {
					$sSelectQuery .= " WHERE id = '$iPartnerId' ";
				}
			}

			$sSelectQuery .= " ORDER BY id DESC";
			$rSelectResult = dbQuery($sSelectQuery);
			echo dbError();

			while ($oSelectRow = dbFetchObject($rSelectResult)) {
				$iTempPartnerId = $oSelectRow->id;

				// check if voucher already created for the selected partner and for selected month
				unset($aPaymentArray);
				//$aPaymentArray = array();
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

				$aPaymentArray['gAcct'] = array();
				$aPaymentArray['sourceCode'] = array();
				$aPaymentArray['rate'] = array();
				$aPaymentArray['amount'] = array();

				$sPartnerQuery = "SELECT *
				  FROM   partnerCompanies
				  WHERE  id = '$iTempPartnerId'";
				$rPartnerResult = dbQuery($sPartnerQuery);

				while ($oPartnerRow = dbFetchObject($rPartnerResult)) {
					$sCompanyName = $oPartnerRow->companyName;
					$sPaymentTerms = $oPartnerRow->paymentTerms;
					$sFax = $oPartnerRow->faxNo;
					$sTaxId = $oPartnerRow->taxId;
					$sAcceptCc = $oPartnerRow->acceptCc;
				}

				// get accounting contact info
				$sContactQuery = "SELECT *
				  FROM	 partnerContacts
				  WHERE  partnerId = '$iTempPartnerId'";

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
					$sContactAddress3 = "";
				}

				/***************  If campaign is regular OT/Sweeps campaign and CPA *************************/
				// fourth character in sourceCode would be "t" or "s" or "f"

				$sPaymentQuery = "SELECT otDataHistory.sourceCode, links.rate,
							 count(distinct otDataHistory.email) * rate AS amount
					  FROM   otDataHistory, links, offers
					  WHERE  otDataHistory.dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
					  AND    links.partnerId = '$iTempPartnerId'
					  AND    links.typeCode != 'J'
					  AND    links.campaignRateTypeId = '1'
					  AND    links.sourceCode = otDataHistory.sourceCode
					  AND    processStatus = 'P'
	 				  AND    postalVerified = 'V'
	 				  AND    offers.offerCode = otDataHistory.offerCode
	 				  AND    offers.isNonRevenue != '1'
					  GROUP BY otDataHistory.sourceCode 
					  ORDER BY otDataHistory.sourceCode";

				$rPaymentResult = dbQuery($sPaymentQuery);
				echo dbError();

				$i = 0;
				while ($oPaymentRow = dbFetchObject($rPaymentResult)) {
					$aPaymentArray['gAcct'][$i] = "Marketing";
					$aPaymentArray['sourceCode'][$i] = $oPaymentRow->sourceCode;
					$aPaymentArray['rate'][$i] = $oPaymentRow->rate;
					$aPaymentArray['amount'][$i] = $oPaymentRow->amount;
					$i++;
				}

				/***************** End of regular OT campaign calculation **************************/

				/***************  If campaign is regular OT/Sweeps campaign and Revenue Share *************************/
				// fourth character in sourceCode would be "t" or "s" or "f"
				$sPaymentQuery = "SELECT otDataHistory.sourceCode, links.rate,
							 sum(otDataHistory.revPerLead * rate) AS amount
					  FROM   otDataHistory, links, offers
					  WHERE  otDataHistory.dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
					  AND    links.partnerId = '$iTempPartnerId'
					  AND    links.typeCode != 'J'
					  AND    links.campaignRateTypeId = '4'
					  AND    links.sourceCode = otDataHistory.sourceCode
					  AND    processStatus = 'P'
	 				  AND    postalVerified = 'V'
	 				  AND    offers.offerCode = otDataHistory.offerCode
	 				  AND    offers.isNonRevenue != '1'
					  GROUP BY otDataHistory.sourceCode 
					  ORDER BY otDataHistory.sourceCode";
				$rPaymentResult = dbQuery($sPaymentQuery);
				echo dbError();

				while ($oPaymentRow = dbFetchObject($rPaymentResult)) {
					$aPaymentArray['gAcct'][$i] = "Marketing";
					$aPaymentArray['sourceCode'][$i] = $oPaymentRow->sourceCode;
					$aPaymentArray['rate'][$i] = $oPaymentRow->rate;
					$aPaymentArray['amount'][$i] = $oPaymentRow->amount;
					$i++;
				}

				/***************** End of regular OT/Sweeps campaign and Revenue Share calculation **************************/

				/***************  If campaign is regular OT/Sweeps campaign and CPM *************************/
				// fourth character in sourceCode would be "t" or "s" or "f"

				$sPaymentQuery = "SELECT otDataHistory.sourceCode, links.rate,
							 count(distinct otDataHistory.email) * rate AS amount
					  FROM   otDataHistory, links, offers
					  WHERE  otDataHistory.dateTimeAdded BETWEEN '$sDateTimeFrom' AND '$sDateTimeTo'
					  AND    links.partnerId = '$iTempPartnerId'
					  AND    links.typeCode != 'J'
					  AND    links.campaignRateTypeId = '3'
					  AND    links.sourceCode = otDataHistory.sourceCode
					  AND    processStatus = 'P'
	 				  AND    postalVerified = 'V'
	 				  AND    offers.offerCode = otDataHistory.offerCode
	 				  AND    offers.isNonRevenue != '1'
					  GROUP BY otDataHistory.sourceCode 
					  ORDER BY otDataHistory.sourceCode";

				$rPaymentResult = dbQuery($sPaymentQuery);
				echo dbError();

				while ($oPaymentRow = dbFetchObject($rPaymentResult)) {
					$aPaymentArray['gAcct'][$i] = "Marketing";
					$aPaymentArray['sourceCode'][$i] = $oPaymentRow->sourceCode;
					$aPaymentArray['rate'][$i] = $oPaymentRow->rate;
					$aPaymentArray['amount'][$i] = $oPaymentRow->amount;
					$i++;
				}

				/***************** End of regular OT campaign calculation **************************/
				
				
				
				/***************  If campaign is regular OT/Sweeps campaign and CPC *************************/
				// fourth character in sourceCode would be "t" or "s" or "f"
				$sPaymentQuery = "SELECT links.sourceCode, links.rate,
							 sum(clicks) * rate AS amount
					  FROM   bdRedirectsTrackingHistorySum, links	
					  WHERE  clickDate BETWEEN '$sDateFrom' AND '$sDateTo'
					  AND    links.partnerId = '$iTempPartnerId'
					  AND    links.typeCode != 'J'
					  AND    links.campaignRateTypeId = '2'
					  AND    links.sourceCode = bdRedirectsTrackingHistorySum.sourceCode					  
					  GROUP BY bdRedirectsTrackingHistorySum.sourceCode 
					  ORDER BY bdRedirectsTrackingHistorySum.sourceCode";
				$rPaymentResult = dbQuery($sPaymentQuery);
				echo dbError();
				while ($oPaymentRow = dbFetchObject($rPaymentResult)) {
					$aPaymentArray['gAcct'][$i] = "Marketing";
					$aPaymentArray['sourceCode'][$i] = $oPaymentRow->sourceCode;
					$aPaymentArray['rate'][$i] = $oPaymentRow->rate;
					$aPaymentArray['amount'][$i] = $oPaymentRow->amount;
					$i++;
				}
				/***************** End of regular OT campaign calculation **************************/

				/***************** If campaign is Join campaign  *****************************/
				// fourth character in sourceCode would be "j"
				$sCampaignsQuery = "SELECT *
						FROM   links
						WHERE  partnerId = '$iTempPartnerId'
						AND    typeCode = 'j'
						AND    campaignRateTypeId = '1' ";
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
					echo dbError();
					while ($oSubRow = dbFetchObject($rSubResult)) {
						$aPaymentArray['gAcct'][$i] = "Marketing";
						$aPaymentArray['sourceCode'][$i] = $sSourceCode;
						$aPaymentArray['rate'][$i] = $fRate;
						$aPaymentArray['amount'][$i] = $oSubRow->amount;
						$i++;
					}
				}

				/***************** End of CPA campaign calculation **************************/
				$fTotalAmount = 0.0;
				$fTotalAmount = array_sum($aPaymentArray['amount']);
				
				// get the next voucher no.
					$sTempQuery = "SELECT max(voucherNo) as maxVoucherNo
						   FROM   paymentVouchers";
					$rTempResult = dbQuery($sTempQuery);
					$iMaxVoucherNo = 0;
					while ($oTempRow = dbFetchObject($rTempResult)) {
						$iMaxVoucherNo = $oTempRow->maxVoucherNo;
					}
	
					$iNewVoucherNo = $iMaxVoucherNo + 1;
					if ($iNewVoucherNo < 100) {
						$iNewVoucherNo = 100;
					}

				if ($fTotalAmount > 0) {
					
	
					// make entry into paymentVouchers table
					$sCheckQuery = "SELECT *
						FROM   paymentVouchers
						WHERE  partnerId = '$iTempPartnerId' 
						AND    voucherMonthYear = '$iYear-$iMonth'";
					$rCheckResult = dbQuery($sCheckQuery);
					echo dbError();
					if ( dbNumRows($rCheckResult) == 0) {
						$sVoucherInsertQuery = "INSERT INTO paymentVouchers(voucherNo, partnerId, contact, email, phoneNo,
									 	address1, address2, address3, paymentTerms, acceptCc, faxNo, taxId, 
									 	voucherMonthYear, totalAmountDue, dueDate, dateTimeCreated, createdBy )
								VALUES('$iNewVoucherNo', '$iTempPartnerId', \"$sContactName\", '$sContactEmail', '$sContactPhone',
									 \"$sContactAddress\", \"$sContactAddress2\", \"$sContactAddress3\", \"$sPaymentTerms\", 
									\"$sAcceptCc\", \"$sFax\", \"$sTaxId\", \"$sVoucherMonthYear\", \"".$fTotalAmount."\", 
									\"$sDueDate\", now(), '".$sTrackingUser."')";
						$rVoucherInsertResult = dbQuery($sVoucherInsertQuery);
						echo dbError();
					} else {
						while ($oCheckRow = dbFetchObject($rCheckResult)) {
							$iNewVoucherNo = $oCheckRow->voucherNo;
						}
						$sVoucherUpdateQuery = "UPDATE paymentVouchers
												SET    contact = \"$sContactName\",
													   email = '$sContactEmail', 
													   phoneNo = '$sContactPhone',
									 				   address1 = \"$sContactAddress\", 
													   address2 = \"$sContactAddress2\", 
													   address3 = \"$sContactAddress3\", 
													   paymentTerms = \"$sPaymentTerms\", 
													   acceptCc = \"$sAcceptCc\", 
													   faxNo = \"$sFax\", 
													   taxId = \"$sTaxId\", 
													   voucherMonthYear = \"$sVoucherMonthYear\", 
													   totalAmountDue = \"".$fTotalAmount."\", 
													   dueDate = \"$sDueDate\", 
													   dateTimeCreated = now(), 
													   createdBy = '".$sTrackingUser."'
												WHERE  voucherNo = '$iNewVoucherNo'";
						$rVoucherUpdateResult = dbQuery($sVoucherUpdateQuery);
						echo dbError();
					}
				
					// first, delete all the existing transaction entries for this voucher
					$sDeleteQuery = "DELETE FROM paymentVoucherTransactions
									 WHERE  voucherNo = '$iNewVoucherNo'";
					$rDeleteResult = dbQuery($sDeleteQuery);
	
					for ($i=0; $i < count($aPaymentArray['sourceCode']); $i++) {
						$sTransactionQuery = "INSERT INTO paymentVoucherTransactions(voucherNo, gAcct, sourceCode, rate, amount)
											  VALUES('$iNewVoucherNo', '".$aPaymentArray['gAcct'][$i]."', '".$aPaymentArray['sourceCode'][$i]."', 
										'".$aPaymentArray['rate'][$i]."', '".round($aPaymentArray['amount'][$i],2)."')"; 
						$rTransactionResult = dbQuery($sTransactionQuery);
						echo dbError();
					}
				}
			}
		} // end if recalculate

		/// display voucher/s

		// get voucher data from vouchers table
		$sVoucherQuery = "SELECT paymentVouchers.*, partnerCompanies.companyName, repDesignated
						   FROM   paymentVouchers, partnerCompanies
						   WHERE  paymentVouchers.partnerId = partnerCompanies.id						   
						   AND	  voucherMonthYear = '$iYear-$iMonth'";
		if ($iRepDesignated != 'all' && $iRepDesignated != '') {
			$sVoucherQuery .= " AND FIND_IN_SET(\"$sTempRepDesignated\", repDesignated) > 0 ";
		}

		if ($iPartnerId != 'all' && $iPartnerId != '') {
			$sVoucherQuery .= " AND partnerCompanies.id = '$iPartnerId' ";
		}

		if ($iVoucherNo) {
			$sVoucherQuery .= " AND voucherNo = '$iVoucherNo'";
		}

		$rVoucherResult = dbQuery($sVoucherQuery);
		echo dbError();
		while ($oVoucherRow = dbFetchObject($rVoucherResult)) {
			$oPdf->AddPage();
			unset($aPaymentArray);
			$iVoucherNo = $oVoucherRow->voucherNo;
			$iTempPartnerId = $oVoucherRow->id;
			$sCompanyName = $oVoucherRow->companyName;
			$iTempRepDesignated = $oVoucherRow->repDesignated;
			$sPaymentTerms = $oVoucherRow->paymentTerms;
			$sFax = $oVoucherRow->faxNo;
			$sTaxId = $oVoucherRow->taxId;
			$sAcceptCc = $oVoucherRow->acceptCc;
			$sContactName = $oVoucherRow->contact;
			$sContactAddress = $oVoucherRow->address1;
			$sContactAddress2 = $oVoucherRow->address2;
			$sContactAddress3 = $oVoucherRow->address3;
			$sContactEmail = $oVoucherRow->email;
			$sContactPhone = $oVoucherRow->phoneNo;
			$fTotalAmount = number_format(round($oVoucherRow->totalAmountDue,2),2);

			$sRepDesignatedNames = '';
			if ($iTempRepDesignated != '') {
				$sRepQuery = "SELECT *
			  FROM   nbUsers
			  WHERE  id IN (".$iTempRepDesignated.")";

				$rRepResult = dbQuery($sRepQuery);
				echo dbError();
				while ($oRepRow = dbFetchObject($rRepResult)) {
					$sRepDesignatedNames .= $oRepRow->userName.", ";
				}

				if ($sRepDesignatedNames != '') {
					$sRepDesignatedNames = substr($sRepDesignatedNames,0, strlen($sRepDesignatedNames)-2);
				}
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

			$iTempVoucherNo = str_pad($iVoucherNo, 6, '0', STR_PAD_LEFT);

			//Image(string file, float x, float y [, float w [, float h [, string type [, mixed link]]]])

			$oPdf->Image("$sGblSiteRoot/images/ampereLogo.jpg", 20,10);
			$oPdf->SetFont('Arial', '', '8');
			$oPdf->SetMargins("20","20","20");
			$oPdf->SetXY(80,25);
			$oPdf->Cell(40,10,'3400 Dundee Rd. Suite 236 - Northbrook, IL 60062 847-205-9320 FAX: 847-205-9340');
			$oPdf->Ln(20);

			$oPdf->SetFont('Arial', 'B', '11');
			$oPdf->Cell(200, 10, "Payment Voucher $iTempVoucherNo", '', '', 'C');

			$oPdf->Ln();
			$oPdf->setX(40);
			$oPdf->SetFont('Arial', 'B', '11');
			$oPdf->Cell(20, 8, "Acct. Exec.: ");
			$oPdf->setX(80);
			$oPdf->SetFont('Arial', '', '11');
			$oPdf->Cell(20, 8, "$sRepDesignatedNames");

			$oPdf->Ln();
			$oPdf->setX(40);
			$oPdf->SetFont('Arial', 'B', '11');
			$oPdf->Cell(20, 8, "Name: ");
			$oPdf->setX(80);
			$oPdf->SetFont('Arial', '', '11');
			$oPdf->Cell(20, 8, "$sCompanyName");

			$oPdf->Ln();
			$oPdf->setX(40);
			$oPdf->SetFont('Arial', 'B', '11');
			$oPdf->Cell(20, 8, "Address: ");
			$oPdf->setX(80);
			$oPdf->SetFont('Arial', '', '11');
			$oPdf->Cell(20, 8, "$sContactAddress");

			$oPdf->Ln();
			//$oPdf->SetFont('Arial', 'B', '8');
			//$oPdf->Cell(20, 4, "Name: ");
			$oPdf->setX(80);
			$oPdf->SetFont('Arial', '', '11');
			$oPdf->Cell(20, 8, "$sContactAddress2");

			$oPdf->Ln();
			//$oPdf->SetFont('Arial', 'B', '8');
			//$oPdf->Cell(20, 4, "Name: ");
			$oPdf->setX(80);
			$oPdf->SetFont('Arial', '', '11');
			$oPdf->Cell(20, 8, "$sContactAddress3");


			$oPdf->Ln();
			$oPdf->setX(40);
			$oPdf->SetFont('Arial', 'B', '11');
			$oPdf->Cell(20, 8, "Phone: ");
			$oPdf->setX(80);
			$oPdf->SetFont('Arial', '', '11');
			$oPdf->Cell(20, 8, "$sContactPhone");

			$oPdf->SetFont('Arial', 'B', '11');
			$oPdf->setX(130);
			$oPdf->Cell(20, 8, "Fax: ");
			$oPdf->setX(170);
			$oPdf->SetFont('Arial', '', '11');
			$oPdf->Cell(20, 8, "$sFax");


			$oPdf->Ln();
			$oPdf->setX(40);
			$oPdf->SetFont('Arial', 'B', '11');
			$oPdf->Cell(20, 8, "E-Mail: ");
			$oPdf->setX(80);
			$oPdf->SetFont('Arial', '', '11');
			$oPdf->Cell(20, 8, "$sContactEmail");

			$oPdf->SetFont('Arial', 'B', '11');
			$oPdf->setX(130);
			$oPdf->Cell(20, 8, "Tax I.D.#: ");
			$oPdf->setX(170);
			$oPdf->SetFont('Arial', '', '11');
			$oPdf->Cell(20, 8, "$sTaxId");


			$oPdf->Ln();
			$oPdf->setX(40);
			$oPdf->SetFont('Arial', 'B', '11');
			$oPdf->Cell(20, 8, "Contact Name: ");
			$oPdf->setX(80);
			$oPdf->SetFont('Arial', '', '11');
			$oPdf->Cell(20, 8, "$sContactName");

			$oPdf->Ln();
			$oPdf->setX(40);
			$oPdf->SetFont('Arial', 'B', '11');
			$oPdf->Cell(20, 8, "Payment terms: ");
			$oPdf->setX(80);
			$oPdf->SetFont('Arial', '', '11');
			$oPdf->Cell(20, 8, "$sPaymentTerms");

			$oPdf->Ln();
			$oPdf->setX(40);
			$oPdf->SetFont('Arial', 'B', '11');
			$oPdf->Cell(25, 8, "Credit Cards Accepted? ");
			$oPdf->setX(90);
			$oPdf->SetFont('Arial', '', '11');
			$oPdf->Cell(20, 8, "$sAcceptCc");


			$oPdf->Ln(16);
			$oPdf->SetFont('Arial', 'B', '11');
			$oPdf->setX(30);
			$oPdf->Cell(30, 8, "G/L Acct","1");
			$oPdf->Cell(110, 8, "Description", "1");
			$oPdf->Cell(30, 8, "Amount", "1");
			$oPdf->Ln(8);

			$oPdf->SetFont('Arial', '', '11');

			for ($i=0; $i < count($aPaymentArray['sourceCode']); $i++) {
				$oPdf->setX(30);
				$oPdf->Cell(30, 8, $aPaymentArray['gAcct'][$i],"1");
				$oPdf->Cell(110, 8, "$sVoucherMonthYearDisplay lead generation for ".$aPaymentArray['sourceCode'][$i],"1");
				$oPdf->Cell(30, 8, number_format(round($aPaymentArray['amount'][$i],2),2), "1",'','R');			
				$oPdf->Ln(8);
				$iCurrY = $oPdf->GetY();
				if ($iCurrY >= 250) {
					$oPdf->addPage();
				}
			}

			$oPdf->Ln(8);
			$oPdf->SetX(120);
			$oPdf->Cell(50, 8, "Total amount due:", "1");
			$oPdf->Cell(30,8,"\$ ".$fTotalAmount,"1", '', 'R');
			$oPdf->Ln();
			$oPdf->SetX(120);
			$oPdf->Cell(50, 8, "Due date:", "1");
			$oPdf->Cell(30, 8, "$sDueDateDisplay", "1", '', 'R');

			$oPdf->SetFont('Arial', 'B', '8');

			$oPdf->Ln(16);
			$oPdf->SetX(30);
			$oPdf->Cell(45, 4, "Ampere Media contact approval: ");
			$oPdf->Cell(65, 4, "_____________________________________");
			$oPdf->Cell(10, 4, "Date: ");
			$oPdf->Cell(30, 4, "_____________________");
			$oPdf->Ln(7);
			$oPdf->SetX(30);
			$oPdf->Cell(45, 4, "Additional approval, if required: ");
			$oPdf->Cell(65, 4, "_____________________________________");
			$oPdf->Cell(10, 4, "Date: ");
			$oPdf->Cell(30, 4, "_____________________");
			$oPdf->Ln(7);
			$oPdf->SetX(30);
			$oPdf->Cell(45, 4, "President approval: ");
			$oPdf->Cell(65, 4, "_____________________________________");
			$oPdf->Cell(10, 4, "Date: ");
			$oPdf->Cell(30, 4, "_____________________");

		}

		//} // end of partner select while loop

		$sFileName = "vouchers_".$iCurrMonth.$iCurrDay."_".$iCurrHH.$iCurrMM.$iCurrSS.".pdf";

		$oPdf->Output("$sGblWebRoot/temp/$sFileName","F");
		$oPdf->Close();

		echo "<script language=JavaScript>
				void(window.open(\"$sGblSiteRoot/download.php?sFile=$sFileName\",\"_blank\",\"height=150, width=300, scrollbars=yes, resizable=yes, status=yes\"));				
				self.close();
			  </script>";

	} // end of load check else loop

	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

	//	include("../../includes/adminAddHeader.php");

} else {
	echo "You are not authorized to access this page...";
}
?>