<?php

/*********

Script to Display List/Delete MyFree Footer Menu

*********/

include("../../includes/paths.php");
include("$sGblLibsPath/stringFunctions.php");

$sPageTitle = "MyFree Footer Management";

session_start();

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {
	
	if ($delete) {
		
		// if record deleted...
		// Manage Offers of this category
		//or don't allow to delete if offer exists in this category
		
		$deleteQuery = "DELETE FROM edFooterLinks
			   			WHERE id = '$id'"; 
		
		// start of track users' activity in nibbles
		$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
		$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
				  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")";
		$rResult = dbQuery($sAddQuery);
		echo  dbError();
		// end of track users' activity in nibbles
		
		$result = mysql_query($deleteQuery);
		if (!($result)) {
			echo mysql_error();
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
		
		case "categoryId" :
		$currOrder = $categoryIdOrder;
		$categoryIdOrder = ($categoryIdOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "url" :
		$currOrder = $urlOrder;
		$urlOrder = ($urlOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "frontPageDisplay" :
		$currOrder = $frontPageDisplayOrder;
		$frontPageDisplayOrder = ($frontPageDisplayOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "offerPageDisplay" :
		$currOrder = $offerPageDisplayOrder;
		$offerPageDisplayOrder = ($offerPageDisplayOrder != "DESC" ? "DESC" : "ASC");
		break;		
		default:
		$currOrder = $linkTextOrder;
		$linkTextOrder = ($linkTextOrder != "DESC" ? "DESC" : "ASC");
	}	
	
	// Query to get the list of Categories
	$selectQuery = "SELECT *
					FROM edFooterLinks
	 				ORDER BY ";
	
	 if ($orderColumn == "frontPageDisplay" || $orderColumn == "offerPageDisplay") {
	 	$selectQuery .= " 0x41 + ";
	 }
	 $selectQuery .= " $orderColumn $currOrder";
	
	 
	// start of track users' activity in nibbles
	$sTrackingUser = $_SERVER['PHP_AUTH_USER'];
	$sAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action)
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"View Report: $selectQuery\")";
	$rResult = dbQuery($sAddQuery);
	echo  dbError();
	// end of track users' activity in nibbles
	 
	 
	 $result = mysql_query($selectQuery);
	//echo $selectQuery;
	if ($result) {
		$numRecords = mysql_num_rows($result);
		if ($numRecords > 0) {
			
			while ($row = mysql_fetch_object($result)) {
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				if($row->categoryId !='')
					$isOfferURL = "Y";
				else 
					$isOfferURL = "N";
					
				$footerList .= "<tr class=$bgcolorClass>
					<td>$row->linkText</td>
					<td>$row->url</td>
					<td>$isOfferURL</td>					
					<td>$row->frontPageDisplay</td>
					<td>$row->offerPageDisplay</td>
					<td><a href='JavaScript:void(window.open(\"addLink.php?iMenuId=$iMenuId&id=".$row->id."\", \"AddCategory\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
					&nbsp; <a href='JavaScript:confirmDelete(this,".$row->id.");' >Delete</a>
					</td></tr>";
			}
		} else {
			$sMessage = "No records exist...";
		}
		mysql_free_result($result);
		
	} else {
		echo mysql_error();
	}
		
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addLink.php?iMenuId=$iMenuId\", \"AddCategory\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";	
	
	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
			<input type=hidden name=id value='$id'>";
	
	$sortLink = $PHP_SELF."?iMenuId=$iMenuId";
	
	$sortFooterMenuLink = "<a href='JavaScript:void(window.open(\"sortFooterMenu.php?iMenuId=$iMenuId\",\"\",\"scrollbars=yes, resizable=yes\"));'>Sort Footer Menu</a>";
	
	
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
<tr><Td><?php echo $addButton;?></td><td colspan=5><?php echo $sortFooterMenuLink;?></td></tr>
<tr>	
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=linkText&linkTextOrder=<?php echo $linkTextOrder;?>" class=header>Link Text</a></td>
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=url&urlOrder=<?php echo $urlOrder;?>" class=header>URL</a></td>
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=categoryId&categoryIdOrder=<?php echo $categoryIdOrder;?>" class=header>Is Offer Page URL</a></td>
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=frontPageDisplay&frontPageDisplayOrder=<?php echo $frontPageDisplayOrder;?>" class=header>Front Page Display</td>
	<TD class=header><a href="<?php echo $sortLink;?>&orderColumn=offerPageDisplay&offerPageDisplayOrder=<?php echo $offerPageDisplayOrder;?>" class=header>Offer Page Display</td>
	<th>&nbsp; </th>
</tr>
<?php echo $footerList;?>
<tr><Td><?php echo $addButton;?></td><td colspan=5><?php echo $sortFooterMenuLink;?></td></tr>
</table>
</form>

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>