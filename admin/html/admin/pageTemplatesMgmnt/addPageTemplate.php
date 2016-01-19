<?php

include("../../includes/paths.php");
session_start();

$sPageTitle = "Nibbles - Page Template Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	if (($sSaveClose || $sSaveNew) && !($id)) {
		if ($sTemplateContent != '' && $sTemplateName !='' && $sTemplateType !='') {
			// check if already exists
			$sCheckQuery = "SELECT *
							FROM   pageTemplates
							WHERE  templateName = \"$sTemplateName\"";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult) > 0) {
				$sMessage = "Template Name Already Exists...";
				$bKeepValues = true;
			} else {
				$sTemplateContent = addslashes($sTemplateContent);
				$sAddQuery = "INSERT INTO pageTemplates (templateName,templateType,templateContent, oneOfferReq, eachOfferReq) 
								VALUES(\"$sTemplateName\", '$sTemplateType', \"$sTemplateContent\",
								'$sAtleastOneOfferRequired','$sEachOfferRequired')";
				$rAddResult = dbQuery($sAddQuery);
				
				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sAddQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles	
			}
		} else {
			$sMessage = "Template Name / Page Template / Template Type Required...";
			$bKeepValues = true;
		}
	} elseif (($sSaveClose || $sSaveNew) && ($id)) {
		if ($sTemplateContent != '' && $sTemplateName !='' && $sTemplateType !='') {
			$sCheckQuery = "SELECT *
							FROM   pageTemplates
							WHERE  templateName = \"$sTemplateName\"
							AND id !='$id'";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult) > 0) {
				$sMessage = "Template Name Already Exists...";
				$bKeepValues = true;
			} else {
				$sTemplateContent = addslashes($sTemplateContent);
				$editQuery = "UPDATE pageTemplates 
							SET templateName = \"$sTemplateName\",
							templateContent = \"$sTemplateContent\",
							templateType = '$sTemplateType',
							oneOfferReq = '$sAtleastOneOfferRequired',
							eachOfferReq = '$sEachOfferRequired'
							WHERE  id = '$id'";
				$result = mysql_query($editQuery);
				
				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($editQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles	
			}
		} else {
			$sMessage = "Template Name / Page Template / Template Type Required...";
			$bKeepValues = true;
		}
	}
	
	if ($sSaveClose) {
		if ($bKeepValues != true) {
			$sTemplateName = '';
			$sTemplateContent = '';
			$sAtleastOneOfferRequired = '';
			$sEachOfferRequired = '';
			$sTemplateType = '';
			$id = '';
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
			$sTemplateName = '';
			$sTemplateContent = '';
			$sTemplateType = '';
			$sAtleastOneOfferRequired = '';
			$sEachOfferRequired = '';
			$id = '';
		}
	}
	
	if ($id != '') {
		$selectQuery = "SELECT * FROM   pageTemplates WHERE  id = '$id'";
		$result = mysql_query($selectQuery);
		while ($row = mysql_fetch_object($result)) {
			$sTemplateName = $row->templateName;
			$sTemplateContent = $row->templateContent;
			$sAtleastOneOfferRequired = $row->oneOfferReq;
			$sEachOfferRequired = $row->eachOfferReq;
			$sTemplateType = $row->templateType;
		}
	}
	
	$sBPSelected = '';
	$sEPSelected = '';
	$sRPSelected = '';
	$sFRPSelected = '';
	$sOPSelected = '';
	$sSPNSSelected = '';
	$sSPSSelected = '';
	$sPPSelected = '';
	$sNoneSelected = '';

	switch ($sTemplateType) {
		case "BP":
		$sBPSelected = "selected";
		break;
		case "EP":
		$sEPSelected = "selected";
		break;
		case "RP":
		$sRPSelected = "selected";
		break;
		case "FRP":
		$sFRPSelected = "selected";
		break;
		case "OP":
		$sOPSelected = "selected";
		break;
		case "SPNS":
		$sSPNSSelected = "selected";
		break;
		case "SPS":
		$sSPSSelected = "selected";
		break;
		case "PP":
		$sPPSelected = "selected";
		default:
		$sNoneSelected = "selected";
	}

	$sTemplateTypeOptions = "<option value='' $sNoneSelected>
			<option value='BP' $sBPSelected>B Pages
			<option value='EP' $sEPSelected>Email Capture
			<option value='RP' $sRPSelected>Registration Page
			<option value='FRP' $sFRPSelected>Full Registration Page
			<option value='OP' $sOPSelected>Open Page (Open They Host / Open We Host)
			<option value='SPNS' $sSPNSSelected>Standard Page Non-Stacked
			<option value='SPS' $sSPSSelected>Standard Page Stacked
			<option value='PP' $sPPSelected>Partner Page";

	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=id value='$id'>";
	
	if ($sAtleastOneOfferRequired == '') {
		$sAtleastOneOfferRequired = 'Y';
	}
	
	if ($sEachOfferRequired == '') {
		$sEachOfferRequired = 'Y';
	}
	
	include("../../includes/adminAddHeader.php");
	?>
	
	<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
	<?php echo $sHidden;?>
	<?php echo $sReloadWindowOpener;?>
	<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		<tr><td width=35%>Template Name: </td>
			<td><input type="text" name=sTemplateName maxlength="255" size='50' value="<?php echo $sTemplateName; ?>">
			</td>
		</tr>
		
		<tr>
		<td>Template Type</td>
		<td><select name="sTemplateType">
			<?php echo $sTemplateTypeOptions; ?>
		</select>
		</td>
		</tr>
		
		
		<tr><td width=35%>Atleast 1 Offer Required: </td>
		<td><input type="radio" name='sAtleastOneOfferRequired' value="Y" <?php if ($sAtleastOneOfferRequired=='Y') { echo 'checked'; } ?> > Yes
			&nbsp;&nbsp;&nbsp;
			<input type="radio" name='sAtleastOneOfferRequired' value="N" <?php if ($sAtleastOneOfferRequired=='N') { echo 'checked'; } ?> > No
		</td>
		</tr>
		
		<tr><td width=35%>Each Offer Required: </td>
		<td><input type="radio" name='sEachOfferRequired' value="Y" <?php if ($sEachOfferRequired=='Y') { echo 'checked'; } ?> > Yes
			&nbsp;&nbsp;&nbsp;
			<input type="radio" name='sEachOfferRequired' value="N" <?php if ($sEachOfferRequired=='N') { echo 'checked'; } ?> > No
			&nbsp;&nbsp;&nbsp;(For yes/no offers only.)
			</td>
		</tr>
	
		<tr><td width=35%>Page Template: </td>
		<td><textarea name=sTemplateContent rows=20 cols=80><?php echo $sTemplateContent;?></textarea></td>
		</tr>
		
		
	</table>	
		
	<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>