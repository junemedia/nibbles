<?php

/*******

Script to Display You Won Admin Menu

*********/

include("../../includes/paths.php");

$sPageTitle = "Handcrafters Village Admin Menu";

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {
	
$sParentMenuFolder = "hcvAdmin";

	
	$menuQuery = "SELECT *
				  FROM menu
				  WHERE parentMenu = '$iMenuId'
				  ORDER BY	category, menuItem";
	$menuResult = dbQuery($menuQuery);
	$num = 0;
	while ($menuRow = dbFetchObject($menuResult)) {
		if ($menuRow->category != $oldCategory || $oldCategory == '') {
			if($num%2 != 0)
			$menuList .= "<td bgcolor = \"eeeeee\">&nbsp;</tD>";
			$menuList .= "</tr><tr>
				<td colspan=\"2\" align=\"center\" bgcolor = \"c1c1c1\"><b>$menuRow->category</b></td>
				</tr><tr>";
			$num = 0;
		}
		
		// interpret $SERVER_NAME variable, if it's there in menuLink
		if (strstr($menuRow->menuLink,"\$SERVER_NAME"))
		{
			$menuLink = ereg_replace("\\\$SERVER_NAME",$SERVER_NAME,$menuRow->menuLink);			
		} else {
			$menuLink = $menuRow->menuLink;
		}
			
		$menuList .= "<td valign=\"top\" bgcolor = \"eeeeee\" width=\"50%\">
					<ul>
					<li><a href=\"". $menuLink."?iMenuId=$menuRow->id&iParentMenuId=$menuRow->parentMenu&sParentMenuFolder=$sParentMenuFolder\"><b>$menuRow->menuItem</b></a></li>
					</ul></td>";		
		
		$num++;
		if ($num%2 == 0) {
			$menuList .= "</tr>";
		}
		
		$oldCategory = $menuRow->category;
		
	}
	// In last row, Fill the remaining empty TD with grey color
	if ( $num%2 != 0)
		$menuList .= "<td bgcolor = \"eeeeee\">&nbsp;</tD>";
	$menuList .= "</tr>";
		
	include("$sGblIncludePath/adminHeader.php");	
?>

<!-- content starts here -->
<table align="center" width="550">
<?php echo $menuList; ?>
</table>
<!-- content ends here -->

<?php
	include("$sGblIncludePath/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>