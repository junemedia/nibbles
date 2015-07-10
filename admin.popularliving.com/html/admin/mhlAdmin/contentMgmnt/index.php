<?php 

/***********

Script to Manage Site Contents of MyHealthyLiving site

*************/

include("../../../includes/paths.php");

$sPageTitle = "MyHealthyLiving Web Site Content";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
		
	
	// SELECT HCV DATABASE
	dbSelect($sGblMhlDBName);	
	
	// Set Default order column
	if (!($orderColumn)) {
		$orderColumn = "varName";
		$companyNameOrder = "ASC";
	}
	
	// set current order(ASC or DESC) and order (ASC or DESC) to be used in particular column link
	switch ($orderColumn) {
		default:
		$currOrder = $varNameOrder;
		$varNameOrder = ($varNameOrder != "DESC" ? "DESC" : "ASC");
	}
	
	// Query to get the list of BDPartners
	$selectQuery = "SELECT *
					FROM   siteContentVars
					ORDER BY ".$orderColumn." $currOrder";
	
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
				
				$varsList .= "<tr class=$bgcolorClass><td>$row->varName</td><td>
							<a href='JavaScript:void(window.open(\"addContent.php?iMenuId=$iMenuId&id=".$row->id."&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"AddContent\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>Edit</a>
							</td></tr>";
			}
		} else {
			$sMessage = "No Records Exist...";
		}
		dbFreeResult($result);
		
	} else {
		echo dbError();
	}
	
	
	$addButton = "<input type=button name=add value=Add onClick='JavaScript:void(window.open(\"addContent.php?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder\", \"\", \"height=450, width=600, scrollbars=yes, resizable=yes, status=yes\"));'>";
		
	
	// Hidden variable to be passed with form submit
	$hidden = "<input type=hidden name=iMenuId value='$iMenuId'>
				<input type=hidden name=iParentMenuId value='$iParentMenuId'>
				<input type=hidden name=sParentMenuFolder value='$sParentMenuFolder'>
				<input type=hidden name=id value='$id'>";
	
	$sortLink = $PHP_SELF."?iMenuId=$iMenuId&iParentMenuId=$iParentMenuId&sParentMenuFolder=$sParentMenuFolder";
	
	include("$sGblIncludePath/adminHeader.php");

?>


<form name=form1 action='<?php echo $PHP_SELF;?>'>

<?php echo $hidden;?>

<table cellpadding=5 cellspacing=0 bgcolor=c9c9c9 width=95% align=center>

<tr><th colspan=3 align=left><?php echo $addButton;?></th></tr>
<tr>
	<td align=left class=header>Site Content Variable Name</td>	
	
	<td>&nbsp; </td>
</tr>
<?php echo $varsList;?>
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

