<?php

include("../../includes/paths.php");
session_start();

$sPageTitle = "Nibbles - Email Links";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	$sEmail = (!eregi("^[A-Za-z0-9\._-]+[@]{1,1}[A-Za-z0-9-]+[\.]{1}[A-Za-z0-9\.-]+[A-Za-z]$", trim($sEmail)) ? '' : trim($sEmail));
	$sSubject = (!ereg("^[a-zA-Z0-9 \'\x2e\#\:\\\/\,\’\&\@()\°_-]{1,}$", trim($sSubject)) ? '' : trim($sSubject));
	$sNote = (!ereg("^[a-zA-Z0-9 \'\x2e\#\:\\\/\,\’\&\@()\°_-]{1,}$", trim($sNote)) ? '' : trim($sNote));
	$sSourceCode = (!ctype_alnum(trim($src)) ? '' : trim($src));
	$aSourceCodes = (!is_array($aSourceCodes) ? array() : $aSourceCodes);
	
	//echo "email:$sEmail\n subject:$sSubject\n src:$sSourceCode\n source codes:".print_r($aSourceCodes, true)."\n";
	
	if ($sSubmit) {
		$sMessage = '';
		if ($sEmail == '') {
			$sMessage = "Email Address Required...";
			$bKeepValues = true;
		} elseif ($sSubject == ''){
			$sMessage = "Subject Required...";
			$bKeepValues = true;
		} elseif ($aSourceCodes == array()){
			$sMessage = "Please Select Source Codes to Send...";
			$bKeepValues = true;
		} 	
		
		/************************
		
		IMPORTANT: this script looks at the flowId on any link, to see if that link is valid for Nibbles 2, or,
		if there is no flowId, valid for Legacy Nibbles.		
		
		*************************/
		if($sSubmit == 'Submit' && $sMessage == ''){

			//echo "ok, we're in here.";
			$sLinksEmailBody = "$sNote\n\n";
							
			$sGetLinksSQL = "SELECT L.* , D.domainName FROM links L left join  domains D on L.domainId = D.id WHERE L.sourceCode IN ('".join("','",$aSourceCodes)."')";
			//echo "$sGetLinksSQL<br>";
			$rGetLinks = dbQuery($sGetLinksSQL);
			while($oGetLinks = dbFetchObject($rGetLinks)){
				$sRedirectDomain = ($oGetLinks->domainName ? "http://".$oGetLinks->domainName."/nibbles2/ot.php" : "http://www.popularliving.com/nibbles2/ot.php");
				if($oGetLinks->flowId){
					$sLinksEmailBody .= $sRedirectDomain . "?src=". strtolower($oGetLinks->sourceCode)."\n";
				} else {
					$sLinksEmailBody .= $sGblSourceRedirectsPath . "?src=". strtolower($oGetLinks->sourceCode)."\n";
				}
			}
					
			//echo "$sLinksEmailBody";
			mail($sEmail, "$sSubject", $sLinksEmailBody,"From: nibbles@amperemedia.com");
			
		}

	}
	
	$sGetSourceCodesSQL = "SELECT sourceCode FROM links";
	$rGetSourceCodes = dbQuery($sGetSourceCodesSQL);
	$sSourceCodeOptions = '';
	while($oGetSourceCodes = dbFetchObject($rGetSourceCodes)){
		$sSourceCodeOptions .= "<option value='$oGetSourceCodes->sourceCode' ".(in_array($oGetSourceCodes->sourceCode, $aSourceCodes) || $oGetSourceCodes->sourceCode == $sSourceCode ? 'selected' : '')." >$oGetSourceCodes->sourceCode</option>";
	}
	
	// Hidden fields to be passed with form submission
	$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>";
	
	//include("../../includes/adminAddHeader.php");
	if($sMessage == '' and $sSubmit == 'Submit'){
		echo "<script language='javascript'>window.close();</script>";
	}
	?>
	
	<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
	<?php echo $sHidden;?>
	<?php echo $sReloadWindowOpener;?>
	<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
		
		<tr><td width=35%>To: </td>
		<td><input type='text' name='sEmail' value='<?php echo $sEmail;?>'></td>
		<td><font color=Red>*</font></td>
		</tr>
		
		<tr><td width=35%>Subject: </td>
		<td><input type='text' name='sSubject' value='<?php echo $sSubject;?>'></td>
		<td><font color=Red>*</font></td>
		</tr>
				
		<tr><td width=35%>Note: </td>
		<td><textarea name='sNote'><?php echo $sNote;?></textarea></td>
		</tr>
		
		<tr><td width=35%>Source Codes: </td>
		<td><select name='aSourceCodes[]' multiple size=15><?php echo $sSourceCodeOptions;?></select></td>
		<td><font color=Red>*</font></td>
		</tr>
		
		
		</tr>
	
	</table>
	
	
	<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><TD colspan=2 align=center >
		<input type=submit name=sSubmit value='Submit'> &nbsp; &nbsp; 
		</td><td></td>
	</tr>	
	</table>
	
	
	<?php
	//include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>