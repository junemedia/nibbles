<?php

/*******

Script to Display List/Add/Edit/Delete Publication information

*******/

include("../../includes/paths.php");


$sPageTitle = "Publication Information";

session_start();

// Check if user is permitted to view this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
		
	if ($delete) {
		// if record deleted...
		
		$deleteQuery = "DELETE FROM publications
				   WHERE id = '$id'"; 
		
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		
		
		$result = mysql_query($deleteQuery);
		if(!($result)) {
			echo mysql_error();
		}
		//reset $id to null
		$id = '';
	}
	
	// Set Default order column
	if (!($orderColumn)) {
		$orderColumn = "publicationName";
		$sourceCodeOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($orderColumn) {
		case "publicationCode" :
		$currOrder = $publicationCodeOrder;
		$publicationCodeOrder = ($publicationCodeOrder != "DESC" ? "DESC" : "ASC");
		break;
		default:
		$currOrder = $publicationNameOrder;
		$publicationNameOrder = ($publicationNameOrder != "DESC" ? "DESC" : "ASC");
	}
	
	// Select Query to display list of Publications
	$selectQuery = "SELECT *
				FROM publications
				ORDER BY ".$orderColumn." $currOrder";
	//WHERE id IN ('130','132','131','125','129','126','128','39','135','134','127','123','136')
	$result = mysql_query($selectQuery);
	
	if ($result) {
		
		if (mysql_num_rows($result) > 0) {
			
			while ($row = mysql_fetch_object($result)) {
				
				// For alternate background color
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				
				if ($row->releasePrevNight == 'Y') {
					$releasePrevNightChecked = "checked";
				} else {
					$releasePrevNightChecked = "";
				}
				
				$publicationList .= "<tr class=$bgcolorClass><td>$row->publicationName</td>
					<td>$row->publicationCode</td>
					<td>$row->standardSchedule</td>
					<td>$row->soloSchedule</td>
					<td>$row->releaseTime</td>
					<td><input type=checkbox $releasePrevNightChecked disabled></td>
					<td><a href='JavaScript:void(window.open(\"addPublication.php?iMenuId=$iMenuId&id=".$row->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a>
					</td></tr>";
			}
		} else {
			$message = "No Records Exist...";
		}
		mysql_free_result($result);
	} else {
		echo mysql_error();
	}
	
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addPublication.php?iMenuId=$iMenuId\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
	
	// Hidden variables to be passed with form submission
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";
	
	$sortLink = $PHP_SELF."?iMenuId=$iMenuId";
	$complianceLink = "index.php?iMenuId=$iMenuId";
	
	
	include("../../includes/adminHeader.php");	
	
	?>
	
	
<script language=JavaScript>
				function confirmDelete(form1,id)
				{
					if(confirm('Are you sure to delete this record ?'))
					{							
						document.form1.elements['delete'].value='Delete';
						document.form1.elements['id'].value=id;
						document.form1.submit();								
					}
				}						
</script>

<body>
<br>
	
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $hidden;?>
<input type=hidden name=delete>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td><?php echo $addButton;?></td><td colspan=3><a href='<?php echo $complianceLink;?>'>Back to Compliance</a></td></tr>
<tr>
	<TD align=left><a href="<?php echo $sortLink;?>&orderColumn=publicationName&publicationNameOrder=<?php echo $publicationNameOrder;?>" class=header>Publication Name</a></TD>
	<TD align=left><a href="<?php echo $sortLink;?>&orderColumn=publicationCode&publicationCodeOrder=<?php echo $publicationCodeOrder;?>" class=header>Publication Code</a></TD>
	<TD align=left class=header>Standard Schedule</TD>
	<TD align=left class=header>Solo Schedule</TD>
	<TD align=left class=header>Release Time</TD>
	<TD align=left class=header>Release Prev. Night</TD>
	<TD>&nbsp; </TD
</tr>
<?php echo $publicationList;?>
<tr><td><?php echo $addButton;?>


</td><td colspan=3><a href='<?php echo $complianceLink;?>'>Back to Compliance</a></td></tr>
</table>
	
</form>

<?php

	include("../../includes/adminFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>

	
