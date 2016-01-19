<?php

include("../../includes/paths.php");
session_start();

$sPageTitle = "Nibbles - Campaigns Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($sSaveClose || $sSaveNew || $sSaveContinue) {
		$sMessage = '';
		if ($sCampaignName == '') {
			$sMessage = "Campaign Name Required...";
			$bKeepValues = true;
		} else if (($aEmailCaptureContent == array()) || !($aEmailCaptureContent) || (count($aEmailCaptureContent) == 0)){
			$sMessage = "Email Capture Content Required...";
			$bKeepValues = true;
		} elseif ($sRegPageContent == '') {
			$sMessage = "Reg Page Content Required...";
			$bKeepValues = true;
		} elseif ($sFullRegPageContent == '') {
			$sMessage = "Full Reg Page Content Required...";
			$bKeepValues = true;
		}
		
		if ($sMessage == '') {
			$sEmailCaptureContent = addslashes($sEmailCaptureContent);
			$sRegPageContent = addslashes($sRegPageContent);
			$sFullRegPageContent = addslashes($sFullRegPageContent);
			$sDescription = addslashes($sDescription);
			$sNotes = addslashes($sNotes);
			$sInLineCss = addslashes($sInLineCss);

		
			if (!($id)) {
				// check if already exists
				$sCheckQuery = "SELECT *
								FROM   campaigns
								WHERE  campaignName = \"$sCampaignName\"";
				$rCheckResult = dbQuery($sCheckQuery);
				if (dbNumRows($rCheckResult) > 0) {
					$sMessage = "Campaign Name Already Exists...";
					$bKeepValues = true;
				} else {
					// $sShowSkipSubmitCth - the possible values are:  0 for skip, 1 for submit, and 2 for both skip and submit
					$sAddQuery = "INSERT INTO campaigns (campaignName,ePage,regPage,
									fullRegPage,dateTimeCreated,description,notes,showSkipSubmitCth,inLineCSS) 
									VALUES(\"$sCampaignName\",\"$sEmailCaptureContent\", \"$sRegPageContent\",
									\"$sFullRegPageContent\",NOW(),\"$sDescription\",\"$sNotes\",'$sShowSkipSubmitCth',\"$sInLineCss\")";
					$rAddResult = dbQuery($sAddQuery);
					$id = mysql_insert_id();
					
					// start of track users' activity in nibbles
					$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sAddQuery) . "\")";
					$rLogResult = dbQuery($sLogAddQuery);
					// end of track users' activity in nibbles
					
	
					$sInsertSQL = "INSERT INTO campaignsEmailCreative (campaignId, creativeId) values ";
					$aCreatives = array();
					foreach ($aEmailCaptureContent as $index => $iEmailCapId) {
						array_push($aCreatives, "('$id','$iEmailCapId')");
					}
					$sInsertSQL .= join(',',$aCreatives);
					$res = dbQuery($sInsertSQL);
				}
			} else {
				$sCheckQuery = "SELECT * FROM campaigns
								WHERE  campaignName = \"$sCampaignName\"
								AND id !='$id'";
				$rCheckResult = dbQuery($sCheckQuery);
				if (dbNumRows($rCheckResult) > 0) {
					$sMessage = "Campaign Name Already Exists...";
					$bKeepValues = true;
				} else {
					// $sShowSkipSubmitCth - the possible values are:  0 for skip, 1 for submit, and 2 for both skip and submit
					$editQuery = "UPDATE campaigns 
									SET campaignName = \"$sCampaignName\",
									regPage = \"$sRegPageContent\",
									fullRegPage = \"$sFullRegPageContent\",
									description = \"$sDescription\",
									notes = \"$sNotes\",
									showSkipSubmitCth = '$sShowSkipSubmitCth',
									inLineCSS = \"$sInLineCss\"
								WHERE  id = '$id'";
					$result = mysql_query($editQuery);
	
					// start of track users' activity in nibbles
					$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($editQuery) . "\")";
					$rLogResult = dbQuery($sLogAddQuery);
					// end of track users' activity in nibbles
				}
			
				
				$sDeleteSQL = "DELETE FROM campaignsEmailCreative WHERE campaignId = '$id'";
				$res = dbQuery($sDeleteSQL);
				
				$sInsertSQL = "INSERT INTO campaignsEmailCreative (campaignId, creativeId) values ";
				$aCreatives = array();
				foreach($aEmailCaptureContent as $index => $iEmailCapId){
					array_push($aCreatives, "('$id','$iEmailCapId')");
				}
				$sInsertSQL .= join(',',$aCreatives);
				$res = dbQuery($sInsertSQL);
			}
		}
	}
	
	if ($sSaveClose) {
		if ($bKeepValues != true) {
			$id = '';
			$sCampaignName = '';
			$sEmailCaptureContent = '';
			$sRegPageContent = '';
			$sFullRegPageContent = '';
			$sDescription = '';
			$sNotes = '';
			echo "<script language=JavaScript>
				window.opener.location.reload();
				self.close();
				</script>";			
			exit();
		}
	} else if ($sSaveNew) {
		if ($bKeepValues != true) {
			$sReloadWindowOpener = "<script language=JavaScript>
						window.opener.location.reload();
						</script>";			
			$id = '';
			$sCampaignName = '';
			$sEmailCaptureContent = '';
			$sRegPageContent = '';
			$sFullRegPageContent = '';
			$sDescription = '';
			$sNotes = '';
		}
	}
	
	// $sShowSkipSubmitCth - the possible values are:  0 for skip, 1 for submit, and 2 for both skip and submit
	$sShowSkipSubmitCth = 0;
	if ($id != '') {
		$selectQuery = "SELECT * FROM campaigns WHERE  id = '$id'";
		$result = mysql_query($selectQuery);
		while ($row = mysql_fetch_object($result)) {
			$sCampaignName = $row->campaignName;
			$sEmailCaptureContent = $row->ePage;
			$sRegPageContent = $row->regPage;
			$sFullRegPageContent = $row->fullRegPage;
			$sDescription = $row->description;
			$sNotes = $row->notes;
			$sShowSkipSubmitCth = $row->showSkipSubmitCth;
			$sInLineCss = $row->inLineCSS;
		}
	}

	$contentQuery = "SELECT emailCapCreative.id as eId, emailCapCreative.*, campaignsEmailCreative.campaignId,  campaignsEmailCreative.creativeId FROM emailCapCreative LEFT JOIN campaignsEmailCreative ON (campaignsEmailCreative.creativeId = emailCapCreative.id AND campaignsEmailCreative.campaignId = '$id') order by emailCapCreative.name asc";
	$contentResult = mysql_query($contentQuery);
	$sEmailCaptureContentOptions = "<option value=''></option>";
	while ($row = mysql_fetch_object($contentResult)) {
		$sEmailCaptureContentOptions .= "<option value='".$row->eId."' ".($row->creativeId == $row->eId ? ' selected' : '').">".$row->name."</option>";
			
	}

	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=id value='$id'>";
	
	include("../../includes/adminAddHeader.php");
	?>
	
	<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
	<?php echo $sHidden;?>
	<?php echo $sReloadWindowOpener;?>
	<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><td width=35%>Campaign Name: </td>
			<td><input type="text" name=sCampaignName maxlength="255" size='50' value="<?php echo $sCampaignName; ?>">
			</td>
		</tr>
		
	
		<tr><td width=35%>Email Capture Form: </td>
		<td><select name='aEmailCaptureContent[]' multiple size=15><?php echo $sEmailCaptureContentOptions;?></select></td>
		<!--<td><textarea name=sEmailCaptureContent rows=5 cols=50><?php //echo $sEmailCaptureContent;?></textarea></td>-->
		</tr>
		
		<tr><td width=35%>Reg Form: </td>
		<td><textarea name=sRegPageContent rows=5 cols=50><?php echo $sRegPageContent;?></textarea></td>
		</tr>

		<tr><td width=35%>Full Reg Form: </td>
		<td><textarea name=sFullRegPageContent rows=5 cols=50><?php echo $sFullRegPageContent;?></textarea></td>
		</tr>

		<tr><td width=35%>Description: </td>
		<td><textarea name=sDescription rows=3 cols=50><?php echo $sDescription;?></textarea></td>
		</tr>

		<tr><td width=35%>Notes: </td>
		<td><textarea name=sNotes rows=3 cols=50><?php echo $sNotes;?></textarea></td>
		</tr>
		
		
		<tr><td width=35%>Inline CSS: </td>
		<td><textarea name=sInLineCss rows=3 cols=50><?php echo $sInLineCss;?></textarea></td>
		</tr>
		
		
		<!-- $sShowSkipSubmitCth - the possible values are:  0 for skip, 1 for submit, and 2 for both skip and submit -->
		<tr><td width=35%>Show Skip/Submit Button (Close They Host): </td>
		<td>
			<input type="radio" name="sShowSkipSubmitCth" value="0" <?php if($sShowSkipSubmitCth==0) { echo 'checked'; } ?>> Skip Only
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="sShowSkipSubmitCth" value="1" <?php if($sShowSkipSubmitCth==1) { echo 'checked'; } ?>> Submit Only
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="radio" name="sShowSkipSubmitCth" value="2" <?php if($sShowSkipSubmitCth==2) { echo 'checked'; } ?>> Both
		</td>
		</tr>
		
		
		<tr><td width=35%><b>Notes:</b></td>
		<td>The functionality of both buttons work as a "skip" button. 
			Showing both or either one on the offer is for aesthetic purposes only.
		</td>
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