<?php

/*******

Script to Add/Edit Publications

*******/

include("../../includes/paths.php");

session_start();

$sPageTitle = "Nibbles PopUp Management - Add/Edit PopUp";

if (hasAccessRight($iMenuId) || isAdmin()) {
	
if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	$publicationName = ucfirst($publicationName);
	$addQuery = "INSERT INTO edOfferPopUps(popupName, url, vSize, hSize)
				 VALUES ('$popupName', '$url', '$vSize', '$hSize')";

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $addQuery\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles		
	
	
	$result = mysql_query($addQuery);

	if (! $result) {		
		echo mysql_error();
	}
	
} else if ( ($sSaveClose || $sSaveNew) && ($id)) {
		
	// If record edited
	$editQuery = "UPDATE edOfferPopUps
				  SET 	 popupName = '$popupName',
				  		 url = '$url',
				  		 vSize = '$vSize',
				  		 hSize = '$hSize'
				  WHERE  id = '$id'";

	// start of track users' activity in nibbles 
	$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

	$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
	  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")"; 
	$rLogResult = dbQuery($sLogAddQuery); 
	echo  dbError(); 
	// end of track users' activity in nibbles		
	
	
	$result = mysql_query($editQuery);	
	if (! $result) {		
		echo mysql_error();
	}
}

if ($sSaveClose) {
	echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";		
	// exit from this script
	exit();			
} else if ($sSaveNew) {
	$reloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";
	// Reset textboxes for new record
	$popupName = '';
	$url = '';
	$hSize = '';
	$vSize = '';	
}

if ($id != '') {
	// If Clicked Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   edOfferPopUps
			  		WHERE  id = '$id'";
	$result = mysql_query($selectQuery);
	
	if ($result) {		
		while ($row = mysql_fetch_object($result)) {
			$popupName = $row->popupName;
			$url = $row->url;
			$vSize = $row->vSize;			
			$hSize = $row->hSize;	
		}
		mysql_free_result($result);
	} else {
		echo mysql_error();
	}	
} else {	
	// If add button is clicked, display another two buttons
	$sNnewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=abandonNew value=' Abandon & New  '>";	
}

// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";

include("../../includes/adminAddHeader.php");

?>

<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $sReloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
<tr><td>PopUp Name</td>
		<td><input type=text name='popupName' value='<?php echo $popupName;?>'></td>
	</tr>
	<tr><td>PopUp URL</td>
		<td><input type=text name='url' value='<?php echo $url;?>' size=55></td>
	</tr>	
	<tr><td>PopUp Width</td>
		<td><input type=text name='hSize' value='<?php echo $hSize;?>' size=5></td>
	</tr>				
	<tr><td>PopUp Height</td>
		<td><input type=text name='vSize' value='<?php echo $vSize;?>' size=5></td>
	</tr>						
</table>

<?php
	include("../../includes/adminAddFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>