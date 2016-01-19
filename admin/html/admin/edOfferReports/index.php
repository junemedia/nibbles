<?php

/*******

Script to Display Offer Reports Menu
*********/

include("../../includes/paths.php");

$sPageTitle = "Editorial Offer Reports Menu";

session_start();

$menuFolder = "edOfferReports";

// Check user permission to access this page

if (hasAccessRight($iMenuId) || isAdmin()) {


	// get offer menuId and folder info					
	$menuQuery = "SELECT *
				  FROM   menu
				  WHERE  category = 'Editorial'
				  AND	 menuItem = 'Offers Management'";
	$menuResult = mysql_query($menuQuery);
	while ($menuRow = mysql_fetch_object($menuResult)) {
		
		$offerMenuId = $menuRow->id;
		$offerMenuFolder = substr($menuRow->menuLink,0,strpos($menuRow->menuLink, '/'));		
	}
	
	if ($offerMenuId && $offerMenuFolder) {
		$offerListLink = "<a href = '../$offerMenuFolder/index.php?iMenuId=$offerMenuId&reportMenuId=$iMenuId&reportMenuFolder=$menuFolder'>Offers List</a>";
		$offerRedirectsReportLink = "<a href = '../$offerMenuFolder/report.php?iMenuId=$offerMenuId&reportMenuId=$iMenuId&reportMenuFolder=$menuFolder'>Offer Redirects Report</a>";
		$offerPixelsReportLink = "<a href = '../$offerMenuFolder/pixelReport.php?iMenuId=$offerMenuId&reportMenuId=$iMenuId&reportMenuFolder=$menuFolder'>Offer Pixels Report</a>";
	}
	
	// get nl menuId and folder info
	$menuQuery = "SELECT *
				  FROM   menu
				  WHERE  menuItem = 'Newsletters Pixels Management'";
	$menuResult = mysql_query($menuQuery);
	while ($menuRow = mysql_fetch_object($menuResult)) {
		$nlMenuId = $menuRow->id;
		$nlMenuFolder = substr($menuRow->menuLink,0,strpos($menuRow->menuLink, '/'));		
	}
	if ($nlMenuId && $nlMenuFolder) {
		$nlPixelsReportLink = "<a href = '../$nlMenuFolder/report.php?iMenuId=$nlMenuId&reportMenuId=$iMenuId&reportMenuFolder=$menuFolder'>Newsletters Pixels Report</a>";
	}

	
	$setPopUpDelayLink = "<a href = 'setPopUpDelay.php?iMenuId=$iMenuId'>Set PopUp Delay</a>";
$updateIndexTextLink = "<a href = 'updateIndexText.php?iMenuId=$iMenuId'>Manage Index.php Text</a>";
$editIndexHeadlineLink = "<a href = 'editIndexHeadline.php?iMenuId=$iMenuId'>Edit Index.php Headline<a/>";
$editPage4PopupTextLink = "<a href = 'editPopUpText.php?iMenuId=$iMenuId'>Edit PopUp Text</a>";
$reportLink = "<a href='report.php?iMenuId=$iMenuId'>You Won Reporting</a>";

	include("../../includes/adminHeader.php");	
	
?>
<table align="center" width="550">
<tr>
<td colspan="2" align="center" bgcolor = "c1c1c1"><b>Offer Reports</b></td>
</tr>
<tr><td valign="top" bgcolor = "eeeeee" width="50%" height=30>
		<ul>
		<li><?php echo $offerListLink;?></li>
		</ul>
	</td>
	<td valign="top" bgcolor = "eeeeee" width="50%">
		<ul>
		<li><?php echo $offerRedirectsReportLink;?></li>
		</ul>				
	</td>
</tr>
<tr><td valign="top" bgcolor = "eeeeee" width="50%" height=30>
		<ul>
		<li><?php echo $offerPixelsReportLink;?></li>
		</ul>
	</td>
	<td valign="top" bgcolor = "eeeeee" width="50%">		
		<ul>
		<li><?php echo $nlPixelsReportLink;?></li>
		</ul>
	</td>
</tr>

</table>

<?php
	include("../../includes/adminFooter.php");
} else {
	echo "You are not authorized to access this page...";
}
?>