<?php

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles - Sites Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	if ($sSaveClose || $sSaveNew) {
		$sMessage = '';
		if ($sSiteName == '') {
			$sMessage = "Site Name Required...";
			$bKeepValues = true;
		} elseif ($sSuppSite == '') {
			$sMessage = "Suppresion Site Required...";
			$bKeepValues = true;
		} elseif ($iPrivacyPolicyId == '') {
			$sMessage = "Privacy Policy Required...";
			$bKeepValues = true;
		} elseif ($iTermsConditions == '') {
			$sMessage = "Terms And Conditions Required...";
			$bKeepValues = true;
		} elseif ($iDomainId == '') {
			$sMessage = "Domain Required...";
			$bKeepValues = true;
		}
		
		if (!($id)) {
			$sCheckQuery = "SELECT * FROM sites
							WHERE  siteName = \"$sSiteName\"";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult) > 0) {
				$sMessage = "Site Name Already Exists...";
				$bKeepValues = true;
			}
		} else {
			$sCheckQuery = "SELECT * FROM sites
							WHERE  siteName = \"$sSiteName\"
							AND id !='$id'";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult) > 0) {
				$sMessage = "Site Name Already Exists...";
				$bKeepValues = true;
			}
		}
		
		if ($sMessage == '') {
			if (!($id)) {
				$sAddQuery = "INSERT INTO sites (siteName,suppressionSite,privacyPolicyId,termsConditionsId,domainId) 
						VALUES(\"$sSiteName\",\"$sSuppSite\",'$iPrivacyPolicyId','$iTermsConditions', '$iDomainId')";
				$rAddResult = dbQuery($sAddQuery);

				if ($rAddResult) {
					$sGetLastInsertId = "SELECT id FROM sites
								WHERE siteName = \"$sSiteName\"
								AND suppressionSite = \"$sSuppSite\"
								AND privacyPolicyId = '$iPrivacyPolicyId'
								AND termsConditionsId = '$iTermsConditions'
								AND domainId = '$iDomainId'";
					$rGetLastInsertId = dbQuery($sGetLastInsertId);
					while ($oLastIdRow = mysql_fetch_object($rGetLastInsertId)) {
						$iInsertedId = $oLastIdRow->id;
					}
				}
			} elseif ($id) {
				$sAddQuery = "UPDATE sites 
							SET siteName = \"$sSiteName\",
							suppressionSite = \"$sSuppSite\",
							privacyPolicyId = '$iPrivacyPolicyId',
							termsConditionsId = '$iTermsConditions',
							domainId = '$iDomainId'
							WHERE  id = '$id'";
				$result = mysql_query($sAddQuery);
			}

			// start of track users' activity in nibbles
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sAddQuery) . "\")";
			$rLogResult = dbQuery($sLogAddQuery);
			// end of track users' activity in nibbles

			
			if ($_FILES['userfile']['tmp_name'] && $_FILES['userfile']['tmp_name']!="none") {
				if ($id) { $iInsertedId = $id; }
				$aImageFileNameArray = explode(".",$_FILES['userfile']['name']);
				$i = count($aImageFileNameArray) - 1;
				$sImageFileName = $iInsertedId.".".$aImageFileNameArray[$i];
				move_uploaded_file( $_FILES['userfile']['tmp_name'], "/home/sites/admin.popularliving.com/html/nibbles2/flowHeader/$sImageFileName");
				exec("cp /home/sites/admin.popularliving.com/html/nibbles2/flowHeader/$sImageFileName /home/sites/popularliving/html/nibbles2/flowHeader/");
				$sUpdateQuery = "UPDATE sites
						SET    header = '$sImageFileName'
						WHERE  id = '$iInsertedId'";
				$rUpdateResult = dbQuery($sUpdateQuery);
				
				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sUpdateQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
			}
		}
	}
	
	if ($sSaveClose) {
		if ($bKeepValues != true) {
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
			$sSiteName = '';
			$sHeaderFile = '';
			$sSuppSite = '';
			$iPrivacyPolicyId = '';
			$iTermsConditions = '';
			$iDomainId = '';
			$sDisplayImage = '';
			$id = '';
		}
	}
	
	if ($id != '') {
		$selectQuery = "SELECT * FROM   sites WHERE  id = '$id'";
		$result = mysql_query($selectQuery);
		while ($row = mysql_fetch_object($result)) {
			$sSiteName = $row->siteName;
			$sSuppSite = $row->suppressionSite;
			$iPrivacyPolicyId = $row->privacyPolicyId;
			$iTermsConditions = $row->termsConditionsId;
			$iDomainId = $row->domainId;
			
			if ($row->header !='') {
				$sDisplayImage = "<img src='http://web1.popularliving.com/nibbles2/flowHeader/$row->header'>";
			} else {
				$sDisplayImage = "Using Default Image";
			}
		}
	}

	
	$sGetSuppSite = "SELECT distinct site 
					FROM suppressionList 
					WHERE site !=''
					ORDER BY site ASC";
	$rGetSuppSite = mysql_query($sGetSuppSite);
	$sSuppSiteOptions = "<option value=''>";
	while ($sTempRow = mysql_fetch_object($rGetSuppSite)) {
		if ($sTempRow->site == $sSuppSite) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		$sSuppSiteOptions .= "<option value='$sTempRow->site' $sSelected>$sTempRow->site";
	}
	
	

	$sGetPrivacy = "SELECT id,name FROM privacyPolicy ORDER BY name ASC";
	$rGetPrivacy = mysql_query($sGetPrivacy);
	$sPrivacyOptions = "<option value=''>";
	while ($sPpRow = mysql_fetch_object($rGetPrivacy)) {
		if ($sPpRow->id == $iPrivacyPolicyId) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		$sPrivacyOptions .= "<option value='$sPpRow->id' $sSelected>$sPpRow->name";
	}
	
	
	$sGetTandC = "SELECT id,name FROM termsConditions ORDER BY name ASC";
	$rGetTandC = mysql_query($sGetTandC);
	$sTermsConditionOptions = "<option value=''>";
	while ($sTcRow = mysql_fetch_object($rGetTandC)) {
		if ($sTcRow->id == $iTermsConditions) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		$sTermsConditionOptions .= "<option value='$sTcRow->id' $sSelected>$sTcRow->name";
	}
	

	$sGetDomain = "SELECT id,domainName FROM domains ORDER BY domainName ASC";
	$rGetDomain = mysql_query($sGetDomain);
	$sDomainOptions = "<option value=''>";
	while ($sDomainRow = mysql_fetch_object($rGetDomain)) {
		if ($sDomainRow->id == $iDomainId) {
			$sSelected = "selected";
		} else {
			$sSelected = "";
		}
		$sDomainOptions .= "<option value='$sDomainRow->id' $sSelected>$sDomainRow->domainName";
	}
	
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=id value='$id'>";
	
include("../../includes/adminAddHeader.php");
?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post enctype="multipart/form-data">
<?php echo $sHidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

	<tr><td>Site Name</td>
	<td>
		<input type="text" name="sSiteName" value="<?php echo $sSiteName; ?>" size=50>
	</td></tr>


	<tr><td>Header Image</td>
		<td colspan=2><input type=file name=userfile>
		<br><?php echo $sDisplayImage; ?>
		</td>
	</tr>
		
	
	<tr><td>Suppresion Site</td>
		<td><select name='sSuppSite'>
		<?php echo $sSuppSiteOptions;?>
		</select>
	</td></tr>
	
	
	<tr><td>Privacy Policy</td>
		<td><select name='iPrivacyPolicyId'>
		<?php echo $sPrivacyOptions;?>
		</select>
	</td></tr>
	
	
	<tr><td>Terms & Conditions</td>
		<td><select name='iTermsConditions'>
		<?php echo $sTermsConditionOptions;?>
		</select>
	</td></tr>
	
	
	<tr><td>Domain</td>
		<td><select name='iDomainId'>
		<?php echo $sDomainOptions;?>
		</select>
	</td></tr>
	
	
	
</table>
	
<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>