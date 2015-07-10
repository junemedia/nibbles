<?php

include("../../includes/paths.php");

session_start();

if (hasAccessRight($iMenuId) || isAdmin()) {

$sMainMenuQuery = "SELECT *
				  FROM menu
				  WHERE id = '$iMenuId'";
$rMainMenuResult = dbQuery($sMainMenuQuery);
while ($oMainMenuRow = dbFetchObject($rMainMenuResult)) {
	$sPageTitle = $oMainMenuRow->menuItem;	
}	


//if (session_is_registered("sSesUserId"))
//{
	
		
	$sMenuQuery = "SELECT *
				  FROM menu
				  WHERE parentMenu = '$iMenuId'
				  AND   displayMenu = 'Y'
				  ORDER BY	category, menuItem";
	$rMenuResult = dbQuery($sMenuQuery);
	$iNum = 0;
	while ($oMenuRow = dbFetchObject($rMenuResult)) {
		if ($oMenuRow->category != $sOldCategory || $sOldCategory == '') {
			if ($iNum%2 != 0)
			$sMenuList .= "<td bgcolor = \"eeeeee\">&nbsp;</tD>";
			$sMenuList .= "</tr><tr>
				<td colspan=\"2\" align=\"center\" bgcolor = \"c1c1c1\"><b>&nbsp;</b></td>
				</tr><tr>";
			$iNum = 0;
		}
		
		// interpret $SERVER_NAME variable, if it's there in menuLink
		if (strstr($oMenuRow->menuLink,"\$SERVER_NAME"))
		{
			$sMenuLink = ereg_replace("\\\$SERVER_NAME",$SERVER_NAME,$oMenuRow->menuLink);			
		} else {
			$sMenuLink = $oMenuRow->menuLink;
		}
			
		$sMenuList .= "<td valign=\"top\" bgcolor = \"eeeeee\" width=\"50%\">
					<ul>
					<li><a href=\"". $sMenuLink."?iMenuId=$oMenuRow->id&iParentMenuId=$iMenuId\">$oMenuRow->menuItem</a> &nbsp;";
				if ($oMenuRow->description != '') {
						$sMenuList .= "<A href='JavaScript:void(window.open(\"menuDesc.php?iMenuId=$oMenuRow->id\", \"\", \"height=150, width=250, scrollbars=auto, resizable=yes, status=no\"));' class=header>?</a>";
				}
				$sMenuList .= "</li>
					</ul></td>";
		
		$iNum++;
		if ($iNum%2 == 0) {
			$sMenuList .= "</tr>";
		}
		
		$sOldCategory = $oMenuRow->category;
		
	}
	// In last row, Fill the remaining empty TD with grey color
	if ( $iNum%2 != 0) {
		$sMenuList .= "<td bgcolor = \"eeeeee\">&nbsp;</tD>";
	}
	$sMenuList .= "</tr>";

	include("../../includes/adminHeader.php");	
?>

<!-- content starts here -->

<table align="center" width="600">
<?php echo $sMenuList;?>
</table>

<!-- content ends here -->

<?php 
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}

?>