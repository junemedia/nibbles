<?php 

/***********

Script to Manage Site Contents of MyHealthyLiving site

*************/

include("../../../includes/paths.php");


$sPageTitle = "Handcrafters Village Projects Management";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	// SELECT HCV DATABASE
	dbSelect($sGblHcvDBName);	
	
	
	if ($delete) {
		$deleteQuery = "DELETE FROM craftProjects
						WHERE       id = '$id'";
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		
		$deleteResult = dbQuery($deleteQuery);
		
		echo dbError();
		
		// delete the image here
		
		if ($deleteResult) {
			$sDeleteQuery2 = "DELETE FROM categoryMap
							  WHERE  projectId = '$id'";

			// start of track users' activity in nibbles
			$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
			$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
					  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $sDeleteQuery2\")";
			$rResult = dbQuery($sAddQuery);
			echo  dbError();
			// end of track users' activity in nibbles
		
		
			$rDeleteResult2 = dbQuery($sDeleteQuery2);
			echo dbError();
		}
	}

	// Set Default order column
	if (!($orderColumn)) {
		$orderColumn = "title";
		$titleOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($orderColumn) {
		case "description":
		$currOrder = $descriptionOrder;
		$descriptionOrder = ($descriptionOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "image":
		$currOrder = $imageOrder;
		$imageOrder = ($imageOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "isFeatired":
		$currOrder = $isFeaturedOrder;
		$isFeaturedOrder = ($isFeaturedOrder != "DESC" ? "DESC" : "ASC");
		break;
		default:
		$currOrder = $titleOrder;
		$titleOrder = ($titleOrder != "DESC" ? "DESC" : "ASC");
	}
		
	// Query to get the list of BDPartners
	$selectQuery = "SELECT *
					FROM   craftProjects
					ORDER BY $orderColumn $currOrder";
	
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Report: $selectQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
	
	$result = dbQuery($selectQuery);
	
	if ($result) {
		if (dbNumRows($result) > 0) {
			
			while ($row = dbFetchObject($result)) {
				
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
												
				$projectList .= "<tr class=$bgcolorClass><td>$row->title</td>								
								<td>$row->description</td><td>$row->image</td>
								<td>$row->isFeatured</td>
								<td><a href='JavaScript:void(window.open(\"addProject.php?iMenuId=$iMenuId&id=".$row->id."&iParentMenuId=$iParentMenuId\", \"AddContent\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
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
	
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addProject.php?iMenuId=$iMenuId&iParentMenuId = $iParentMenuId\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
	
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
	<td class=header><a href='<?php echo $sortLink;?>&orderColumn=title&titleOrder=<?php echo $titleOrder;?>' class=header>Title</a></td>	
	<td class=header><a href='<?php echo $sortLink;?>&orderColumn=description&descriptionOrder=<?php echo $descriptionOrder;?>' class=header>Description</a></td>
	<td class=header><a href='<?php echo $sortLink;?>&orderColumn=image&imageOrder=<?php echo $imageOrder;?>' class=header>Image</a></td>
	<td class=header><a href='<?php echo $sortLink;?>&orderColumn=image&imageOrder=<?php echo $isFeaturedOrder;?>' class=header>Is Featured</a></td>
	<td>&nbsp; </td>
</tr>
<?php echo $projectList;?>
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

