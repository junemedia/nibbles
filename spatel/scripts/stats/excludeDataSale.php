<?php

include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");


	$sQuery = "SELECT DISTINCT sourceCode
				FROM links, partnerCompanies
				WHERE links.partnerId = partnerCompanies.id
				AND excludeDataSale = '1'";
	$rResultQuery = dbQuery($sQuery);
	if ( dbNumRows($rResultQuery) > 0 ) {
		$aSrcArray = array();
		$iii = 0;
		while ($oRow = mysql_fetch_object($rResultQuery)) {
			$aSrcArray[$iii] = $oRow->sourceCode;
			$iii++;
		}

		$aSrcChunks = array();
		for($ii=0;$ii<count($aSrcArray);$ii=$ii) {
			$str = '';
			for($jj=0;(($jj<50)&&($ii+$jj)<count($aSrcArray));$jj++){
				$str .= "'".$aSrcArray[$ii+$jj]."',";
			}
		
			$str = substr($str,0,strlen($str)-1);
			$aSrcChunks[count($aSrcChunks)] = $str;
			$ii += $jj;
		}

		foreach($aSrcChunks as $chunk) {
			$sUpdateQuery = "UPDATE LOW_PRIORITY otDataHistory
							SET excludeDataSale = '1'
							WHERE sourceCode IN ($chunk)";
			$rResultUpdateQuery = dbQuery($sUpdateQuery);
			
			$sUpdateQuery = "UPDATE LOW_PRIORITY otDataHistoryArchive
							SET excludeDataSale = '1'
							WHERE sourceCode IN ($chunk)";
			$rResultUpdateQuery = dbQuery($sUpdateQuery);
			
			$sUpdateAbandedQuery = "UPDATE LOW_PRIORITY abandedOffersHistory
							SET excludeDataSale = '1'
							WHERE sourceCode IN ($chunk)";
			$rUpdateAbandedResult = dbQuery($sUpdateAbandedQuery);
		}
	}
	echo "\nEnd Source Code";
	
	
	
	echo "\nStart Emails";
	$sExcludedEmailQuery = "select email from excludeEmailDataSales";
	$rExcludedEmailResult = dbQuery($sExcludedEmailQuery);
	if ( dbNumRows($rExcludedEmailResult) > 0 ) {
		$aEmailArray = array();
		$ii = 0;
		while ($sEmailRow = mysql_fetch_object($rExcludedEmailResult)) {
			$aEmailArray[$ii] = $sEmailRow->email;
			$ii++;
		}
		
		$aEmailChunks = array();
		for($i=0;$i<count($aEmailArray);$i=$i) {
			$str = '';
			for($j=0;(($j<50)&&($i+$j)<count($aEmailArray));$j++){
				$str .= "'".$aEmailArray[$i+$j]."',";
			}
			$str = substr($str,0,strlen($str)-1);
			$aEmailChunks[count($aEmailChunks)] = $str;
			$i += $j;
		}
		
		foreach($aEmailChunks as $chunk) {
			$sUpdateEmailQuery = "UPDATE LOW_PRIORITY otDataHistory
							SET excludeDataSale = '1'
							WHERE email IN ($chunk)";
			$rUpdateEmailResult = dbQuery($sUpdateEmailQuery);

			$sUpdateEmailQuery = "UPDATE LOW_PRIORITY otDataHistoryArchive
							SET excludeDataSale = '1'
							WHERE email IN ($chunk)";
			$rUpdateEmailResult = dbQuery($sUpdateEmailQuery);

			$sUpdateEmailAbanded = "UPDATE LOW_PRIORITY abandedOffersHistory
							SET excludeDataSale = '1'
							WHERE email IN ($chunk)";
			$rUpdateEmailAbanded = dbQuery($sUpdateEmailAbanded);
		}
	}
	echo "\nEnd Emails";
	
	
	
	
	echo "\nStart TLDs";
	// Get all TLDs
	$sExcludedTLDsQuery = "select TLDs from excludeTLDsDataSales";
	$rExcludedTLDsResult = dbQuery($sExcludedTLDsQuery);
	
	// If above query returns domain, then continue
	if ( dbNumRows($rExcludedTLDsResult) > 0 ) {
		while ($sTLDsRows = mysql_fetch_object($rExcludedTLDsResult)) {
			$sTLDs = $sTLDsRows->TLDs;

			// update otDataHistory table and mark entry as  excluded from data sales.
			$sUpdateTLDsQuery = "UPDATE LOW_PRIORITY otDataHistory
							SET excludeDataSale = '1'
							WHERE email like \"%$sTLDs\"
							AND excludeDataSale != '1'";
			$rUpdateTLDsResult = dbQuery($sUpdateTLDsQuery);
			
			$sUpdateTLDsQuery = "UPDATE LOW_PRIORITY otDataHistoryArchive
							SET excludeDataSale = '1'
							WHERE email like \"%$sTLDs\"
							AND excludeDataSale != '1'";
			$rUpdateTLDsResult = dbQuery($sUpdateTLDsQuery);

			// update abandedOffersHistory table and mark entry as excluded from data sales.
			$sUpdateTLDsAbanded = "UPDATE LOW_PRIORITY abandedOffersHistory
							SET excludeDataSale = '1'
							WHERE email like \"%$sTLDs\"
							AND excludeDataSale != '1'";
			$rUpdateTLDsAbanded = dbQuery($sUpdateTLDsAbanded);
		}
	}
	echo "\nEnd TLDs";



	
	
	echo "\nStart Domains";
	// Get all domains
	$sExcludedDomainQuery = "select domain from excludeDomainsDataSales";
	$rExcludedDomainResult = dbQuery($sExcludedDomainQuery);
	
	// If above query returns domain, then continue
	if ( dbNumRows($rExcludedDomainResult) > 0 ) {
		$aDomainArray = array();
		$a = 0;
		while ($sDomainRow = mysql_fetch_object($rExcludedDomainResult)) {
			$aDomainArray[$a] = "@".$sDomainRow->domain;
			$a++;
		}

		$aDomainChunks = array();
		for($i=0;$i<count($aDomainArray);$i=$i) {
			$str = '';
			for($j=0;(($j<50)&&($i+$j)<count($aDomainArray));$j++){
				$str .= "'".$aDomainArray[$i+$j]."',";
			}
			$str = substr($str,0,strlen($str)-1);
			$aDomainChunks[count($aDomainChunks)] = $str;
			$i += $j;
		}
		
		foreach($aDomainChunks as $chunk) {
			$sUpdateDomainQuery = "UPDATE LOW_PRIORITY otDataHistory
							SET excludeDataSale = '1'
							WHERE SUBSTRING(email,POSITION('@' IN email),LENGTH(email)) IN ($chunk)";
			$rUpdateDomainResult = dbQuery($sUpdateDomainQuery);
			
			$sUpdateDomainQuery = "UPDATE LOW_PRIORITY otDataHistoryArchive
							SET excludeDataSale = '1'
							WHERE SUBSTRING(email,POSITION('@' IN email),LENGTH(email)) IN ($chunk)";
			$rUpdateDomainResult = dbQuery($sUpdateDomainQuery);

			$sUpdateDomainAbanded = "UPDATE LOW_PRIORITY abandedOffersHistory
							SET excludeDataSale = '1'
							WHERE SUBSTRING(email,POSITION('@' IN email),LENGTH(email)) IN ($chunk)";
			$rUpdateDomainAbanded = dbQuery($sUpdateDomainAbanded);
		}
	}
	echo "\nEnd Domains";
	

?>
