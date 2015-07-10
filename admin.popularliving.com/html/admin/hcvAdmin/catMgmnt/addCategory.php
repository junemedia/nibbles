<?php

/*********

Script to Display List/Add/Edit/Delete Affiliate Management Company information

*********/

include("../../../includes/paths.php");

$sPageTitle = "Handcrafters Village Category Management - Add/Edit Category";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	// SELECT HCV DATABASE
	dbSelect($sGblHcvDBName);	
	

if (($sSaveClose || $sSaveNew) && !($id)) {
	// if new data submitted
	if($title == '') {
		$sMessage = "Category Name Is Required...";
		$keepValues = true;
	} else {
		//Check For Dupe
		$checkQuery = "SELECT *
				   FROM   categories
				   WHERE  catName = '$title'";
		$checkResult = dbQuery($checkQuery);
		if (dbNumRows($checkResult) > 0 ) {
			$sMessage = "Category Already Exists...";
			$keepValues = true;
		} else {
			$addQuery = "INSERT INTO categories (title)
				 VALUES ('$title')";
			
			// start of track users' activity in nibbles
			$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Add: $addQuery\")";
			$rResult = dbQuery($sAddQuery);
			echo  dbError();
			// end of track users' activity in nibbles
			
			
			
			$result = dbQuery($addQuery);
			if (!($result)) {
				echo dbError();
			}
		}
	}
	
} elseif (($sSaveClose || $sSaveNew) && ($id)) {
	//if record edited
	if ($title == '') {
		$sMessage = "Category Name Is Required...";
		$keepValues = true;
	} else {
		//Check For Dupe
		$checkQuery = "SELECT *
				   		FROM   categories
				   		WHERE  catName = '$title'
						AND   id != '$id'";
		$checkResult = dbQuery($checkQuery);
		if (dbNumRows($checkResult) > 0 ) {
			$sMessage = "Category Alredy Exists...";
			$keepValues = true;
		} else {
			
			$editQuery = "UPDATE categories
				  	  	  SET 	 title = '$title'								 
				  		  WHERE  id = '$id'";
			
			// start of track users' activity in nibbles
			$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Edit: $editQuery\")";
			$rResult = dbQuery($sAddQuery);
			echo  dbError();
			// end of track users' activity in nibbles
			
			
			$result = dbQuery($editQuery);
			if (!($result)) {
				echo dbError();
			}
		}
	}
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
	}
}

if ($id != '') {
	// If Clicked on Edit, display values in fields
	
	// Get the data to display in HTML fields for the record to be edited
	$selectQuery = "SELECT *
					FROM   categories
			  		WHERE  id = '$id'";
	$result = dbQuery($selectQuery);
	
	if ($result) {
		
		while ($row = dbFetchObject($result)) {
			$title = $row->title;			
		}		
	} else {
		echo dbError();
	}
}  else {
	// If add button is clicked, display another two buttons
	$sNewEntryButtons = "<BR><BR><input type=submit name=sSaveNew value=' Save & New  '> &nbsp; &nbsp;
						<input type=reset name=sAbandonNew value=' Abandon & New  '>";	
}

// Hidden variable to be passed with form submit
$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>			
			<input type=hidden name=iParentMenuId value='$iParentMenuId'>			
			<input type=hidden name=id value='$id'>";

	include("$sGblIncludePath/adminAddHeader.php");	
?>
<form action='<?php echo $PHP_SELF;?>'>
<?php echo $hidden;?>
<?php echo $reloadWindowOpener;?>
<table cellpadding=5 cellspacing=5 bgcolor=c9c9c9 width=95% align=center>
	<tr><td width=35%>Category</td>
		<td><input type=text name='title' value='<?php echo $title;?>' ></td>
	</tr>
			
</table>

	<?php
// include footer

	include("$sGblIncludePath/adminAddFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}				
?>	