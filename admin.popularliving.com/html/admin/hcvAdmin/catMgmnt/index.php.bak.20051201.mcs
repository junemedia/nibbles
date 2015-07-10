<?php 

/***********

Script to Manage Site Contents of HandCraftersVillage site

*************/

include("../../../includes/paths.php");

$sPageTitle = "Handcrafters Village Category Management";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	
	// SELECT HCV DATABASE
	dbSelect($sGblHcvDBName);	
	
	
	if ($delete) {
		$deleteQuery = "DELETE FROM categories
						WHERE       id = '$id'";
		$deleteResult = dbQuery($deleteQuery);
						
	}
				
	// Query to get the list of BDPartners
	$selectQuery = "SELECT *
					FROM   categories
					ORDER BY title";
	
	$result = dbQuery($selectQuery);
	
	if ($result) {
		if (dbNumRows($result) > 0) {
			
			while ($row = dbFetchObject($result)) {
				
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				
				$catList .= "<tr class=$bgcolorClass><td>$row->title</td>								
							<td><a href='JavaScript:void(window.open(\"addCategory.php?iMenuId=$iMenuId&id=".$row->id."&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"AddContent\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
							&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a>
							&nbsp;<a href='JavaScript:void(window.open(\"projects.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder&id=".$row->id."\",\"\",\"scrollbars=yes, resizable=yes, status=yes\"));'>Projects</a> </td></tr>
							</td></tr>";
			}
		} else {
			$sMessage = "No Records Exist...";
		}
		dbFreeResult($result);
		
	} else {
		echo dbError();
	}

	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addCategory.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";

	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iParentMenuId value='$iParentMenuId'>
				<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>
			<input type=hidden name=id value='$id'>";
	
		
	include("$sGblIncludePath/adminHeader.php");	
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

<input type=hidden name=delete>

<?php echo $hidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><th colspan=3 align=left><?php echo $addButton;?></th></tr>
<tr>
	<td class=header>Category Name</td>	
	<td>&nbsp; </td>
</tr>
<?php echo $catList;?>
<tr><th colspan=3 align=left><?php echo $addButton;?></th></tr>
</table>

</form>
<?php
// include footer

	include("$sGblIncludePath/adminFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}				
?>	

