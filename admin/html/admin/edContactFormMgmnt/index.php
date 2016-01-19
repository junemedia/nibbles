<?php

/*********

Script to Display List/Delete Contact Form Management

Called : When Clicked on 'Contact Form management' in MARS Main Menu

*********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Editor's Submission Forms Management";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($delete) {		
		// if record deleted...
		
		$deleteQuery = "DELETE FROM edContactForms
					    WHERE  id = '$id'";
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		$result = mysql_query($deleteQuery);
		
		if ($result) {
			$statDelQuery = "DELETE FROM edContactFormStats
							 WHERE  contactFormId = '$id'";
			
			// start of track users' activity in nibbles
			$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $statDelQuery\")";
			$rResult = dbQuery($sAddQuery);
			echo  dbError();
			// end of track users' activity in nibbles
			
			
			
			$statDelResult = mysql_query($statDelQuery);
		} else {
			echo mysql_error();
		}
		//reset $id to null
		$id = '';
	}
	
	// Query to get the list of Contact Forms
	$selectQuery = "SELECT *
					FROM   edContactForms";
	
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Display Data: $selectQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
	
	
	
	$result = mysql_query($selectQuery);
	if ($result) {
		if (mysql_num_rows($result) > 0) {
			while ($row = mysql_fetch_object($result)) {
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				
				$formsList .= "<tr class=$bgcolorClass><td>$row->formName</td>
								<td>$row->contactEmail</td>
								<td><a href='JavaScript:void(window.open(\"addForm.php?iMenuId=$iMenuId&id=".$row->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
								&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a></td></tr>
								</td>";
			}
		} else {
			$sMessage = "No Records Exist...";
		}		
	} else {
		echo mysql_error();
	}
	
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addForm.php?iMenuId=$iMenuId\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
	
	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";
	
	$sortLink = $PHP_SELF."?iMenuId=$iMenuId";
	$reportLink = "<a href='report.php?iMenuId=$iMenuId'>Editor's Submission Forms Reporting</a>";
	
	
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
	
<form name=form1 action='<?php echo $PHP_SELF;?>'>
<?php echo $hidden;?>
<input type=hidden name=delete>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td><?php echo $addButton;?></td><td><?php echo $reportLink;?></td></tr>
<tr>
	<td align=left class=header>Submission Form</td>	
	<td align=left class=header>Contact eMail(s)</td>	
	<td>&nbsp; </td>
</tr>
<?php echo $formsList;?>
<tr><td><?php echo $addButton;?></td><td><?php echo $reportLink;?></td></tr>
</table>
</form>

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>