<?php

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles Add Masking Domain";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if (hasAccessRight($iMenuId) || isAdmin()) {
	if (($sSaveClose || $sSaveNew) && !($id)) {
		if ($sDomainName !='') {
			// check if already exists
			$sCheckQuery = "SELECT *
							FROM   maskingDomains
							WHERE  domainName = \"$sDomainName\"";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult) > 0) {
				$sMessage = "Domain Name Already Exists...";
				$bKeepValues = true;
			} else {
				$sDescription = addslashes($sDescription);
				$sAddQuery = "INSERT INTO maskingDomains (domainName,description,randomDomain) 
								VALUES(\"$sDomainName\",\"$sDescription\",'$sAvailableRandom')";
				$rAddResult = dbQuery($sAddQuery);
				
				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($sAddQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles	
			}
		} else {
			$sMessage = "Domain Name is Required...";
			$bKeepValues = true;
		}
	} elseif (($sSaveClose || $sSaveNew) && ($id)) {
		if ($sDomainName != '') {
			$sCheckQuery = "SELECT *
							FROM   maskingDomains
							WHERE  domainName = \"$sDomainName\"
							AND id !='$id'";
			$rCheckResult = dbQuery($sCheckQuery);
			if (dbNumRows($rCheckResult) > 0) {
				$sMessage = "Domain Name Already Exists...";
				$bKeepValues = true;
			} else {
				$sDescription = addslashes($sDescription);
				$editQuery = "UPDATE maskingDomains 
							SET description = \"$sDescription\",
							domainName = \"$sDomainName\",
							randomDomain = '$sAvailableRandom'
							WHERE  id = '$id'";
				$result = mysql_query($editQuery);

				// start of track users' activity in nibbles
				$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($editQuery) . "\")";
				$rLogResult = dbQuery($sLogAddQuery);
				// end of track users' activity in nibbles
			}
		} else {
			$sMessage = "Domain Name is Required...";
			$bKeepValues = true;
		}
	}
	
	if ($sSaveClose) {
		if ($bKeepValues != true) {
			$sDescription = '';
			$sDomainName = '';
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
			$sDescription = '';
			$sDomainName = '';
			$id = '';
		}
	}
	
	if ($id != '') {
		$sDescription = '';
		$selectQuery = "SELECT * FROM   maskingDomains WHERE  id = '$id'";
		$result = mysql_query($selectQuery);
		while ($row = mysql_fetch_object($result)) {
			$sDomainName = $row->domainName;
			$sDescription = $row->description;
			$sAvailableRandom = $row->randomDomain;
		}
	}
	
	if ($sAvailableRandom == '') {
		$sAvailableRandom = 'Y';
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
		<tr><td width=35%>Domain Name: </td>
			<td><input type="text" name=sDomainName maxlength="255" size='50' value="<?php echo $sDomainName; ?>">
			</td>
		</tr>
		
		
		<tr><td width=35%>Available For Random Domain: </td>
			<td><input type="radio" name=sAvailableRandom value='Y' <?php if ($sAvailableRandom=='Y') { echo ' checked '; } ?>> Yes 
			<input type="radio" name=sAvailableRandom value='N' <?php if ($sAvailableRandom=='N') { echo ' checked '; } ?>> No
			</td>
		</tr>
		
	
		<tr><td width=35%>Description: </td>
		<td><textarea name=sDescription rows=5 cols=50><?php echo $sDescription;?></textarea></td>
		</tr>
		
		
	</table>	
		
	<?php
	include("../../includes/adminAddFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>