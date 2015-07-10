<?php

/********** IMPORTANT ***************/
// Get correct value for PHP_SELF. Because $PHP_SELF will include query string also
// like abc.php/offerCat/32/...
// Using this $PHP_SELF in any link, the link will become bigger and bigger everytime you click it
// So, use following variable instead of $PHP_SELF in the scripts with search engine friendly URLs

$path = strpos($PHP_SELF, $PATH_INFO);
$NEW_PHP_SELF = substr($PHP_SELF, 0, $path);

// If $PHP_SELF does not contain any parameters, NEW_PHP_SELF will be null
// Then, store $PHP_SELF value in $NEW_PHP_SELF
if(!($NEW_PHP_SELF))
$NEW_PHP_SELF = $PHP_SELF;

/*************** Get GET and POST vars from $_GET and $_POST *****************/
// !\"#$%&'()*+,-\./[\\]^_`|~

$sGblQueryString = '';
while (list($key,$val) = each($HTTP_GET_VARS)) {
	/*if ( !ereg(  "^[0-9A-Za-z!\"#$%&'()*+,-\./[\\^_`|~{}[[:space:]]]*$", $val)) {
	$$key = "";
	} else {*/
	/*
if ($_SERVER['REMOTE_ADDR'] == '198.63.247.2') {
	echo "<BR>".$key." - ".$val;
}*/

	$$key = $val;
	if ($val != '') {
		$sGblQueryString .= "$key=".urlencode($val)."&";
	} else {
		$sGblQueryString .= "$key=$val&";
	}
	//}
	
}
if ($sGblQueryString != '') {
	$sGblQueryString = substr($sGblQueryString,0,strlen($sGblQueryString)-1);
}

while (list($key,$val) = each($HTTP_POST_VARS)) {
	/*if ( !ereg(  "^[0-9A-Za-z!\"#$%&'()*+,-\./[\\^_`|~{}[[:space:]]]*$", $val)) {
	$$key = "";
	} else {*/
	$$key = $val;
	//if ($val != '') {
//		$sPage2QueryString .= "$key=".urlencode($val)."&";
	//} else {
		$sPage2QueryString .= "$key=$val&";
	//}
	//}
}

/***************************************************************************/

$aGblWeekDaysArray = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
$aGblMonthsArray = array('Jan','Feb','Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

function getPageId($sPageName) {
	$sOtPageQuery = "SELECT *
					 FROM   otPages
					 WHERE  pageName = '$sPageName'";
	$rOtPageResult = dbQuery($sOtPageQuery);
	while ($oOtPageRow = dbFetchObject($rOtPageResult)) {
		$iId = $oOtPageRow->id;
	}
	return $iId;
}

function hasAccessRight($iMenuId) {
	
	/*while(list($key,$val)= each($_SERVER)) {
	echo "<BR>$key - ".$val;
	}*/
	$sAccessRightQuery = "SELECT accessRights.*
						  FROM   nbUsers, accessRights
						  WHERE  nbUsers.id = accessRights.userId
						  AND	 menuId = '$iMenuId'						 
						  AND    userName = '".$_SERVER['PHP_AUTH_USER']."' 
						  AND    accessRight = 'Y'";
	//echo $sAccessRightQuery;
	$rAccessRightResult = dbQuery($sAccessRightQuery);
	
	if ( dbNumRows($rAccessRightResult)>0) {
		return true;
	} else {
		return false;
	}
	if ($rAccessRightResult) {
		dbFreeResult($rAccessRightResult);
	}
	
}


function isAdmin() {
	$sAdminQuery = "SELECT nbUsers.*
				  FROM   nbUsers
				  WHERE  userName = '".$_SERVER['PHP_AUTH_USER']."' 
				  AND    level = 'admin'";
	//echo $sAdminQuery;
	$rAdminResult = dbQuery($sAdminQuery);
	
	if ( dbNumRows($rAdminResult)>0) {
		return true;
	} else {
		return false;
	}
	
	if ($rAdminResult) {
		dbFreeResult($rAdminResult);
	}
	
}

function checkOfferCountsOnPage($iPageId= '', $sOfferCode = '') {
	
	if ($iPageId != '' && $iPageId != 0) {
		$sOffersCountQuery = "SELECT otPages.pageName, otPages.minNoOfOffers, otPages.maxNoOfOffers, count(pageMap.id) as offersCount
						  FROM	 otPages, pageMap, offers, offerCompanies
						  WHERE	 otPages.id = pageMap.pageId
						  AND    pageMap.offerCode = offers.offerCode
						  AND    offers.companyId = offerCompanies.id
						  AND    offers.mode = 'A'
						  AND	 offers.isLive = '1'
						  AND    offerCompanies.creditStatus = 'ok'
		 				  AND    pageName NOT LIKE 'test%'
						  AND	 pageId = '$iPageId'
						  GROUP BY pageMap.pageId";
	} else if ($sOfferCode != '') {
		// get the pages on which offer is live
		$sPageQuery = "SELECT *
					   FROM   pageMap
					   WHERE  offerCode = '$sOfferCode'";
			
		$rPageResult = dbQuery($sPageQuery);
		while ($oPageRow = dbFetchObject($rPageResult)) {
			$sOfferOnPages .= $oPageRow->pageId.",";
		}
		if ($sOfferOnPages != '') {
			$sOfferOnPages = substr($sOfferOnPages , 0, strlen($sOfferOnPages)-1);
			
			$sOffersCountQuery = "SELECT otPages.pageName, otPages.minNoOfOffers, otPages.maxNoOfOffers, count(pageMap.id) as offersCount
						  FROM	 otPages, pageMap, offers, offerCompanies
						  WHERE	 otPages.id = pageMap.pageId
						  AND    pageMap.offerCode = offers.offerCode
						  AND    offers.companyId = offerCompanies.id
						  AND    offers.mode = 'A'
						  AND	 offers.isLive = '1'
						  AND    offerCompanies.creditStatus = 'ok'
						  AND	 pageMap.pageId IN (".$sOfferOnPages." )
						  AND    pageName NOT LIKE 'test%'
						  GROUP BY  pageMap.pageId";
		}
	} else {
		$sOffersCountQuery = "SELECT otPages.pageName, otPages.minNoOfOffers, otPages.maxNoOfOffers, count(pageMap.id) as offersCount
						  FROM	 otPages, pageMap, offers, offerCompanies
						  WHERE	 otPages.id = pageMap.pageId
						  AND    pageMap.offerCode = offers.offerCode
						  AND    offers.companyId = offerCompanies.id
						  AND    offers.mode = 'A'
						  AND	 offers.isLive = '1'
						  AND    offerCompanies.creditStatus = 'ok'
		 				  AND    pageName NOT LIKE 'test%'						 
						  GROUP BY pageMap.pageId";
	}
	
	if ($sOffersCountQuery != '') {
		$rOffersCountResult = dbQuery($sOffersCountQuery);
		
		while ($oOffersCountRow = dbFetchObject($rOffersCountResult)) {
			$sPageName = $oOffersCountRow->pageName;
			$iMinNoOfOffers = $oOffersCountRow->minNoOfOffers;
			$iMaxNoOfOffers = $oOffersCountRow->maxNoOfOffers;
			$iOffersCount = $oOffersCountRow->offersCount;
			
			// send email if offers on the page is less than minimum or more than maximum
			if ($iOffersCount < $iMinNoOfOffers || $iOffersCount > $iMaxNoOfOffers) {
				
				if ($iOffersCount < $iMinNoOfOffers) {
					$sEmailSubject = "Offers on $sPageName are less than it should be.";
				} else {
					$sEmailSubject = "Offers on $sPageName are more than it should be.";
				}
				
				$sEmailMessage = "Page name: $sPageName
							  \nOffers on page: $iOffersCount
							  \nMinimum offers on page: $iMinNoOfOffers
							  \nMaximum offers on page: $iMaxNoOfOffers";				
				
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
					$sEmailHeaders .= "cc:";
					$aEmailRecipients = explode(",",$sEmailRecipients);
					$sEmailTo = $aEmailRecipients[0];
					for ($i = 1; $i < count($aEmailRecipients); $i++) {
						$sEmailHeaders .= $aEmailRecipients[$i].",";
					}
					
					if (count($aEmailRecipients) > 1) {
						$sEmailHeaders = substr($sEmailHeaders, 0, strlen($sEmailHeaders)-1);
					}
					mail($sEmailTo, $sEmailSubject, $sEmailMessage, $sEmailHeaders);
				}
			}
		}
	}
}




?>