<?php

/*********

Script to Add/Edit Offer information

*********/
//echo $QUERY_STRING;

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Nibbles Editorial Offers Management - Add/Edit Offer";

if (hasAccessRight($iMenuId) || isAdmin()) {

$currYear = date(Y);
$currMonth = date(m); //01 to 12
$currDay = date(d); // 01 to 31


// replace +++ with its value (&), if it was in parameter value

$filter = ereg_replace("aaazzz","&",$filter);
$exclude = ereg_replace("aaazzz","&",$exclude);	

$checkQuery = "SELECT companyId, seqNo, activationDate, expirationDate
			   FROM   edOffers
			   WHERE  id = '$id'";
$checkResult = mysql_query($checkQuery);
while ($checkRow = mysql_fetch_object($checkResult)) {
	$prevCompanyId = $checkRow->companyId;
	$prevSeqNo = $checkRow->seqNo;
	//$activationDate = $checkRow->activationDate;
	//$expirationDate = $checkRow->expirationDate;		
}

if($sSaveClose || $sSaveNew) {
	
	//echo $numRecords." page ".$page;
	switch ($backTo) {
		case "expiring":
		$pageReloadUrl = "offersExpiringReport.php";
		break;
		case "orphan":
		$pageReloadUrl = "orfanOffersReport.php";
		break;
		default:
		$pageReloadUrl = "index.php";
	}
	$pageReloadUrl .= "?iMenuId=$iMenuId";
	
	// check if selected dates are valid dates
	if (checkDate($monthActivation, $dayActivation, $yearActivation) && checkdate($monthExpiration, $dayExpiration,$yearExpiration)) {
		//if (strtolower(substr($url,0,7)) =="http://" || strtolower(substr($url,0,8)) =="https://") {
		// get three digit minimum seqNo available for the selected company
		$seqNo = 0;
		
		$seqQuery = "SELECT MAX(seqNo) lastSeqNo
					FROM   edOffers
					WHERE  companyId='$companyId'";
		$seqResult = mysql_query($seqQuery);
		while ($seqRow = mysql_fetch_object($seqResult)) {
			$lastSeqNo = $seqRow->lastSeqNo;
		}
		
		$seqQuery = "SELECT seqNo
					 FROM   edOffers
					 WHERE  companyId = '$companyId'
					 ORDER BY seqNo";
		$seqResult = mysql_query($seqQuery);
		$i = 1;
		
		if (mysql_num_rows($seqResult) >0 ) {
			while ($seqRow = mysql_fetch_object($seqResult)) {
				//echo "<BR>".$seqRow->seqNo. " i ".$i." seq no ".$seqNo;
				if ($i == $seqRow->seqNo) {
					// increment $i to the next value for next while iteration
					// $i will be 1 number more than the seqNo. here
					$i++;
					//echo "<BR>AFter i++ ".$seqRow->seqNo. " i ".$i." seq no ".$seqNo;
					// continue until sequence is not broken in between
					continue;
				} else {
					// If sequence is break in between, assign the available seq no
					$seqNo = $i;
					break;
				}
			}
			
			if ($seqNo == 0 ) {
				//echo "<BR>".$seqRow->seqNo. " i ".$i." seq no ".$seqNo;
				
				// If the sequence is not broken anywhere,
				// assign the next seq no by incrementing the sequence var $i
				// Don't increment $i here, because it's already number 1 more than seqNo.
				$seqNo = $i ;
			}
			
		} else {
			// If no records found assign starting seq no 1
			$seqNo = 1;
		}
		
		if (($sSaveClose || $sSaveNew) && ($id)) {
			// Check if it's differnet company than previous
			
			// If company is not changed while editing, don't change the seqno
			if($prevCompanyId == $companyId) {
				$seqNo = $prevSeqNo;
			}
		}
		$companyQuery = "SELECT companyName, code
						FROM edOfferCompanies
						WHERE id = '$companyId'";	
		
		$companyResult = mysql_query($companyQuery);
		
		while ( $companyRow = mysql_fetch_object($companyResult)) {
			$companyCode = $companyRow->code;
		}
		
		if (!($offerCode)) {
			$offerCode = strtolower($companyCode.$seqNo);
		}
		
		// Set the activation date and expiration date according to selcetion
		$activationDate = "$yearActivation-$monthActivation-$dayActivation";
		$expirationDate = "$yearExpiration-$monthExpiration-$dayExpiration";
		// Prepare comma-separated Categories if record added or edited
		
		$categoryQuery = "SELECT id, title
			 			  	  FROM   edOfferCategories
				 		  	  ORDER BY title";
		$categoryResult = mysql_query($categoryQuery);
		$i = 0;
		while ($categoryRow = mysql_fetch_object($categoryResult)) {
			
			// prepare Categories of this offer
			$checkboxName = "category_".$categoryRow->id;
			
			$checkboxValue = $$checkboxName;
			
			if ($checkboxValue != '') {
				$categoriesArray[$i] = $checkboxValue;
				$categoriesString .= $checkboxValue.",";
				$i++;
			}
		}
		
		
		if (strtolower(substr($url,0,7)) =="http://" || strtolower(substr($url,0,8)) =="https://") {
			
			if (($sSaveClose || $sSaveNew) && !($id)) {
				
				// if new data submitted
				
				//check if offercode exists
				$checkQuery = "SELECT *
				   FROM edOffers
				   WHERE offerCode = '$offerCode'";
				$checkResult = mysql_query($checkQuery);
				if (mysql_num_rows($checkResult) == 0) {
					
					$redirectUrl = $sGblOfferRedirectsPath . "?src=". strtolower($offerCode);
					
					if ( count($popupId) >0 ) {
						while (list($key, $val) = each($popupId)) {
							$popupIdList .= "'$val',";
						}
						$popupIdList = substr($popupIdList,0,strlen($popupIdList)-1);
					}
					
					$headline = addslashes($headline);
					$description = addslashes($description);
					$notes = addslashes($notes);
					
					$addQuery = "INSERT INTO edOffers(SQLOfferCode, offerCode, companyId, activationDate, expirationDate,
						headline, description, url, redirectUrl, notes, seqNo, edited, finalApproval, displayInFrame, popOption, popupId, specialStatus, ssSortOrder, dateLastUpdated) 
					 VALUES('$offerCode', '$offerCode', '$companyId', '$activationDate', '$expirationDate', 
						'$headline', '$description', '$url', '$redirectUrl', '$notes', '$seqNo', '$edited', '$finalApproval', '$displayInFrame', '$popOption', \"$popupIdList\", '$specialStatus', '$ssSortOrder', CURRENT_DATE)";

					// start of track users' activity in nibbles 
					$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
			
					$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: " . addslashes($addQuery) . "\")"; 
					$rLogResult = dbQuery($sLogAddQuery); 
					echo  dbError(); 
					// end of track users' activity in nibbles		
					
					
					
					$result = mysql_query($addQuery);
					if (! $result) {
						echo mysql_error();
					} else {
						
						$sCheckQuery = "SELECT id
						   FROM   edOffers
						   WHERE  SQLOfferCode = '$offerCode'"; 
						$rCheckResult = dbQuery($sCheckQuery);
						$sRow = dbFetchObject($rCheckResult);
						
						$offerId = $sRow->id;
						// Insert into OfferCategoryRel according to category checkboxes checked
						if (count($categoriesArray) > 0) {
							for ($i = 0; $i < count($categoriesArray); $i++) {
								$insertQuery = "INSERT INTO edOfferCategoryRel(offerId, categoryId,sortOrder)
									VALUES('$offerId', '".$categoriesArray[$i]."','0')";		
								$insertResult = mysql_query($insertQuery);
								if (!($insertResult)) {
									echo mysql_error();
								}
							}
						}
						$showRedirect = "<center><font face=\"Arial, Helvetica, sans-serif\" size=2><b> Redirect:</b>&nbsp; &nbsp;<a href='JavaScript:void(window.open(\"$redirectUrl\",\"\",\"\"));'>" . $redirectUrl . "</a></font></center>
					<center><font face=\"Arial, Helvetica, sans-serif\" size=2><b> AOL Redirect:</b>&nbsp; &nbsp;".htmlspecialchars("<A href=\" " . $sGblOfferRedirectsPath . "?src=". strtolower($offerCode) . " \">Click Here</a>")."</font></center>
					<center><font face=\"Arial, Helvetica, sans-serif\" size=2><b> Pixel Tracking:</b>&nbsp; &nbsp;".htmlspecialchars("<IMG src=\"" . $sGblOfferPixelsTrackingPath . "?s=$offerCode\" width=\"3\" height=\"2\">")."</font></center>";		
						$sMessage = "Offer Added successfully...";
					}
				} else {
					$sMessage = "Offer Code Exists... $offerCode";
					$keepValues = true;
				}
				
			} elseif (($sSaveClose || $sSaveNew) && ($id)) {
				// If record edited
				//$redirectUrl = $redirectPath . "?src=". strtolower($offerCode);
				if ( count($popupId) >0 ) {
					while (list($key, $val) = each($popupId)) {
						$popupIdList .= "'$val',";
					}
					$popupIdList = substr($popupIdList,0,strlen($popupIdList)-1);
				}
				
				$headline = addslashes($headline);
				$description = addslashes($description);
				$notes = addslashes($notes);

				$editQuery = "UPDATE edOffers
				  			  SET    companyId = '$companyId', 
							 activationDate = '$activationDate',
							 expirationDate = '$expirationDate', 
							 headline = '$headline', 
							 description = '$description',
							 url = '$url',							
							 notes = '$notes',							
							 displayInFrame = '$displayInFrame', 
							 seqNo = '$seqNo',
							 popOption = '$popOption',
							 popupId = \"$popupIdList\",
							 edited = '$edited',
							 finalApproval = '$finalApproval',
							 specialStatus = '$specialStatus',
							 ssSortOrder = '$ssSortOrder',
							 dateLastUpdated = CURRENT_DATE
				  WHERE id = '$id'";	

				// start of track users' activity in nibbles 
				$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
		
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: " . addslashes($editQuery) . "\")"; 
				$rLogResult = dbQuery($sLogAddQuery); 
				echo  dbError(); 
				// end of track users' activity in nibbles		
				
				
				$result = mysql_query($editQuery);
				echo mysql_error();
				// Delete records from OfferCategoryRel with the categories which are not checked
				//	if ($categoriesString != '') {
				// remove last comma from the categories list
				$categoriesString = substr($categoriesString, 0, strlen($categoriesString)-1);
				// Delete if any category unchecked for the offer to be displayed in.
				$deleteQuery = "DELETE FROM edOfferCategoryRel
								WHERE  offerId = '$id'";
				if ($categoriesString != '') {
					$deleteQuery .= " AND    categoryId NOT IN (".$categoriesString.")";
				}
				$deleteResult = mysql_query($deleteQuery);
				//}
				
				if (count($categoriesArray) > 0) {
					for ($i = 0; $i<count($categoriesArray); $i++) {
						$checkQuery = "SELECT *
									   FROM   edOfferCategoryRel
									   WHERE  categoryId = ".$categoriesArray[$i]."
									   AND    offerId = '$id'";
						$checkResult = mysql_query($checkQuery);
						if (mysql_num_rows($checkResult) == 0) {
							// INSERT OfferCategoryRel record
							
							$insertQuery = "INSERT INTO edOfferCategoryRel (categoryId, offerId, sortOrder)
					VALUES('".$categoriesArray[$i]."', '$id', '0')";
							$insertResult = mysql_query($insertQuery);
						}
					}
				}
				
				if (!($editResult)) {
					echo $editQuery.mysql_error();
				}
				
			}
			//echo $filter.$exclude.$searchIn;
			// Find out on which page this sourceCode will appear, set ORDERY BY as sourcecode
			// and go to that page, and display redirect for this sourceCode
			//if ($offerCode) {
			if ($filter != '') {
				
				$filterPart .= " AND ( ";
				
				switch ($searchIn) {
					case "headline" :
					$filterPart .= ($exactMatch == 'Y') ? "headline = '$filter'" : "headline like '%$filter%'";
					break;
					case "description" :
					$filterPart .= ($exactMatch == 'Y') ? "description = '$filter'" : "description like '%$filter%'";
					break;
					case "companyName" :
					$filterPart .= ($exactMatch == 'Y') ? "OC.companyName = '$filter'" : "OC.companyName like '%$filter%'";
					break;
					case "offerCode" :
					$filterPart .= ($exactMatch == 'Y') ? "offerCode = '$filter'" : "offerCode like '%$filter%'";
					break;
					case "dateLastUpdated" :
					$filterPart .= ($exactMatch == 'Y') ? "dateLastUpdated = '$filter'" : "dateLsatUpdated like '%$filter%'";
					break;
					default:
					$filterPart .= ($exactMatch == 'Y') ? "offerCode = '$filter' || OC.companyName = '$filter' || headline = '$filter' || description = '$filter'  || dateLastUpdated = '$filter'" : " offerCode like '%$filter%' || OC.companyName LIKE '%$filter%' || headline like '%$filter%' || description like '%$filter%' || dateLastUpdated like '%$filter%'";
				}
				
				$filterPart .= ") ";
				
			}
			
			if ($exclude != '') {
				$filterPart .= " AND ( ";
				switch ($exclude) {
					case "headline" :
					$filterPart .= "headline NOT LIKE '%$exclude%'";
					break;
					case "description" :
					$filterPart .= "description NOT LIKE '%$exclude%'";
					break;
					case "companyName" :
					$filterPart .= "OC.companyName NOT LIKE '%$exclude%'";
					break;
					case "offerCode" :
					$filterPart .= "offerCode NOT LIKE '%$exclude%'";
					break;
					case "dateLastUpdated" :
					$filterPart .= "dateLastUpdated NOT LIKE '%$exclude%'";
					break;
					default:
					$filterPart .= "offerCode NOT LIKE '%$exclude%' && OC.companyName NOT LIKE '%$exclude%' && headline NOT LIKE '%$exclude%' && description NOT LIKE '%$exclude%' && dateLastUpdated NOT LIKE '%$exclude%'" ;
				}
				$filterPart .= " ) ";
				
			}
			
			$tempQuery = "SELECT count(*) numRecords
			  FROM   edOffers O, edOfferCompanies OC
			  WHERE  O.companyId = OC.id AND (substring(offerCode,1,3) < substring('".$offerCode."',1,3)
			  OR (substring(offerCode,1,3) <= substring('".$offerCode."',1,3)
				AND substring(offerCode,4)+0 < substring('".$offerCode."',4)+0))
			  $filterPart 
			  ORDER BY substring(offerCode,1,3) $currOrder, substring(offerCode,4)+0";
			$tempResult = mysql_query($tempQuery);
			echo mysql_error();
			while ($tempRow = mysql_fetch_object($tempResult)) {
				$numRecords = $tempRow->numRecords;
			}
			//echo $tempQuery;
			$thisRecordNo = $numRecords+1; // because the next record will be the current record (record of this offercode)
			$page = ceil($thisRecordNo/$recPerPage);
			
			$filter = stripslashes($filter);
			$exclude = stripslashes($exclude);
			$filterEncoded = ereg_replace("&","aaazzz",$filter);
			$filterEncoded = urlencode($filterEncoded);
			$excludeEncoded = ereg_replace("&","aaazzz",$exclude);			
			$excludeEncoded = urlencode($excludeEncoded);		
						
			//$filterEncoded = urlencode($filterEncoded);
			//$excludeEncoded = urlencode($excludeEncoded);		
			$pageReloadUrl .= "&filter=$filterEncoded&exactMatch=$exactMatch&exclude=$excludeEncoded&searchIn=$searchIn&recPerPage=$recPerPage&page=$page&offerCode=$offerCode&showRedirect=true";
			//$pageReloadUrl = urlencode($pageReloadUrl);
			
		} else {
			$sMessage = "Offer URL Should start with \"http://\" or \"https://\"...";
			$keepValues = "true";
		}
	}	else {
		$sMessage = "Please Select Valid Dates...";
		$keepValues = true;
	}
}

if ($sSaveClose) {
	
	if ($keepValues != true) {
		echo "<script language=JavaScript>
		window.opener.location.href='".$pageReloadUrl."';
		self.close();
		</script>";
		// exit from this script
		exit();
	}
} else if ($sSaveNew) {
	$reloadWindowOpener = "<script language=JavaScript>
							window.opener.location.href='".$pageReloadUrl."';
							</script>";
	// Reset textboxes for new record
	if ($keepValues != true) {
		$offerCode = "";
		$companyId = "";
		$activationDate = "";
		$expirationDate = "";
		$headline = "";
		$description = "";
		$url = "";
		$redirectUrl = "";
		$notes = "";
		$displayInFrame = "";
		$shoppingSpree = "";
		$edited = "";
		$finalApproval = "";
		$specialStatus = "";
		$ssSortOrder = "";
		$categories = "";
	}
}

if ($id != '' && $keepValues != "true") {
	// If Clicked Edit, display values in fields and
	// buttons to edit/Reset...
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT O.*, OC.categoryId
					FROM   edOffers O LEFT JOIN edOfferCategoryRel OC ON O.id = OC.offerId
			  		WHERE  O.id = '$id'";
	$result = mysql_query($selectQuery);
	
	if ($result) {
		
		while ($row = mysql_fetch_object($result)) {
			$offerCode = $row->offerCode;
			$companyId = $row->companyId;
			$activationDate = $row->activationDate;
			$expirationDate = $row->expirationDate;
			$headline = ascii_encode($row->headline);
			$description = ascii_encode($row->description);
			$url = $row->url;
			//$redirectUrl = $row->redirectUrl;
			$redirectUrl = htmlspecialchars("<A href=\" ". $sGblOfferRedirectsPath."?src=$offerCode \">Click Here</a>");
			$notes = ascii_encode($row->notes);
			$displayInFrame = $row->displayInFrame;
			$shoppingSpree = $row->shoppingSpree;
			$popOption =  $row->popOption;
			$popupIdList = $row->popupId;
			$edited = $row->edited;
			$finalApproval = $row->finalApproval;
			$specialStatus = $row->specialStatus;
			$ssSortOrder = $row->ssSortOrder;
		}
		mysql_free_result($result);
	} else {
		echo mysql_error();
	}
	
} else {
	
	$headline = ascii_encode(stripslashes($headline));
	$description = ascii_encode(stripslashes($description));
	$notes = ascii_encode(stripslashes($notes));
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=abandonNew value=' Abandon & New  '>";	
}

$companyQuery = "SELECT   id, companyName, code
				   FROM     edOfferCompanies
				   ORDER BY companyName";
$companyResult = mysql_query($companyQuery);

while ( $companyRow = mysql_fetch_object($companyResult)) {
	if ($companyRow->id == $companyId) {
		$selected = "selected";
	} else {
		$selected = "";
	}
	$companyOptions .= "<option value='".$companyRow->id."' $selected>".$companyRow->companyName . " - " . $companyRow->code;
}

// set curr date values to be selected by default
if (!($id)) {
	$monthActivation = $currMonth;
	$monthExpiration = $currMonth;
	$dayActivation = $currDay;
	$dayExpiration = $currDay;
	$yearActivation = $currYear;
	$yearExpiration = $currYear+1;
}

if ($id) {
		$monthActivation = substr($activationDate,5,2);
		$monthExpiration = substr($expirationDate,5,2);
		$dayActivation = substr($activationDate,8,2);
		$dayExpiration = substr($expirationDate,8,2);
		$yearActivation = substr($activationDate,0,4);
		$yearExpiration = substr($expirationDate,0,4);
	}

	
	
// prepare month options for From and To date
for ($i = 0; $i < count($aGblMonthsArray); $i++) {
	$value = $i+1;
		
	if ($value < 10) {
		$value = "0".$value;
	}
		
	if ($value == $monthActivation) {
		$fromSel = "selected";
	} else {
		$fromSel = "";
	}
	if ($value == $monthExpiration) {
		$toSel = "selected";
	} else {
		$toSel = "";
	}
	
	$monthActivationOptions .= "<option value='$value' $fromSel>$aGblMonthsArray[$i]";
	$monthExpirationOptions .= "<option value='$value' $toSel>$aGblMonthsArray[$i]";
}

// prepare day options for From and To date
for ($i = 1; $i <= 31; $i++) {
	
	if ($i < 10) {
		$value = "0".$i;
	} else {
		$value = $i;
	}
	
	if ($value == $dayActivation) {
		$fromSel = "selected";
	} else {
		$fromSel = "";
	}
	if ($value == $dayExpiration) {
		$toSel = "selected";
	} else {
		$toSel = "";
	}
	$dayActivationOptions .= "<option value='$value' $fromSel>$i";
	$dayExpirationOptions .= "<option value='$value' $toSel>$i";
}

// prepare year options for From and To date
for ($i = $currYear-1; $i <= $currYear+10; $i++) {
	
	if ($i == $yearActivation) {
		$fromSel = "selected";
	} else {
		$fromSel = "";
	}
	if ($i == $yearExpiration) {
		$toSel = "selected";
	} else {
		$toSel ="";
	}
	
	$yearActivationOptions .= "<option value='$i' $fromSel>$i";
	$yearExpirationOptions .= "<option value='$i' $toSel>$i";
}

// Set frame option checked
$noFrameChecked = "";
$topFrameChecked = "";
$leftFrameChecked = "";
$rightFrameChecked = "";
$bottomFrameChecked = "";

switch ($displayInFrame) {
	case "top":
	$topFrameChecked = "checked";
	break;
	case "left":
	$leftFrameChecked = "checked";
	break;
	case "right":
	$rightFrameChecked = "checked";
	break;
	case "bottom":
	$bottomFrameChecked = "checked";
	break;
	default:
	$noFrameChecked = "checked";
}

// Which popup option is checked
$noPopupChecked = '';
$popupChecked = '';
$popUnderChecked = '';
switch ($popOption) {
	case "popup":
	$popupChecked = "checked";
	break;
	case "popunder":
	$popUnderChecked = "checked";
	break;
	default:
	$noPopupChecked = "checked";
}


// Which special status option is selected
$featuredOfferSelected = '';
$hotBargainSelected = '';
$featuredSweepstakeSelected = '';
$freeBiesSelected = "";
$freeCookingSelected = "";

switch ($specialStatus) {
	case "featuredSweepstake":
	$featuredSweepstakeSelected = "selected";
	break;
	case "hotBargain":
	$hotBargainSelected = "selected";
	break;
	case "featuredOffer":
	$featuredOfferSelected = "selected";
	break;
	case "freeBies":
	$freeBiesSelected = "selected";
	break;
	case "freeCooking":
	$freeCookingSelected = "selected";
	
}

$specialStatusOptions = "<option value=''>
						<option value='featuredOffer' $featuredOfferSelected>Featured Offer
						<option value='hotBargain' $hotBargainSelected>Today's Hot Bargains
						<option value='featuredSweepstake' $featuredSweepstakeSelected>Featured Sweepstake
						<option value='freeBies' $freeBiesSelected>Hot FreeBies
						<option value='freeCooking' $freeCookingSelected>Today's Free Cooking Offer";


// Which PopUp is selected
$popQuery = "SELECT *
			 FROM   edOfferPopUps
			 ORDER BY popupName";
$popResult = mysql_query($popQuery);
while ($popRow = mysql_fetch_object($popResult)) {
	$temp = "'".$popRow->id."'";
	if (strstr($popupIdList,$temp)) {
		$selected = "selected";
	} else {
		$selected = "";
	}
	
	$popupSelectOptions .= "<option value='".$popRow->id."' $selected>$popRow->popupName";
	
}


// If Edited should  be displayed as checked
if ($edited == 'Y') {
	$editedChecked = "checked";
}
// If Final Approval should  be displayed as checked
if ($finalApproval == 'Y') {
	$finalApprovalChecked = "checked";
}

// Prepare checkboxes for Categories
$categoryQuery = "SELECT *
				  FROM   edOfferCategories 
				  ORDER BY category";
$categoryResult = mysql_query($categoryQuery);
echo mysql_error();
$j = 0;
while ($categoryRow = mysql_fetch_object($categoryResult)) {
	$categoryId = $categoryRow->id;
	$category = $categoryRow->category;
	
	$offerQuery = "SELECT offerId
				   FROM   edOfferCategories, edOfferCategoryRel
				   WHERE  edOfferCategoryRel.categoryId = '$categoryId'
				   AND    edOfferCategoryRel.offerId = '$id'
				   AND   edOfferCategoryRel.categoryId = edOfferCategories.id";
	
	$offerResult = mysql_query($offerQuery);
	if(mysql_num_rows($offerResult)>0){
		$categoryChecked  = "checked";
	} else {
		$categoryChecked = "";
	}
	
	if($j%3 == 0) {
		if($j != 0) {
			$categoryCheckboxes .= "</tr>";
		}
		$categoryCheckboxes .= "<tr>";
	}
	
	// check if this is a parent category
	$checkQuery = "SELECT *
				   FROM   edOfferCategories
				   WHERE  parentCategory = '$categoryId'";
	$checkResult = mysql_query($checkQuery);
	if (mysql_num_rows($checkResult)>0 ) {
		$category = "<B>".$category."</B>";
	}
	
	$categoryCheckboxes .= "<td width=5% valign=top><input type=checkbox name='category_".$categoryRow->id."' value='".$categoryRow->id."' $categoryChecked></td><td  width=28%>$category</td>";
	$j++;
}
$categoryCheckboxes .= "</tr>";
$filter = stripslashes($filter);
$exclude = stripslashes($exclude);
// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=id value='$id'>
			<input type=hidden name=offerCode value='$offerCode'>
			<input type=hidden name=sourceCode value='$sourceCode'>
			<input type=hidden name=recPerPage value='$recPerPage'>
			<input type=hidden name=filter value=\"$filter\">
			<input type=hidden name=exactMatch value='$exactMatch'>
			<input type=hidden name=exclude value=\"$exclude\">
			<input type=hidden name=searchIn value='$searchIn'>
			<input type=hidden name=backTo value='$backTo'>";

include("../../includes/adminAddHeader.php");

?>			

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $sReloadWindowOpener;?>
<center><?php echo $showRedirect;?></center>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>	

	<tr><td>Company</td>
		<td><select name=companyId>
		<?php echo $companyOptions;?>
			</select></td>
	</tr>
	<tr><td>Activation Date</td>
		<td><select name=monthActivation>
			<?php echo $monthActivationOptions;?>
			</select> &nbsp;<select name=dayActivation>
			<?php echo $dayActivationOptions;?>
			</select> &nbsp;<select name=yearActivation>
			<?php echo $yearActivationOptions;?>
			</select></td>
	</tr>
	<tr><td>Expiration Date</td>
		<td><select name=monthExpiration>
			<?php echo $monthExpirationOptions;?>
			</select> &nbsp;<select name=dayExpiration>
			<?php echo $dayExpirationOptions;?>
			</select> &nbsp;<select name=yearExpiration>
			<?php echo $yearExpirationOptions;?>
			</select></td>
	</tr>
	<tr><td>Headline</td>
		<td><input type=text name=headline value='<?php echo $headline;?>' size=50></td>
	</tr>	
	<tr><td>Description</td>
		<td><textarea name=description rows=6 cols=40><?php echo $description;?></textarea></td>
	</tr>	
	<tr><td>Offer URL</td>
		<td><input type=text name=url value='<?php echo $url;?>' size=60></td>
	</tr>	
	<tr><td>Redirect URL</td>
		<td><?php echo $redirectUrl;?>
	</tr>
	<tr><td>Notes</td>
		<td><textarea name=notes rows=3 cols=40><?php echo $notes;?></textarea></td>
	</tr>	
	<tr>
		<td>Display Frame</td><td><input name=displayInFrame type=radio value='' <?php echo $noFrameChecked;?>> No Frame		
	</tr>
	<tr>
		<td></td><td><input name=displayInFrame type=radio value='top' <?php echo $topFrameChecked;?>> Top Frame
		&nbsp; &nbsp; &nbsp; &nbsp; <input name=displayInFrame type=radio value='left' <?php echo $leftFrameChecked;?>> Left Frame
	</tr>
	<tr>
		<td></td><td><input name=displayInFrame type=radio value='right' <?php echo $rightFrameChecked;?>> Right Frame
		&nbsp; &nbsp; &nbsp; <input name=displayInFrame type=radio value='bottom' <?php echo $bottomFrameChecked;?>> Bottom Frame		
	</tr>

	<tr><td>PopUp Option</td><td><input type=radio name=popOption value='' <?php echo $noPopupChecked;?>> No PopUp
		&nbsp; &nbsp; &nbsp; <input type=radio name=popOption value='popup' <?php echo $popupChecked;?>> PopUp
		&nbsp; &nbsp; &nbsp; <input type=radio name=popOption value='popunder' <?php echo $popUnderChecked;?>> PopUnder
		</td>
	</tr>
	<tr><td>PopUp/PopUnder</td>
		<td><select name=popupId[] multiple size=3>
		<?php echo $popupSelectOptions;?>
		</select></td>
	</tr>
	<tr><td>Special Status</td>
		<td><select name=specialStatus>
		<?php echo $specialStatusOptions;?>
		</select> &nbsp; &nbsp;  SS Sort Order <input type=text name=ssSortOrder value="<?php echo $ssSortOrder;?>" size=5></td>
	</tr>
	
	<tr><td>Edited</td>
		<td><input type=checkbox name=edited value='Y' <?php echo $editedChecked;?>></td>
	</tr>		
	<tr><td>Final Approval</td>
		<td><input type=checkbox name=finalApproval value='Y' <?php echo $finalApprovalChecked;?>></td>
	</tr>		
	<tr>
	<td colspan=2 align=center class=header>Categories</td>
	</tr>
	</table>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>	
	<tr>
	<?php echo $categoryCheckboxes;?>
	</tr>			
</table>


<?php
	include("../../includes/adminAddFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>
