<?php 

/***********

Script to Manage Footer of Handcrafters Village site

*************/

include("../../../includes/paths.php");

$sPageTitle = "Handcrafters Village Footer Management";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {		
	
	// SELECT HCV DATABASE
	dbSelect($sGblHcvDBName);	
	
	if ($delete) {
		
		// if record deleted...
		// Manage Offers of this category
		//or don't allow to delete if offer exists in this category
		
		$deleteQuery = "DELETE FROM footerLinks
			   			WHERE id = '$id'";
		
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		
		$result = dbQuery($deleteQuery);
		if (!($result)) {
			echo dbError();
		}
		//reset $id to null
		$id = '';
	}
	// set default order by column
	if (!($orderColumn)) {
		$orderColumn = "linkText";
		$linkTextOrder = "ASC";
	}
	
	switch ($orderColumn) {
				
		case "url" :
		$currOrder = $urlOrder;
		$urlOrder = ($urlOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "sortOrder" :
		$currOrder = $sortOrderOrder;
		$sortOrderOrder = ($sortOrderOrder != "DESC" ? "DESC" : "ASC");
		break;		
		default:
		$currOrder = $linkTextOrder;
		$linkTextOrder = ($linkTextOrder != "DESC" ? "DESC" : "ASC");
	}	
	
	// Query to get the list of Categories
	$selectQuery = "SELECT *
					FROM footerLinks
	 				ORDER BY $orderColumn $currOrder";
	
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Display Report: $selectQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
	
	
	 $result = dbQuery($selectQuery);
	
	if ($result) {
		$numRecords = dbNumRows($result);
		if ($numRecords > 0) {
			
			while ($row = dbFetchObject($result)) {
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				
				$footerList .= "<tr class=$bgcolorClass>
					<td>$row->linkText</td>
					<td>$row->url</td>					
					<td>$row->sortOrder</td>					
					<td><a href='JavaScript:void(window.open(\"addLink.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&id=".$row->id."&sParentMenuFolder=$sParentMenuFolder\", \"AddCategory\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a>
					</td></tr>";
			}
		} else {
			$sMessage = "No records exist...";
		}
		dbFreeResult($result);
		
	} else {
		echo dbError();
	}
		
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addLink.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"AddCategory\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";	
	
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
<tr><Td><?php echo $addButton;?></td></tr>
<tr>	
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=linkText&linkTextOrder=<?php echo $linkTextOrder;?>" class=header>Link Text</a></td>
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=url&urlOrder=<?php echo $urlOrder;?>" class=header>URL</a></td>
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=sortOrder&sortOrderOrder=<?php echo $sortOrderOrder;?>" class=header>Sort Order</a></td>
	<th>&nbsp; </th>
</tr>
<?php echo $footerList;?>
<tr><Td><?php echo $addButton;?></td></tr>
</table>

</form>
<?php

include("$sGblIncludePath/adminFooter.php");
				
} else {
	echo "You are not authorized to access this page...";
}				
?>	

