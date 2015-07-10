<?php


include_once("/home/sites/admin.popularliving.com/html/includes/paths.php");

		$sOffersCountQuery = "SELECT otPages.pageName, otPages.minNoOfOffers, otPages.maxNoOfOffers, count(pageMap.id) as offersCount
						  FROM	 otPages, pageMap, offers, offerCompanies
						  WHERE	 otPages.id = pageMap.pageId
						  AND    pageMap.offerCode = offers.offerCode
						  AND    offers.companyId = offerCompanies.id
						  AND    offers.mode = 'A'
						  AND	 offers.isLive = '1'
						  AND    offerCompanies.creditStatus = 'ok'
		 				  AND    pageName NOT LIKE 'test%'		 
						  GROUP BY pageMap.pageId
						  ORDER BY otPages.pageName";

		$rOffersCountResult = dbQuery($sOffersCountQuery);
		
			$sEmailMessageTableHeader = "<table align=center border=1><tr><td><b>&nbsp;&nbsp;Page Name&nbsp;&nbsp;</b></td>
					<td><b>&nbsp;&nbsp;Mode&nbsp;&nbsp;</b></td>
					<td><b>&nbsp;&nbsp;Offers on page&nbsp;&nbsp;</b></td>
					<td><b>&nbsp;&nbsp;Minimum offers on page&nbsp;&nbsp;</b></td>
					<td><b>&nbsp;&nbsp;Maximum offers on page&nbsp;&nbsp;</b></td>
					</tr>";
			
		while ($oOffersCountRow = dbFetchObject($rOffersCountResult)) {
			$sPageName = $oOffersCountRow->pageName;
			$iMinNoOfOffers = $oOffersCountRow->minNoOfOffers;
			$iMaxNoOfOffers = $oOffersCountRow->maxNoOfOffers;
			$iOffersCount = $oOffersCountRow->offersCount;
			
			// send email if offers on the page is less than minimum or more than maximum
			if ($iOffersCount < $iMinNoOfOffers || $iOffersCount > $iMaxNoOfOffers) {

				$sActiveStatusSQL = "SELECT 'A' as state FROM activePages, otPages 
						WHERE otPages.pageName = '$sPageName'
						AND (date_format(otPages.dateTimeAdded, '%Y-%m-%d') BETWEEN date_add(CURRENT_DATE, INTERVAL -30 DAY)
								AND date_add(CURRENT_DATE, INTERVAL -0 DAY) 
								OR activePages.pageName = otPages.pageName)";	
				
				$rActiveStatus = dbQuery($sActiveStatusSQL);
				$oActiveStatus = dbFetchObject($rActiveStatus);
				
				$sEmailSubject = "Offers on otPages are outside limits.";
	
					if ($bgcolor == "#DDDDDD") {
						$bgcolor = "white";
					} else {
						$bgcolor = "#DDDDDD";
					}
				
				$sEmailMessage .= "<tr bgcolor=$bgcolor><td>$sPageName</td>";
				
				if($oActiveStatus->state == 'A'){
					$sEmailMessage .= "<td> A </td>";
				} else {
					$sEmailMessage .= "<td> I </td>";					
				}
				
				$sEmailMessage .= "<td>$iOffersCount</td>
				<td>$iMinNoOfOffers</td>
				<td>$iMaxNoOfOffers</td></tr>";
				
				// get the recipients
				$sEmailRecQuery = "SELECT *
							   FROM   emailRecipients
							   WHERE  purpose = 'offers on page'";
				$rEmailRecResult = dbQuery($sEmailRecQuery);
				while ($oEmailRecRow = dbFetchObject($rEmailRecResult)) {
					$sEmailRecipients = $oEmailRecRow->emailRecipients;
				}
				
				if ($sEmailRecipients != '') {
					$sEmailHeaders = "From: ot@amperemedia.com\r\n";
					$sEmailHeaders .= "X-Mailer: MyFree.com\r\n";
					$sEmailHeaders .= "Content-Type: text/html; charset=iso-8859-1\r\n";
					
					$sEmailHeaders .= "cc:";
					$aEmailRecipients = explode(",",$sEmailRecipients);
					$sEmailTo = $aEmailRecipients[0];
					for ($i = 1; $i < count($aEmailRecipients); $i++) {
						$sEmailHeaders .= $aEmailRecipients[$i].",";
					}
					
					if (count($aEmailRecipients) > 1) {
						$sEmailHeaders = substr($sEmailHeaders, 0, strlen($sEmailHeaders)-1);
					}
					
				}
			}
		}
		$sEmailMessage = $sEmailMessageTableHeader.$sEmailMessage."</table>";

		mail($sEmailTo, $sEmailSubject, $sEmailMessage, $sEmailHeaders);
		
		
?>
