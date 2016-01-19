<?php


include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
$sPageTitle = "Flows - Add/Edit";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
$sPixelUrl = addslashes($sPixelUrl);
if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	if ($sSourceCode != '' && ctype_digit($iDays)) {
		$checkQuery = "SELECT * FROM eOptions
					   WHERE  sourceCode = \"$sSourceCode\"";
		$checkResult = mysql_query($checkQuery);
		if (mysql_num_rows($checkResult) > 0 ) {
			$sMessage = "Source Code Already Exists...";
			$keepValues = true;
		} else {
			$addQuery = "INSERT INTO eOptions(sourceCode, days, redirectUrl, pixel)
					 VALUES(\"$sSourceCode\", '$iDays', \"$sRedirectUrl\", \"$sPixelUrl\")";
			$result = mysql_query($addQuery);
		}
	} else {
		$sMessage = "Invalid Days or Source Code...";
		$keepValues = true;
	}
}

if (($sSaveClose || $sSaveNew) && ($id)) {
	//if record edited
	if ($sSourceCode != '' && ctype_digit($iDays)) {
		$editQuery = "UPDATE eOptions
					SET sourceCode = \"$sSourceCode\",
					days = '$iDays',
					redirectUrl = \"$sRedirectUrl\",
					pixel = \"$sPixelUrl\"
					WHERE  id = '$id'";
		$result = mysql_query($editQuery);
	} else {
		$sMessage = "Invalid Days or Source Code...";
		$keepValues = true;
	}
}

if ($sSaveClose && $sMessage == '') {
	// start of track users' activity in nibbles
	$sTempNotes = $addQuery.$editQuery;
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add or Edit: $sTempNotes\")";
	$rResult = dbQuery($sAddQuery);
	// end of track users' activity in nibbles

	echo "<script language=JavaScript>
		window.opener.location.reload();
		self.close();
		</script>";					
	exit();
}


if ($id != '') {	// If Clicked on Edit, display values in fields
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT * FROM eOptions WHERE id='$id'";
	$result = mysql_query($selectQuery);
	while ($row = mysql_fetch_object($result)) {
		$sSourceCode = $row->sourceCode;
		$iDays = $row->days;
		$sRedirectUrl = $row->redirectUrl;
		$sPixelUrl = $row->pixel;
	}
}

$sSourceCodeQuery = "SELECT sourceCode FROM links order by sourceCode";
$rSourceCodeResult = mysql_query($sSourceCodeQuery);
$sSourceCodeOption = "<option value=''>";
while ($oSourceCodeRow = mysql_fetch_object($rSourceCodeResult)) {
	if ($oSourceCodeRow->sourceCode == $sSourceCode) {
		$sSelected = "selected";
	} else {
		$sSelected = "";
	}
	$sSourceCodeOption .= "<option value='$oSourceCodeRow->sourceCode' $sSelected>$oSourceCodeRow->sourceCode";
}



// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";

include("$sGblIncludePath/adminAddHeader.php");	
?>


<form action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $reloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>

	<tr><td width=35%>Source Code</td>
		<td><select name='sSourceCode'>
		<?php echo $sSourceCodeOption;?>
		</select>&nbsp;&nbsp;Required
		</td>
	</tr>

	<tr><td>Days: </td>
		<td><input type="text" name="iDays" value="<?php echo $iDays; ?>" size=5 maxlength="5">&nbsp;&nbsp;Numeric</td>
	</tr>

	<tr><td>Redirect Url: </td>
		<td><textarea name="sRedirectUrl" rows=5 cols=60><?php echo $sRedirectUrl;?></textarea></td>
	</tr>

	<tr><td>Pixel: </td>
		<td><textarea name="sPixelUrl" rows=5 cols=60><?php echo $sPixelUrl;?></textarea></td>
	</tr>
</table>

<?php

include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	

?>