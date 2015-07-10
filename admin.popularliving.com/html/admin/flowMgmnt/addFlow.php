<?php


include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Flow Management";
$sTrackingUser = $_SERVER['PHP_AUTH_USER'];

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
if (($sSaveClose || $sSaveNew) && !($id)) {
	if ($sFlowName != '' && $sFooter !='') {
		$checkQuery = "SELECT * FROM flows
					   WHERE  flowName = \"$sFlowName\"";
		$checkResult = mysql_query($checkQuery);
		if (mysql_num_rows($checkResult) > 0 ) {
			$sMessage = "Flow Name Already Exists...";
			$keepValues = true;
		} else {
			$sFooter = addslashes($sFooter);
			
			$addQuery = "INSERT INTO flows(flowName,footer,nibblesVersion,showNonRevOffers)
				VALUES(\"$sFlowName\", \"$sFooter\", '2','$sShowNonRevOffers')";
			$result = mysql_query($addQuery);
			
			// start of track users' activity in nibbles
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($addQuery) . "\")";
			$rLogResult = dbQuery($sLogAddQuery);
			// end of track users' activity in nibbles
		}
	} else {
		$sMessage = "Flow Name / Footer Required...";
		$keepValues = true;
	}
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
	if ($sFlowName != '' && $sFooter !='') {
		$checkQuery = "SELECT * FROM flows
					   WHERE  flowName = \"$sFlowName\"
					   AND id !='$id'";
		$checkResult = mysql_query($checkQuery);
		if (mysql_num_rows($checkResult) > 0 ) {
			$sMessage = "Flow Name Already Exists...";
			$keepValues = true;
		} else {
			$sFooter = addslashes($sFooter);
			$editQuery = "UPDATE flows 
						SET flowName = \"$sFlowName\",
						footer = \"$sFooter\",
						showNonRevOffers = '$sShowNonRevOffers'
						WHERE  id = '$id'";
			$result = mysql_query($editQuery);
			
			// start of track users' activity in nibbles
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"" . addslashes($editQuery) . "\")";
			$rLogResult = dbQuery($sLogAddQuery);
			// end of track users' activity in nibbles
		}
	} else {
		$sMessage = "Flow Name / Footer Required...";
		$keepValues = true;
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
		$sFlowName = '';
		$sFooter = '';
		$id = '';
	}
}

if ($id != '') {
	$selectQuery = "SELECT *
					FROM   flows
					WHERE  id = '$id'";
	$result = mysql_query($selectQuery);
	while ($row = mysql_fetch_object($result)) {
		$sFlowName = $row->flowName;
		$sFooter = ascii_encode($row->footer);
		$sShowNonRevOffers = $row->showNonRevOffers;
	}
} else {
	$sShowNonRevOffers = 'N';
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

	<tr><td>Flow Name:</td>
	<td><input type="text" name="sFlowName" size="50" value="<?php echo $sFlowName; ?>"></td>
	</tr>
	
	<tr><td>Footer</td>
		<td><textarea name=sFooter rows=5 cols=50><?php echo $sFooter;?></textarea></td>
	</tr>
	
	<tr><td>Show Non-Revenue Offers</td>
		<td><input type="radio" name="sShowNonRevOffers" value="Y" <?php if($sShowNonRevOffers=='Y') { echo 'checked'; } ?>>Yes
			&nbsp;&nbsp;&nbsp;
			<input type="radio" name="sShowNonRevOffers" value="N" <?php if($sShowNonRevOffers=='N') { echo 'checked'; } ?>>No
		</td>
	</tr>

</table>

<?php

include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	

?>