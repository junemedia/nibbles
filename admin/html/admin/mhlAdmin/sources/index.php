<?php 

/***********

Script to Manage Site Contents of MyHealthyLiving site

*************/

include("../../../includes/paths.php");

$sPageTitle = "MyHealthyLiving Source Codes";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	
	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);		
	
		
	if ($delete) {
		//echo "Dfdf".$id;
		if ($ssId) {
			$deleteQuery = "DELETE FROM sub_source_codes
						WHERE       sscID = '$ssId'";

			// start of track users' activity in nibbles 
			$sTrackingUser = $_SERVER['PHP_AUTH_USER']; 
	
			$sLogAddQuery = "INSERT INTO nibbles.trackNibbleUse(userName, pageName, dateTimeLogged, action) 
			  VALUES('$sTrackingUser', '$PHP_SELF', now(), \"Delete: $deleteQuery\")"; 
			$rLogResult = dbQuery($sLogAddQuery); 
			echo  dbError(); 
			// end of track users' activity in nibbles		
			
			
			$deleteResult = dbQuery($deleteQuery);
			//echo $deleteQuery.mysql_error();
			
		} else if ($id) {
			$deleteQuery = "DELETE FROM source_codes
						WHERE       srcID = '$id'";
			$deleteResult = dbQuery($deleteQuery);
		}		
	}
	// Set Default order column
	if (!($orderColumn)) {
		$orderColumn = "srcCode";
		$srcCodeOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($orderColumn) {
		case "srcName":
		$currOrder = $srcNameOrder;
		$srcNameOrder = ($srcNameOrder != "DESC" ? "DESC" : "ASC");
		break;
		case "srcURL":
		$currOrder = $srcUrlOrder;
		$srcUrlOrder = ($srcUrlOrder != "DESC" ? "DESC" : "ASC");
		break;
		default:
		$currOrder = $srcCodeOrder;
		$srcCodeOrder = ($srcCodeOrder != "DESC" ? "DESC" : "ASC");
	}
	
	// Query to get the list of BDPartners
	$selectQuery = "SELECT *
					FROM   source_codes
					ORDER BY $orderColumn $currOrder";
	
	$result = dbQuery($selectQuery);
	
	if ($result) {
		if (dbNumRows($result) > 0) {
			
			while ($row = dbFetchObject($result)) {
				
				$srcID = $row->srcID;
				
				if ($bgcolorClass == "ODD") {
					$bgcolorClass = "EVEN";
				} else {
					$bgcolorClass = "ODD";
				}
				
				$sourceList .= "<tr class=$bgcolorClass><td class=header>$row->srcCode</td>
						<td>$row->srcName</td><td>$row->srcURL</td>
						<td><a href='JavaScript:void(window.open(\"addSource.php?iMenuId=$iMenuId&id=".$row->srcID."&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"AddContent\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
						&nbsp; <a href='JavaScript:confirmDelete(this,".$row->srcID.");' >Delete</a>
						</td></tr>";
				// $subsource query
				$selectQuery2 = "SELECT *
								 FROM   sub_source_codes
								 WHERE  sscSrcID = '$srcID'";
				$selectResult2 = dbQuery($selectQuery2);
				while ($selectRow2 = dbFetchObject($selectResult2)) {
					if ($bgcolorClass == "ODD") {
						$bgcolorClass = "EVEN";
					} else {
						$bgcolorClass = "ODD";
					}
					
					$sourceList .= "<tr class=$bgcolorClass><td> &nbsp; &nbsp; $selectRow2->sscCode</td>
						<td>$selectRow2->sscName</td><td>$selectRow2->sscURL</td>
						<td><a href='JavaScript:void(window.open(\"addSource.php?iMenuId=$iMenuId&ssId=".$selectRow2->sscID."&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"AddContent\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
						&nbsp; <a href='JavaScript:confirmDelete2(this,".$selectRow2->sscID.");' >Delete</a></td></tr>";
				}
			}
		} else {
			$sMessage = "No Records Exist...";
		}
		dbFreeResult($result);
		
	} else {
		echo dbError();
	}
	
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addSource.php?menuId=$menuId&menuFolder=$menuFolder&parentMenuId=$parentMenuId&parentMenuFolder=$parentMenuFolder\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
	
	
	
	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iParentMenuId value='$iParentMenuId'>
				<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>
			<input type=hidden name=id value='$id'>
			<input type=hidden name=ssId value='$ssId'>";
	
	$reportsLink = "<a href='trafficSourceReport.php?iMenuId=$iMenuIdiParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder'>Traffic Source Report</a>
					&nbsp; &nbsp; <a href='orderSourceReport.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder'>Order Source Report</a>
					&nbsp; &nbsp; <a href='testOrderSrcReport.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder'>Test Order Source Report</a>";
	
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
				
				function confirmDelete2(form1,id)
				{
					if(confirm('Are you sure to delete this record ?'))
					{							
						document.form1.elements['delete'].value='Delete';
						document.form1.elements['ssId'].value=id;
						document.form1.submit();								
					}
				}		
</script>
<form name=form1 action='<?php echo $PHP_SELF;?>'>

<input type=hidden name=delete>

<?php echo $hidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><td colspan=4 align=left><?php echo $addButton;?> &nbsp; * Source Codes with alignment are sub source codes. &nbsp; &nbsp;  <?php echo $reportsLink;?></td></tr>
<tr>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=srcCode&srcCodeOrder=<?php echo $srcCodeOrder;?>' class=header>Source Code</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=srcName&srcNameOrder=<?php echo $srcNameOrder;?>' class=header>Source Name</a></td>
	<td align=left><a href='<?php echo $sortLink;?>&orderColumn=srcURL&srcUrlOrder=<?php echo $srcUrlOrder;?>' class=header>Source URL</a></td>	
	<td>&nbsp; </td>
</tr>
<?php echo $sourceList;?>
<tr><td colspan=4 align=left><?php echo $addButton;?> &nbsp; * Source Codes with alignment are sub source codes. &nbsp; &nbsp;  <?php echo $reportsLink;?></td></tr>
</table>

</form>


<?php
} else {
	echo "You are not authorized to access this page...";
}
?>	

