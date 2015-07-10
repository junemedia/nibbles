<?php

/*******

Script to Display List/Add/Edit/Delete  Seed Email Accounts information

*********/

include("../../includes/paths.php");


$sPageTitle = "Seed Email Accounts";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($delete) {		
		// if record deleted...
		
		$deleteQuery = "DELETE FROM seedEmailAccounts
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
	
	
	//Select Query to display list of Seed Email Accounts
	
	$selectQuery = "SELECT *
					FROM seedEmailAccounts";
	
	
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Report: $selectQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
	
	
	$result = mysql_query($selectQuery);
	
	if ($result) {
				
		if (mysql_num_rows($result) > 0) {
			//Prepare heading line of records...
			
			while ($row = mysql_fetch_object($result)) {
				
				// For alternate background color of rows
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				
				$accountList .= "<tr class=$bgcolorClass><td>$row->ISPName</td>
					<td>$row->ISPType</td>
					<td>$row->ISPCode</td>
					<td>$row->userName</td>
					<td>$row->passwd</td>			
					<td>$row->mailServer</td>
					<td><a href='JavaScript:void(window.open(\"addAccount.php?iMenuId=$iMenuId&id=".$row->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a>
					&nbsp;</td></tr>";				
			}
		} else {
			$message = "No Records Exist...";
		}
		
		mysql_free_result($result);
		
	} else {
		echo mysql_error();
	}
	
	if ($id!='') {
		// If Clicked to edit, get the data to display in fields and
		// buttons to edit it...
		
		$selectQuery = "SELECT *
						FROM   seedEmailAccounts
			  			WHERE  id = '$id'";
		$result = mysql_query($selectQuery);
		
		if ($result) {
			
			while ($row = mysql_fetch_object($result)) {
				$ISPName = $row->ISPName;
				$ISPType = $row->ISPType;
				$userName = $row->userName;
				$passwd = $row->passwd;
				$mailServer = $row->mailServer;
			}
			
			mysql_free_result($result);
		} else {
			echo mysql_error();
		}
	}
	
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addAccount.php?iMenuId=$iMenuId&menuFolder=$menuFolder\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
	
	//Set the links to be displayed on this page
	$reportLink="report.php?iMenuId=$iMenuId";
	$publicationLink="publication.php?iMenuId=$iMenuId";
	
	// Hidden variable to be passed with Form submission
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";
	
	
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
	<tr><td><?php echo $addButton;?></td>
		<td colspan=4><a href='<?php echo $publicationLink;?>'>Publication information</a> &nbsp; 
			<a href='<?php echo $reportLink;?>'>Compliance Reporting</a></td>
	</tr>
	<tr>
		<td align=left class=header>ISP Name</td>
		<td align=left class=header>ISP Type</td>	
		<td align=left class=header>ISP Code</td>	
		<td align=left class=header>User Name</td>
		<td align=left class=header>Password</td>
		<td align=left class=header>Mail Server</td>	
		<td>&nbsp; </td>
	</tr>
	<?php echo $accountList;?>
		<tr><td><?php echo $addButton;?></td>
		<td colspan=4><a href='<?php echo $publicationLink;?>'>Publication information</a> &nbsp; 
			<a href='<?php echo $reportLink;?>'>Compliance Reporting</a></td>
	</tr>
</table>
</form>

<?php

	include("../../includes/adminFooter.php");
	
} else {
	echo "You are not authorized to access this page...";
}
?>

		
