<?php


include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
$sPageTitle = "Flows - Add/Edit";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
if ($sIsBlock == '') { $sIsBlock = 'N'; }

if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	if ($sSourceCode != '') {
		$checkQuery = "SELECT * FROM foreignIpHandling
					   WHERE  sourceCode = \"$sSourceCode\"";
		$checkResult = mysql_query($checkQuery);
		if (mysql_num_rows($checkResult) > 0 ) {
			$sMessage = "Source Code Already Exists...";
			$keepValues = true;
		} else {
			$addQuery = "INSERT INTO foreignIpHandling(sourceCode, redirectUrl, isBlock)
					 VALUES(\"$sSourceCode\", \"$sRedirectUrl\", '$sIsBlock')";
			$result = mysql_query($addQuery);
		}
	} else {
		$sMessage = "Invalid Source Code...";
		$keepValues = true;
	}
}

if (($sSaveClose || $sSaveNew) && ($id)) {
	//if record edited
	if ($sSourceCode != '') {
		$editQuery = "UPDATE foreignIpHandling
					SET sourceCode = \"$sSourceCode\",
					redirectUrl = \"$sRedirectUrl\",
					isBlock = '$sIsBlock'
					WHERE  id = '$id'";
		$result = mysql_query($editQuery);
	} else {
		$sMessage = "Invalid Source Code...";
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
	$selectQuery = "SELECT * FROM foreignIpHandling WHERE id='$id'";
	$result = mysql_query($selectQuery);
	while ($row = mysql_fetch_object($result)) {
		$sSourceCode = $row->sourceCode;
		$sRedirectUrl = $row->redirectUrl;
		$sIsBlock = $row->isBlock;
	}
	if ($sIsBlock == 'Y') {
		$sIsBlockChecked = " checked ";
	} else {
		$sIsBlockChecked = "";
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

	<tr><td>Block: </td>
		<td><input type="checkbox" name="sIsBlock" value="Y" <?php echo $sIsBlockChecked ?>></td>
	</tr>

	<tr><td>Redirect Url: </td>
		<td><textarea name="sRedirectUrl" rows=5 cols=60><?php echo $sRedirectUrl;?></textarea></td>
	</tr>
	
	
	<tr><td colspan="2" class="header"><br>Notes -</td></tr>
	<tr><td colspan="2">
		[email] = email address<br>		[first] = first name<br>
		[last] = last name<br>		[address] = street address<br>
		[address2] = street address 2<br>		[city] = city<br>
		[state] = state<br>		[zip] = zip<br>
		[phone] = phone number xxx-xxx-xxxx<br>		[ipAddress] = user remote ip address<br>
		[phone_areaCode] = phone area code xxx<br>		[phone_exchange] = phone exchange xxx<br>
		[phone_number] = phone number xxxx<br>		[birthYear] = birth year<br>
		[birthMonth] = birth month<br>		[birthDay] = birth day<br>
		[gender] = gender M/F<br>		[sourcecode] = source code<br>
		[ss] = sub source code<br>		[mm] = current month<br>
		[dd] = current day<br>		[yyyy] = current year (4 digit)<br>
		[yy] = current year (2 digit)<br>		[hh] = current hour<br>
		[ii] = current min<br>		[sec] = current sec<br>
		[gVariable] = g variable<br>		[country] = foreign country
	</td></tr>
	<tr><td colspan="2"><br><br></td></tr>
	


</table>

<?php

include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	

?>