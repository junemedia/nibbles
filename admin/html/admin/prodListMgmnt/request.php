<?php
//Script to Add/Edit Ot Page

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
include("$sGblLibsPath/urlFunctions.php");
include("$sGblLibsPath/dateFunctions.php");

$sToday = date('Y')."-".date('m')."-".date('d');
$sTomorrow = DateAdd("d", 1, date('Y')."-".date('m')."-".date('d'));
session_start();
$sPageTitle = "Nibbles Production List - Add/Edit Request In Production List";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($sSaveClose || $sSaveNew || $sSaveContinue) {
		// When New Record Submitted
		if (!($iId)) {
			if ($whichSRequest==1) {
				$sRequest = $sRequest1;	
			} else {
				$sRequest = $sRequest2;	
			}
			
			// Check if offer already exists...
			$sCheckQuery = "SELECT *
					   FROM   productionList
					   WHERE  request = '$sRequest'"; 
			$rCheckResult = dbQuery($sCheckQuery);
				
			if (dbNumRows($rCheckResult) == 0) {
				
				// get preceding order items and calculate time as per
				// cobrands 2 hrs
				// new offer 3 hrs
				// changes to existing offers 1 hr
				if ($sRequestType == 'New Offer') {
					$iHours = 3;
				} else if ($sRequestType == 'New Co-Brand') {
					$iHours = 2;
				} else if ($sRequestType == 'Changes To Existing Offer') {
					$iHours = 1;
				} else if ($sRequestType == 'Changes to Existing Co-Brand') {
					$iHours = 1;
				} else {
					$iHours = 1;
				}

				// Insert record if everything is fine
				$sComments = addslashes($sComments);
				$sAddQuery = "INSERT INTO productionList(request, dateEntered, owner, requestType, 
									offerPage, comments, status, hours)
							  VALUES(\"$sRequest\", CURRENT_DATE, \"$sOwner\", \"$sRequestType\", 
									\"$sOfferPage\", \"$sComments\", \"newRequest\", \"$iHours\")";
	
				// start of track users' activity in nibbles 
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: " . addslashes($sAddQuery) . "\")"; 
				$rLogResult = dbQuery($sLogAddQuery); 
				echo  dbError(); 
				// end of track users' activity in nibbles		

				$rResult = dbQuery($sAddQuery);	
			} else {
				while ($oCurrRow = dbFetchObject($rCheckResult)) {
					$sMessage = "Request Already Exists...Click here to <a href='JavaScript:void(window.open(\"addItem.php?iMenuId=$iMenuId&iId=".$oCurrRow->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));self.close();'>Edit the request.</a>";
				}
				$bKeepValues = true;
			}
				
			if ( $rResult ) {
				// send priority changed email to jr, josh
				$sEmailMessage = "New production request added by $sOwner";
				$sHeaders = "From:nibbles@amperemedia.com\r\n";
				$sSubject = "Production sheet request";
				mail("spatel@amperemedia.com", $sSubject, $sEmailMessage, $sHeaders);
			} else {
				echo dbError();
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
				$sRequest = "";
			}
		}
	}
	

	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
		
	$sNewCobrandSelected = "";
	$sNewOfferSelected = "";
	$sChangeOfferSelected = "";
	$sChangeCobrandSelected = "";	
	$sOtherSelected = "";
	$sNewCampSelected = '';
	$sCurrCampSelected = '';
	
	switch ($sRequestType) {
		case "New Campaign":
		$sNewCampSelected = "selected";
		break;
		case "Changes To Existing Campaign":
		$sCurrCampSelected = "selected";
		break;
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
		default:
		$sOtherSelected = "selected";
		break;
	}

	$sRequestTypeOptions = "<option value='Changes to Existing Co-Brand' $sChangeCobrandSelected>Changes to Existing Co-Brand
						  <option value='Changes To Existing Offer' $sChangeOfferSelected>Changes To Existing Offer
						  <option value='New Co-Brand' $sNewCobrandSelected>New Co-Brand
					  	<option value='New Offer' $sNewOfferSelected>New Offer
					  	<option value='Other' $sOtherSelected>Other
					  	<option value='New Campaign' $sNewCampSelected>New Campaign
					  	<option value='Changes To Existing Campaign' $sCurrCampSelected>Changes To Existing Campaign";
		

	$sRepQuery = "SELECT * FROM   nbUsers ORDER BY firstName";
	$rRepResult = dbQuery($sRepQuery);
	echo dbError();
	while ($oRepRow = dbFetchObject($rRepResult)) {
		if ((strtolower($sOwner) == strtolower($oRepRow->userName)) || (strtolower($sTrackingUser) == strtolower($oRepRow->userName))) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		$sOwnerOptions .= "<option value='".$oRepRow->userName."' $sSelected>$oRepRow->userName";
	}
	

	$sOfferCodeQuery = "SELECT offerCode
		 FROM   offers
		 ORDER BY offerCode";
	$rOfferCodeResult = dbQuery($sOfferCodeQuery);
	echo dbError();
	while ($oRepRow = dbFetchObject($rOfferCodeResult)) {
		if (strtolower($sOfferCode) == strtolower($oRepRow->offerCode)) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		$sOfferCodeOptions .= "<option value='".$oRepRow->offerCode."' $sSelected>$oRepRow->offerCode";
	}
			
			
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iId value='$iId'>";
	
	include("../../includes/adminAddHeader.php");
	$sComments = stripslashes($sComments);
?>
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post enctype=multipart/form-data>
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<script>
function test() {
	if ((document.form1.sRequestType.options[3].selected) || (document.form1.sRequestType.options[1].selected)) {
		document.form1.sRequest1.disabled=false;
		document.form1.whichSRequest.value=1;
		document.form1.sRequest2.disabled=true;
	} else {
		document.form1.sRequest1.disabled=true;
		document.form1.sRequest2.disabled=false;
		document.form1.whichSRequest.value=2;
	}
}
</script>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td>Request Type</td>
		<td colspan=3><select id="sRequestType" name='sRequestType' onchange="test();">
			<?php echo $sRequestTypeOptions;?>
			</select></td>
	</tr>

	<tr><td>Request</td>
			<td><select id="sRequest1" name='sRequest1' disabled>
			<option value='' selected></option>
			<?php echo $sOfferCodeOptions;?>
			</select><font color="red">&nbsp;&nbsp;For Changes To Existing Offer and New Offer</font></td>
	</tr>

	<tr><td>Request</td>
		<td colspan=2>
		<input type=text id="sRequest2" name='sRequest2' value='<?php echo $sRequest;?>'>
	<font color="red">&nbsp;&nbsp;For Changes to Existing Co-Brand, New Co-Brand, New Campaign, 
	Changes To Existing Campaign, and Other</font>
		</td></tr>								
	<input type="hidden" name="whichSRequest" id ="whichSRequest">
	<tr><td>Owner</td>
		<td colspan=3><select name=sOwner>
					<?php echo $sOwnerOptions;?>
					</select></td>
	</tr>

	<tr><td>Offer Page</td>
		<td colspan=3><input type=text name='sOfferPage' value="<?php echo $sOfferPage;?>"></td>
	</tr>
	
	<tr><td>Comments</td>
		<td colspan=3><textarea name='sComments'  rows=10 cols=50><?php echo $sComments;?></textarea></td>
	</tr>
		
</table>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD colspan=2 align=center >
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