<?php

/*********

Script to Display List/Add/Edit/Delete Affiliate Management Company information

*********/

include("../../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "FunPage Category Management - Add/Edit Category";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	
if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	//Check For Dupe
	$checkQuery = "SELECT *
				   FROM   funPageCategories
				   WHERE  title = '$title'";
	$checkResult = mysql_query($checkQuery);
	if (mysql_num_rows($checkResult) > 0 ) {
		$message = "Category exists.";
		$keepValues = true;
	} else {
		
		$addQuery = "INSERT INTO funPageCategories(title, active, sortOrder)
				 VALUES('$title', '$active', '$sortOrder')";
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $addQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		
		$result = mysql_query($addQuery);
		if (! $result) {
			echo mysql_error();
		}
	}
	
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
	//if record edited
	
	//Check For Dupe
	$checkQuery = "SELECT *
				   FROM   funPageCategories
				   WHERE  title = '$title'
					AND   id != '$id'";
	$checkResult = mysql_query($checkQuery);
	if (mysql_num_rows($checkResult) > 0 ) {
		$message = "Category exists.";
		$keepValues = true;
	} else {
		
		$editQuery = "UPDATE funPageCategories
				  SET 	 title = '$title',
						 active = '$active',
						 sortOrder = '$sortOrder'						 
				  WHERE  id = '$id'";
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		
		$result = mysql_query($editQuery);
		echo $editQuery.mysql_error();
	}
	//echo $editQuery.$result;
}

if ($sSaveClose) {
	if ($keepValues !=true) {
		echo "<script language=JavaScript>
			window.opener.location.reload();
			self.close();
			</script>";					
		// exit from this script
		exit();
	}
} else if ($sSaveNew) {
	$reloadWindowOpener = "<script language=JavaScript>
							window.opener.location.reload();
							</script>";
	// Reset textboxes for new record
	if ($keepValues != true) {
		$title = '';
		$active='';
		$sortOrder = '';		
	}
}

if ($id != '') {
	// If Clicked on Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   funPageCategories
					WHERE  id = '$id'";
	$result = mysql_query($selectQuery);
	
	if ($result) {
		
		while ($row = mysql_fetch_object($result)) {
			$title = ascii_encode($row->title);					
			$active = $row->active;
			$sortOrder = $row->sortOrder;			
		}
		
		mysql_free_result($result);
		
	} else {
		
		echo mysql_error();
	}
}  else {
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}
if ($active) {
	$activeChecked = "checked";
}
// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>
			<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>			
			<input type=hidden name=id value='$id'>";

	include("$sGblIncludePath/adminAddHeader.php");	
?>


<form action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $reloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>

	<tr><td width=35%>Category Title</td>
		<td><input type=text name='title' value='<?php echo $title;?>' ></td>
	</tr>
	<tr><td>Active</td>
		<td><input type=checkbox value='1' name='active' <?php echo $activeChecked;?>></td>
	</tr>
	<tr><td>Sort Order</td>
		<td><input type=text name='sortOrder' value='<?php echo $sortOrder;?>' ></td>
	</tr>
		
</table>

<?php

include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}	

?>