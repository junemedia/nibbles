<?php


include_once("../../includes/paths.php");
include_once("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Rate Structure Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
if (($sSaveClose || $sSaveNew) && !($id)) {
	//this is a new pixel
	
	//check that all of the required fields are there
	if($sDescription == ''){
		$sMessage .= 'Description is required...<br>';
	}
	if($sAbbreviation == ''){
		$sMessage .= "Abbreviation is required...<br>";
	}
	
	if ($sMessage =='') {
		
		$addQuery = "INSERT INTO campaignRateStructure (description, rateType, captureType)
				VALUES(\"$sDescription\", \"$sAbbreviation\", '$sCaptureType')";
		$result = mysql_query($addQuery);
			
		if(!$result){
			$sMessage .= dbError();
		}
		
		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($addQuery) . "\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
		
	} 
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
	//this is an existing pixel
	
	
	//check that all of the required fields are there
	if($sDescription == ''){
		$sMessage .= 'Description is required...<br>';
	}
	if($sAbbreviation == ''){
		$sMessage .= "Abbreviation is required...<br>";
	}
	
	if ($sMessage =='') {
				
		$sPixelHtml = addslashes($sPixelHtml);
		
		$addQuery = "UPDATE campaignRateStructure 
					SET description = \"$sDescription\", 
						rateType = \"$sAbbreviation\", 
						captureType ='$sCaptureType'
					WHERE id = '$id'";
		$result = mysql_query($addQuery);
			
		// start of track users' activity in nibbles
		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($addQuery) . "\")";
		$rLogResult = dbQuery($sLogAddQuery);
		// end of track users' activity in nibbles
		
	} 
}

if ($sMessage == '') {
	if ($sSaveClose) {
			echo "<script language=JavaScript>
				window.opener.location.reload();
				$sPopUpUrl
				self.close();
				</script>";					
			exit();
	} else if ($sSaveNew) {
		$reloadWindowOpener = "<script language=JavaScript>
						window.opener.location.reload();
						</script>";
		$sDescription = '';
		$sAbbreviation = '';
		$sCaptureType = '';
	}
}

if ($id != '') {
	$selectQuery = "SELECT *
					FROM   campaignRateStructure
					WHERE  id = '$id'";
	$result = mysql_query($selectQuery);
	while ($row = mysql_fetch_object($result)) {
		
		$sDescription = $row->description;
		$sAbbreviation = $row->rateType;
		$sCaptureType = $row->captureType;
	}
} else {
	//defaults
	$sDescription = '';
	$sAbbreviation = '';
	$sCaptureType = 'emailCapture';
}




// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";

include("$sGblIncludePath/adminAddHeader.php");	
?>


<form action='<?php echo $PHP_SELF;?>' method=post name='form1' >
<?php echo $hidden;?>
<?php echo $reloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
<tr>
	<td>Description:</td><td> <input name='sDescription' value='<?php echo $sDescription;?>' size=120></td>
</tr>
<tr>
	<td>Abbreviation:</td><td> <input name='sAbbreviation' value='<?php echo $sAbbreviation;?>' ></td>
</tr>
<tr>
	<td>Capture Type:</td><td> 
		<select name='sCaptureType'>
			<option value='emailCapture' <?php echo ($sCaptureType == 'emailCapture' ? 'selected' : ''); ?> >Email Capture
			<option value='memberCapture' <?php echo ($sCaptureType == 'memberCapture' ? 'selected' : ''); ?> >Member Capture
			<option value='neither' <?php echo ($sCaptureType == 'neither' ? 'selected' : ''); ?> >Neither Email Capture nor Member Capture
		</select>
	</td>
</tr>
</table>
<?php

include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	

?>