<?php

/*********

Script to Add/Edit Ot Page

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblLibsPath/urlFunctions.php");

session_start();

$sPageTitle = "Nibbles Production List - Add/Edit Offer In Production List";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($sSaveClose || $sSaveNew || $sSaveContinue) {
		// When New Record Submitted
		
		// if estimate date unknown, put at last updating priority as max+1
		if ($iEstimateDateUnknown) {
			$sTempQuery = "SELECT max(priority) as maxPriority
						   FROM   productionList
						   WHERE  id != '$iId'";
			$rTempResult = dbQuery($sTempQuery);
			while ($oTempRow = dbFetchObject($rTempResult)) {
				$iMaxPriority = $oTempRow->maxPriority;
			}
						   
			$iPriority = $iMaxPriority + 1;
		}

		if (!($iId)) {
			
			// Check if code already exists...
			$sCheckQuery = "SELECT *
					   FROM   productionList
					   WHERE  offer = '$sOffer'"; 
			$rCheckResult = dbQuery($sCheckQuery);
			
			if (dbNumRows($rCheckResult) == 0) {
				
				// get preceding order items and calculate time as per
				// cobrands 2 hrs
				// new offer 3 hrs
				// changes to existing offers 1 hr
				
				
				$sTempQuery = "SELECT *
							   FROM   productionList
							   WHERE  priority < '$iPriority'
							   AND    offerType IN ('Cobrands', 'New Offers', 'Changes To Existing Offers')";
				$rTempResult = dbQuery($sTempQuery);
				
				echo dbERror();
				$iPrecedingHours = 0;
				$iPrecedingDays = 0;
				while ($oTempRow = dbFetchObject($rTempResult)) {
					$sTempOfferType = $oTempRow->offerType;
					
					
					switch ($sTempOfferType) {
						
						case "Cobrands":
						$iPrecedingHours += 2;
						break;
						case "New Offers":
						$iPrecedingHours += 3;
						break;
						case "Changes To Existing Offers";
						$iPrecedingHours += 1;
						break;
					}
					if ($iPrecedingHours >=6) {
						$iPrecedingDays++;
						$iPrecedingHours -= 6;
					}
				}
				
				
				// get next date
				$sDateQuery = "SELECT date_add(CURRENT_DATE, INTERVAL $iPrecedingDays DAY) estimateDate,
									  date_format(date_add(CURRENT_DATE, INTERVAL $iPrecedingDays DAY),'%a') estimateDay";
				$rDateResult= dbQuery($sDateQuery);
				while ($oDateRow = dbFetchObject($rDateResult)) {
					$sEstimateDate = $oDateRow->estimateDate;
					$sEstimateDay = strtolower($oDateRow->estimateDay);
				}
				
				
				if ($sEstimateDay =='sat'  || $sEstimateDay == 'sun') {
					if ($sEstimateDay =='sat' ) {
						$sDateQuery2 = "SELECT date_add('$sEstimateDate', INTERVAL 2 DAY) as estimateDay";
					} else if ($sEstimateDay =='sun' ) {
						$sDateQuery2 = "SELECT date_add('$sEstimateDate', INTERVAL 1 DAY) as estimateDay";
					}
					$rDateResult2= dbQuery($sDateQuery2);
					while ($oDateRow2 = dbFetchObject($rDateResult2)) {
						$sEstimateDate = $oDateRow2->estimateDate;
						
					}
				}
				
				
				// Insert record if everything is fine
				
				$sAddQuery = "INSERT INTO productionList(priority, offer, dateEntered, owner, offerType, offerPage, comments, estimateDate, estimateDateUnknown)
					 VALUES('$iPriority', \"$sOffer\", CURRENT_DATE, \"$sOwner\", \"$sOfferType\", \"$sOfferPage\", \"$sComments\", \"$sEstimateDate\",'$iEstimateDateUnknown')";
				
				$rResult = dbQuery($sAddQuery);
				
				if ( !($rResult) ) {
					echo dbError();
				}
			} else {
				$sMessage = "Offer Already Exists...";
				$bKeepValues = true;
			}
			
		} else if ($iId) {
			
			// When Record Edited
			// Check if code already exists...
			$sCheckQuery = "SELECT *
					   FROM   productionList
					   WHERE  offer = '$sOffer'
					   AND    id != '$iId'"; 
			$rCheckResult = dbQuery($sCheckQuery);
			
			if (dbNumRows($rCheckResult) == 0) {
								
				if ($_SERVER['PHP_AUTH_USER'] == 'phil' || $_SERVER['PHP_AUTH_USER'] == 'stuart') {
					$sEditQuery = "UPDATE   productionList
					   			   SET 		priority = '$iPriority'
								   WHERE    id = '$iId'";
				} else {
				$sEditQuery = "UPDATE   productionList
					   			SET 	priority = '$iPriority',
										dateEntered = \"$sDateEntered\",
										offer = \"$sOffer\",
										owner = \"$sOwner\",
										offerType = \"$sOfferType\",
										offerPage = \"$sOfferPage\",
										comments = \"$sComments\",
										estimateDate = \"$sEstimateDate\",
										estimateDateUnknown = '$iEstimateDateUnknown'
		 			   			WHERE    id = '$iId'";
				}
				$rResult = dbQuery($sEditQuery);
				echo dbError();
				if ($rResult) {
					
					$sSelectQuery = "SELECT *
									 FROM   productionList
									 WHERE  offerType IN ('Co-Brands', 'New Offers', 'Changes To Existing Offers')
									 ORDER BY priority";
					$rSelectResult = dbQuery($sSelectQuery);
					echo dbError();
					$iPrecedingDays = 0;
					$iPrecedingHours = 0;
					while ($oSelectRow = dbFetchObject($rSelectResult)) {
						
						$iTempId = $oSelectRow->id;
						
						$sTempOfferType = $oSelectRow->offerType;
						
						switch ($sTempOfferType) {
							
							case "Co-Brands":
							$iPrecedingHours += 2;
							break;
							case "New Offers":
							$iPrecedingHours += 3;
							break;
							case "Changes To Existing Offers";
							$iPrecedingHours += 1;
							break;
						}
						if ($iPrecedingHours > 6) {
							$iPrecedingDays++;
							$iPrecedingHours -= 6;
						}
						
						
						// get next date
						$sEstimateDate = '';
						$sEstimateDay = '';
						$sDateQuery = "SELECT date_add(CURRENT_DATE, INTERVAL $iPrecedingDays DAY) estimateDate,
									  date_format(date_add(CURRENT_DATE, INTERVAL $iPrecedingDays DAY),'%a') estimateDay";
						$rDateResult= dbQuery($sDateQuery);
						while ($oDateRow = dbFetchObject($rDateResult)) {
							$sEstimateDate = $oDateRow->estimateDate;
							$sEstimateDay = strtolower($oDateRow->estimateDay);
						}
						
						//echo $iPrecedingDays; 
						
						if ($sEstimateDay =='sat'  || $sEstimateDay == 'sun') {
							if ($sEstimateDay =='sat' ) {
								$sDateQuery2 = "SELECT date_add('".$sEstimateDate."', INTERVAL 2 DAY) as estimateDate";
								$iPrecedingDays += 2;
							} else if ($sEstimateDay =='sun' ) {
								$sDateQuery2 = "SELECT date_add('".$sEstimateDate."', INTERVAL 1 DAY) as estimateDate";
								$iPrecedingDays = $iPrecedingDays + 1;
							}
							$rDateResult2= dbQuery($sDateQuery2);
							echo dbError();
							while ($oDateRow2 = dbFetchObject($rDateResult2)) {
								$sEstimateDate = $oDateRow2->estimateDate;
								
							}
						}
						
						$sTempUpdateQuery = "UPDATE productionList
											 SET estimateDate = '$sEstimateDate'
											 WHERE id = '$iTempId'";
						$rTempUpdateResult = dbQuery($sTempUpdateQuery);
						//echo "<BR>$sTempOfferType $iPrecedingDays $iPrecedingHours ".$sTempUpdateQuery. dbError();
						echo dbError();
						
					}
					
				} else {
					$sMessage = dbError();
					$bKeepValues = true;
				}
			}
		}
		
		if ($sSaveContinue) {
			if ($bKeepValues != true) {
				echo "<script language=JavaScript>
		window.opener.location.reload();	
		</script>";
				// exit from this script
			}
		} else if ($sSaveClose) {
			if ($bKeepValues != true) {
				echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";
				// exit from this script
				exit();
			}
		} else if ($sSaveNew) {
			
			if ($bKeepValues != true) {
				$sReloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";		
				
				$sOffer = "";
			}
		}
	}
	
	
	if ($iId) {
		
		// Get the data to display in HTML fields for the record to be edited
		$sSelectQuery = "SELECT *
					 FROM   productionList
			  		 WHERE  id = '$iId'";
		$rResult = dbQuery($sSelectQuery);
		
		if ($rResult) {
			
			while ($oRow = dbFetchObject($rResult)) {
				$iPriority = $oRow->priority;
				$sOffer = $oRow->offer;
				$sDateEntered = $oRow->dateEntered;
				$sOwner = $oRow->owner;
				$sOfferType = $oRow->offerType;
				$sOfferPage = $oRow->offerPage;
				$sComments = ascii_encode($oRow->comments);
				$sEstimateDate = $oRow->estimateDate;
				$iEstimateDateUnknown = $oRow->estimateDateUnknown;
				$sDateEnteredField = "<tr><td>Date Entered</td>
						<td colspan=3><input type=text name='sDateEntered' value='$sDateEntered'></td>
						</tr>	";
				
				//$sEstimateDateField = "<tr><td>Estimate Date</td>
						//<td colspan=3><input type=text name='sEstimateDate' value='$sEstimateDate'></td>
						//</tr>	";
			}
			
			dbFreeResult($rResult);
		} else {
			echo dbError();
		}
	} else {
		
		// If add button is clicked, display another two buttons
		$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
	}
	
	$sCobrandsSelected = "";
	$sNewOffersSelected = "";
	$sChangeOffersSelected = "";
	$sAwaitingApprovalSelected = "";
	$sCatalogOffersSelected = "";
	
	switch ($sOfferType) {
		
		case "Co-Brands":
		$sCobrandsSelected = "selected";
		break;
		case "New Offers":
		$sNewOffersSelected = "selected";
		break;
		case "Changes To Existing Offers";
		$sChangeOffersSelected = "Selected";
		break;
		case "Offers Awaiting Approval":
		$sAwaitingApprovalSelected = "selected";
		break;
		case "Catalog Offers":
		$sCatalogOffersSelected = "selected";
		break;
		
	}
	
	
	$sEstimateDateUnknownChecked = '';
	if ($iEstimateDateUnknown) {
		$sEstimateDateUnknownChecked = "checked";
	}
	
	$sOfferTypeOptions = "<option value='Co-Brands' $sCobrandsSelected>Co-Brands
					  <option value='New Offers' $sNewOffersSelected>New Offers
					  <option value='Changes To Existing Offers' $sChangeOffersSelected>Changes To Existing Offers
					  <option value='Offers Awaiting Approval' $sAwaitingApprovalSelected>Offers Awaiting Approval
					  <option value='Catalog Offers' $sCatalogOffersSelected>Catalog Offers";
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";
	
	include("../../includes/adminAddHeader.php");
	
?>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post enctype=multipart/form-data>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>

	<tr><td>Priority</td>
		<td colspan=3><input type=text name='iPriority' value="<?php echo $iPriority;?>"></td>
	</tr>
	<?php 
	if (! isAdmin()) {
		?>
		
		<tr><td>Offer</td>
		<td colspan=3><?php echo $sOffer;?></td>
	</tr>
	<?php
	
	} else {

		?>
	<tr><td>Offer</td>
		<td colspan=3><input type=text name='sOffer' value="<?php echo $sOffer;?>"></td>
	</tr>
	<?php echo $sDateEnteredField;?>
	<tr><td>Owner</td>
		<td colspan=3><input type=text name='sOwner' value='<?php echo $sOwner;?>'></td>
	</tr>	
	<tr><td>Offer Type</td>
		<td colspan=3><select name='sOfferType'>
			<?php echo $sOfferTypeOptions;?>
			</select></td>
	</tr>
	<tr><td>Offer Page</td>
		<td colspan=3><input type=text name='sOfferPage' value="<?php echo $sOfferPage;?>"></td>
	</tr>
	
	<tr><td>Comments</td>
		<td colspan=3><textarea name='sComments'  rows=10 cols=50><?php echo $sComments;?></textarea></td>
	</tr>
	
	<tr><td>Estimate Date</td>
		<td colspan=3><input type=text name='sEstimateDate' value='<?php echo $sEstimateDate;?>'>
		&nbsp; &nbsp; Estimate Date Unknown <input type=checkbox name=iEstimateDateUnknown value='1' <?php echo $sEstimateDateUnknownChecked;?>> </td>
	</tr>	
	
</table>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD colspan=2 align=center >
		<input type=submit name=sSaveContinue value='Save & Continue'> &nbsp; &nbsp; 
		</td><td></td>
	</tr>	
	</table>
<?php
	}
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>