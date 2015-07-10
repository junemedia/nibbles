<?php

/*********

Script to Add/Edit Production sheet Request

**********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblLibsPath/urlFunctions.php");
include("$sGblLibsPath/dateFunctions.php");

$sToday = date('Y')."-".date('m')."-".date('d');
$sTomorrow = DateAdd("d", 1, date('Y')."-".date('m')."-".date('d'));


session_start();

$sPageTitle = "Nibbles Production List - Add/Edit Request In Production List";

if (hasAccessRight($iMenuId) || isAdmin()) {

	if ($sSaveClose || $sSaveNew || $sSaveContinue) {
		// When New Record Submitted

		// if estimate date unknown, put at last updating priority as max+1
		if ($sStatus == 'unknownSchedule') {
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

			// Check if request already exists...
			$sCheckQuery = "SELECT *
					   FROM   productionList
					   WHERE  request = '$sRequest'"; 
			$rCheckResult = dbQuery($sCheckQuery);

			if (dbNumRows($rCheckResult) == 0) {

				// get preceding order items and calculate time as per
				// cobrands 2 hrs
				// new offer 3 hrs
				// changes to existing offers 1 hr

				$sTempQuery = "SELECT *
							   FROM   productionList
							   WHERE  priority < '$iPriority'
							   AND    requestType IN ('New Co-Brand', 'New Offer', 'Changes To Existing Co-Brand', 'Changes To Existing Offer', 'Other')";
				$rTempResult = dbQuery($sTempQuery);

				echo dbError();
				$iPrecedingHours = 0;
				$iPrecedingDays = 0;
				while ($oTempRow = dbFetchObject($rTempResult)) {
					$sTempRequestType = $oTempRow->requestType;


					switch ($sTempRequestType) {

						case "New Co-Brand":
						$iPrecedingHours += getVar('newCoBrandWorkHours');
						break;
						case "New Offer":
						$iPrecedingHours += getVar('newOfferWorkHours');
						break;
						case "Changes To Existing Co-Brand";
						$iPrecedingHours += getVar('changesToExistingCoBrandWorkHours');
						break;
						case "Changes To Existing Offer";
						$iPrecedingHours += getVar('changesToExistingOfferWorkHours');
						break;
						case "Other";
						$iPrecedingHours += getVar('otherWorkHours');
						break;
					}
					if ($iPrecedingHours >= getVar('dailyWorkHours')) {
						$iPrecedingDays++;
						$iPrecedingHours -= getVar('dailyWorkHours');
					}
				}
				//mail('bbevis@amperemedia.com','preceeding things',"$iPrecedingDays is preceeding days, $iPrecedingHours is preceeding hours");

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

					$sEstimateDate = $oDateRow2->estimateDate;
				}


				if ($iHours == '') {
					if ($sRequestType == 'New Offer') {
						$iTempHours = 3;
					} else if ($sRequestType == 'New Co-Brand') {
						$iTempHours = 2;
					} else if ($sRequestType == 'Changes To Existing Offer') {
						$iTempHours = 1;
					} else if ($sRequestType == 'Changes to Existing Co-Brand') {
						$iTempHours = 1;
					} else {
						$iTempHours = 1;
					}
				} else {
					if (!ctype_digit($iHours)) {
						if ($sRequestType == 'New Offer') {
							$iTempHours = 3;
						} else if ($sRequestType == 'New Co-Brand') {
							$iTempHours = 2;
						} else if ($sRequestType == 'Changes To Existing Offer') {
							$iTempHours = 1;
						} else if ($sRequestType == 'Changes to Existing Co-Brand') {
							$iTempHours = 1;
						} else {
							$iTempHours = 1;
						}
						$sMessage = "Estimated Hours Must Be Numeric...";
						$bKeepValues = true;
					} else {
						$iTempHours = intval($iHours);
					}
				}
				
				// Insert record if everything is fine
				$sComments = addslashes($sComments);
				$sAddQuery = "INSERT INTO productionList(priority, request, dateEntered, owner, requestType, offerPage,
								comments, estimateDate, completionDate, status, hours)
						  VALUES('$iPriority', \"$sRequest\", CURRENT_DATE, \"$sOwner\", \"$sRequestType\", \"$sOfferPage\", 
								\"$sComments\", \"$sEstimateDate\",\"$sCompletionDate\", \"$sStatus\", \"$iTempHours\")";
				
				mail('bbevis@amperemedia.com','new prodlist query',$sAddQuery);

				// start of track users' activity in nibbles 
				$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: " . addslashes($sAddQuery) . "\")"; 
				$rLogResult = dbQuery($sLogAddQuery); 
				echo  dbError(); 
				// end of track users' activity in nibbles		
				
				
				$rResult = dbQuery($sAddQuery);

				$mailEmailTo = "ot@myfree.com";
				$mailSubject = "New OT Request from $sOwner ($sOfferPage)";
				$mailReportContent = "A new request has been submitted by $sOwner.\n\n";
				$mailReportContent .= "Request Type:  $sRequestType\n";
				$mailReportContent .= "Page Name:     $sOfferPage\n";
				$mailReportContent .= "Request Name:  $sRequest\n\n";
				$mailReportContent .= "Priority: $iPriority\n\n";
				$mailReportContent .= "Comments:\n\n";
				$mailReportContent .= "$sComments";


				$mailHeaders = "From: Automated Request Notification <ot@myfree.com>\n";

				mail($mailEmailTo, $mailSubject, $mailReportContent, $mailHeaders);

			}  else {
				$sMessage = "Request Already Exists...";
				$bKeepValues = true;
			}

			if ( $rResult ) {
				if ($sEstimateDate == $sToday || $sEstimateDate == $sTomorrow) {
					// send priority changed email to jr, josh
					$sEmailMessage = "New production item $sRequest added with priority $iOldPriority to $iPriority and estimate date $sEstimateDate";
					$sHeaders = "From:nibbles@amperemedia.com\r\n";
					$sHeaders .= "cc: ";
					$sEmailQuery = "SELECT *
								   	FROM   emailRecipients
			 						WHERE  purpose = 'production sheet update'";
					$rEmailResult = dbQuery($sEmailQuery);
					echo dbError();
					while ($oEmailRow = dbFetchObject($rEmailResult)) {
						$sRecipients = $oEmailRow->emailRecipients;
					}

					if (!($sEmailTo)) {
						$sEmailTo = substr($sRecipients,0,strlen($sRecipients)-strrpos(strrev($sRecipients),","));
					}

					$sCcTo = substr($sRecipients,strlen($sEmailTo));

					$sHeaders .= ", $sCcTo";
					$sHeaders .= "\r\n";

					$sSubject = "Production sheet update";

					mail($sEmailTo, $sSubject, $sEmailMessage, $sHeaders);

				}
			} else {
				echo dbError();
			}


		} else if ($iId) {



			// When Record Edited      && $sCompletionDate == '0000-00-00'
			if ($sStatus == 'completed') {
				$sCompletionDate=$sToday;
				// check if priority order changed
				$sSelectQuery = "SELECT *
							FROM   productionList
							WHERE  id = '$iId'";
				$rSelectResult = dbQuery($sSelectQuery);
				echo dbError();
				while ($oSelectRow = dbFetchObject($rSelectResult)) {
					$iOldPriority = $oSelectRow->priority;
					$sOldRequest = $oSelectRow->request;
					$sOldEstimateDate = $oSelectRow->estimateDate;

				}

				// Check if code already exists...
				$sCheckQuery = "SELECT *
					   FROM   productionList
					   WHERE  request = '$sRequest'
					   AND    id != '$iId'"; 
				$rCheckResult = dbQuery($sCheckQuery);

				if (dbNumRows($rCheckResult) == 0) {

					if ($iPriority != $iOldPriority) {
						// send priority changed email to jr, josh
						$sEmailMessage = "Production item $sOldRequest scheduled on $sOldEstimateDate has been changed from priority $iOldPriority to $iPriority";
						$sHeaders = "From:nibbles@amperemedia.com\r\n";
						$sHeaders .= "cc: ";
						$sEmailQuery = "SELECT *
								   	FROM   emailRecipients
			 						WHERE  purpose = 'production sheet update'";
						$rEmailResult = dbQuery($sEmailQuery);
						echo dbError();
						while ($oEmailRow = dbFetchObject($rEmailResult)) {
							$sRecipients = $oEmailRow->emailRecipients;
						}

						if (!($sEmailTo)) {
							$sEmailTo = substr($sRecipients,0,strlen($sRecipients)-strrpos(strrev($sRecipients),","));
						}

						$sCcTo = substr($sRecipients,strlen($sEmailTo));

						$sHeaders .= ", $sCcTo";
						$sHeaders .= "\r\n";

						$sSubject = "Production sheet update";

						mail($sEmailTo, $sSubject, $sEmailMessage, $sHeaders);

					}

					if (($iPriority != $iOldPriority) && ($_SERVER['PHP_AUTH_USER'] == 'phil' || $_SERVER['PHP_AUTH_USER'] == 'stuart')) {
						$sEditQuery = "UPDATE   productionList
					   			   SET 		priority = '$iPriority'";

						if ($iPriority > $iOldPriority) {
							$sEditQuery .= ", priorityChanged = 'Up' ";
						} else if ($iPriority < $iOldPriority) {
							$sEditQuery .= ", priorityChanged = 'Down' ";
						}

						$sEditQuery .= " WHERE    id = '$iId'";

					} else {
						
						$sComments = addslashes($sComments);
						
						$sEditQuery = "UPDATE   productionList
					   			SET 	priority = '$iPriority',
										dateEntered = \"$sDateEntered\",
										request = \"$sRequest\",
										owner = \"$sOwner\",
										requestType = \"$sRequestType\",
										offerPage = \"$sOfferPage\",
										comments = \"$sComments\",
										estimateDate = \"$sEstimateDate\",
										completionDate = '$sCompletionDate',
										status = '$sStatus'";

						if ($iPriority > $iOldPriority) {
							$sEditQuery .= ", priorityChanged = 'Up' ";
						} else if ($iPriority < $iOldPriority) {
							$sEditQuery .= ", priorityChanged = 'Down' ";
						}

						$sEditQuery .= " WHERE    id = '$iId'";
					}

					// start of track users' activity in nibbles 
					$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
					$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: " . addslashes($sEditQuery) . "\")"; 
					$rLogResult = dbQuery($sLogAddQuery); 
					echo  dbError(); 
					// end of track users' activity in nibbles		
					
					
					$rResult = dbQuery($sEditQuery);
					echo dbError();
					if (!($rResult)) {
						$sMessage = dbError();
						$bKeepValues = true;
					}
				}
			} else {
				$sComments = addslashes($sComments);
				$sEditQuery = "UPDATE   productionList
					   			SET 	priority = '$iPriority',
										dateEntered = \"$sDateEntered\",
										request = \"$sRequest\",
										owner = \"$sOwner\",
										requestType = \"$sRequestType\",
										offerPage = \"$sOfferPage\",
										comments = \"$sComments\",
										estimateDate = \"$sEstimateDate\",
										completionDate = '$sCompletionDate',
										status = '$sStatus'";

				if (isAdmin() || $_SERVER['PHP_AUTH_USER'] == 'phil' || $_SERVER['PHP_AUTH_USER'] == 'stuart') {
					if (ctype_digit($iHours)) {
						$sEditQuery .= ", hours = \"$iHours\" ";
					} else {
						$sMessage = "Estimated Hours Must Be Numeric...";
						$bKeepValues = true;
					}
				}
				
				if ($iPriority > $iOldPriority) {
					$sEditQuery .= ", priorityChanged = 'Up' ";
				} else if ($iPriority < $iOldPriority) {
					$sEditQuery .= ", priorityChanged = 'Down' ";
				}

				$sEditQuery .= " WHERE    id = '$iId'";
			}
			
			
			if ($sStatus == 'completed' || $sStatus == 'deleted') {
				$sAddLogQuery = "INSERT INTO productionListLog(dateTimeAdded, userName, request, status)
						  VALUES(NOW(), '".$_SERVER['PHP_AUTH_USER']."', \"$sRequest\", \"$sStatus\")";
				$rLogResult = dbQuery($sAddLogQuery);
			}


			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: " . addslashes($sEditQuery) . "\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$rResult = dbQuery($sEditQuery);
			echo dbError();
			if (!($rResult)) {
				$sMessage = dbError();
				$bKeepValues = true;
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
					if (!(window.opener.closed)) {
						window.opener.location.reload();
					}
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

				$sRequest = "";
			}
		}
	}


	$sStatus = '';
	if ($iId) {

		// Get the data to display in HTML fields for the record to be edited
		$sSelectQuery = "SELECT *
					 FROM   productionList
			  		 WHERE  id = '$iId'";
		$rResult = dbQuery($sSelectQuery);

		if ($rResult) {

			while ($oRow = dbFetchObject($rResult)) {
				$iPriority = $oRow->priority;
				$sRequest = $oRow->request;
				$sDateEntered = $oRow->dateEntered;
				$sOwner = $oRow->owner;
				$sRequestType = $oRow->requestType;
				$sOfferPage = $oRow->offerPage;
				$sComments = ascii_encode($oRow->comments);
				$sEstimateDate = $oRow->estimateDate;
				$iHours = $oRow->hours;
				//$iEstimateDateUnknown = $oRow->estimateDateUnknown;
				if ($_SERVER['PHP_AUTH_USER'] == 'athomashow' || $_SERVER['PHP_AUTH_USER'] == 'cwright' || $_SERVER['PHP_AUTH_USER'] == 'sgreenwald' || $_SERVER['PHP_AUTH_USER'] == 'kkousins' || $_SERVER['PHP_AUTH_USER'] == 'smallavarapu' || $_SERVER['PHP_AUTH_USER'] == 'treichert' || $_SERVER['PHP_AUTH_USER'] == 'kblindt') {
					$sDateEnteredField = "<tr><td>Date Entered</td>
						<td colspan=3><input type=text name='sDateEntered' value='$sDateEntered' readonly></td>
						</tr>	";
				} else {
					$sDateEnteredField = "<tr><td>Date Entered</td>
						<td colspan=3><input type=text name='sDateEntered' value='$sDateEntered'></td>
						</tr>	";
				}

				$sCompletionDate = $oRow->completionDate;
				$sStatus = $oRow->status;
			}

			dbFreeResult($rResult);
		} else {
			echo dbError();
		}
	} else {
		$sComments = ascii_encode(stripslashes($oRow->comments));
		// If add button is clicked, display another two buttons
		$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
	}

	$sNewCobrandSelected = "";
	$sNewOfferSelected = "";
	$sChangeOfferSelected = "";
	$sChangeCobrandSelected = "";
	$sOtherSelected = "";
	$sNewCampSelected = '';
	$sCurrCampSelected = '';

	switch ($sRequestType) {
		case "New Co-Brand":
		$sNewCobrandSelected = "selected";
		break;
		case "New Offer":
		$sNewOfferSelected = "selected";
		break;
		case "Changes To Existing Offer";
		$sChangeOfferSelected = "Selected";
		break;
		case "Changes to Existing Co-Brand":
		$sChangeCobrandSelected = "selected";
		break;
		case "New Campaign":
		$sNewCampSelected = "selected";
		break;
		case "Changes To Existing Campaign":
		$sCurrCampSelected = "selected";
		break;
		default:
		$sOtherSelected = "selected";
		break;
	}


	$sRequestTypeOptions = "<option value='Changes to Existing Co-Brand' $sChangeCobrandSelected>Changes to Existing Co-Brand
						  <option value='Changes To Existing Offer' $sChangeOfferSelected>Changes To Existing Offer
					  <option value='Changes To Existing Campaign' $sCurrCampSelected>Changes To Existing Campaign
					  <option value='New Co-Brand' $sNewCobrandSelected>New Co-Brand
					  	<option value='New Offer' $sNewOfferSelected>New Offer
					  	<option value='New Campaign' $sNewCampSelected>New Campaign
					  	<option value='Other' $sOtherSelected>Other";


	$sNewRequestSelected = "";
	$sScheduledSelected = "";
	$sAwaitingSelected = "";
	$sUnknownScheduleSelected = '';
	$sCompletedSelected = "";
	$sDeletedSelected = '';
	
	if ($sStatus == '') {
		$sNewRequestSelected = "selected";
	}

	switch ($sStatus) {

		case "newRequest":
		$sNewRequestSelected = "selected";
		break;
		case "scheduled":
		$sScheduledSelected = "selected";
		break;
		case "awaitingApproval";
		$sAwaitingApprovalSelected = "selected";
		break;
		case "unknownSchedule";
		$sUnknownScheduleSelected = "selected";
		break;
		case "completed":
		$sCompletedSelected = "selected";
		break;
		case "deleted":
		$sDeletedSelected = "selected";
		break;
	}
	
	if ($_SERVER['PHP_AUTH_USER'] == 'athomashow' || $_SERVER['PHP_AUTH_USER'] == 'cwright' || $_SERVER['PHP_AUTH_USER'] == 'jyacob' || $_SERVER['PHP_AUTH_USER'] == 'kkousins' || $_SERVER['PHP_AUTH_USER'] == 'larrym' || $_SERVER['PHP_AUTH_USER'] == 'treichert' || $_SERVER['PHP_AUTH_USER'] == 'kblindt') {
		$sStatusOptions = "<option value='completed' $sCompletedSelected>Completed
						<option value='deleted' $sDeletedSelected onClick=alert('Are&nbsp;you&nbsp;sure&nbsp;you&nbsp;want&nbsp;to&nbsp;delete&nbsp;this&nbsp;offer&nbsp;from&nbsp;the&nbsp;Production&nbsp;Sheet?');>Deleted
						<option value='newRequest' $sNewRequestSelected>New Request
					    <option value='$sStatus' $sUpdateNotesSelected selected>Update Notes";
	} else {
		$sStatusOptions = "<option value='awaitingApproval' $sAwaitingApprovalSelected>Awaiting Approval
						<option value='completed' $sCompletedSelected>Completed
						<option value='deleted' $sDeletedSelected>Deleted
						<option value='newRequest' $sNewRequestSelected>New Request
					   	<option value='scheduled' $sScheduledSelected>Scheduled
					    <option value='unknownSchedule' $sUnknownScheduleSelected>Unknown Schedule";
	}


	$sRepQuery = "SELECT *
				 FROM   nbUsers
				 ORDER BY firstName";

	$rRepResult = dbQuery($sRepQuery);
	echo dbError();

	while ($oRepRow = dbFetchObject($rRepResult)) {
		if (strtolower($sOwner) == strtolower($oRepRow->userName)) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}


		$sOwnerOptions .= "<option value='".$oRepRow->userName."' $sSelected>$oRepRow->userName";

	}


	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";

	include("../../includes/adminAddHeader.php");

?>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post enctype=multipart/form-data>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>


<?php
if ($_SERVER['PHP_AUTH_USER'] == 'athomashow' || $_SERVER['PHP_AUTH_USER'] == 'cwright' || $_SERVER['PHP_AUTH_USER'] == 'greg' || $_SERVER['PHP_AUTH_USER'] == 'kkousins' || $_SERVER['PHP_AUTH_USER'] == 'larrym' || $_SERVER['PHP_AUTH_USER'] == 'treichert') {
?>

	<tr><td>Priority</td>
		<td colspan=3><input type=text name='iPriority' value="<?php echo $iPriority;?>" readonly></td>
	</tr>
	
	<tr><td>Request</td>
		<td colspan=3><input type=text name='sRequest' value="<?php echo $sRequest;?>" readonly></td>
	</tr>
	<?php echo $sDateEnteredField;?>
	<tr><td>Owner</td>
		<td colspan=3><select name=deadOwner disabled>
					<?php echo $sOwnerOptions;?>
					</select><input type="hidden" name="sOwner" value='<?php echo $sOwner;?>'></td>
	</tr>
	<tr><td>Request Type</td>
		<td colspan=3><select name='deadRequestType' disabled>
			<?php echo $sRequestTypeOptions;?>
			</select><input type="hidden" name="sRequestType" value='<?php echo $sRequestType;?>'></td>
	</tr>
	<tr><td>Offer Page</td>
		<td colspan=3><input type=text name='sOfferPage' value="<?php echo $sOfferPage;?>" readonly></td>
	</tr>

	<tr><td>Comments<br>&nbsp;<font color=red>editable</font></td>
		<td colspan=3><textarea name='sComments'  rows=10 cols=50><?php echo $sComments;?></textarea></td>
	</tr>

	<tr><td>Estimate Date</td>
		<td colspan=3><input type=text name='sEstimateDate' value='<?php echo $sEstimateDate;?>' readonly>
		</td>
	</tr>
	
	<tr><td>Status</td>
		<td colspan=3><select name=sStatus>
			<?php echo $sStatusOptions;?>
		</select>&nbsp;<font color=red>editable</font></td>
	</tr>
	
	<tr><td>Est Hours</td>
		<td colspan=3><input type=text name='iHours' value="<?php echo $iHours;?>" maxlength="3" size="3" readonly></td>
	</tr>
	
<?php
} else {
?>
	<tr><td>Priority</td>
		<td colspan=3><input type=text name='iPriority' value="<?php echo $iPriority;?>"></td>
	</tr>
	
	<tr><td>Request</td>
		<td colspan=3><input type=text name='sRequest' value="<?php echo $sRequest;?>"></td>
	</tr>
	<?php echo $sDateEnteredField;?>
	<tr><td>Owner</td>
		<td colspan=3><select name=sOwner>
					<?php echo $sOwnerOptions;?>
					</select></td>
	</tr>	
	<tr><td>Request Type</td>
		<td colspan=3><select name='sRequestType'>
			<?php echo $sRequestTypeOptions;?>
			</select></td>
	</tr>
	<tr><td>Offer Page</td>
		<td colspan=3><input type=text name='sOfferPage' value="<?php echo $sOfferPage;?>"></td>
	</tr>

	<tr><td>Comments</td>
		<td colspan=3><textarea name='sComments' rows=10 cols=50><?php echo $sComments;?></textarea></td>
	</tr>

	<tr><td>Estimate Date</td>
		<td colspan=3><input type=text name='sEstimateDate' value='<?php echo $sEstimateDate;?>'>
		</td>
	</tr>	
	<tr><td>Completion Date</td>
		<td colspan=3><input type=text name='sCompletionDate' value='<?php echo $sCompletionDate;?>'></td>
	</tr>	
	<tr><td>Status</td>
		<td colspan=3><select name=sStatus>
			<?php echo $sStatusOptions;?>
		</select></td>
	</tr>
	
	<tr><td>Est Hours</td>
		<td colspan=3><input type=text name='iHours' value="<?php echo $iHours;?>" maxlength="3" size="3"></td>
	</tr>
	

<?php
}
?>

</table>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td colspan=2 align=center>
		<input type=submit name=sSaveContinue value='Save & Continue'> &nbsp; &nbsp; 
		</td><td></td>
	</tr>
	</table>
<?php
include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>