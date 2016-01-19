<?php 

/***********

Script to Manage Site Contents of MyHealthyLiving site

*************/

include("../../../includes/paths.php");


$sPageTitle = "FunPage - Fun Pages";

session_start();

$imagePath = "$sGblFpWebRoot/images";
$soundPath = "$sGblFpWebRoot/sounds";


if (hasAccessRight($iMenuId) || isAdmin()) {
		
	
	if ($delete) {
		// get emage file and sound file names
		$tempQuery = "SELECT *
				  FROM   funPages
				  WHERE  id = '$id'";
		$tempResult = mysql_query($tempQuery);
		while ($tempRow = mysql_fetch_object($tempResult)) {
			$imageFile = $tempRow->image;
			$soundFile = $tempRow->sound;
		}
		
		$deleteQuery = "DELETE FROM funPages
						WHERE       id = '$id'";
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		
		$deleteResult = mysql_query($deleteQuery);
		if ($deleteResult) {
			@unlink("$imagePath/$imageFile");
			@unlink("$soundPath/$soundFile");
		}
		
	}
	// Set Default order column
	if (!($orderColumn)) {
		$orderColumn = "title";
		$titleOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($orderColumn) {
		case "image":
		$currOrder = $imageOrder;
		$imageOrder = ($imageOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "sound":
		$currOrder = $soundOrder;
		$soundOrder = ($soundOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "featured":
		$currOrder = $featuredOrder;
		$featuredOrder = ($featuredOrder != "DESC" ? "DESC" : "ASC");
		break;
		default:
		$currOrder = $titleOrder;
		$titleOrder = ($titleOrder != "DESC" ? "DESC" : "ASC");
	}
	
	// Query to get the list of BDPartners
	$selectQuery = "SELECT *
					FROM   funPages					
					ORDER BY ";
	
	
	if ($orderColumn == "image" || $orderColumn == "sound") {
		$selectQuery .= " substring($orderColumn,1,9) $currOrder, substring($orderColumn,10)+0 $currOrder";
	} else {
		$selectQuery .= " $orderColumn $currOrder";
	}
	
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
				
				$fpList .= "<tr class=$bgcolorClass>
							<td>$row->title</td><td>$row->image</td>
							<td>$row->sound</td><td>$row->featured</td>
							<td><a href='JavaScript:void(window.open(\"addFP.php?iMenuId=$iMenuId&id=".$row->id."&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"AddContent\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
							&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a></td></tr>
							</td>";
			}
		} else {
			$message = "No Records Exist...";
		}
		mysql_free_result($result);
		
	} else {
		echo mysql_error();
	}
	
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addFP.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
	
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
<td align=left><a href='<?php echo $sortLink;?>&orderColumn=title&titleOrder=<?php echo $titleOrder;?>' class=header>Title</a></td>
	<td align=left><a href='{<?php echo $sortLink;?>&orderColumn=image&imageOrder=<?php echo $imageOrder;?>' class=header>Image</a></td>
	<td align=left><a href='{<?php echo $sortLink;?>&orderColumn=sound&soundOrder=<?php echo $soundOrder;?>' class=header>Sound</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=featured&featuredOrder=<?php echo $featuredOrder;?>' class=header>Featured</a></td>
	<td>&nbsp; </td>
</tr>
<?php echo $fpList;?>
<tr><th colspan=7 align=left><?php echo $addButton;?></th></tr>
</table>

</form>


<?php
	include("../../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>	

