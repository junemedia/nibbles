<?php 

/***********

Script to Manage Site Contents of MyHealthyLiving site

*************/

include("../../../includes/paths.php");

$sPageTitle = "MyHealthyLiving Category Management";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);
	
	if ($delete) {
		$deleteQuery = "DELETE FROM categories
						WHERE       id = '$id'";

		// start of track users' activity in nibbles 
		$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 

		$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
		  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")"; 
		$rLogResult = dbQuery($sLogAddQuery); 
		echo  dbError(); 
		// end of track users' activity in nibbles		
		
		
		$deleteResult = dbQuery($deleteQuery);
		
		// delete the image here
		
	}
	
	// Set Default order column
	if (!($orderColumn)) {
		$orderColumn = "catName";
		$catNameOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($orderColumn) {
		case "catFullName":
		$currOrder = $catFullNameOrder;
		$catFullNameOrder = ($catFullNameOrder != "DESC" ? "DESC" : "ASC");
		break;
		default:
		$currOrder = $catNameOrder;
		$catNameOrder = ($catNameOrder != "DESC" ? "DESC" : "ASC");
	}
	
	// Query to get the list of BDPartners
	$selectQuery = "SELECT *
					FROM   categories
					ORDER BY $orderColumn $currOrder";
	
	$result = dbQuery($selectQuery);
	
	if ($result) {
		if (dbNumRows($result) > 0) {
			
			while ($row = dbFetchObject($result)) {
				
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				
				$catList .= "<tr class=$bgcolorClass><td>$row->catName</td>
								<td>$row->catFullName</td>
								<td>$row->image</td>
						<td><a href='JavaScript:void(window.open(\"addCategory.php?iMenuId=$iMenuId&id=".$row->id."&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"AddContent\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
						&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a></td></tr>";
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
	
	$sortLink = $PHP_SELF."?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder";
	
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
	<td ><a href='<?php echo $sortLink;?>&orderColumn=catName&catNameOrder={CAT_NAME_ORDER}' class=header>Category Name</a></td>
	<td ><a href='<?php echo $sortLink;?>&orderColumn=catFullName&catFullNameOrder={CAT_FULL_NAME_ORDER}' class=header>Category Full Name</a></td>
	<td class=header>Image</td>	
	<td>&nbsp; </td>
</tr>
<?php echo $catList;?>
<tr><th colspan=7 align=left><?php echo $addButton;?></th></tr>
</table>

</form>
<?php

// include footer

	include("$sGblIncludePath/adminFooter.php");				

} else {
	echo "You are not authorized to access this page...";
}				
?>	

