<?php 

/***********

Script to Manage Site Contents of HandCraftersVillage site

*************/

include("../../../includes/paths.php");


$sPageTitle = "Handcrafters Village Site Variables Management";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {		
	
	// SELECT HCV DATABASE
	dbSelect($sGblHcvDBName);		
	
	if ($delete) {
		$deleteQuery = "DELETE FROM siteVars
						WHERE       id = '$id'";
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		
		$deleteResult = dbQuery($deleteQuery);
		
		// delete the image here
		
	}
				
	// Query to get the list of BDPartners
	$selectQuery = "SELECT *
					FROM   siteVars
					ORDER BY varName";
	
	$result = dbQuery($selectQuery);
	
	if ($result) {
		if (dbNumRows($result) > 0) {
			
			while ($row = dbFetchObject($result)) {
				
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				
				$varList .= "<tr class=$bgcolorClass><td valign=top>$row->varName</td>								
							<td>".htmlspecialchars(substr($row->varText,0,100))."</td>
							<td><a href='JavaScript:void(window.open(\"addVar.php?iMenuId=$iMenuId&id=".$row->id."&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"AddContent\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
							&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a></td></tr>
							</td></tr>";
			}
		} else {
			$sMessage = "No Records Exist...";
		}
		dbFreeResult($result);
		
	} else {
		echo dbError();
	}
	
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addVar.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
	
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
	<td width=25% class=header>Variable Name</td>	
	<td  width=60% class=header>Variable Content</td>
	<td></td>
</tr>
<?php echo $varList;?>
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

