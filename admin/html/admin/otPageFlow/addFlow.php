<?php


include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Flows - Add/Edit";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {


if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	if ($sSourceCode != '' && $sHeader1 !='' && $sHeader2 !='' && $sHeader3 !='' && $sFooter !='') {
		$checkQuery = "SELECT * FROM flows
					   WHERE  sourceCode = \"$sSourceCode\"";
		$checkResult = mysql_query($checkQuery);
		if (mysql_num_rows($checkResult) > 0 ) {
			$sMessage = "Source Code Already Exists...";
			$keepValues = true;
		} else {
			$sHeader1 = addslashes($sHeader1);
			$sHeader2 = addslashes($sHeader2);
			$sHeader3 = addslashes($sHeader3);
			$sFooter = addslashes($sFooter);
			
			$addQuery = "INSERT INTO flows(sourceCode, description, header, header2, header3, footer,nibblesVersion)
				VALUES(\"$sSourceCode\", \"$sDescription\", \"$sHeader1\", \"$sHeader2\", \"$sHeader3\", \"$sFooter\",'1')";

			// start of track users' activity in nibbles
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $addQuery\")";
			$rResult = dbQuery($sAddQuery);
			// end of track users' activity in nibbles
			
			$result = mysql_query($addQuery);
		}
	} else {
		$sMessage = "Source Code, Header, and Footer are required...";
		$keepValues = true;
	}
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
	//if record edited
	if ($sSourceCode != '' && $sHeader1 !='' && $sHeader2 !='' && $sHeader3 !='' && $sFooter !='') {
		$sHeader1 = addslashes($sHeader1);
		$sHeader2 = addslashes($sHeader2);
		$sHeader3 = addslashes($sHeader3);
		$sFooter = addslashes($sFooter);
		
		$editQuery = "UPDATE flows 
					SET description = \"$sDescription\",
					sourceCode = \"$sSourceCode\", 
					header = \"$sHeader1\", 
					header2 = \"$sHeader2\", 
					header3 = \"$sHeader3\", 
					footer = \"$sFooter\"
					WHERE  id = '$id'";
		$result = mysql_query($editQuery);
	} else {
		$sMessage = "Source Code, Header, and Footer are required...";
		$keepValues = true;
	}
}

if ($sMessage == '') {
	if ($sSaveClose) {
			echo "<script language=JavaScript>
				window.opener.location.reload();
				self.close();
				</script>";					
			exit();
	} else if ($sSaveNew) {
		$reloadWindowOpener = "<script language=JavaScript>
						window.opener.location.reload();
						</script>";
		$sDescription = '';
		$sSourceCode = '';
		$id = '';
	}
}

if ($id != '') {
	// If Clicked on Edit, display values in fields
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   flows
					WHERE  id = '$id'";
	$result = mysql_query($selectQuery);
	
	if ($result) {
		
		while ($row = mysql_fetch_object($result)) {
			$sSourceCode = $row->sourceCode;
			$sDescription = $row->description;
			$sHeader1 = $row->header;
			$sHeader2 = $row->header2;
			$sHeader3 = $row->header3;
			$sFooter = $row->footer;
		}
		
		mysql_free_result($result);
		
	} else {
		
		echo mysql_error();
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
		</select>
		</td>
	</tr>
	
	<tr><td>Description</td>
		<td><textarea name=sDescription rows=3 cols=50><?php echo $sDescription;?></textarea></td>
	</tr>

	<tr><td>Header 1</td>
		<td><textarea name=sHeader1 rows=5 cols=50><?php echo $sHeader1;?></textarea></td>
	</tr>
	
	<tr><td>Header 2</td>
		<td><textarea name=sHeader2 rows=5 cols=50><?php echo $sHeader2;?></textarea></td>
	</tr>
	
	<tr><td>Header 3</td>
		<td><textarea name=sHeader3 rows=5 cols=50><?php echo $sHeader3;?></textarea></td>
	</tr>

	<tr><td>Footer</td>
		<td><textarea name=sFooter rows=5 cols=50><?php echo $sFooter;?></textarea></td>
	</tr>

</table>

<?php

include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	

?>