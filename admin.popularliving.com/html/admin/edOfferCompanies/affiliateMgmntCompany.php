<?php

/*********

Script to Display List/Delete Affiliate Management Company information

*********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "Affiliate Management Company";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($delete) {
		// if record deleted...
		// Manage offerCompanies related to this affiliate Company
		// Or don't allow to delete the record
		
		$deleteQuery = "DELETE FROM edAffiliateMgmntCompanies
					    WHERE  id = '$id'";
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sTempAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete Entry: $deleteQuery\")";
		$rResult = dbQuery($sTempAddQuery);
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
		$orderColumn = "companyName";
		$companyNameOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($orderColumn) {
		default:
		$currOrder = $companyNameOrder;
		$companyNameOrder = ($companyNameOrder != "DESC" ? "DESC" : "ASC");
	}
	
	// Query to get the list of BDPartners
	$selectQuery = "SELECT *
					FROM   edAffiliateMgmntCompanies
					ORDER BY ".$orderColumn." $currOrder";
	$result = mysql_query($selectQuery);
	
	if ($result) {
		if (mysql_num_rows($result) > 0) {
			
			while ($row = mysql_fetch_object($result)) {
				
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				$companyName = ascii_encode($row->companyName);
				$notes = ascii_encode($row->notes);
				$companyList .= "<tr class=$bgcolorClass><td>$companyName</td>
								<td>$notes</td><td>
								<a href='JavaScript:void(window.open(\"addAffiliateMgmntCompany.php?iMenuId=$iMenuId&id=".$row->id."\", \"AddAccount\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
								&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a></td></tr>";				
			}
		} else {
			$sMessage = "No Records Exist...";
		}
		mysql_free_result($result);
		
	} else {
		echo mysql_error();
	}
	
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addAffiliateMgmntCompany.php?iMenuId=$iMenuId&menuFolder=$menuFolder\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
	
// Hidden variable to be passed with form submit
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
	
<form name=form1 action='<?php echo $PHP_SELF;?>' method=post>
<?php echo $hidden;?>
<?php echo $sReloadWindowOpener;?>

<input type=hidden name=delete>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>
<tr><td><?php echo $addButton;?></td></tr>
<tr>
	<td align=left class=header>Company Name</td>	
	<td align=left class=header>Notes</td>
	<td>&nbsp; </td>
</tr>
<?php echo $companyList;?>
<tr><td><?php echo $addButton;?></td></tr>
</table>
</form>


<?php

include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>

	
