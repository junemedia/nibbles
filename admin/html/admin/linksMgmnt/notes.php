<?php

/*********
Script to Display List/Add/Edit/Delete Redirs Notes
*********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

session_start();

$sPageTitle = "Links Notes Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

if ($sSaveClose  && $iId) {		
		$sEditQuery = "UPDATE links
					  SET notes = \"$sNotes\"
					  WHERE id = '$iId'";
		$rResult = dbQuery($sEditQuery);

		// start of track users' activity in nibbles
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), 'Notes Updated - Id: $iId')";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
}

if ($sSaveClose) {
	// Close the window if record inserted correctly
		echo "<script language=JavaScript>
				//window.opener.location.reload();
				self.close();
				</script>";					
		// exit from this script
		exit();
}

if ($iId != '') {
	// If Clicked on Edit, display values in fields and
	// buttons to edit/Reset...
	
	// Get the data to display in HTML fields for the record to be edited
	$sSelectQuery = "SELECT *
					FROM   links
			  		WHERE  id = '$iId'";
	$rResult = dbQuery($sSelectQuery);
	echo dbError();
	if ($rResult) {
		
		while ($oRow = dbFetchObject($rResult)) {			
			$sNotes = ascii_encode($oRow->notes);
		}
		dbFreeResult($rResult);
	} else {
		echo dbError();
	}
} else {
	$sNotes = ascii_encode(stripslashes($sNotes));
}

// Hidden variable to be passed with form submit
$sHidden = "<input type=hidden name=iMenuId value='$iMenuId'>				
			<input type=hidden name=iId value='$iId'>";

?>

<html>
<head>
<title><?php echo $sPageTitle;?></title>
<LINK rel="stylesheet" href="<?php echo $sGblAdminSiteRoot;?>/styles.css" type="text/css" >
</head>

<body>
<br>
	
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $sHidden;?>

<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	
	<tr><td>Notes</td>
		<td><textarea name='sNotes' rows=20 cols=50><?php echo $sNotes;?></textarea></td>
	</tr>
	<tr><TD colspan=2 align=center >
	<input type=submit name=sSaveClose value='Save & Close'></td><td></td>
	</tr>	
</table>

</form>
</body>
</html>